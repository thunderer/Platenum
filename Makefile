composer-install:
	PHP_VERSION=7.4 docker-compose run --rm composer composer install
composer-update:
	PHP_VERSION=7.4 docker-compose run --rm composer composer update
composer-require:
	PHP_VERSION=7.4 docker-compose run --rm composer composer require ${PACKAGE}
composer-require-dev:
	PHP_VERSION=7.4 docker-compose run --rm composer composer require --dev ${PACKAGE}

test: test-phpunit
test-phpunit:
	PHP_VERSION=7.4 docker-compose run --rm php php -v
	PHP_VERSION=7.4 docker-compose run --rm php php vendor/bin/phpunit --coverage-text
	make qa-psalm
	make qa-infection
test-phpunit-local:
	php -v
	php vendor/bin/phpunit --coverage-text
	php vendor/bin/psalm --no-cache
	php vendor/bin/infection

travis:
	PHP_VERSION=7.1.3 make travis-job
	PHP_VERSION=7.2 make travis-job
	PHP_VERSION=7.3 make travis-job
	PHP_VERSION=7.4 make travis-job
	PHP_VERSION=7.4 docker-compose run --rm composer composer config --unset platform
travis-job:
	docker-compose run --rm composer composer config platform.php ${PHP_VERSION}
	docker-compose run --rm composer composer update -q
	PHP_VERSION=${PHP_VERSION} docker-compose run --rm php php -v
	PHP_VERSION=${PHP_VERSION} docker-compose run --rm php php vendor/bin/phpunit
	PHP_VERSION=${PHP_VERSION} docker-compose run --rm php php vendor/bin/psalm --no-cache

qa-psalm:
	PHP_VERSION=7.4 docker-compose run --rm php php vendor/bin/psalm --no-cache
qa-infection:
	PHP_VERSION=7.4 docker-compose run --rm php php vendor/bin/infection

run-php:
	PHP_VERSION=7.4 docker-compose run --rm php php ${FILE}
