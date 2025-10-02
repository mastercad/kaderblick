#!/bin/bash
DATE=$(date -u +"%Y-%m-%dT%H:%M:%S%z")
sed -i "s|<meta property=\"article:published_time\" content=\"[^\"]*\" />|<meta property=\"article:published_time\" content=\"$DATE\" />|" "$(dirname "$0")/../index.html"
