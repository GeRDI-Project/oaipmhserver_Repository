#!/bin/bash

dockerRegistry="docker-registry.gerdi.research.lrz.de:5043"
imageName="archive/oaipmhserver"
imageUrl="${dockerRegistry}/${imageName}"

docker build -t "${imageName}:latest" .
docker push "${dockerRegistry}/${imageName}:latest"

gittags=$(git tag -l --points-at HEAD)
if [ ! -z "$gittags" ]
then
    for gittag in $gittags
    do
        docker tag "${imageName}" "${imageName}:${gittag}"
        docker push "${dockerRegistry}/${imageName}:${gittag}"
    done
fi
