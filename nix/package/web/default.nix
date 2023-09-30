{
  lib,
  buildNpmPackage,
}:
buildNpmPackage {
  name = "traewelling-web";
  src = lib.cleanSource ../../..;

  npmDepsHash = "sha256-dSfHgmjvSbfEUwP6CnbulcVBHy2Qn135E/+XMA3kiK0=";

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
