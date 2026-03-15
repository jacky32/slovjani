# Run app in dev environment

```bash
  docker compose up
```

# Production deploy and run

1. Create a production env file and set strong secrets:

```bash
cp .env.production.sample .env.production
```

2. Build and start production services (MySQL + app):

```bash
./scripts/deploy_production.sh
```

To also export startup logs into a specific file:

```bash
LOG_FILE=./logs/production-startup.log ./scripts/deploy_production.sh
```

3. Stop production services:

```bash
docker compose -f compose.production.yaml --env-file .env.production down
```

4. Check production logs:

```bash
docker compose -f compose.production.yaml --env-file .env.production logs -f nginx php_app mysql
```

5. Export logs into a specific file:

```bash
./scripts/export_production_logs.sh ./logs/production.log
```

Follow and continuously append logs:

```bash
./scripts/export_production_logs.sh ./logs/production.log --follow
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

- alternatively, all tests can be run without specifying a particular file

```bash
  ./vendor/bin/phpunit
```

## Google Calendar API

- The app includes a lightweight Google Calendar client in `app/services/google_calendar_service.php`.
- Required environment variables:

```bash
  GOOGLE_CALENDAR_ID=your-calendar-id@group.calendar.google.com
  GOOGLE_SERVICE_ACCOUNT_EMAIL=service-account@project-id.iam.gserviceaccount.com
  GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"
```

- Optional environment variables:

```bash
  GOOGLE_CALENDAR_DELEGATED_USER=calendar-admin@example.com
  GOOGLE_CALENDAR_TIMEZONE=Europe/Prague
  GOOGLE_API_TIMEOUT_SECONDS=15
```

### PHP Doc

Generates documentation into `/docs/app/` from the `app/`, `config/`, `db/`, `lib/` and `public/` folders.
Based on PHPDoc comments in the application code.

```bash
  docker run --rm -v "$(pwd):/data" "phpdoc/phpdoc:3"
```

Documentation export settings can be modified in `./phpdoc.dist.xml`

## Javascript

# Flatpickr

- used for datetime input stylings
- can be added to view by including the `layouts/_flatpickr.html.php` partial at the end of the file like this:

```php
  <?= $this->renderPartial("layouts/_flatpickr") ?>
```

# Localization

- To use YAML, libyaml and the extension must be installed via PECL

```bash
  apt-get install -y libyaml-dev
  pecl install yaml
```

- then it must be activated in php.ini via

```
  extension=yaml.so
```

# PlantUML

PlantUML needs to be installed, e.g. via Homebrew (macOS):

```bash
  brew install plantuml
```

Specific diagrams can then be exported like this:

```bash
  plantuml docs/diagrams/sitemap.plantuml
```

To regenerate all diagrams, a wildcard can be used:

```bash
  plantuml docs/diagrams/*.plantuml
```
