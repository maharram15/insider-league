up:
	docker-compose --env-file=./.env up -d
build:
	docker-compose --env-file=./.env up -d --build
down:
	docker-compose --env-file=./.env down
migrate:
	docker-compose exec app php artisan migrate
createNetwork:
	docker network create amin
sleep:
	sleep 3
install: createNetwork up sleep migrate
restart: down sleep up
rebuild: down sleep build
bash:
	docker-compose exec app bash
