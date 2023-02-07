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

In the `docker` folder you will find a sample docker-compose.yml and minimal settings in the .env files. Adjust the
values according to your requirements and start the containers:

```bash
cd docker
docker-compose up
```

You can have sample data created if you set the environment variable `SEED_DB=true`.

### Option 2: Manual installation (e.g. for local development)

To set up a Träwelling instance you'll need:

* [npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)
* [MariaDB](https://mariadb.org/download) or [MySQL](https://www.mysql.com/de/downloads/) (SQLite is used for running
  tests)
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

## Contributing

Contributions are more than welcome. Please open an issue for bugs or feature requests. If you want to implement the
feature - great; if you don't want to, that's fine, too.

If you add code:

* If you edit the language files, please check if your change is applicable at least in english.

* If you work on the front page (see screenshot above), please consider updating the screenshot.

* Please consider adding unit and integration tests, especially if you're adding new features.

If you want, you can [join our discord server](https://discord.gg/QypAnG2qAw) for discussions, support and more: https://discord.gg/QypAnG2qAw

### Translations

We currently support the languages German, English, Polish, French and Dutch. We would like to become even more
international and for this we need you and your language skills.

We use a [Weblate instance](https://translate.codeberg.org/engage/trawelling/) to manage the translations. There you can add
new translations and correct mistakes.

### Security

If you have identified a security issue, please refrain from directly creating an issue or PullRequest so that the
vulnerability is not exploited.

Instead, please contact security@traewelling.de or use other [contact methods](https://traewelling.de/security.txt).

## License

We are using the [Affero General Public License](/LICENSE) ([why?](http://www.gnu.org/licenses/why-affero-gpl)) - you
are required to publish changes that you make to this software. Please refrain from creating your own public instance of
Träwelling, instead try to create a better version for everyone.
