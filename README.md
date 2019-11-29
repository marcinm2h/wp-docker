
# Development

- pull git repo
- setup .evn files
- push do origin
- to deploy -> change pull on server

# Docker help

https://docs.docker.com/compose/wordpress/

## up / down
```
docker-compose up -d
docker-compose down
```

## down + clean db
```
docker-compose down --volumes
```

## list containers
```
docker ps
```
## bash on container
```
docker exec -it my_wordpress_wordpress_1 /bin/bash
```

## stop / remove all
```
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
```
