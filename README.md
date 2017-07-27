# Battleships (CLI Client)

## Battleships (sea battle) game - CLI online client
Alternative for [Web Client](https://github.com/jlekowski/battleships-webclient). Requires Bash, PHP, and Internet connection to play online.

### LINKS
* https://github.com/jlekowski/battleships-api - API the library is for
* https://github.com/jlekowski/battleships-offline - offline version
* https://github.com/jlekowski/battleships-webclient - Web Client for API
* https://github.com/jlekowski/battleships - legacy full web version

---
#### Yes, the code is not too well organised (euphemism) and requires a lot of work.
---

## === Installation ===
1. Download/clone this repository.
2. Install dev dependencies.
```
composer install --no-dev
```

## === Test ===
1. Install dev dependencies.
```
composer install --dev
```
2. Run unit tests.
```
bin/phpunit
```

## === Usage ===
1. From command line.
```
# start game
bin/game

# using custom API 
bin/game --url http://battleships-api.vagrant

# more options
bin/game --help
```

## === Changelog ===

* version **0.1** (alpha)
  * Working version of the CLI Client with limited functionality
