#!/usr/bin/env bash

# This script checks the current commit against the latest tags hash. If they are equal, the tags name is saved into the file VERSION

# Get the latest tag name
TAG=$(git describe --tags --abbrev=0)

# Get the latest tag hash
TAG_HASH=$(git rev-list -n 1 $TAG)

# Get the current commit hash
COMMIT_HASH=$(git rev-parse HEAD)

echo $COMMIT_HASH > VERSION

# If the latest tag hash is equal to the current commit hash, save the tag name to the file VERSION
if [ "$TAG_HASH" == "$COMMIT_HASH" ]; then
    echo $TAG > VERSION
fi
