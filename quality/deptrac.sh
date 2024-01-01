#!/bin/bash

# needed for output: apt-get install graphviz

# Go into the project folder
cd "$( dirname "${BASH_SOURCE[0]}" )"
cd ../website/

# Create the build folder
LOG_FOLDER="../quality/build/"
mkdir -p $LOG_FOLDER

./vendor/bin/deptrac analyse --config-file=./.depfile.mvc.yaml     --no-cache

./vendor/bin/deptrac analyse --config-file=./.depfile.mvc.yaml     --no-cache --formatter=graphviz-image --output="${LOG_FOLDER}deptrac-mvc.png"

# Output
firefox "${LOG_FOLDER}deptrac-mvc.png"
