# wordpress-unsecure-auth-cookie

Plug-in to disable Wordpress secure auth cookie, if the same auth cookie is wanted to be valid for both HTTP and HTTPS side of a website.

## Usage

Install the plug-in regularly, and it sets all cookies without secure bit.

In order for the same cookie name to be present on both HTTP and HTTPS sites, you must set siteurl to be the same for both HTTP and HTTPS sites. That can be done by using Wordpress admin, or by setting WP_SITE_URL on wp-config.php.

You can ease it up by adding the following lines to your wp-config.php, which just sets the COOKIEHASH based on $_SERVER["HTTP_HOST"] - if FORCE_UNSECURE_AUTH_COOKIE environment variable is set true:

```
    /**
     * If wordpress-unsecure-auth-cookie plug-in is enabled, we'll set COOKIEHASH without http:// or https:// in the beginning.
     */
     define('FORCE_UNSECURE_AUTH_COOKIE', getenv('FORCE_UNSECURE_AUTH_COOKIE') == 'true');
    if (defined('FORCE_UNSECURE_AUTH_COOKIE')) {
        define('COOKIEHASH', md5( $_SERVER["HTTP_HOST"] ) );
    }
```

That's it!