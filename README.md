# Requirements

- Docker
- Docker Compose

# Development

- pull git repo
- setup .env files (copy example.env to .env for a start)

# Deployment

change pull on server

# Docker help

https://docs.docker.com/compose/wordpress/

## up / down

```
docker-compose up -d
docker-compose down
```

## down + clean db
**WARNING! This command will clean yor database!**

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
