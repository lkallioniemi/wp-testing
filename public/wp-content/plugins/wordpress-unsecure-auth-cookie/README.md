# wordpress-unsecure-auth-cookie

Plug-in to disable Wordpress secure auth cookie, if the same auth cookie is wanted to be valid for both HTTP and HTTPS side of a website.

## Usage

Install the plug-in regularly, and it sets all cookies without secure bit.

To enable the plug-in, you need to also add the following lines to your wp-config.php, so that COOKIEHASH matches on both HTTP and HTTPS sides.

```
    define('FORCE_UNSECURE_AUTH_COOKIE', getenv('FORCE_UNSECURE_AUTH_COOKIE') == 'true');
    if (defined('FORCE_UNSECURE_AUTH_COOKIE')) {
        define('COOKIEHASH', md5( $_SERVER['HTTP_HOST'] ) );
    }
```

That's it!