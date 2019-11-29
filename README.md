https://docs.docker.com/compose/wordpress/

# up / down
docker-compose up -d
docker-compose down

# list containers
docker ps

# bash on container
docker exec -it my_wordpress_wordpress_1 /bin/bash

# stop / remove all
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)