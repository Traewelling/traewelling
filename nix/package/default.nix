{
  lib,
  stdenv,
  php,
  dataDir ? "/var/lib/traewelling",
  runtimeDir ? "/run/traewelling",
  pkgs,
  lndir,
}: let
  web = pkgs.callPackage ./web {};
  package =
    (import ./composition.nix {
      inherit pkgs;
      inherit (stdenv.hostPlatform) system;
      noDev = true;
    })
    .overrideAttrs (attrs: {
      installPhase = ''
        ${attrs.installPhase}

        rm -R $out/bootstrap/cache
        # Move static contents for the NixOS module to pick it up, if needed.
        mv $out/bootstrap $out/bootstrap-static
        mv $out/storage $out/storage-static
        ln -s ${dataDir}/.env $out/.env
        ln -s ${dataDir}/storage $out/
        ln -s ${dataDir}/storage/app/public $out/public/storage
        ln -s ${runtimeDir} $out/bootstrap
        ${lndir}/bin/lndir -silent ${web} $out/public
        chmod +x $out/artisan
      '';
    });
in
  package.override rec {
    pname = "traewelling";
    version = "unstable-2023-09-02";

    src = lib.cleanSource ../..;

    meta = {
      description = "Free check-in service to log your public transit journeys";
      license = lib.licenses.agpl3Only;
      homepage = "https://traewellling.de";
      inherit (php.meta) platforms;
    };
  }
