wordpress-on-heroku
====================

## Table of contents:

* [Getting started][getting-started]
	* [Project structure][project-structure]
	* [Pre-installed plugins][wordpress-plugins]
* [Prerequisites][prerequisites]
	* [Brew modules][brew-modules] (PHP, MEMCACHED, MYSQL)
	* [Heroku Toolbelt][install-heroku]
	* [Github repo][github-repo]
	* [Amazon S3 Bucket][amazon-s3-bucket]
* [Workflow][workflow]
	* [Installation][workflow-installation]
	* [Normal development cycle][workflow-development]
* [Creating a new project][create-new-project]
* [Initial local setup][local-setup]
	* [Create a local MySQL Database][create-db]
	* [Setting up the local environment][local-env]
* [Heroku setup][heroku-setup]
	* [Create a new Heroku app][create-heroku-app] (master)
	* [Create a new Heroku app][create-staging-app] (staging)
* [Setting up WordPress][wp-setup]
	* [Configuration][wp-config] (wp-config.php)
	* [Localization][wp-lang]
	* [Install a theme][wordpress-theme]
	* [Linking to existing Heroku app][link-to-existing-heroku-app]
	* [Pushing to Heroku][deploy-heroku] (master)
	* [Pushing to staging enviroment][deploy-staging] (staging)
* [Accessing local WordPress installation][start-local-wp]
	* [Amazon S3 Setup][amazon-setup]
		* [Settings][amazon-settings]
		* [Transfer media assets][copy-assets]
* [Database management][db-management]
	* [Starting Search and Replace DB][search-replace-db]
	* [Export to Heroku][export-local-db]
	* [Import from Heroku][import-heroku-db]
* [Import from legacy WordPress installation][import-wordpress]

## <a name="getting-started"></a>GETTING STARTED

### <a name="project-structure"></a>Project structure

```
├── public                     # WordPress files are stored here
│   └── wp-content
│     ├── plugins
│     └── themes
├── util                       # Utils
    └── Search-Replace-DB       # localhost:5999
├── .gitignore
└── README.md
```

### <a name="wordpress-plugins"></a>Pre-installed plugins

The repository comes bundled with the following WordPress plugins:

* Akismet : <http://wordpress.org/plugins/akismet/>               # Spam prevention
* Batcache : <http://wordpress.org/plugins/batcache/>             # Page caching (uses MemCachier)
* MemCachier : <http://wordpress.org/extend/plugins/memcached/>   # WordPress object cache
* WP Read-Only : <http://wordpress.org/plugins/wpro/>             # Stores files on Amazon S3

## <a name="prerequisites"></a>PREREQUISITES

### <a name="brew-modules"></a>Brew modules (PHP, MEMCACHED, MYSQL)

Use brew install to install the following modules:

```sh
$ brew install php55 php55-memcache memcached mysql libevent
```
See <https://github.com/josegonzalez/homebrew-php> for more information on how to install these with brew.

When the installation is done, start memcached and mysql as instructed. If you already have them installed, make sure you've got the following setps covered:

#### Memcached

To have launchd start `memcached` at login:

```sh
ln -sfv /usr/local/opt/memcached/*.plist ~/Library/LaunchAgents
```

Then to load `memcached` now:

```sh
launchctl load ~/Library/LaunchAgents/homebrew.mxcl.memcached.plist
```

Or, if you don't want/need `launchctl`, you can just run:

```sh
/usr/local/opt/memcached/bin/memcached
```

#### MySQL

	Todo ...

### <a name="install-heroku"></a>Heroku Toolbelt

For instructions on how to set up Heroku, follow these steps: <https://toolbelt.heroku.com/>

### <a name="github-repo"></a>Github repository

Create a new repository for this project on [Github][github-frc]. If you don't have sufficent priviledges, ask a fellow coder for help.

### <a name="amazon-s3-bucket"></a>Amazon S3 Bucket

You will also need to create an [Amazon S3 Bucket][amazon-s3-console] and get a separate **S3 client**. If you don't have sufficent priviledges to access the S3 Bucket, you know who to ask.

The client will be used to transfer over files and make sure WordPress is set up correctly. We recommend using [Transmit](http://www.panic.com/transmit/), unless you prefer using the browser. You will need the *AWS key*, *secret* and *bucket name*.

## <a name="workflow"></a>WORKFLOW

### <a name="workflow-installation"></a>Installation:

1. Clone the repository
2. Remove `.git`
3. Create and initialize new Github repositry
4. Create a new Amazon S3 Bucket
5. Create a local database
6. [Install a theme][wordpress-theme]
7. Set everything up on localhost, commit, and [push to Github][deploy-github]
8. [Export local database to Heroku][export-local-db]
9. Push to [master][deploy-heroku] and [staging][deploy-staging]

See [creating a new project][create-new-project] for details.

### <a name="workflow-development"></a>Normal development cycle:

1. Clone the repository for local development
2. [Import database][import-heroku-db] from Heroku to localhost
3. <a href="#search-replace-db">Search & replace</a> `HEROKUALLURL` with `localhost:5000`
3. Develop on localhost, commit, and [push to Github][deploy-github]
4. Push to staging [pushing to the staging environment][deploy-staging] and check that everything is ok
5. [Push to master][deploy-heroku]

## <a name="create-new-project"></a>CREATING A NEW PROJECT

Clone the project:

```sh
$ git clone git@github.com:frc/wordpress-on-heroku.git PROJECTNAME
$ cd PROJECTNAME
```

Remove the existing .git repository so you don't accidentally push back to the source:

```sh
$ rm -rf .git
```

Initialize the Github repository which [you created earlier][github-repo]:

```sh
$ git init
$ git add -A
$ git commit -m 'Initial commit'
$ git remote add origin git@github.com:frc/PROJECTNAME.git
$ git push -u origin master
```

## <a name="local-setup"></a>INITIAL LOCAL SETUP

### Install WordPress to public/wordpress directory

```sh
$ curl http://wordpress.org/latest.tar.gz | tar xz -C public
```

### <a name="create-db"></a>Create a local MySQL database

```sh
$ mysqladmin -uroot create DATABASENAME
```

### <a name="local-env"></a>Setting up the local environment

Define your environment variables in a file called `.env` (this will be ignored Git):

	CLEARDB_DATABASE_URL=mysql://root:@localhost/DATABASENAME
	WP_CACHE=true
	MEMCACHIER_SERVERS=localhost:11211

In every terminal used to run the server, run:

```sh
$ export `cat .env`
```

## <a name="heroku-setup"></a>HEROKU SETUP

Make sure you [installed everything you need][install-heroku] before you proceed.

### <a name="create-heroku-app"></a>Create a new heroku app (production)

APPNAME should be the site name written together, lowercase and without "www". For example, www.example.com should be examplecom.
```sh
heroku create --region eu APPNAME
```

Transfer the app's ownership from your *personal account* to *frc* on [Heroku](https://www.heroku.com/) or join the app by accessing the [frc apps](https://dashboard.heroku.com/orgs/frc/apps) directory.

```sh
cd APPNAME
heroku addons:add cleardb:ignite
heroku addons:add sendgrid:starter
heroku addons:add memcachier:dev
heroku addons:add scheduler:standard
heroku addons:add papertrail:choklad
```

**Note**: If you run into performance issues or you're setting up a site that requires more processing power, you can upgrade  cleardb from `ignite` to  `drift` so long as you remember that this will start costing **$50 / month**.

### Ignore useless log entries

Set Papertrail to ignore
```
(HTTP\/1\.1\" |status=)(200|304)
```
so that it doesn't fill up. This is in [Heroku Dashboard](https://dashboard.heroku.com/) -> APP -> Resources -> Papertrail -> "Filter Unnecessary Logs".

### <a name="create-staging-app"></a>Create a new heroku app (staging)

```sh
$ heroku create --region eu --buildpack APPNAME-staging
```

Join the app on [Heroku](https://www.heroku.com/) by logging in and accessing the [frc apps](https://dashboard.heroku.com/orgs/frc/apps) directory.
Add similar addons as for production, if needed.

**Note**: Since this is a staging environment, the database should be set to `ignite` to avoid any unnecessary costs.

## <a name="wp-setup"></a>SETTING UP WORDPRESS

WordPress will be unpacked to location "public/wordpress" every time you push to Heroku. By having it in a separate directory - as opposed to unpacking it to `public/` - we avoid any possible conflicts with our own files.

### <a name="wp-config"></a>wp-config.php

Go to `public` and edit the `wp-config.php` file:

* Generate new Authentication Unique keys and Salts
* Define a new Database Table prefix

### <a name="wp-lang"></a>Localization

To specify a different WordPress language (for example Finnish), simply define the language in the `public/wp-config.php` file:

	define('WPLANG', 'fi');

### <a name="wordpress-theme"></a>Install a theme

Frantic has its own [WordPress theme][frantic-wp-theme] located in another repository. If you choose to use this theme, clone it to your `public/wp-content/themes/` folder and do the following:

* Remove the `.git` folder from the theme to avoid pushing to the wrong repository
* Remove `.gitignore` from the theme folder and use the one in the root folder instead.

### <a name="link-to-existing-heroku-app"></a>Linking to an existing Heroku app

If your site is already running on Heroku, you can set the remote and start pushing there:

```sh
$ git remote set-url heroku git@heroku.com:APPNAME.git
$ git push heroku master
```

### <a name="deploy-heroku"></a>Pushing to Heroku

Setting up the remote (you only need to do this once):

```sh
$ heroku info
$ git remote add heroku HEROKUGITURL
```

Pushing:

```sh
$ git push # always push to Github first
$ git push heroku master
```

### <a name="deploy-staging"></a>Pushing to staging environment

Setting up the remote (you only need to do this once):

```sh
$ heroku info
$ git remote add dev HEROKUSTAGINGGITURL
```

Pushing:

```sh
$ git push # always push to Github first
$ git push dev master
```

## <a name="local-wp"></a>ACCESSING WORDPRESS LOCALLY

Start the local WordPress server (from the root directory):

```sh
$ php -S localhost:5000 -t public
```

Check <http://localhost:5000/> and perform the basic installation for WordPress.

Save the *admin username* and *password* as a comment in `public/wp-config.php` and activate all necessary plugins.

### <a name="amazon-setup"></a>Amazon S3 Setup

Access your local WordPress installation and activate the [WPRO plugin](https://github.com/alfreddatakillen/wpro) plugin (this should already be included in the buildpack).

Get access to the shared bucket `BUCKETNAME` and check the user policy:

```
User policy:
{
	"Version": "2014-02-21",
	"Statement": [
		{
			"Effect": "Allow",
			"Action": "s3:*",
			"Resource": "arn:aws:s3:::BUCKETNAME/APPNAME/*"
		}
	]
}
```

#### <a name="amazon-settings"></a> Configure (Settings / WPRO Settings):

* Prepend all paths with folder: APPNAME/staging
* AWS Key & AWS Secret: Get these from the [console][amazon-s3-bucket]
* S3 Bucket: BUCKETNAME
* [ ] Virtual hosting is enabled for this bucket.
* Bucket AWS Region: EU (Ireland) Region

Test by uploading new media, and make sure the URL contains `BUCKETNAME`

### <a name="copy-existing-assets"></a>Upload pre-existing content

Copy all files from `wp-content/uploads` to the `S3 BUCKETNAME/APPNAME/`

## <a name="db-management"></a>DATABASE MANAGEMENT

### <a name="search-replace-db"></a>Search Replace DB

This script was made to aid the process of migrating PHP and MySQL based websites. This is used to rewrite URLs stored in the database, including the ones in [PHP serialized format](http://php.net/manual/en/function.serialize.php).

Source: <https://github.com/interconnectit/Search-Replace-DB/>

Location: `util/Search-Replace-DB/`

#### Usage

Start the server from the root folder:

```sh
$ php -S localhost:5999 -t util/Search-Replace-DB/
```

Open <http://localhost:5999/> and fill in the fields as needed. Choose the `Dry run` button to do a test run without searching/replacing.

**Note**: Whenever we reference `http://localhost:5999/` you will need have this running in the background.

### <a name="export-local-db"></a>Export local database to Heroku

1. Dump local database to CLEARDB

	If there's content in CLEARDB database, take a backup:
	* Go to <https://dashboard.heroku.com/orgs/frc/apps>
	* Click your `APPNAME`
	* Click "ClearDB MySQL Database"
	* Click the correct database (check from heroku config)
	* Open Backups & Jobs tab
	* Click "Create a backup now"

	Then make the actual dump:

	```sh
	$ heroku config:get CLEARDB_DATABASE_URL
	$ mysqldump -uroot DATABASENAME | mysql -uxxx -pxxx -hxxx heroku_xxx ## see config above
	```

2. Migrate database URLs
	* Start [Search Replace DB](#search-replace-db) and open <http://localhost:5999/>
	* Specify **CLEARDB** database connection info
	* Replace `localhost:5000` with `HEROKUAPPURL`

	To get the `HEROKUAPPURL`, type:

	```sh
	$ heroku info
	```

3. Flush the `memcached`

	```sh
	$ heroku addons:open memcachier
	```

### <a name="import-heroku-db"></a>Import database from Heroku

1. Dump CLEARDB to your local database

	```sh
	$ heroku config:get CLEARDB_DATABASE_URL
	$ mysqldump -uxxx -pxxx -hxxx heroku_xxx | mysql -uroot DATABASENAME
	```

2. Migrate database URLs
	* Start [Search Replace DB](#search-replace-db) and open <http://localhost:5999/>
	* Specify **LOCAL** database connection info
	* Replace `HEROKUAPPURL` with `localhost:5000`

3. Flush the local `memcached`

	See `$ brew info memcached` for details.

### <a name="import-wordpress"></a>IMPORT FROM LEGACY WP INSTALLATION

Create a new project, [see above][create-new-project].

### Dump the database (at the old server HOSTNAME)

You will need to get access to the old server in order to be able to access the database.

```sh
$ mysqldump -uxxx -pyyy DATABASENAME >~/DATABASENAME.sql
```

#### Import to localhost (at the `localhost`)

```sh
$ scp HOSTNAME:DATABASENAME.sql /tmp/
$ mysql -uroot DATABASENAME </tmp/DATABASENAME.sql
```

#### Alternative way

You can also use an Mac OS app like [Sequel](http://www.sequelpro.com/) to save some time:

1. Create an account for the remote `HOSTNAME`
2. Access the remote database
3. **File** > **Export** and **save it** to your local drive with the default settings
4. Create another account for your local installation (assuming you've already created [a local database][local-db])
5. Access the local database
6. **File** > **Import**

#### Migrate database URLs `LEGACYAPPURL` => `localhost:5000`

1. Start [Search Replace DB](#search-replace-db) and open <http://localhost:5999/>
2. Specify local database connection info
3. Replace `LEGACYAPPURL` with `localhost:5000`

#### Transfer media assets to your local installation

```sh
$ scp -r HOSTNAME:/PATHTOWPISNTALLATION/wp-content/uploads public/wp-content/
```

#### Start the local WordPress server (from the root directory)

```sh
$ php -S localhost:5000 -t public
```

Check that everything is ok <http://localhost:5000/wp-admin/>

You will need to activate the [WPRO plugin](https://github.com/alfreddatakillen/wpro) to make WordPress sync media assets to the S3 Bucket. See [Amazon S3 Setup][amazon-setup] for information on how to set this up.

#### <a name="transfer-assets-to-s3"></a>Transfer assets to S3 Bucket

1. Copy all files from `wp-content/uploads` to the `S3 BUCKETNAME/APPNAME/staging` directory. See [Amazon S3 Setup][amazon-setup] for information on how to upload media assets to the bucket.
2. Start [Search Replace DB][search-replace-db] and open <http://localhost:5999/>
3. Run MySQL command:

	```sh
	$ mysql -uroot DATABASENAME -e 'select * from wp_options where option_name like "wpro-aws%"'
	```

4. Replace `localhost:5000/wp-content/uploads` with `BUCKETNAME/BUCKETPATH/staging`
5. Remove uploads from local storage and verify that all images, attachments etc. work correctly.

	```sh
	$ rm -rf public/wp-content/uploads
	```

[getting-started]:#getting-started
[project-structure]:#project-structure
[wordpress-plugins]:#wordpress-plugins
[workflow]:#workflow
[workflow-installation]:#workflow-installation
[workflow-development]:#workflow-development
[wordpress-theme]:#wordpress-theme
[frantic-wp-theme]:https://github.com/frc/Frantic-WP-theme
[prerequisites]:#prerequisites
[amazon-s3-bucket]:#amazon-s3-bucket
[brew-modules]:#brew-modules
[install-heroku]:#install-heroku
[github-repo]:#github-repo
[github-frc]:https://github.com/frc/
[amazon-s3-console]:http://aws.amazon.com/console/
[create-new-project]:#create-new-project
[local-setup]:#local-setup
[create-db]:#create-db
[local-env]:#local-env
[wp-setup]:#wp-setup
[wp-config]:#wp-config
[wp-lang]:#wp-lang
[start-local-wp]:#start-local-wp
[amazon-setup]:#amazon-setup
[amazon-settings]:#amazon-settings
[copy-assets]:#copy-assets
[copy-existing-assets]:#copy-existing-assets
[heroku-setup]:#heroku-setup
[create-heroku-app]:#create-heroku-app
[create-staging-app]:#create-staging-app
[link-to-existing-heroku-app]:#link-to-existing-heroku-app
[deploy-github]:#deploy-github
[deploy-heroku]:#deploy-heroku
[deploy-staging]:#deploy-staging
[db-management]:#db-management
[search-replace-db]:#search-replace-db
[import-heroku-db]:#import-heroku-db
[export-local-db]:#export-local-db
[import-wordpress]:#import-wordpress
[transfer-assets-to-s3]:#upload-s3
[workflow]:#workflow
