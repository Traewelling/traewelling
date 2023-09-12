#!/usr/bin/env nix-shell
#! nix-shell -i bash -p nix git

set -eo pipefail

GIT_TAG="$(git describe --tags --abbrev=0)"
OLD_VERSION="$(nix eval --raw ".#default.version")"
sed -i -E -e "s#version = \"$OLD_VERSION\"#version = \"${GIT_TAG#v}\"#" ./default.nix

OLD_HASH="$(nix eval --raw ".#default.vendorHash")"
EMPTY_HASH="sha256-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA="
sed -i -E -e "s#vendorHash = \".*\"#vendorHash = \"$EMPTY_HASH\"#" ./default.nix

NEW_HASH="$(nix build .#default 2>&1 | tail -n3 | grep 'got:' | cut -d: -f2- | xargs echo || true)"
sed -i -E -e "s#vendorHash = \"$EMPTY_HASH\"#vendorHash = \"${NEW_HASH:-$OLD_HASH}\"#" ./default.nix
