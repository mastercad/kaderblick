# drop test db
docker compose exec api bin/console doctrine:schema:drop --force --full-database --env=test

# migrate test db
docker compose exec api bin/console doctrine:migration:migrate -n --env=test

# fixtures einspielen
docker compose exec api bin/console doctrine:fixtures:load -n --env=test --group=master --group=test

