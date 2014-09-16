#!/bin/sh
if [ $# -eq 0 ]; then
  echo "Usage: $0 <projectdirectory>"
  echo
  echo For example: $0 ../your-best-project/
  exit 1
fi

cp .gitignore $1
cp Procfile $1
mkdir -p $1/bin
cp bin/cron.sh $1/bin/cron.sh
cp composer.json $1
mkdir -p $1/config/public
cp public/.user.ini $1/config/public/.user.ini
cp nginx.conf $1

cd $1
mkdir util
git mv config/public public
git mv config/vendor/php/Search-Replace-DB util
git rm -r config
git add .gitignore
git add Procfile
git add bin/cron.sh
git add composer.json
git add public/.user.ini
git add nginx.conf

heroku config:unset NGINX_VERSION
heroku config:unset PHP_VERSION
heroku config:unset WORDPRESS_VERSION
heroku config:unset WORDPRESS_DIR

heroku config:unset BUILDPACK_NGINX_VERSION
heroku config:unset BUILDPACK_PHP_VERSION
heroku config:unset BUILDPACK_WORDPRESS_VERSION
heroku config:unset BUILDPACK_WORDPRESS_DIR

echo
echo Automatic part of the migration done.
echo
echo "1) Migrate nginx.conf contents into /nginx.conf"
echo "2) Check the comparison of the commit before committing"
echo "3) Update README.md of your project"
echo "4) Run:"
echo "     composer update"
echo "     git add composer.lock"
echo
echo " If composer fails to install deps, see https://github.com/frc/wordpress-on-heroku#prerequisites"
