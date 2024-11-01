<?php
defined('ABSPATH') || die('Cheatin\' uh?');

$currentDir = dirname(__FILE__);

define('_WSC_NAMESPACE_', 'WSC');
define('_WSC_PLUGIN_NAME_', 'website-security-check');
define('_WSC_THEME_NAME_', 'default');
defined('_WSC_SUPPORT_SITE_') || define('_WSC_SUPPORT_SITE_', 'https://wpplugins.tips');
defined('_WSC_ACCOUNT_SITE_') || define('_WSC_ACCOUNT_SITE_', 'https://account.wpplugins.tips');
defined('_WSC_API_SITE_') || define('_WSC_API_SITE_', _WSC_ACCOUNT_SITE_);
define('_WSC_SUPPORT_EMAIL_', 'contact@wpplugins.tips');

/* Directories */
define('_WSC_ROOT_DIR_', realpath($currentDir . '/..'));
define('_WSC_CLASSES_DIR_', _WSC_ROOT_DIR_ . '/classes/');
define('_WSC_CONTROLLER_DIR_', _WSC_ROOT_DIR_ . '/controllers/');
define('_WSC_MODEL_DIR_', _WSC_ROOT_DIR_ . '/models/');
define('_WSC_TRANSLATIONS_DIR_', _WSC_ROOT_DIR_ . '/languages/');
define('_WSC_THEME_DIR_', _WSC_ROOT_DIR_ . '/view/');

/* URLS */
define('_WSC_URL_', plugins_url() . '/' . _WSC_PLUGIN_NAME_);
define('_WSC_THEME_URL_', _WSC_URL_ . '/view/');