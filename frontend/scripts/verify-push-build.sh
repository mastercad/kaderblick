#!/usr/bin/env bash
# =================================================================
#  Push Notification Production Readiness Verification
# =================================================================
#  This script ensures the production build contains all required
#  push notification code in the Service Worker. Run this as part
#  of CI/CD to prevent regression where push handlers are missing.
#
#  Usage:  ./scripts/verify-push-build.sh
#  Exit 0 = OK, Exit 1 = push handlers missing from production SW
# =================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FRONTEND_DIR="$(dirname "$SCRIPT_DIR")"
DIST_DIR="$FRONTEND_DIR/dist"

echo "========================================"
echo " Push Notification Build Verification"
echo "========================================"
echo ""

# Step 1: Build (if dist doesn't exist or --build flag passed)
if [[ ! -d "$DIST_DIR" ]] || [[ "${1:-}" == "--build" ]]; then
  echo "[1/3] Building production bundle..."
  cd "$FRONTEND_DIR"
  npm run build
  echo ""
else
  echo "[1/3] Using existing dist/ build"
  echo ""
fi

# Step 2: Find the SW file
SW_FILE=""
for candidate in "$DIST_DIR/sw.js" "$DIST_DIR/sw.mjs"; do
  if [[ -f "$candidate" ]]; then
    SW_FILE="$candidate"
    break
  fi
done

if [[ -z "$SW_FILE" ]]; then
  echo "FAIL: No service worker file found in $DIST_DIR (expected sw.js or sw.mjs)"
  exit 1
fi

echo "[2/3] Found SW file: $SW_FILE"
SW_CONTENT=$(cat "$SW_FILE")
echo "       File size: $(wc -c < "$SW_FILE") bytes"
echo ""

# Step 3: Check for required push notification strings
echo "[3/3] Checking required push notification patterns..."
echo ""

ERRORS=0

check_pattern() {
  local pattern="$1"
  local description="$2"
  
  if echo "$SW_CONTENT" | grep -qE "$pattern"; then
    echo "  ✓ $description"
  else
    echo "  ✗ MISSING: $description (pattern: $pattern)"
    ERRORS=$((ERRORS + 1))
  fi
}

# ---- Critical Push Handlers ----
echo " Critical Push Handlers:"
check_pattern "addEventListener.*['\"]push['\"]|push.*addEventListener" "push event listener"
check_pattern "showNotification" "showNotification call"
check_pattern "addEventListener.*['\"]notificationclick['\"]|notificationclick.*addEventListener" "notificationclick event listener"

echo ""

# ---- Service Worker Lifecycle (for auto-update) ----
echo " SW Lifecycle:"
check_pattern "skipWaiting" "skipWaiting() for immediate activation"
check_pattern "clients\.claim|clients\[.*claim" "clients.claim() for immediate control"

echo ""

# ---- Push Notification Content ----
echo " Push Notification Content:"
check_pattern "icon.*192|192.*icon|icon-192" "icon reference (icon-192)"
check_pattern "Kaderblick|kaderblick" "App name in SW"

echo ""

# ---- Workbox Precaching ----
echo " Workbox Integration:"
check_pattern "precacheAndRoute|precache" "Workbox precaching"
# __WB_MANIFEST is replaced by Vite during build with an actual manifest array.
# In the built file we expect the injected manifest (array of URLs/revisions), not the placeholder.
check_pattern "__WB_MANIFEST|\{\"revision\"|\{\"url\"" "Workbox manifest (injected or placeholder)"

echo ""

# ---- Navigation / Click handling ----
echo " Click/Navigation Handling:"
check_pattern "openWindow|navigate" "Window open/navigate on notification click"

echo ""

# Summary
echo "========================================"
if [[ $ERRORS -eq 0 ]]; then
  echo " ✓ ALL CHECKS PASSED — Push notifications are production-ready!"
  echo "========================================"
  exit 0
else
  echo " ✗ $ERRORS CHECK(S) FAILED — Push notifications will NOT work in production!"
  echo ""
  echo " The Service Worker is missing critical push notification code."
  echo " This usually happens when VitePWA's strategy is wrong (generateSW vs injectManifest)"
  echo " or when the custom SW source (src/sw.ts) was not correctly compiled."
  echo "========================================"
  exit 1
fi
