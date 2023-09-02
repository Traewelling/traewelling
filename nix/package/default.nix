{
  lib,
  stdenv,
  php,
  # dataDir ? "/var/lib/traewelling",
  # runtimeDir ? "/run/traewelling",
  pkgs,
  lndir,
}: let
  web = pkgs.callPackage ./web {};
in
  php.buildComposerProject {
    pname = "traewelling";
    version = "0.0.0";

    src = lib.cleanSource ../..;

    meta = {
      description = "Free check-in service to log your public transit journeys";
      license = lib.licenses.agpl3Only;
      homepage = "https://traewellling.de";
      inherit (php.meta) platforms;
    };

    postInstall = ''
      ${lndir}/bin/lndir -silent ${web} $out/share/php/traewelling/public
    '';

    vendorHash = "sha256-qyqH9o3zW5L1rg+IKFfRPWgQMm5ioeGmVeOEXjWoo8w=";
  }
