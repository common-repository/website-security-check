<?php
/*
  Copyright (c) 2016 - 2020, WPPlugins.
  The copyrights to the software code in this file are licensed under the (revised) BSD open source license.

  Plugin Name: Website Security Check
  Plugin URI: website-security-check
  Description: Detect if your WordPress site has Vulnerabilities and Security flaws
  Version: 1.2.00
  Author: WPPlugins - WordPress Security Plugins
  Author URI: https://wpplugins.tips
  License: GPLv2 or later
  License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
  Text Domain: hide-my-wp
  Domain Path: /languages
 */
if ( ! defined( 'WSC_VERSION' ) ) {
	define( 'WSC_VERSION', '1.2.00' );
	/* Call config files */
	require( dirname( __FILE__ ) . '/debug/index.php' );
	require( dirname( __FILE__ ) . '/config/config.php' );

	/* inport main classes */
	require_once( _WSC_CLASSES_DIR_ . 'ObjController.php' );
	WSC_Classes_ObjController::getClass( 'WSC_Classes_FrontController' );

}