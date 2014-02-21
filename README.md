wordpress-on-heroku
====================

## Table of contents:

* [Project structure] [project-structure]
* [Prerequisites] [prerequisites]
	* [Brew modules] [brew-modules] (PHP, MEMCACHED, MYSQL)
	* [Heroku Toolbelt] [install-heroku]
	* [Github repo] [github-repo]
	* [Amazon S3 Bucket] [amazon-s3-bucket]
* [Creating a new project] [create-new-project]
* [Initial local setup] [local-setup]
	* [Create a local MySQL Database] [create-db]
	* [Setting up the local environment] [local-env]
* [Heroku setup] [heroku-setup]
	* [Create a new Heroku app] [create-heroku-app]
	* [Setting up WordPress] [wp-setup]
	* [Localization] [wp-lang]
	* [Deploying to existing Heroku app] [deploy-to-existing]
	* [Pushing to Heroku] [deploy-heroku]
		* [Pushing to staging enviroment] [deploy-staging] (dev)
* [Accessing local WordPress installation] [start-local-wp]
	* [Amazon S3 Setup] [amazon-setup]
		* [Settings] [amazon-settings]
		* [Transfer media assets] [copy-assets]
* [Database management] [db-management]
	* [Starting Search and Replace DB] [search-replace-db]
	* [Export to Heroku] [export-local-db]
	* [Import from Heroku] [import-heroku-db]
* [Import from legacy WordPress installation] [import-wordpress]
* [Workflow] [workflow]

## <a name="project-structure"></a>PROJECT STRUCTURE

```
├── config
│   ├── public                            # WordPress files are stored here
│   │   └── wp-content
│   │     ├── plugins
│   │     └── themes
│   └── vendor                            # Config files for vendored apps
│      ├── nginx
│      │   └── conf                       # nginx.conf + wordpress.conf.erb
│      └── php                            # php.ini
│          ├── Search-Replace-DB-master   # localhost:5999
│          └── etc                        # php-fpm.conf
├── .gitignore
└── README.md
```

## <a name="prerequisites"></a>PREREQUISITES

### <a name="brew-modules"></a>BREW MODULES (PHP, MEMCACHED, MYSQL)

Use brew install to install the following modules:

```sh
$ brew install php55 php55-memcache memcached mysql
```
See <https://github.com/josegonzalez/homebrew-php> for more information on how to install these with brew.

When the installation is done, start memcached and mysql as instructed.

### <a name="install-heroku"></a>HEROKU TOOLBELT

For instructions on how to set up Heroku, follow these steps: <https://toolbelt.heroku.com/>

### <a name="github-repo"></a>GITHUB REPOSITORY

Create a new repository for this project on [Github] [github-frc] If you don't have sufficent priviledges, ask a fellow coder for help.

### <a name="amazon-s3-bucket"></a>AMAZON S3 BUCKET

You will also need to create an [Amazon S3 Bucket] [amazon-s3-console] and get a separate **S3 client**. If you don't have sufficent priviledges to access the S3 Bucket, you know who to ask.

The client will be used to transfer over files and make sure WordPress is set up correctly. We recommend using [Transmit] (http://www.panic.com/transmit/), unless you prefer using the browser. You will need the *AWS key*, *secret* and *bucket name*.

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

Initialize the Github repository which [you created earlier] [github-repo]:

```sh
$ git init
$ git add -A
$ git commit -m 'Initial commit'
$ git remote add origin git@github.com:frc/PROJECTNAME.git
$ git push -u origin master
```

## <a name="local-setup"></a>INITIAL LOCAL SETUP

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
export `cat .env`
```

## <a name="heroku-setup"></a>HEROKU SETUP

Make sure you [installed everything you need] [install-heroku] before you proceed.

### <a name="create-heroku-app"></a>Create a new heroku app

Join the app on [Heroku] (https://www.heroku.com/) by logging in and accessing the [frc apps] (https://dashboard.heroku.com/orgs/frc/apps) directory. Once you've joined it, type:

```sh
$ heroku create --region eu --buildpack https://github.com/frc/heroku-buildpack-wordpress PROJECTNAME
```

Check Heroku and make sure all the addons are installed. If not, you will have to add them manually.

Example to add them manually from the command line:

```sh
$ heroku addons:add cleardb:ignite --app PROJECTNAME
```

For a full list, see <https://github.com/frc/heroku-buildpack-wordpress/blob/master/bin/release>

**Note**: If you run into performance issues or you're setting up a site that requires more processing power, you can upgrade  cleardb from `ignite` to  `drift` so long as you remember that this will start costing **$50 / month**.

#### <a name="create-heroku-app"></a>Create a heroku app for staging

This will be used to test any recent changes before pushing it to the live instance `heroku`

```sh
$ heroku create --region eu --buildpack https://github.com/frc/heroku-buildpack-wordpress PROJECTNAME-staging
```

You will need to check the addons again and make sure everything is there. If they didn't show up, see above for instructions on how to add them manually.

### <a name="wp-setup"></a>SETTING UP WORDPRESS

```sh
$ heroku config:set WORDPRESS_DIR=wordpress --app PROJECTNAME
$ heroku labs:enable user-env-compile --app PROJECTNAME
```

Go to `config/public` and edit the `wp-config.php` file:

* Generate new Authentication Unique keys and Salts
* Define a new Database Table prefix

### <a name="wp-lang"></a>Localization

To specify a different WordPress language (for example Finnish), type:

```sh
$ heroku config:add WORDPRESS_LANGUAGE=fi
```

You will also need to define the language in the `config/public/wp-config.php` file:

	define('WPLANG', 'fi');

### <a name="deploy-heroku"></a>Pushing to Heroku

```sh
$ heroku info
$ git remote add heroku HEROKUGITURL
$ git push heroku master
```

#### <a name="deploy-staging"></a>Pushing to staging environment

```sh
$ heroku info
$ git remote add dev HEROKUSTAGINGGITURL
$ git push dev master
```

Alternatively, if you've created the staging branch, you can also push it using this command:

```sh
$ git push dev staging
```

### <a name="deploy-to-existing"></a>Deploying to an existing Heroku app

If your site is already running on Heroku, you can set the remote and start pushing there:

```sh
$ git remote set-url heroku git@heroku.com:PROJECTNAME.git
$ git push heroku master
```

## <a name="local-wp"></a>ACCESSING WORDPRESS LOCALLY

Start the local WordPress server (from the root directory):

```sh
$ php -S localhost:5000 -t config/public
```

Check <http://localhost:5000/> and perform the basic installation for WordPress.

Save the *admin username* and *password* as a comment in `config/public/wp-config.php` and activate all necessary plugins.

### <a name="amazon-setup"></a>Amazon S3 Setup

Access your local WordPress installation and activate the [WPRO plugin] (https://github.com/alfreddatakillen/wpro) plugin (this should already be included in the buildpack).

Get access to the shared bucket `BUCKETNAME` and check the user policy:

```
User policy:
{
	"Version": "2014-02-21",
	"Statement": [
		{
			"Effect": "Allow",
			"Action": "s3:*",
			"Resource": "arn:aws:s3:::BUCKETNAME/PROJECTNAME/*"
		}
	]
}
```

#### <a name="amazon-settings"></a> Configure (Settings / WPRO Settings):

* Prepend all paths with folder: PROJECTNAME/staging
* AWS Key & AWS Secret: Get these from the [console] [amazon-s3-bucket]
* S3 Bucket: BUCKETNAME
* [x] Virtual hosting is enabled for this bucket.
* Bucket AWS Region: EU (Ireland) Region

Test by uploading new media, and make sure the URL contains `BUCKETNAME`.

### <a name="copy-existing-assets"></a>Upload pre-existing content

	Todo ...

## <a name="db-management"></a>DATABASE MANAGEMENT

	Todo ...

### <a name="search-replace-db"></a>Search Replace DB

This script was made to aid the process of migrating PHP and MySQL based websites. This is used to rewrite URLs stored in the database, including the ones in [PHP serialized format](http://php.net/manual/en/function.serialize.php).

Source: <https://github.com/interconnectit/Search-Replace-DB/>

Project location: `config/vendor/php`

#### Usage

Go to `config/vendor/php/Search-Replace-DB-master/` and start the server:

	$ php -S localhost:5999 -t .

Open <http://localhost:5999/> and fill in the fields as needed. Choose the `Dry run` button to do a test run without searching/replacing.

**Note**: Whenever we reference `http://localhost:5999/` you will need have this running in the background.

### <a name="export-local-db"></a>EXPORT Local database TO Heroku

	Todo ...

### <a name="import-heroku-db"></a>IMPORT database FROM Heroku

	Todo ...

## <a name="import-wordpress"></a>IMPORT FROM LEGACY WORDPRESS INSTALLATION

Create a new project, [see above] [create-new-project].

### Dump the database (at the old server HOSTNAME)

You will need to get access to the old server in order to be able to access the database.

	$ mysqldump -uxxx -pyyy DATABASENAME >~/DATABASENAME.sql

#### Import to localhost (at the `localhost`)

	$ scp HOSTNAME:DATABASENAME.sql /tmp/
	$ mysql -uroot DATABASENAME </tmp/DATABASENAME.sql

#### Alternative way

You can also use an Mac OS app like [Sequel] (http://www.sequelpro.com/) to save some time:

1. Create an account for the remote `HOSTNAME`
2. Access the remote database
3. **File** > **Export** and **save it** to your local drive with the default settings
4. Create another account for your local installation (assuming you've already created [a local database] [local-db])
5. Access the local database
6. **File** > **Import**

#### Migrate database URLs `LEGACYAPPURL` => `localhost:5000`

1. Start [Search Replace DB] (#search-replace-db) and open <http://localhost:5999/>
2. Specify local database connection info
3. Replace `LEGACYAPPURL` with `localhost:5000`

#### Transfer media assets to your local installation

```sh
$ scp -r HOSTNAME:/PATHTOWPISNTALLATION/wp-content/uploads config/public/wp-content/
```

#### Start the local WordPress server (from the root directory)

```sh
$ php -S localhost:5000 -t config/public
```

Check that everything is ok <http://localhost:5000/wp-admin/>

You will need to activate the [WPRO plugin] (https://github.com/alfreddatakillen/wpro) to make WordPress sync media assets to the S3 Bucket. See [Amazon S3 Setup] [amazon-setup] for information on how to set this up.

#### <a name="transfer-assets-to-s3"></a>Transfer assets to S3 Bucket

1. Copy all files from `wp-content/uploads` to the `S3 BUCKETNAME/PROJECTNAME/staging` directory. See [Amazon S3 Setup] [amazon-setup] for information on how to upload media assets to the bucket.
2. Start [Search Replace DB] [search-replace-db] and open <http://localhost:5999/>
3. Run MySQL command:

	```mysql
	mysql -uroot DATABASENAME -e 'select * from wp_options where option_name like "wpro-aws%"'
	```

4. Replace `localhost:5000/wp-content/uploads` with `BUCKETNAME/BUCKETPATH/staging`
5. Remove uploads from local storage and verify that all images, attachments etc. work correctly.

	```sh
	rm -rf config/public/wp-content/uploads
	```

## <a name="workflow"></a>WORKFLOW

Normal development cycle:

1. Clone the repository for local development ( see "[Getting Started] [getting-started]" )
2. [Import database] [import-heroku-db] from Heroku to localhost
3. Develop on localhost, commit, and [push to Heroku] [deploy-heroku] for staging

[getting-started]:#getting-started
[project-structure]:#project-structure
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
[wp-lang]:#wp-lang
[start-local-wp]:#start-local-wp
[amazon-setup]:#amazon-setup
[amazon-settings]:#amazon-settings
[copy-assets]:#copy-assets
[copy-existing-assets]:#copy-existing-assets
[heroku-setup]:#heroku-setup
[deploy-to-existing]:#deploy-to-existing
[create-heroku-app]:#create-heroku-app
[deploy-heroku]:#deploy-heroku
[deploy-staging]:#deploy-staging
[db-management]:#db-management
[search-replace-db]:#search-replace-db
[import-heroku-db]:#import-heroku-db
[export-local-db]:#export-local-db
[import-wordpress]:#import-wordpress
[transfer-assets-to-s3]:#upload-s3
[workflow]:#workflow
