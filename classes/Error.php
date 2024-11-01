<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class WSC_Classes_Error extends WSC_Classes_FrontController {

    /** @var array */
    private static $errors = array();

    /**
     * The error controller for StarBox
     */
    function __construct() {
        parent::__construct();

        if (!defined('ABSPATH'))
            self::setError(__('The home directory is not set!', _WSC_PLUGIN_NAME_), 'fatal');

        /* Check the PHP version */
        if (PHP_VERSION_ID < 5300) {
            self::setError(__('The PHP version has to be greater than 5.13', _WSC_PLUGIN_NAME_), 'fatal');
        }
    }

    /**
     * Show the error in wrodpress
     *
     * @param string $error
     * @param string $type
     * @param null $index
     */
    public static function setError($error = '', $type = 'notice', $index = null) {
        if (!isset($index)) {
            $index = count(self::$errors);
        }

        self::$errors[$index] = array(
            'type' => $type,
            'text' => $error);
    }

    /**
     * This hook will show the error in WP header
     */
    public function hookNotices() {
        WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController')->loadMedia('alert');

        if (is_array(self::$errors) &&
            ((is_string(WSC_Classes_Tools::getValue('page', '')) && stripos(WSC_Classes_Tools::getValue('page', ''), _WSC_NAMESPACE_) !== false) ||
                (is_string(WSC_Classes_Tools::getValue('plugin', '')) && stripos(WSC_Classes_Tools::getValue('plugin', ''), _WSC_PLUGIN_NAME_) !== false))
        ) {
            foreach (self::$errors as $error) {

                switch ($error['type']) {
                    case 'fatal':
                        self::showError(ucfirst(_WSC_PLUGIN_NAME_ . " " . $error['type']) . ': ' . $error['text'], $error['type']);
                        die();
                        break;
                    default:
                        self::showError($error['text'], $error['type']);
                }
            }
        }
        self::$errors = array();
    }

    /**
     * Show the notices to WP
     *
     * @param $message
     * @param string $type
     */
    public static function showError($message, $type = '') {
        if (file_exists(_WSC_THEME_DIR_ . 'Notices.php')) {
            include(_WSC_THEME_DIR_ . 'Notices.php');
        } else {
            echo $message;
        }
    }

}