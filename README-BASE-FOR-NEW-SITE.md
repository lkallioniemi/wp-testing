NORMAL DEVELOPMENT CYCLE
------------------------

    1. Clone the repository for local development.
    2. Import database from heroku to localhost.
    3. Develop on localhost, commit, and push to heroku for staging.

### INITIAL SETUP

    git clone git@github.com:frc/REPOSITORY_NAME.git REPOSITORY_NAME
    cd REPOSITORY_NAME
    git remote add heroku git@heroku.com:HEROKU_APP_NAME.git
    mysqladmin -uroot create DATABASENAME
    composer install

#### Define your environment variables

    For example, put this to file ".env"
    (git ignored) in project root.

        CLEARDB_DATABASE_URL=mysql://root:@localhost/DATABASENAME
        WP_CACHE=true
        MEMCACHIER_SERVERS=localhost:11211

    In every terminal used to run the server:

        export `cat .env`

### POPULATING THE LOCAL DATABASE

    Dump CLEARDB to your local database
        heroku config:get CLEARDB_DATABASE_URL
        mysqldump -uxxx -pxxx -hxxx heroku_xxx >/tmp/DATABASENAME.sql ## see config above
        mysql -uroot DATABASENAME </tmp/DATABASENAME.sql

    Migrate database URLs
        php -S localhost:5999 -t util/Search-Replace-DB
        open http://localhost:5999/
        ## specify LOCAL database connection info
        ## replace HEROKUAPPURL with localhost:5000

    Flush the memcached
        killall memcached
        ## see "brew info memcached" for details

### STARTING THE LOCAL DEV ENVIRONMENT

    php -S localhost:5000 -t public

### DEPLOY CHANGES TO GITHUB

    Commit everything
    git pull -r
    <resolve conflicts>
    git push

### DEPLOY CHANGES LIVE

    git push heroku master
