#!/bin/bash

# This file is part of the GeRDI software suite
# Author Tobias Weber <weber@lrz.de> 
# License: https://www.apache.org/licenses/LICENSE-2.0
# Absolutely no warranty given!

branch=$(git rev-parse --abbrev-ref HEAD)
if [ "$branch" = "master" ]
then
    dockerRegistry="docker-registry.gerdi.research.lrz.de:5043"
    imageName="archive/oaipmhserver"
    imageUrl="${dockerRegistry}/${imageName}"

    docker build -t "${imageUrl}:latest" .
    docker push "${imageUrl}:latest"

    gittags=$(git tag -l --points-at HEAD)
    if [ ! -z "$gittags" ]
    then
        for gittag in $gittags
        do
            docker tag "${imageUrl}" "${imageUrl}:${gittag}"
            docker push "${imageUrl}:${gittag}"
        done
    fi
else
    echo "On branch $branch - will not build (open a PR to master to build and deploy)"
fi
