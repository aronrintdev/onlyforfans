#!/bin/bash

# Migrated Database with no seeded data
rm database/templateEmpty.sqlite; touch database/templateEmpty.sqlite; DB_DATABASE="database/templateEmpty.sqlite" DB_FINANCIAL_DATABASE="database/templateEmpty.sqlite" APP_ENV="testing" php artisan migrate:refresh --database="templateEmpty" --env="testing"

# Database with seeded data
rm database/template.sqlite; touch database/template.sqlite; DB_DATABASE="database/template.sqlite" DB_FINANCIAL_DATABASE="database/template.sqlite" APP_ENV="testing" php artisan migrate:refresh --seed --database="template" --env="testing"

return 0
