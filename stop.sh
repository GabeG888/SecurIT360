#!/bin/bash
docker rm $(docker stop $(docker ps -a -q --filter ancestor=securit360gabeg888 --format="{{.ID}}"))

