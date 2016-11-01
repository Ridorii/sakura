# Sakura
![StyleCI](https://styleci.io/repos/45261697/shield)
![SensioLabsInsight](https://insight.sensiolabs.com/projects/6c9b3813-0f50-406c-ab26-665e11876bc9/mini.png)

## Requirements
- PHP 7.0.0 or newer
- A database engine compatible with your PHP install and Laravel/Illuminate's database abstraction layer, MySQL 5.7 recommended.
- [Composer](https://getcomposer.org/)
- [NPM from NodeJS](https://nodejs.org/)

## Installing
### Backend
Copy config.example.ini, set everything up to your liking (database is most important). I'd also recommend setting `show_errors` to `true` for development. Then run the following commands in the root.
```
composer install
php mahou database-install
php mahou database-migrate
php mahou setup
```
After that you can either use `php mahou serve` to use the built in development server or serve the public folder through your webserver of choice.

### Frontend
To compile the LESS and TypeScript assets you need to have the individual compiler installed, both are available from npm and can be installed through the following command:
```
npm install -g less typescript
```
After that install the required libraries by running `npm install` and from then on to compile the files you need to run `build.sh`.

If your editor yells at you that it can't find certain namespaces try running `build.sh` since that generates the required typings (.d.ts files).

## Contributing
Right now I'm not accepting big PRs because of a set of big things not being fully implemented yet, bug fix PRs are more than welcome though!

## License
Sakura is licensed under the Apache License version 2. Check the [LICENSE file](https://github.com/flashwave/sakura/blob/master/LICENSE) for the full thing.
