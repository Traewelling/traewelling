{...}: {
  perSystem = {
    config,
    pkgs,
    lib,
    ...
  }: {
    devenv.shells.default = {
      config,
      inputs,
      system,
      ...
    }: {
      containers = lib.mkForce {};
      languages = {
        php.enable = true;
        javascript.enable = true;
      };
      dotenv.enable = true;
      services.mysql = {
        enable = true;
        ensureUsers = [
          {
            name = config.env.DB_USERNAME;
            password = config.env.DB_PASSWORD;
            ensurePermissions = {
              "*.*" = "ALL PRIVILEGES";
            };
          }
        ];
        initialDatabases = [
          {
            name = config.env.DB_DATABASE;
          }
        ];
      };
      scripts = let
        composer = "${config.languages.php.packages.composer}/bin/composer";
        php = "${config.languages.php.package}/bin/php";
        npm = "${config.languages.javascript.package}/bin/npm";
        mysql = config.services.mysql.package;

        envKeys = builtins.attrNames config.env;
        unsetEnv = builtins.concatStringsSep "\n" (
          map (key: "unset ${key}") envKeys
        );
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
          ${composer} install > /dev/null
          echo "Installing npm packages"
          ${npm} ci > /dev/null

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
          ${php} artisan key:generate > /dev/null
          echo "Initializing Passport"
          ${php} artisan passport:install > /dev/null
        '';
        serve.exec = ''
          APP_URL=http://localhost:8000 ${npm} run dev &
          # Unset .env variables, so laravel reads the .env files by itself
          ${unsetEnv}
          ${npm} run dev &
          ${php} artisan serve
        '';
        update-nix-package-deps.exec = ''
          set -eo pipefail

          pushd nix/package
          ./update.sh
          pushd web
          ./update.sh
          popd
          popd

          nix fmt ./nix/package
        '';
        artisan.exec = ''
          # Unset .env variables, so laravel reads the .env files by itself
          ${unsetEnv}
          exec ${php} artisan $@
        '';
      };
    };
  };
}
