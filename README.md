## Features

1. packages - create custom packages.
2. members - user management system.
3. services and billing cycles - annually, weekly, daily, fixed etc.
4. attendance - used to mark attendance of who's in the gym.
5. activities or system logs
6. subscription management
7. branches
8. notifications - alert admin when members' subscriptions are about to end.

## Screenshots

![My Image](https://raw.github.com/johndavedecano/PHPLaravelGymManagementSystem/main/profile.png)
![My Image](https://raw.github.com/johndavedecano/PHPLaravelGymManagementSystem/main/package.png)

## Installation

1. API Setup

```bash
$ git clone git@github.com:johndavedecano/laragym.git project
$ cd project
$ composer install
$ cp .env.example .env # THEN EDIT YOUR ENV FILE ACCORDING TO YOUR OWN SETTINGS.
$ php artisan key:generate
$ php artisan storage:link
$ php artisan migrate
$ php artisan db:seed
$ php artisan serve
```

2. SveletKit Frontend Setup

```base
$ cd resources/apps/admin
$ cp .env.example .env # edit this file accordingly
$ npm install
$ npm run dev
```

## Tests

If you want to contribute to this project, feel free to do it and open a PR. However, make sure you have tests for what you implement.

In order to run tests:

- create a `homestead_test` database on your machine;
- run `./vendor/bin/phpunit`;

If you want to specify a different name for the test database, don't forget to change the value in the `phpunix.xml` file.

## Routes

![My Image](https://raw.github.com/johndavedecano/PHPLaravelGymManagementSystem/main/routes.png)

