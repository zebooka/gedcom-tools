
all: composer test install

composer:
	docker-compose -f docker-compose.composer.yml up

test:
	docker-compose -f docker-compose.tests.yml up

install: composer
	docker-compose -f docker-compose.build.yml up
