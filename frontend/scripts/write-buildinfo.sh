#!/bin/bash

BUILDNUM=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
echo "{ \"build\": \"$BUILDNUM\" }" > "$(dirname "$0")/../public/buildinfo.json"
