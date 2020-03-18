test: test-phpunit
test-phpunit:
	PHP_VERSION=7.4 docker-compose run --rm php php -v
	PHP_VERSION=7.4 docker-compose run --rm php php vendor/bin/phpunit --coverage-text
test-phpunit-local:
	php -v
	php vendor/bin/phpunit --coverage-text

travis:
	PHP_VERSION=7.1 make travis-job
	PHP_VERSION=7.2 make travis-job
	PHP_VERSION=7.3 make travis-job
	PHP_VERSION=7.4 make travis-job
	PHP_VERSION=7.4 docker-compose run --rm composer composer config --unset platform
travis-job:
	docker-compose run --rm composer composer config platform.php ${PHP_VERSION}
	docker-compose run --rm composer composer update -q
	PHP_VERSION=${PHP_VERSION} docker-compose run --rm php php -v
	PHP_VERSION=${PHP_VERSION} docker-compose run --rm php php vendor/bin/phpunit
