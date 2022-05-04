.PHONY: all composer tests build start clean

all: composer tests build

composer:
	docker-compose run --rm -e COMPOSER_NO_DEV=1 -- php73 composer install -v -n -d /app
	docker-compose run --rm -e COMPOSER_VENDOR_DIR=vendor-dev -- php73 composer install -v -n -d /app

tests:
	docker-compose run --rm -- php73 /app/tests/run.sh
	docker-compose run --rm -- php74 /app/tests/run.sh
	docker-compose run --rm -- php80 /app/tests/run.sh
	docker-compose run --rm -- php81 /app/tests/run.sh

build:
	docker-compose run --rm -e PHAR_SKELETON_ALIAS="gedcom-tools.phar" -e PHAR_SKELETON_NAMESPACE="Zebooka\Gedcom\Application" -- php73 /app/build-phar.php

start:
	docker-compose up

clean:
	docker-compose down --rmi local
