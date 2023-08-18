# [Träwelling](https://traewelling.de)

> Träwelling is a free check-in service that lets you tell your friends where you are and where you can log your public
> transit journeys. In short, you can check into trains and get points for it.
> Check it out at [traewelling.de](https://traewelling.de).

![Resources build with `prod`](https://img.shields.io/github/actions/workflow/status/Traewelling/traewelling/nodejs-prod.yml?branch=develop&logo=github)
![Composer install and test Laravel](https://img.shields.io/github/actions/workflow/status/Traewelling/traewelling/phpunit.yml?branch=develop&label=Laravel&logo=github)
[![Gitmoji](https://img.shields.io/badge/gitmoji-%20😜%20😍-FFDD67.svg)](https://gitmoji.dev)
[![Codacy Badge](https://img.shields.io/codacy/grade/60765ceacee5494184476eae9bf27a1f)](https://app.codacy.com/gh/Traewelling/traewelling?utm_source=github.com&utm_medium=referral&utm_content=Traewelling/traewelling&utm_campaign=Badge_Grade_Dashboard)
[![Codacy Coverage Badge](https://img.shields.io/codacy/coverage/60765ceacee5494184476eae9bf27a1f)](https://www.codacy.com/gh/Traewelling/traewelling/dashboard?utm_source=github.com&utm_medium=referral&utm_content=Traewelling/traewelling&utm_campaign=Badge_Coverage)
[![Translation status](https://translate.codeberg.org/widgets/trawelling/-/traewelling/svg-badge.svg)](https://translate.codeberg.org/engage/trawelling/)
![License](https://img.shields.io/github/license/traewelling/traewelling)
[![Staging Environment](https://img.shields.io/github/actions/workflow/status/traewelling/traewelling/staging-environment.yml?branch=develop&color=%234f46e5&label=Staging%20Environment&logo=%F0%9F%9A%80)](https://trwl-develop-environment.fly.dev)

[![Träwelling Screenshot](traewelling.jpg)](https://traewelling.de)

## Features

* Check into trains, trams, busses and more travel types in most of Europe
* Track your work trips, e.g. for tax returns and travel expenses
* Follow other people and see where they're going
* Meet new friends who are on the same trip
* Find who's going to an event and is with you in your journey
* Optional sharing to Mastodon
* See statistics about your trips
* Export your trips to CSV, JSON or PDF
* Create own applications with our API
* Available in German, English, Polish, French and Dutch

## Set up an instance

### Option 1: Using docker compose

In the `docker` folder you will find a sample number of docker-compose files and minimal settings in the .env files. A `dev.docker-compose.yml` file is useful if you want to develop Träwelling locally. Adjust the values according to your requirements and start the containers:

```bash
cd docker
docker compose -f dev.docker-compose.yml up
```

You can have sample data created if you set the environment variable `SEED_DB=true`.

### Option 2: Manual installation (e.g. for local development)

To set up a Träwelling instance you'll need:

* [npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
* [MariaDB](https://mariadb.org/download) or [MySQL](https://www.mysql.com/de/downloads/) (SQLite is used for running
  tests) (You can use [Docker Compose](https://docs.docker.com/compose/) and the [dev.docker-compose.yml](dev.docker-compose.yml) for that)
* [Composer](https://getcomposer.org/download/)
* PHP 8.1 and the dependencies mentioned in composer.json

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

### Option 3: Local Development using [Nix](https://nixos.org/)

Nix is a cross-platform package manager for Linux and macOS systems.
It also provides per project development environments.
There is a also a Linux Distribution called NixOS which builds on top of nix,
but it's not required to use nix and this development environment.

Our [nix flake](flake.nix) includes such an environment with a pre configured MySQL instance.

If you want to use it:

- [Install nix](https://github.com/DeterminateSystems/nix-installer) if you haven't already. (Make sure you've [enabled flakes and the nix command](https://nixos.wiki/wiki/Flakes#Permanent))
- Activate the environment either by using the [direnv](https://direnv.net/) shell hook or by executing `nix develop --impure` ([why-impure?](https://devenv.sh/guides/using-with-flakes/#getting-started)) in every terminal where you need the dev environment
- Run `devenv up` in another terminal
- Run `setup-devenv` in your terminal to install composer and npm packages and migrate and seed the database
- Run `serve` to start serving the application on http://127.0.0.1:8000/

## Developing and contributing

We want to let you know that Träwelling is a leisure project, developed and maintained by a team of volunteers who
dedicate their spare time to the project. We do our best to address issues and improve the platform, but please keep in
mind that we may not always be able to respond to requests immediately. However, we welcome anyone who wants to
contribute to the project! If you find a bug or have an idea for a new feature, please feel free to open an issue on
GitHub. We also encourage you to help us out by fixing bugs and implementing new features yourself. When submitting a
pull request, please keep it small and focused, and open multiple pull requests if needed to make the review process
smoother and faster. Thank you for your support as we work together to make Träwelling the best it can be!

If you add code:

* If you edit the language files, please check if your change is applicable at least in english.

* If you work on the front page (see screenshot above), please consider updating the screenshot.

* Please consider adding unit and integration tests, especially if you're adding new features.

If you want, you can [join our discord server](https://discord.gg/QypAnG2qAw) for discussions, support and
more: https://discord.gg/QypAnG2qAw

### Translations

We currently support the languages German, English, Polish, French and Dutch. We would like to become even more
international and for this we need you and your language skills.

We use a [Weblate instance](https://translate.codeberg.org/engage/trawelling/) to manage the translations.
There you can add new translations and correct mistakes.

### Security

If you have identified a security issue, please refrain from directly creating an issue or PullRequest so that the
vulnerability is not exploited.

Instead, please contact security@traewelling.de or use other [contact methods](https://traewelling.de/security.txt).

## License

We are using the [Affero General Public License](/LICENSE) ([why?](http://www.gnu.org/licenses/why-affero-gpl)) - you
are required to publish changes that you make to this software. Please refrain from creating your own public instance of
Träwelling, instead try to create a better version for everyone.
