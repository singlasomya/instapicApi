Before Staet please install 
1) php 
2) composer

Production api

1) Clone Source code from github
git clone https://github.com/singlasomya/instapicApi.git

2) There should be an .env.example file in the project which you can rename to .env. Change Databse and Aws S3 Access key:
APP_NAME=Instapic
APP_ENV=local
APP_KEY=base64:RwkNJVVQ7HK2FRDGsKf7BcNNxOAI6MnwdJFYWX0sH+I=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

FILESYSTEM_DRIVER=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-east-1
AWS_BUCKET=

3) Database Migrate
php artisan migrate

4) change public and storage folder permission
chmod -R o+w storage

5) composer i

6) php artisan key:generate

7) php artisan migrate

8) php artisan config:cache

9) For Local wihtout server
  php artisan serve
