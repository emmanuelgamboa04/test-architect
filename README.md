# test-architect
Test for Architect


#Up service Docker
docker-compose up -d

#Down service Docker
docker-compose down

#Generate Api Documentation Swagger
docker-compose exec php php /var/www/html/artisan l5-swagger:generate

#URL Api Documentation Swagger
http://localhost:8888/api/documentation#/

#Command for login mysql from terminal
docker-compose exec mysql mysql -u homestead -p

#LARAVEL

#List of routes
 docker-compose exec php php /var/www/html/artisan route:list

#Migrate tables
docker-compose exec php php /var/www/html/artisan migrate:fresh --seed

#Generate Controllers
docker-compose exec php php /var/www/html/artisan make:controller Api/V1/UsuarioController

#Generate Models
docker-compose exec php php /var/www/html/artisan make:model Api/V1/Video
