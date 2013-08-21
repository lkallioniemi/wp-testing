<?php

define( 'WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'].'/wp-content' );
define( 'WP_CONTENT_URL', 'http://' . $_SERVER['HTTP_HOST'] .'/wp-content' );

define('WP_USE_THEMES', true);
require('./wordpress/wp-blog-header.php');