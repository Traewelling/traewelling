{
  lib,
  buildNpmPackage,
}:
buildNpmPackage {
  name = "traewelling-web";
  src = lib.cleanSource ../../..;

  npmDepsHash = "sha256-3WmVa6GoJkiAXDh3IqHbkeRxZLFhSCbbaEOPoNF4ybU=";
  npmPackFlags = ["--ignore-scripts"];
  npmBuildScript = "production";

  prePatch = ''
    # delete public directory to only get web output results in this derivation
    rm -rf public
  '';

  installPhase = ''
    runHook preInstall
    cp -r public $out
    runHook postInstall
  '';
}
