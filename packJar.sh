#!/bin/bash
ssh sdk@172.21.161.49 "source ~/.bash_profile;cd /home/sdk/workspace;./buildJar.sh ${1}"
scp sdk@172.21.161.49:/home/sdk/workspace/MobageAlliance.jar ~/Desktop/
