# OAIPMH Server

If you mount a sqlite-database to the correct mount point (see below), the docker container will serve its contents via OAI-PMH.

## Setup
To run the image you can do

```bash
docker run -p8001:80 \
    --mount type=bind,src=/path/to/production.db,dst=/var/www/production.db \
    docker-registry.gerdi.research.lrz.de:5043/archive/oaipmhserver
```

tba: kuberentes deployment

