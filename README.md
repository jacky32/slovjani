# Run app in dev environment

```bash
  docker compose up
```

# Compile composer packages to /vendor

```bash
  docker compose exec php_app composer install --no-dev --prefer-dist
```

# External libraries used

## PHP

### delight-im/PHP-auth

- for user authentication
- https://github.com/delight-im/PHP-Auth?tab=readme-ov-file#usage

### PHP Unit

- tests

```bash
  ./vendor/bin/phpunit ./tests/lib/helpers_test.php
```

- případně lze všechny testy spustit bez specifikování konkrétního souboru

```bash
  ./vendor/bin/phpunit
```

### PHP Doc

Vygeneruje dokumentaci do `/docs/app/` z `app/`, `config/`, `db/`, `lib/` a `public/` složek.
Na základě komentářů v PHPDoc v kódu aplikace.

```bash
  docker run --rm -v "$(pwd):/data" "phpdoc/phpdoc:3"
```

Nastavení exportu dokumentace lze upravit v `./phpdoc.dist.xml`

## Javascript

# Flatpickr

- used for datetime input stylings
- can be added to view by including the layouts/\_flatpickr.html.php partial at the end of the file like this:

```php
  <?= $this->renderPartial("layouts/_flatpickr") ?>
```

# Lokalizace

- Pro možnost využití YAMLu se musí nainstalovat libyaml a extension skrz PECL

```bash
  apt-get install -y libyaml-dev
  pecl install yaml
```

- pak se musí v php.ini aktivovat skrz

```
  extension=yaml.so
```

# PlantUML

Je potřeba nainstalovat plantuml, např. přes Homebrew (macOS):

```bash
  brew install plantuml
```

Následně lze konkrétní diagramy exportovat takto:

```bash
  plantuml docs/diagrams/sitemap.plantuml
```

Pro přegenerování všech diagramů lze využít wildcard:

```bash
  plantuml docs/diagrams/*.plantuml
```
