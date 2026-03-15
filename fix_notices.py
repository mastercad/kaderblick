#!/usr/bin/env python3
"""
Fix PHPUnit notices by finding ->method() calls without ->expects() in mock chains.
Strategy: Convert mock properties that NEVER have strict expectations to createStub().
For mixed-use mocks, add ->expects($this->any()) before ->method().
"""

import re
import os
from pathlib import Path

TEST_FILES = [
    "api/tests/Unit/Command/SendSurveyRemindersCommandTest.php",
    "api/tests/Unit/Command/SendUnsentNotificationsCommandTest.php",
    "api/tests/Unit/Service/TaskEventGeneratorServiceTest.php",
    "api/tests/Unit/Service/CalendarEventServiceTest.php",
    "api/tests/Unit/Service/UserVerificationServiceTest.php",
    "api/tests/Unit/Service/TitleCalculationServiceLeagueTest.php",
    "api/tests/Unit/Service/TitleCalculationServiceFullTest.php",
    "api/tests/Unit/Service/PushNotificationServiceTest.php",
    "api/tests/Unit/Service/XPServiceTest.php",
    "api/tests/Unit/Service/TeamMembershipServiceTest.php",
    "api/tests/Unit/Service/SurveyNotificationServiceTest.php",
    "api/tests/Unit/Service/NotificationServiceTest.php",
    "api/tests/Unit/Repository/TacticPresetRepositoryTest.php",
    "api/tests/Unit/DataFixtures/CupFixturesTest.php",
    "api/tests/Unit/Controller/CalendarControllerCancelTest.php",
    "api/tests/Unit/Controller/CalendarControllerPermissionsTest.php",
    "api/tests/Unit/Controller/ParticipationControllerAccessTest.php",
    "api/tests/Unit/Controller/TacticPresetControllerTest.php",
    "api/tests/Unit/Controller/CalendarControllerXpTest.php",
    "api/tests/Unit/Controller/Api/SurveyResponseControllerXpTest.php",
    "api/tests/Unit/Controller/Api/TeamRidesControllerXpTest.php",
    "api/tests/Unit/Controller/Api/TaskControllerXpTest.php",
    "api/tests/Unit/Controller/Api/ParticipationControllerXpTest.php",
    "api/tests/Unit/Controller/PushControllerTest.php",
    "api/tests/Unit/EventSubscriber/PersonCreatedListenerTest.php",
    "api/tests/Feature/Controller/ReportContextDataTest.php",
    "api/tests/Feature/Controller/VerificationControllerTest.php",
    "api/tests/Feature/RegistrationTest.php",
]

WORKSPACE = Path("/media/Austausch/Projekte/fussballverein/webapp")

# Strict expectation patterns (not ->any())
STRICT_EXPECTS = re.compile(r'->expects\(\s*\$this->(once|exactly|atLeast|atMost|never|atLeastOnce)\s*\(')
ANY_EXPECTS = re.compile(r'->expects\(\s*\$this->any\(\)')
METHOD_CALL = re.compile(r'->method\(')
EXPECTS_CALL = re.compile(r'->expects\(')


def analyze_file(filepath):
    """
    Analyze a test file to find mock property names and whether they
    have strict expectations (once/exactly/never etc.) set anywhere.
    Returns:
    - strict_mocks: set of property accesses that use strict expectations
    - pure_stub_mocks: set of property accesses that only use any() or no expects
    """
    content = open(filepath).read()
    
    # Find all property declarations with MockObject type hints
    # Pattern: private SomeClass&MockObject $propName;
    # or: private MockObject&SomeClass $propName;
    prop_pattern = re.compile(
        r'private\s+[^;]*MockObject[^;]*\$(\w+)\s*;',
        re.MULTILINE
    )
    mock_props = set(prop_pattern.findall(content))
    
    if not mock_props:
        return set(), set()
    
    strict_mocks = set()
    pure_stub_mocks = set()
    
    for prop in mock_props:
        # Check if this prop has strict expectations anywhere
        # Look for: $this->propName->expects($this->once() ...
        # or $this->propName->expects($this->exactly( ...
        strict_pattern = re.compile(
            r'\$this->' + re.escape(prop) + r'->expects\(\s*\$this->(once|exactly|atLeast|atMost|never|atLeastOnce)\s*\('
        )
        method_without_expects_pattern = re.compile(
            r'\$this->' + re.escape(prop) + r'((?!->expects\()[^;])*->method\('
        )
        
        has_strict = bool(strict_pattern.search(content))
        
        if has_strict:
            strict_mocks.add(prop)
        else:
            pure_stub_mocks.add(prop)
    
    return strict_mocks, pure_stub_mocks


def find_method_without_expects_lines(content, prop_name):
    """Find line ranges where $this->prop->method() is called without preceding expects()."""
    lines = content.split('\n')
    problem_lines = []
    
    for i, line in enumerate(lines):
        # Check if this line has ->method( for this prop
        if f'$this->{prop_name}' in line and '->method(' in line and '->expects(' not in line:
            # Check if the previous lines in the chain have ->expects(
            # Look backwards up to 5 lines for start of chain
            chain_start = i
            for j in range(i - 1, max(i - 6, -1), -1):
                prev = lines[j].rstrip()
                if prev.endswith('->'):
                    chain_start = j
                elif j < i and (prev.endswith(',') or prev.endswith('(')):
                    break
                elif '->' in prev:
                    chain_start = j
                else:
                    break
            
            # Get the chain text
            chain_text = '\n'.join(lines[chain_start:i+1])
            if '->expects(' not in chain_text:
                problem_lines.append((i, line))
    
    return problem_lines


def fix_file_convert_to_stub(filepath, pure_stub_props):
    """Convert pure stub properties to createStub() and update type hints."""
    with open(filepath, 'r') as f:
        content = f.read()
    
    original = content
    
    for prop in pure_stub_props:
        # Find the type for createMock: $this->prop = $this->createMock(SomeClass::class);
        mock_pattern = re.compile(
            r'(\$this->' + re.escape(prop) + r'\s*=\s*\$this->)createMock(\([^)]+\))',
        )
        content = mock_pattern.sub(r'\1createStub\2', content)
        
        # Remove ->expects($this->any()) before ->method( for this prop
        any_expects_pattern = re.compile(
            r'(\$this->' + re.escape(prop) + r'[^;]*?)' +
            r'->expects\(\s*\$this->any\(\)\s*\)(->method\()',
            re.DOTALL
        )
        content = any_expects_pattern.sub(r'\1\2', content)
        
        # Update type hints: SomeClass&MockObject -> SomeClass&Stub
        # Find property declaration and fix type
        prop_decl = re.compile(
            r'(private\s+)([^;]*?)(&MockObject|MockObject&)([^;]*?)(\s+\$' + re.escape(prop) + r'\s*;)',
            re.MULTILINE
        )
        def replace_type(m):
            before = m.group(1)
            part1 = m.group(2)
            intersection = m.group(3)
            part2 = m.group(4)
            end = m.group(5)
            if intersection == '&MockObject':
                new_intersection = '&Stub'
            else:
                new_intersection = 'Stub&'
            return before + part1 + new_intersection + part2 + end
        content = prop_decl.sub(replace_type, content)
    
    # Fix imports: add Stub import if needed, keep MockObject if still used
    if 'createStub' in content and 'MockObject' not in content.replace('use PHPUnit\\Framework\\MockObject\\MockObject;', ''):
        # Replace MockObject import with Stub
        content = content.replace(
            'use PHPUnit\\Framework\\MockObject\\MockObject;',
            'use PHPUnit\\Framework\\MockObject\\Stub;'
        )
    elif 'createStub' in content and '&Stub' in content and '&MockObject' not in content:
        content = content.replace(
            'use PHPUnit\\Framework\\MockObject\\MockObject;',
            'use PHPUnit\\Framework\\MockObject\\Stub;'
        )
    
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        return True
    return False


def add_any_expects_to_method_calls(filepath, strict_props):
    """Add ->expects($this->any()) before bare ->method() calls for strict properties."""
    with open(filepath, 'r') as f:
        content = f.read()
    
    original = content
    
    for prop in strict_props:
        # Pattern: $this->prop->method( (not preceded by ->expects( in the chain)
        # This is complex with multiline chains, so use a token approach
        
        # Simple case: same-line pattern like $this->prop->method(
        # where the line contains ->method( but NOT ->expects(
        lines = content.split('\n')
        new_lines = []
        i = 0
        while i < len(lines):
            line = lines[i]
            if (f'$this->{prop}' in line and '->method(' in line and 
                    '->expects(' not in line and '->expects(' not in (lines[i-1] if i > 0 else '')):
                # Check previous line for chain continuation
                prev_lines_text = lines[i-1] if i > 0 else ''
                if prev_lines_text.rstrip().endswith('->'):
                    # Part of a multi-line chain starting earlier - skip, too complex
                    new_lines.append(line)
                else:
                    # Insert ->expects($this->any()) before ->method(
                    line = line.replace(
                        f'$this->{prop}->method(',
                        f'$this->{prop}->expects($this->any())->method('
                    )
                    new_lines.append(line)
            else:
                new_lines.append(line)
            i += 1
        
        content = '\n'.join(new_lines)
    
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        return True
    return False


def process_file(rel_path):
    filepath = WORKSPACE / rel_path
    if not filepath.exists():
        print(f"  SKIP (not found): {rel_path}")
        return
    
    strict_mocks, pure_stub_mocks = analyze_file(filepath)
    
    if not strict_mocks and not pure_stub_mocks:
        print(f"  NO MOCKS: {rel_path}")
        return
    
    changed = False
    
    if pure_stub_mocks:
        print(f"  STUB props in {rel_path}: {pure_stub_mocks}")
        if fix_file_convert_to_stub(filepath, pure_stub_mocks):
            changed = True
            print(f"    -> converted to createStub()")
    
    if strict_mocks:
        print(f"  MOCK props in {rel_path}: {strict_mocks}")
        if add_any_expects_to_method_calls(filepath, strict_mocks):
            changed = True
            print(f"    -> added expects($this->any()) to bare method() calls")
    
    if not changed:
        print(f"  NO CHANGES needed: {rel_path}")


print("Analyzing and fixing PHPUnit notice-generating files...\n")
for f in TEST_FILES:
    process_file(f)

print("\nDone!")
