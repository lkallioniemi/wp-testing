export CLEARDB_DATABASE_URL=mysql://root:@localhost/DATABASENAME
export WP_CACHE=true
export MEMCACHIER_SERVERS=localhost:11211
php -S ${1:-localhost:5001} -t config/public
