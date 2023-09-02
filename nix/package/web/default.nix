{
  lib,
  buildNpmPackage,
}:
buildNpmPackage {
  name = "traewelling-web";
  src = lib.cleanSource ../../..;

  npmDepsHash = "sha256-NWkPCEXTiUXT782GC+Q4ysdCILqn6BEkzvDIcZxGlBI=";

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
