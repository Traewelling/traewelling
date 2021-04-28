# [Träwelling](https://traewelling.de)

> Träwelling is a free check-in service that lets you tell your friends where you are and where you can log your public transit journeys. In short, you can check into trains and get points for it. Check it out at [traewelling.de](https://traewelling.de).

![Resources build with `prod`](https://img.shields.io/github/workflow/status/Traewelling/traewelling/Resources%20build%20with%20%60prod%60?label=npm%20prod&logo=github)
![Resources build with `dev`](https://img.shields.io/github/workflow/status/Traewelling/traewelling/Resources%20build%20with%20%60dev%60?label=npm%20dev&logo=github)
![Composer install and test Laravel](https://img.shields.io/github/workflow/status/Traewelling/traewelling/Laravel?label=Laravel&logo=github)
[![Gitmoji](https://img.shields.io/badge/gitmoji-%20😜%20😍-FFDD67.svg)](https://gitmoji.dev)
[![Codacy Badge](https://img.shields.io/codacy/grade/60765ceacee5494184476eae9bf27a1f)](https://app.codacy.com/gh/Traewelling/traewelling?utm_source=github.com&utm_medium=referral&utm_content=Traewelling/traewelling&utm_campaign=Badge_Grade_Dashboard)
[![Codacy Coverage Badge](https://img.shields.io/codacy/coverage/60765ceacee5494184476eae9bf27a1f)](https://www.codacy.com/gh/Traewelling/traewelling/dashboard?utm_source=github.com&utm_medium=referral&utm_content=Traewelling/traewelling&utm_campaign=Badge_Coverage)
[![Translation status](https://weblate.bubu1.eu/widgets/trawelling/-/common/svg-badge.svg)](https://weblate.bubu1.eu/engage/trawelling/)
![License](https://img.shields.io/github/license/traewelling/traewelling)
[![Träwelling Screenshot](traewelling.jpg)](https://traewelling.de)

## Features

* Check into trains and other public transport options in most of Europe
* Track your work trips, e.g. for tax returns and travel expenses
* Follow other people and see where they're going
* Meet new friends who are on the same train
* Find who's going to an event and is in your train
* Automatic sharing to Twitter and Mastodon
* Beautiful sharepics for e.g. Instagram Stories
* All texts in German and English

## Set up an instance

We're using the [Laravel framework](https://laravel.com/docs/8.0) which depends on:

* PHP 8.0 (or higher)
  * PHP GD library 
* Composer
* NodeJS
  * [npm](https://www.npmjs.com/)
* A database of choice, e.g. MariaDB or SQLite (preferrably MariaDB)
  * If you're using SQLite, make sure you have `php-sqlite` installed 
* A local instance of [db-rest v5](https://github.com/derhuerst/db-rest/tree/5)

After setting up those things, you can clone the repository and get the dependencies:

```sh
composer install
npm install
```

Now, you can create your own environment configuration:

```sh
cp .env.example .env
vi .env
```

Please change whatever sounds wrong to you. This is also the place to add API keys (e.g. for Twitter). While you will not need all of those, you can stumple into weird bugs.

Then, generate some application keys and migrate the database to the latest level:

```sh
php artisan key:generate
php artisan migrate 
#for example data use 
#php artisan migrate --seed
php artisan passport:install
```

Last, but not least, you can run `npm run dev` to build the frontend and watch for changes in the `resources/` folder.

Use your webserver of choice or artisan (`php artisan serve`) to boot the application. You should see the Träwelling homepage.

## Contributing

Contributions are more than welcome. Please open an issue for bugs or feature requests. If you want to implement the feature - great; if you don't want to, that's fine, too.

If you add code:
* Please also commit the changes in the `public/` folder, just as `npm run dev` produces them. Yes, it's weird, but that's how we roll.
* If you edit the language files, please check if your change is applicable for all languages.
* If you work on the front page (see screenshot above), please consider updating the screenshot.
* Unless you really want to work on Träwelling for a long time, we cannot support more languages. It would be sad to have half-baked languages that have missing strings after a while.
* Please consider adding unit and integration tests, especially if you're adding new features.

### Translations
We currently support the languages German and English. A few translations also in Swedish. We would like to become even more international and for this we need you and your language skills.

We use a [Weblate instance](https://weblate.bubu1.eu/projects/trawelling/) to manage the translations. There you can add new translations and correct mistakes.

## License
We are using the [Affero General Public License](/LICENSE) ([why?](http://www.gnu.org/licenses/why-affero-gpl)) - you are required to publish changes that you make to this software. Please refrain from creating your own public instance of Träwelling, instead try to create a better version for everyone.
