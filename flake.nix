{
  description = "Traewelling";
  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs/nixpkgs-unstable";
    flake-parts.url = "github:hercules-ci/flake-parts";
    devenv.url = "github:cachix/devenv";
  };
  outputs = inputs @ {flake-parts, ...}:
    flake-parts.lib.mkFlake {inherit inputs;} {
      imports = [
        inputs.devenv.flakeModule
      ];
      systems = [
        "x86_64-linux"
        "aarch64-linux"
        "x86_64-darwin"
        "aarch64-darwin"
      ];
      perSystem = {
        config,
        pkgs,
        ...
      }: {
        devenv.shells.default = {
          languages = {
            php.enable = true;
            javascript.enable = true;
          };
          dotenv.disableHint = true;
          services.mysql = {
            enable = true;
            ensureUsers = [
              {
                name = "homestead";
                password = "secret";
                ensurePermissions = {
                  "*.*" = "ALL PRIVILEGES";
                };
              }
            ];
            initialDatabases = [
              {
                name = "homestead";
              }
            ];
          };
          scripts =
          let
            composer = "${config.devenv.shells.default.languages.php.packages.composer}/bin/composer";
            php = "${config.devenv.shells.default.languages.php.package}/bin/php";
            npm = "${config.devenv.shells.default.languages.javascript.package}/bin/npm";
            mysql = config.devenv.shells.default.services.mysql.package;
          in {
            setup-devenv.exec = ''
              set -eo pipefail
              if [ ! -f .env ]
              then
                echo "Copying .env.example to .env"
                cp .env.example .env
              fi
              set -a; source .env; set +a
              echo "Installing composer packages"
              ${composer} install > /dev/null 2>&1
              echo "Installing npm packages"
              ${npm} ci $REDIRECTION > /dev/null 2>&1

              if [[ "$DB_CONNECTION" == "mysql" ]];
              then
                echo "Waiting for MySQL Database to be ready."
                echo "  Make sure to run 'devenv up' in another terminal to start the MySQL server."
                while ! ${mysql}/bin/mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p="$DB_PASSWORD" --silent; do
                  sleep 1
                done

                echo "Migrating database"
                ${php} artisan migrate:fresh --seed
              else
                echo "You seem to be not using mysql. Skipping migrations."
              fi

              echo "Generating Keys"
              ${php} artisan key:generate > /dev/null 2>&1
              echo "Initializing Passport"
              ${php} artisan passport:install > /dev/null 2>&1
            '';
            serve.exec = ''
              ${npm} run watch &
              ${php} artisan serve
            '';
          };
        };
        formatter = pkgs.alejandra;
      };
    };
}
