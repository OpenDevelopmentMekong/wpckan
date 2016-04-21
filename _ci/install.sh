#!/usr/bin/env bash

set -e
echo "Installing dependencies"
pip install ansible
#composer self-update
#composer install --prefer-source --no-interaction --dev

echo "Downloading and unzipping odm_automation"
wget https://github.com/OpenDevelopmentMekong/odm-automation/archive/master.zip -O /tmp/odm_automation.zip
unzip /tmp/odm_automation.zip -d /tmp/

echo "decrypting private key and adding it key to ssh agent"
openssl aes-256-cbc -K $encrypted_f5c2fe88ed3f_key -iv $encrypted_f5c2fe88ed3f_iv -in odm_tech_rsa.enc -out ~/.ssh/id_rsa -d
chmod 600 ~/.ssh/id_rsa
eval `ssh-agent -s`
ssh-add ~/.ssh/id_rsa
