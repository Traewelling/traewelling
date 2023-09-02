#!/usr/bin/env nix-shell
#! nix-shell -i bash -p nix nix-prefetch-scripts

# check if composer2nix is installed
if ! command -v composer2nix &> /dev/null; then
  echo "Please use the devshell or install composer2nix (https://github.com/svanderburg/composer2nix) to run this script."
  exit 1
fi

composer2nix --name "traewelling" \
    --composition=composition.nix \
    --no-dev \
    --config-file=../../composer.json \
    --lock-file=../../composer.lock
