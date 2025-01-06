#!/bin/bash

downloadUrl="https://github.com/tus/tusd/releases/latest/download/tusd_linux_amd64.tar.gz"
outputPath="./tusd.zip"
executablePath="./tusd_linux_amd64/tusd"

if [ ! -f "$executablePath" ]; then
    curl -L -o "$outputPath" "$downloadUrl"
    tar -xvf "$outputPath"
    rm "$outputPath"
fi

# Execute tusd
./tusd_linux_amd64/tusd -hooks-http http://localhost:8000/api/upload -hooks-http-forward-headers Authorization -hooks-enabled-events pre-create,post-finish
