[![Build Status](https://travis-ci.com/marcinm2h/wp-docker.svg?token=fKQnZUT2NWo4wjBGfXEp&branch=master)](https://travis-ci.com/marcinm2h/wp-docker)

# Requirements

- Docker
- Docker Compose

# Development

- pull git repo
- setup .env files (copy example.env to .env for a start)
- chose your config (.dev - remote database, or .prod - local database)
- start project with `docker-compose up -d`

# Commiting + deployment

- create feature branch `git checkout -b my_feautre_branch_name`
- create changes
- commit changes `git commit -m "my feature"`
- create pull request - push to origin `git push origin my_feature_branch_name`
- merge pull request to master
- ssh to remote server and pull `ssh root@vps_ip "cd /home/marcin/wp-docker/ && git pull"`

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
docker exec -it [your_container_name] /bin/bash
```

## stop / remove all

```
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
```

# MySql help

## login to container

```
docker-compose exec db bash
```

## login to mysql

```
mysql -u [username] -p
```

## show tables

```
use [dababase];
show tables; # tables names
describe wp_posts; # table schema
```
