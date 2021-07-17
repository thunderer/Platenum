PHP_VERSION ?= 7.4
PHP := docker-compose run --rm php-${PHP_VERSION}

docker-build:
	docker-compose build

composer-install:
	${PHP} composer install
composer-self-update:
	${PHP} composer self-update
composer-update:
	${PHP} composer update
composer-require:
	${PHP} composer require ${PACKAGE}
composer-require-dev:
	${PHP} composer require --dev ${PACKAGE}

test: test-phpunit test-infection qa-psalm
test-phpunit:
	${PHP} php -v
	${PHP} php vendor/bin/phpunit --coverage-text
test-phpunit-local:
	php -v
	php vendor/bin/phpunit --coverage-text
	php vendor/bin/psalm --no-cache
	php vendor/bin/infection
test-infection:
	${PHP} php vendor/bin/infection -j2

travis:
	PHP_VERSION=7.1 make travis-job
	PHP_VERSION=7.2 make travis-job
	PHP_VERSION=7.3 make travis-job
	PHP_VERSION=7.4 make travis-job
	PHP_VERSION=8.0 make travis-job
travis-job:
	${PHP} composer update
	${PHP} php -v
	${PHP} php vendor/bin/phpunit
	${PHP} php vendor/bin/psalm --no-cache

qa-psalm:
	${PHP} php vendor/bin/psalm --no-cache --find-unused-psalm-suppress
qa-psalm-suppressed:
	grep -rn psalm-suppress src

clean:
	rm -rfv .phpunit.result.cache coverage.xml coverage infection.log

run-php:
	${PHP} php ${FILE}
