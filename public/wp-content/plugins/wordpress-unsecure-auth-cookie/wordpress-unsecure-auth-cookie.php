<?php
/**
Plugin Name: Wordpress Unsecure Auth Cookie
Plugin URI: https://github.com/jussikinnula/wordpress-unsecure-auth-cookie
Description: Plug-in to force cookies to be created without secure bit on them, for use of using same sessions in HTTP and HTTPS site.
Version: 0.3
Author: Jussi Kinnula / Frantic
Author URI: http://www.frantic.com
License: GPLv2
 */

/**
 * The wp_set_auth_cookie is directly copied from WordPress' pluggable.php -file,
 * and modified to not to set secure bit, if environment variable is set.
 */

if ( !function_exists('wp_set_auth_cookie') ) :
/**
 * Sets the authentication cookies based on user ID.
 *
 * The $remember parameter increases the time that the cookie will be kept. The
 * default the cookie is kept without remembering is two days. When $remember is
 * set, the cookies will be kept for 14 days or two weeks.
 *
 * @since 2.5.0
 *
 * @param int $user_id User ID
 * @param bool $remember Whether to remember the user
 * @param mixed $secure  Whether the admin cookies should only be sent over HTTPS.
 *                       Default is_ssl().
 */
function wp_set_auth_cookie($user_id, $remember = false, $secure = '') {
	if ( $remember ) {
		/**
		 * Filter the duration of the authentication cookie expiration period.
		 *
		 * @since 2.8.0
		 *
		 * @param int  $length   Duration of the expiration period in seconds.
		 * @param int  $user_id  User ID.
		 * @param bool $remember Whether to remember the user login. Default false.
		 */
		$expiration = time() + apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_id, $remember );

		/*
		 * Ensure the browser will continue to send the cookie after the expiration time is reached.
		 * Needed for the login grace period in wp_validate_auth_cookie().
		 */
		$expire = $expiration + ( 12 * HOUR_IN_SECONDS );
	} else {
		/** This filter is documented in wp-includes/pluggable.php */
		$expiration = time() + apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user_id, $remember );
		$expire = 0;
	}

	if ( '' === $secure ) {
		$secure = is_ssl();
	}

	// Frontend cookie is secure when the auth cookie is secure and the site's home URL is forced HTTPS.
	$secure_logged_in_cookie = $secure && 'https' === parse_url( get_option( 'home' ), PHP_URL_SCHEME );

	/**
	 * Filter whether the connection is secure.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $secure  Whether the connection is secure.
	 * @param int  $user_id User ID.
	 */
	$secure = apply_filters( 'secure_auth_cookie', $secure, $user_id );

	/**
	 * Filter whether to use a secure cookie when logged-in.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $secure_logged_in_cookie Whether to use a secure cookie when logged-in.
	 * @param int  $user_id                 User ID.
	 * @param bool $secure                  Whether the connection is secure.
	 */
	$secure_logged_in_cookie = apply_filters( 'secure_logged_in_cookie', $secure_logged_in_cookie, $user_id, $secure );

	if ( $secure ) {
		$auth_cookie_name = SECURE_AUTH_COOKIE;
		$scheme = 'secure_auth';
	} else {
		$auth_cookie_name = AUTH_COOKIE;
		$scheme = 'auth';
	}

	$manager = WP_Session_Tokens::get_instance( $user_id );
	$token = $manager->create( $expiration );

	$auth_cookie = wp_generate_auth_cookie( $user_id, $expiration, $scheme, $token );
	$logged_in_cookie = wp_generate_auth_cookie( $user_id, $expiration, 'logged_in', $token );

	/**
	 * Fires immediately before the authentication cookie is set.
	 *
	 * @since 2.5.0
	 *
	 * @param string $auth_cookie Authentication cookie.
	 * @param int    $expire      Login grace period in seconds. Default 43,200 seconds, or 12 hours.
	 * @param int    $expiration  Duration in seconds the authentication cookie should be valid.
	 *                            Default 1,209,600 seconds, or 14 days.
	 * @param int    $user_id     User ID.
	 * @param string $scheme      Authentication scheme. Values include 'auth', 'secure_auth', or 'logged_in'.
	 */
	do_action( 'set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme );

	/**
	 * Fires immediately before the secure authentication cookie is set.
	 *
	 * @since 2.6.0
	 *
	 * @param string $logged_in_cookie The logged-in cookie.
	 * @param int    $expire           Login grace period in seconds. Default 43,200 seconds, or 12 hours.
	 * @param int    $expiration       Duration in seconds the authentication cookie should be valid.
	 *                                 Default 1,209,600 seconds, or 14 days.
	 * @param int    $user_id          User ID.
	 * @param string $scheme           Authentication scheme. Default 'logged_in'.
	 */
	do_action( 'set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in' );

	//debug_logger("auth_cookie=".$auth_cookie." logged_in_cookie=".$logged_in_cookie." secure_logged_in_cookie=".$secure_logged_in_cookie." COOKIE_DOMAIN=".COOKIE_DOMAIN);

	setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, COOKIE_DOMAIN, false, true);
	setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, false, true);
	setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, COOKIE_DOMAIN, false, true);
	if ( COOKIEPATH != SITECOOKIEPATH )
		setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
}
endif;