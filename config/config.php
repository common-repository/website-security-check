<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * The configuration file
 */
!defined('WSC_REQUEST_TIME') && define('WSC_REQUEST_TIME', microtime(true));
defined('_WSC_NONCE_ID_') || define('_WSC_NONCE_ID_', NONCE_KEY);

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ((int) @$version[0] * 1000 + (int) @$version[1] * 100 + ((isset($version[2])) ? ((int) $version[2] * 10) : 0)));
}
if (!defined('WP_VERSION_ID') && isset($wp_version)) {
    $version = explode('.', $wp_version);
    define('WP_VERSION_ID', ((int) @$version[0] * 1000 + (int) @$version[1] * 100 + ((isset($version[2])) ? ((int) $version[2] * 10) : 0)));
}else{
    !defined('WP_VERSION_ID') && define('WP_VERSION_ID', '3000');
}

if (!defined('WSC_VERSION_ID')) {
    $version = explode('.', WSC_VERSION);
    define('WSC_VERSION_ID', ((int) @$version[0] * 10000 + (int) @$version[1] * 1000 + ((isset($version[2])) ? ((int) $version[2] * 1) : 0)));
}

/* No path file? error ... */
require_once(dirname(__FILE__) . '/paths.php');

/* Define the record name in the Option and UserMeta tables */
define('WSC_OPTION', 'wsc_options');
