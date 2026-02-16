# Run app in dev environment

```bash
  docker compose up
```

# Compile composer packages to /vendor

```bash
  docker compose exec php_app composer install --no-dev --prefer-dist
```

# External libraries used

## delight-im/PHP-auth

- for user authentication
- https://github.com/delight-im/PHP-Auth?tab=readme-ov-file#usage

## PHP Unit

- tests

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
