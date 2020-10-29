PHP_VERSION ?= 7.4
COMPOSER := docker-compose run --rm composer
PHP := docker-compose run --rm php-${PHP_VERSION}

composer-install:
	${COMPOSER} composer install
composer-self-update:
	${COMPOSER} composer self-update
composer-update:
	${COMPOSER} composer update
composer-require:
	${COMPOSER} composer require ${PACKAGE}
composer-require-dev:
	${COMPOSER} composer require --dev ${PACKAGE}

test: test-phpunit
test-phpunit:
	${PHP} php -v
	${PHP} php vendor/bin/phpunit --coverage-text
	make test-infection
	make qa-psalm
test-phpunit-local:
	php -v
	php vendor/bin/phpunit --coverage-text
	php vendor/bin/psalm --no-cache
	php vendor/bin/infection
test-infection:
	${PHP} php vendor/bin/infection -j2

travis:
	PHP_VERSION=7.1.3 make travis-job
	PHP_VERSION=7.2 make travis-job
	PHP_VERSION=7.3 make travis-job
	PHP_VERSION=7.4 make travis-job
	PHP_VERSION=7.4 docker-compose run --rm composer composer config --unset platform
travis-job:
	docker-compose run --rm composer composer config platform.php ${PHP_VERSION}
	docker-compose run --rm composer composer update
	${PHP} php -v
	${PHP} php vendor/bin/phpunit
	${PHP} php vendor/bin/psalm --no-cache

qa-psalm:
	${PHP} php vendor/bin/psalm --no-cache

run-php:
	${PHP} php ${FILE}
