
exec-no-xdebug:= docker compose exec -e XDEBUG_MODE=off -u root app
exec:= docker compose exec -u www-data app

#.PHONY: build up down restart logs shell phpstan phpunit cs-fix cs-check db-setup test
.PHONY: build up down restart logs shell phpstan phpunit cs-fix cs-check db-setup test stle validate

build:
	docker compose up -d --build --remove-orphans

up:
	docker compose up -d

down:
	docker compose down

restart: down up

logs:
	docker compose logs -f

shell:
	docker compose exec app bash

composer-install:
	$(exec-no-xdebug) composer install

db-setup:
	$(exec-no-xdebug) bin/console doctrine:database:drop --force --if-exists
	$(exec-no-xdebug) bin/console doctrine:database:create
	$(exec-no-xdebug) bin/console doctrine:migrations:migrate -n
	$(exec-no-xdebug) bin/console doctrine:fixtures:load  --no-interaction -n

#Hinzugefügt am 27.11: Update von Claudia Schmidt
style:
	$(exec-no-xdebug) vendor/bin/php-cs-fixer fix

#Hinzugefügt am 27.11: Update von Claudia Schmidt
validate:
	$(exec-no-xdebug) vendor/bin/phpstan analyse

test:
	$(exec-no-xdebug) vendor/bin/phpunit --testdox --colors="always"

fix:
	$(exec-no-xdebug) vendor/bin/php-cs-fixer fix --allow-risky=yes

analyze:
	$(exec-no-xdebug) vendor/bin/phpstan

tailwind:
	$(exec-no-xdebug) bin/console tailwind:build
