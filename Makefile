init: docker-down-clear \
	docker-pull \
 	docker-build \
 	 docker-up \
 	  docker-composer-install \
 	   docker-wait \
 	    docker-migrate \
 	     docker-seed
up: docker-up
down: docker-down

docker-up:
	docker-compose up -d

docker-down:
	docker-compose down --remove-orphans

docker-down-clear:
	docker-compose down -v --remove-orphans

docker-pull:
	docker-compose pull

docker-build:
	docker-compose build --pull --progress=plain

docker-composer-install:
	docker-compose run --rm -w /app/src api-php-cli composer install

docker-wait:
	@echo "Waiting for PostgreSQL to be ready..."
	@docker-compose run --rm api-php-cli sh -c "\
		until nc -z api-postgres 5432; do \
			echo 'Waiting for PostgreSQL...'; \
			sleep 2; \
		done; \
		echo 'PostgreSQL is ready!'"

docker-migrate:
	docker-compose run --rm api-php-cli php src/artisan migrate

docker-seed:
	docker-compose run --rm api-php-cli php src/artisan db:seed
