# Set up a development instance

If you have any issues regarding your local setup, you can ask the
community [in our discussions section](https://github.com/Traewelling/traewelling/discussions/categories/questions-support).

There are several ways to get your development instance up and running -
depending on your previous knowledge and host system, you may find one way easier than another.

### Option 1: Manual installation

To set up a Träwelling instance you'll need:

* [npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
* [MariaDB](https://mariadb.org/download) or [MySQL](https://www.mysql.com/de/downloads/) (SQLite is used for running
  tests) (You can use [Docker Compose](https://docs.docker.com/compose/) and
  the [dev.docker-compose.yml](../../docker-compose.yml) for that)
* [Composer](https://getcomposer.org/download/)
* PHP and the dependencies mentioned in composer.json

A local instance of [db-rest v5](https://github.com/derhuerst/db-rest/tree/5) is recommended for the connection to
HAFAS. Alternatively you can use a public instance (see .env.example).

After setting up these, you can clone the repository and install the project's dependencies:

```sh
composer install
npm install
npm run dev
```

Now, you can create your own environment configuration:

```sh
cp .env.example .env
vi .env
```

Please change whatever sounds wrong to you. This is also the place to add API keys (e.g. for Mastodon).
While you will not need all of those, you can stumble into weird bugs.

Then, generate some application keys and migrate the database to the latest level:

```sh
php artisan optimize
php artisan key:generate
php artisan migrate 
#for example data use 
#php artisan db:seed
php artisan passport:install
```

Use your webserver of choice or the in php included dev server (`php artisan serve`) to boot the application.
You should see the Träwelling homepage at http://localhost:8000.

Additionally, for continuous functionality:

- Create a cron job to run `php artisan schedule:run` every minute.
- Set up a service initiating with `php artisan queue:work` to handle essential background tasks.
  Consider creating separate services for the default and webhooks queue if this is a larger installation.

### Option 2: Using docker compose

In the root directory of this repository, you can find a `docker-compose.yml` file which is using the configuration
in `.env.docker`. With a working docker installation, you can start everything you need *for backend development*
with `docker compose up -d`.

To change frontend resources that need to be compiled (any `.scss`, `.js` or `.vue` file), we expect you to have a
working nodejs environment on your system. You can then un-comment the last volume mount in the `docker-compose.yml` (
see `services->app->volumes`) and restart the container. On the host system, run `npm install` and `npm run dev` to
create artefacts with your changes.

You can generate sample data by setting the environment variable `SEED_DB: true` for the `app` container. This will seed
the database and reset the oauth master keys every time on restart, when the variable is present. The seeder uses some
default profile pictures which are stored in `public/uploads/avatars` in the host system. To see them in the web
application, copy them to the `avatars` folder in the repository root, which is preserved over restarts.

If you are working on scheduled code or with background jobs, you need to restart the worker containers after making
code changes. Otherwise, the workers will keep their PHP processes with the old code running.

### Option 3: Local Development using [Nix](https://nixos.org/)

Nix is a cross-platform package manager for Linux and macOS systems.
It also provides per project development environments.
There is a also a Linux Distribution called NixOS which builds on top of nix,
but it's not required to use nix and this development environment.

Our [nix flake](../flake.nix) includes such an environment with a pre configured MySQL instance.

If you want to use it:

- [Install nix](https://github.com/DeterminateSystems/nix-installer) if you haven't already. (Make sure
  you've [enabled flakes and the nix command](https://nixos.wiki/wiki/Flakes#Permanent))
- Activate the environment either by using the [direnv](https://direnv.net/) shell hook or by
  executing `nix develop --impure` ([why-impure?](https://devenv.sh/guides/using-with-flakes/#getting-started)) in every
  terminal where you need the dev environment
- Run `devenv up` in another terminal
- Run `setup-devenv` in your terminal to copy the example `.env` file, install composer and npm packages, and migrate
  and seed the database
- Run `serve` to start serving the application on http://127.0.0.1:8000/
