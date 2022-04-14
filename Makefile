
all: composer-dev test composer install

composer:
	COMPOSER_NO_DEV=1 docker-compose -f docker-compose.composer.yml up

composer-dev:
	COMPOSER_VENDOR_DIR=vendor-dev docker-compose -f docker-compose.composer.yml up

test:
	docker-compose -f docker-compose.tests.yml up

install:
	docker-compose -f docker-compose.build.yml up
