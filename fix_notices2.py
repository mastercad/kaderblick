#!/usr/bin/env python3
"""
Fix PHPUnit notices: bare ->method() calls in createMock() test files.
 
Strategy per mock property/variable:
 - If the mock object has NO strict expectations (once/exactly/never/atLeast/atMost)
   in the ENTIRE file → convert createMock → createStub (and strip ->expects($this->any()))
 - If it HAS strict expectations → add ->expects($this->any()) to every bare ->method() call
"""

import re
from pathlib import Path

BASE = Path("/media/Austausch/Projekte/fussballverein/webapp/api")
TEST_BASE = BASE / "tests"

STRICT_RE = re.compile(
    r'->expects\(\s*\$this->(once|exactly|atLeastOnce|atMost|never)\s*\('
)
ANY_EXPECTS_RE = re.compile(
    r'->expects\(\s*\$this->any\(\)\s*\)'
)


def get_mock_assignments(content):
    """Find all 'NAME = $this->createMock(CLASS)' assignments.
    Returns dict: var_name -> class (var_name may be like 'this->foo' or 'bar' for locals)
    """
    # Property in setUp: $this->prop = $this->createMock(Cls::class)
    prop_re = re.compile(
        r'\$this->(\w+)\s*=\s*\$this->createMock\(([^)]+)\)'
    )
    # Local: $varname = $this->createMock(Cls::class)
    local_re = re.compile(
        r'\$(\w+)\s*=\s*\$this->createMock\(([^)]+)\)'
    )
    
    mocks = {}  # name -> class
    for m in prop_re.finditer(content):
        mocks[f'this->{m.group(1)}'] = m.group(2).strip()
    for m in local_re.finditer(content):
        name = m.group(1)
        if name != 'this':  # skip the self-reference
            mocks[name] = m.group(2).strip()
    return mocks


def has_strict_expects(content, mock_name):
    """Check if mock_name has any strict expectations in the file."""
    # Look for $this->propName->expects($this->once()/exactly()/etc
    # or $localVar->expects(...)
    if mock_name.startswith('this->'):
        prop = mock_name[6:]  # strip 'this->'
        pattern = re.compile(
            r'\$this->' + re.escape(prop) + r'->expects\(\s*\$this->(once|exactly|atLeastOnce|atMost|never)\s*\('
        )
    else:
        pattern = re.compile(
            r'\$' + re.escape(mock_name) + r'->expects\(\s*\$this->(once|exactly|atLeastOnce|atMost|never)\s*\('
        )
    return bool(pattern.search(content))


def convert_to_stub(content, mock_name, cls_name):
    """Convert createMock to createStub for a given mock name."""
    if mock_name.startswith('this->'):
        prop = mock_name[6:]
        # Replace $this->prop = $this->createMock(...) 
        old = f'$this->{prop} = $this->createMock({cls_name})'
        new = f'$this->{prop} = $this->createStub({cls_name})'
        content = content.replace(old, new)
        # Strip ->expects($this->any()) from calls on this prop
        content = re.sub(
            r'(\$this->' + re.escape(prop) + r')' +
            r'->expects\(\s*\$this->any\(\)\s*\)(->method\()',
            r'\1\2',
            content
        )
    else:
        # Local variable
        old = f'${mock_name} = $this->createMock({cls_name})'
        new = f'${mock_name} = $this->createStub({cls_name})'
        content = content.replace(old, new)
        # Strip ->expects($this->any()) 
        content = re.sub(
            r'(\$' + re.escape(mock_name) + r')' +
            r'->expects\(\s*\$this->any\(\)\s*\)(->method\()',
            r'\1\2',
            content
        )
    return content


def add_any_expects(content, mock_name):
    """Add ->expects($this->any()) before bare ->method( calls for this mock."""
    if mock_name.startswith('this->'):
        prop = mock_name[6:]
        # On same line: $this->prop->method(...) without ->expects(
        def replace_bare(m):
            before = m.group(1)
            after = m.group(2)
            return before + '->expects($this->any())' + after
        
        pattern = re.compile(
            r'(\$this->' + re.escape(prop) + r')' +
            r'(->method\()'
        )
        
        lines = content.split('\n')
        new_lines = []
        for i, line in enumerate(lines):
            # Check if this line has $this->prop->method( without ->expects(
            if (f'$this->{prop}' in line and '->method(' in line and
                    '->expects(' not in line):
                prev = lines[i-1].rstrip() if i > 0 else ''
                if not prev.endswith('->'):
                    line = line.replace(
                        f'$this->{prop}->method(',
                        f'$this->{prop}->expects($this->any())->method('
                    )
            new_lines.append(line)
        content = '\n'.join(new_lines)
    else:
        lines = content.split('\n')
        new_lines = []
        for i, line in enumerate(lines):
            if (f'${mock_name}' in line and '->method(' in line and
                    '->expects(' not in line):
                prev = lines[i-1].rstrip() if i > 0 else ''
                if not prev.endswith('->'):
                    line = line.replace(
                        f'${mock_name}->method(',
                        f'${mock_name}->expects($this->any())->method('
                    )
            new_lines.append(line)
        content = '\n'.join(new_lines)
    return content


def fix_imports(content, filepath):
    """Update MockObject/Stub imports based on actual usage."""
    has_mock = 'createMock(' in content
    has_stub = 'createStub(' in content
    has_mock_import = 'use PHPUnit\\Framework\\MockObject\\MockObject;' in content
    has_stub_import = 'use PHPUnit\\Framework\\MockObject\\Stub;' in content
    
    if not has_mock and has_stub and has_mock_import and not has_stub_import:
        content = content.replace(
            'use PHPUnit\\Framework\\MockObject\\MockObject;',
            'use PHPUnit\\Framework\\MockObject\\Stub;'
        )
        # Also fix property type hints
        content = content.replace('&MockObject', '&Stub')
        content = content.replace('MockObject&', 'Stub&')
    elif has_stub and not has_stub_import and 'Stub' not in content:
        pass  # No change needed
    
    return content


def process_file(php_file):
    content = php_file.read_text()
    
    if 'createMock(' not in content:
        return False, 0, 0
    
    # Check if any bare method calls exist
    lines = content.split('\n')
    bare_lines = []
    for i, line in enumerate(lines):
        if '->method(' in line and '->expects(' not in line:
            prev = lines[i-1].rstrip() if i > 0 else ''
            if not prev.endswith('->'):
                bare_lines.append(i+1)
    
    if not bare_lines:
        return False, 0, 0
    
    original = content
    mocks = get_mock_assignments(content)
    
    converted_to_stub = []
    added_expects = []
    
    for mock_name, cls_name in mocks.items():
        is_strict = has_strict_expects(content, mock_name)
        if is_strict:
            new_content = add_any_expects(content, mock_name)
            if new_content != content:
                added_expects.append(mock_name)
                content = new_content
        else:
            new_content = convert_to_stub(content, mock_name, cls_name)
            if new_content != content:
                converted_to_stub.append(mock_name)
                content = new_content
    
    if content != original:
        content = fix_imports(content, php_file)
        php_file.write_text(content)
        return True, len(converted_to_stub), len(added_expects)
    
    return False, 0, 0


print("Fixing remaining bare ->method() calls...\n")
total_changed = 0
total_stubs = 0
total_expects = 0

for php_file in sorted(TEST_BASE.rglob("*.php")):
    rel = str(php_file.relative_to(BASE))
    changed, n_stubs, n_expects = process_file(php_file)
    if changed:
        total_changed += 1
        total_stubs += n_stubs
        total_expects += n_expects
        print(f"  FIXED {rel}")
        if n_stubs:
            print(f"         converted {n_stubs} mock(s) to stub")
        if n_expects:
            print(f"         added expects($this->any()) to {n_expects} mock(s)")

print(f"\nFixed {total_changed} files: {total_stubs} stubs converted, {total_expects} mocks got expects(any)")

# Final check: remaining bare method calls in createMock files
print("\nFinal scan for remaining bare ->method() in createMock files:")
remaining = []
for php_file in sorted(TEST_BASE.rglob("*.php")):
    content = php_file.read_text()
    if 'createMock(' not in content:
        continue
    lines = content.split('\n')
    for i, line in enumerate(lines):
        if '->method(' in line and '->expects(' not in line:
            prev = lines[i-1].rstrip() if i > 0 else ''
            if not prev.endswith('->'):
                remaining.append((str(php_file.relative_to(BASE)), i+1, line.strip()[:80]))

if remaining:
    print(f"  {len(remaining)} remaining (multi-line chains or unresolved):")
    for f, lineno, text in remaining:
        print(f"  {f}:{lineno}: {text}")
else:
    print("  None! All resolved.")
