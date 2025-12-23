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
