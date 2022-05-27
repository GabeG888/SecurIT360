#!/bin/bash
docker build -t securit360gabeg888 .
docker run -d -p 888:80 securit360gabeg888
