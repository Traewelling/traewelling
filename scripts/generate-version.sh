#!/usr/bin/env bash

# This script checks the current commit against the latest tags hash.
# If they are equal, the tags name is saved into the file VERSION
# It's called automatically by installing composer dependencies (see post-install-cmd in composer.json)

# Colors for fancy output
GREEN="\033[1;32m"
CYAN="\033[1;36m"
YELLOW="\033[1;33m"
RED="\033[1;31m"
RESET="\033[0m"

echo -e "${CYAN}Starting version update script...${RESET}"

# Get the repository root directory
REPO_ROOT=$(git rev-parse --show-toplevel)
echo -e "${YELLOW}Repository root determined: ${GREEN}$REPO_ROOT${RESET}"

# Get the latest tag name
TAG=$(git describe --tags --abbrev=0)
echo -e "${YELLOW}Latest tag name: ${GREEN}$TAG${RESET}"

# Get the latest tag hash
TAG_HASH=$(git rev-list -n 1 "$TAG")
echo -e "${YELLOW}Latest tag hash: ${GREEN}$TAG_HASH${RESET}"

# Get the current commit hash
COMMIT_HASH=$(git rev-parse HEAD)
echo -e "${YELLOW}Current commit hash: ${GREEN}$COMMIT_HASH${RESET}"

echo -e "${CYAN}Writing current commit hash to ${GREEN}VERSION${CYAN} file...${RESET}"
echo "$COMMIT_HASH" > "$REPO_ROOT/VERSION"

# If the latest tag hash is equal to the current commit hash, save the tag name to the file VERSION
if [ "$TAG_HASH" == "$COMMIT_HASH" ]; then
    echo -e "${CYAN}Hashes match! Writing tag name ${GREEN}$TAG${CYAN} to ${GREEN}VERSION${CYAN} file...${RESET}"
    echo "$TAG" > "$REPO_ROOT/VERSION"
    echo -e "${GREEN}Version file updated successfully!${RESET}"
else
    echo -e "${RED}Hashes do not match. The VERSION file contains only the current commit hash.${RESET}"
fi

echo -e "${CYAN}Version update script completed. 🎉${RESET}"
