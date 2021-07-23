#!/bin/sh
# ---------------------------------------------------------------------------- #
#  Sets up dev enviroment
# ---------------------------------------------------------------------------- #

echo ""
echo "#----------------------------------------------------------------------------#"
echo "#                          Setting up PHP enviroment                         #"
echo "#----------------------------------------------------------------------------#"

composer install

echo ""
echo "#----------------------------------------------------------------------------#"
echo "#                           Setting up node_modules                          #"
echo "#----------------------------------------------------------------------------#"

# Load .env file for FONTAWESOME_NPM_AUTH_TOKEN
. .env
npm install

echo ""
echo "#----------------------------------------------------------------------------#"
echo "#                        Setting up Laravel Enviroment                       #"
echo "#----------------------------------------------------------------------------#"

echo ""
echo " == Migrating DB =="
php artisan migrate

echo ""
echo " == Clearing cache =="
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo "#----------------------------------------------------------------------------#"
echo "#                           Setting Up MeiliSearch                           #"
echo "#----------------------------------------------------------------------------#"
echo ""

FILE=./meilisearch
if test -f "$FILE"; then
  echo "  ==  Meilisearch already installed =="
else
  echo "  == Installing MeilieSearch =="
  curl -L https://install.meilisearch.com | sh
fi

echo ""
echo "#----------------------------------------------------------------------------#"
echo "#                                  Finished                                  #"
echo "#----------------------------------------------------------------------------#"
echo ""
