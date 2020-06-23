# [Träwelling](https://traewelling.de)

> Träwelling is a free check-in service that lets you tell your friends where you are and where you can log your public transit journeys. In short, you can check into trains and get points for it. Check it out at [traewelling.de](https://traewelling.de).

![Resources build with `prod`](https://github.com/Traewelling/traewelling/workflows/Resources%20build%20with%20%60prod%60/badge.svg)
![Resources build with `dev`](https://github.com/Traewelling/traewelling/workflows/Resources%20build%20with%20%60dev%60/badge.svg)
![Composer install and test Laravel](https://github.com/Traewelling/traewelling/workflows/Laravel/badge.svg)

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/382932224520478a8f07a8df615a51bd)](https://app.codacy.com/gh/Traewelling/traewelling?utm_source=github.com&utm_medium=referral&utm_content=Traewelling/traewelling&utm_campaign=Badge_Grade_Dashboard)
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

We're using the [Laravel framework](https://laravel.com/docs/5.8) which depends on:

* PHP 7.2 (or higher)
* Composer
* NodeJS and npm
* A database of choice, e.g. MariaDB or SQLite (preferrably MariaDB)
* A local instance of [db-rest v3](https://github.com/derhuerst/db-rest/tree/3)

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

We are using the [Affero General Public License](/LICENSE) ([why?](http://www.gnu.org/licenses/why-affero-gpl)) - you are required to publish changes that you make to this software. Please refrain from creating your own public instance of Träwelling, instead try to create a better version for everyone.
