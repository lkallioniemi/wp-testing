<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) || !WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
    status_header( 404 );
    exit;
}

// Delete all options
delete_site_option('wordpress_unsecure_auth_cookie_installed');