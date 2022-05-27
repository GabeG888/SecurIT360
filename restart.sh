#!/bin/bash
docker rm $(docker stop $(docker ps -a -q --filter ancestor=securit360gabeg888 --format="{{.ID}}"))
docker build -t securit360gabeg888 .
docker run -d -p 888:80 securit360gabeg888
