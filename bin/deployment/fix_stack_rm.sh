#!/bin/bash
# see : https://github.com/moby/moby/issues/30942

until [ -z "$(docker service ls --filter label=com.docker.stack.namespace=$1 -q)" ] || [ "$2" -lt 0 ]; do
  sleep 1;
done

until [ -z "$(docker network ls --filter label=com.docker.stack.namespace=$1 -q)" ] || [ "$2" -lt 0 ]; do
  sleep 1;
done
