#!/usr/bin/env nix-shell
#! nix-shell -i bash -p nix prefetch-npm-deps

NPM_HASH=$(prefetch-npm-deps ../../../package-lock.json)
sed -i -E -e "s#npmDepsHash = \".*\"#npmDepsHash = \"$NPM_HASH\"#" ./default.nix
