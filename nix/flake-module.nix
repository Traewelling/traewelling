{...}: {
  imports = [
    ./shell.nix
  ];

  perSystem = {pkgs, ...}: {
    packages.default = pkgs.callPackage ./package {};
  };
}
