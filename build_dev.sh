#!/usr/bin/env sh

cp .env.example .env
docker-compose up --build -d
docker-compose exec app sh -c "composer install -d /project/app/ --no-interaction"
docker-compose exec app sh -c "cd /project/app && ./init --env=Development --overwrite=All --delete=All"
docker-compose exec app sh -c "cd /project/app && ./yii migrate --interactive=0"
docker-compose exec app sh -c "cd /project/app && ./yii fixture '*' --namespace='common\fixtures' --interactive=0"