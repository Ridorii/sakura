# Sakura

![StyleCI](https://styleci.io/repos/45261697/shield)
![SensioLabsInsight](https://insight.sensiolabs.com/projects/6c9b3813-0f50-406c-ab26-665e11876bc9/mini.png)

## Requirements

- PHP 7.0.0 or newer
- A database engine compatible with your PHP install and Laravel/Illuminate's database abstraction layer, MySQL 5.7 recommended.

I will include a full list of required extensions later.

## Development setup

Copy config.example.ini, set everything up to your liking (database is most important). I'd also recommend setting `show_errors` to `true` for development. Then run the following commands in the root.

```
php mahou database-install
php mahou database-migrate
php mahou setup
```

After that you can either use `php mahou serve` to use the built in development server or serve the public folder through your webserver of choice.

## Contributing

Right now I'm not accepting big PRs because of a set of big things not being fully implemented yet, bug fix PRs are more than welcome though!

## License

Sakura is licensed under the Apache License version 2. Check the [LICENSE file](https://github.com/flashwave/sakura/blob/master/LICENSE) for the full thing or if you just want a quick summary [click here](https://i.flash.moe/vlcsnap-2016-03-09-17h45m55s452.png).
