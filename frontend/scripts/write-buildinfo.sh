#!/bin/bash

BUILDNUM=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
echo "{ \"build\": \"$BUILDNUM\" }" > ./public/buildinfo.json
