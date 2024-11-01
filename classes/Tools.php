<?php
defined('ABSPATH') || die('Cheatin\' uh?');

/**
 * Handles the parameters and url
 *
 * @author StarBox
 */
class WSC_Classes_Tools extends WSC_Classes_FrontController {

    /** @var array Saved options in database */
    public static $init = array(), $default = array(), $lite = array(), $ninja = array();
    public static $options = array();
    public static $debug = array();
    public static $is_multisite;
    public static $active_plugins;

    /** @var integer Count the errors in site */
    static $errors_count = 0;

    public function __construct() {
        //Check the max memory usage
        $maxmemory = self::getMaxMemory();
        if ($maxmemory && $maxmemory < 60) {
            if (defined('WP_MAX_MEMORY_LIMIT') && (int)WP_MAX_MEMORY_LIMIT > 60) {
                @ini_set('memory_limit', apply_filters('admin_memory_limit', WP_MAX_MEMORY_LIMIT));
                $maxmemory = self::getMaxMemory();
                if ($maxmemory && $maxmemory < 60) {
                    define('WSC_DISABLE', true);
                    WSC_Classes_Error::setError(sprintf(__('Your memory limit is %sM. You need at least %sM to prevent loading errors in frontend. See: %sIncreasing memory allocated to PHP%s', _WSC_PLUGIN_NAME_), $maxmemory, 64, '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>'));
                }
            } else {
                define('WSC_DISABLE', true);
                WSC_Classes_Error::setError(sprintf(__('Your memory limit is %sM. You need at least %sM to prevent loading errors in frontend. See: %sIncreasing memory allocated to PHP%s', _WSC_PLUGIN_NAME_), $maxmemory, 64, '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">', '</a>'));
            }
        }
        //Get the plugin options from database
        self::$options = self::getOptions();

        //Load multilanguage
        add_filter("init", array($this, 'loadMultilanguage'));

        //add review link in plugin list
        add_filter("plugin_row_meta", array($this, 'hookExtraLinks'), 10, 4);

        //add setting link in plugin
        add_filter('plugin_action_links', array($this, 'hookActionlink'), 5, 2);
    }

    /**
     * Check the memory and make sure it's enough
     * @return bool|string
     */
    public static function getMaxMemory() {
        try {
            $memory_limit = @ini_get('memory_limit');
            if ((int)$memory_limit > 0) {
                if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
                    if ($matches[2] == 'G') {
                        $memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnM -> nnn MB
                    } elseif ($matches[2] == 'M') {
                        $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                    } elseif ($matches[2] == 'K') {
                        $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                    }
                }

                if ((int)$memory_limit > 0) {
                    return number_format((int)$memory_limit / 1024 / 1024, 0, '', '');
                }
            }
        } catch (Exception $e) {
        }

        return false;

    }

    /**
     * Load the Options from user option table in DB
     *
     * @return array|mixed|object
     */
    public static function getOptions() {
        $keymeta = WSC_OPTION;
        $uploads = (defined('UPLOADS') ? UPLOADS : 'uploads');

        self::$init = array(
            'wsc_ver' => 0,
            'wsc_security_alert' => 1,
            'api_token' => false,
            'wsc_token' => '',
            'wsc_upload_url' => $uploads
        );

        if (is_multisite() && defined('BLOG_ID_CURRENT_SITE')) {
            $options = json_decode(get_blog_option(BLOG_ID_CURRENT_SITE, $keymeta), true);
        } else {
            $options = json_decode(get_option($keymeta), true);
        }

        if (is_array($options)) {
            $options = @array_merge(self::$init,  $options);
        } else {
            $options = self::$init;
        }

        return $options;
    }

    /**
     * Get the option from database
     * @param $key
     * @return mixed
     */
    public static function getOption($key) {
        if (!isset(self::$options[$key])) {
            self::$options = self::getOptions();

            if (!isset(self::$options[$key])) {
                self::$options[$key] = 0;
            }
        }

        return self::$options[$key];
    }

    /**
     * Save the Options in user option table in DB
     *
     * @param string $key
     * @param string $value
     *
     */
    public static function saveOptions($key = null, $value = '') {
        $keymeta = WSC_OPTION;

        if (isset($key)) {
            self::$options[$key] = $value;
        }

        if (is_multisite() && defined('BLOG_ID_CURRENT_SITE')) {
            update_blog_option(BLOG_ID_CURRENT_SITE, $keymeta, json_encode(self::$options));
        } else {
            update_option($keymeta, json_encode(self::$options));
        }
    }


    /**
     * Adds extra links to plugin  page
     *
     * @param $meta
     * @param $file
     * @param $data
     * @param $status
     * @return array
     */
    public function hookExtraLinks($meta, $file, $data = null, $status = null) {
        if ($file == _WSC_PLUGIN_NAME_ . '/index.php') {
            echo '<style>
                .ml-stars{display:inline-block;color:#ffb900;position:relative;top:3px}
                .ml-stars svg{fill:#ffb900}
                .ml-stars svg:hover{fill:#ffb900}
                .ml-stars svg:hover ~ svg{fill:none}
            </style>';

            $meta[] = "<a href='https://hidemywpghost.com/knowledge-base/' target='_blank'>" . __('Documentation', _WSC_PLUGIN_NAME_) . "</a>";
            $meta[] = "<a href='https://wordpress.org/support/plugin/website-security-check/reviews/#new-post' target='_blank' title='" . __('Leave a review', _WSC_PLUGIN_NAME_) . "'><i class='ml-stars'><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg><svg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg></i></a>";
        }
        return $meta;
    }


    /**
     * Add a link to settings in the plugin list
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function hookActionlink($links, $file) {
        if ($file == _WSC_PLUGIN_NAME_ . '/index.php') {
            $link = '<a href="' . admin_url('admin.php?page=wsc_securitycheck') . '" title="Website Security Check">' . __('Security Check', _WSC_PLUGIN_NAME_) . '</a>';
            array_unshift($links, $link);
        }

        return $links;
    }

    /**
     * Load the multilanguage support from .mo
     */
    public static function loadMultilanguage() {
        if (!defined('WP_PLUGIN_DIR')) {
            load_plugin_textdomain(_WSC_PLUGIN_NAME_, _WSC_PLUGIN_NAME_ . '/languages/');
        } else {
            load_plugin_textdomain(_WSC_PLUGIN_NAME_, null, _WSC_PLUGIN_NAME_ . '/languages/');
        }
    }

    /**
     * Check if it's Ajax call
     * @return bool
     */
    public static function isAjax() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return true;
        }
        return false;
    }

    /**
     * Change the paths in admin and for logged users
     * @return bool
     */
    public static function doChangesAdmin() {
        if (function_exists('is_user_logged_in') && function_exists('current_user_can')) {
            if (!is_admin() && !is_network_admin()) {
                if (WSC_Classes_Tools::getOption('wsc_hide_loggedusers') || !is_user_logged_in()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the plugin settings URL
     * @param string $page
     * @return string
     */
    public static function getSettingsUrl($page = 'wsc_securitycheck') {
        if (!is_multisite()) {
            return admin_url('admin.php?page=' . $page);
        } else {
            return network_admin_url('admin.php?page=' . $page);
        }
    }

    /**
     * Set the header type
     * @param string $type
     */
    public static function setHeader($type) {
        switch ($type) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'text':
                header("Content-type: text/plain");
                break;
        }
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     *
     * @param string $key Value key
     * @param boolean $keep_newlines Keep the new lines in variable in case of texareas
     * @param mixed $defaultValue (optional)
     * @return mixed Value
     */
    public static function getValue($key = null, $defaultValue = false, $keep_newlines = false) {
        if (!isset($key) || $key == '') {
            return false;
        }

        $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

        if (is_string($ret) === true) {
            if ($keep_newlines === false) {
                if (in_array($key, array('wsc_email_address', 'wsc_email'))) { //validate email address
                    $ret = preg_replace('/[^A-Za-z0-9-_\.\#\/\*\@]/', '', $ret);
                } elseif (in_array($key, array('wsc_disable_name'))) { //validate url parameter
                    $ret = preg_replace('/[^A-Za-z0-9-_]/', '', $ret);
                } else {
                    $ret = preg_replace('/[^A-Za-z0-9-_\/\.]/', '', $ret); //validate fields
                }
                $ret = sanitize_text_field($ret);
            } else {
                $ret = preg_replace('/[^A-Za-z0-9-_.\#\n\r\s\/\* ]\@/', '', $ret);
                if (function_exists('sanitize_textarea_field')) {
                    $ret = sanitize_textarea_field($ret);
                }
            }
        }

        return wp_unslash($ret);
    }

    /**
     * Check if the parameter is set
     *
     * @param string $key
     * @return boolean
     */
    public static function getIsset($key = null) {
        if (!isset($key) || $key == '') {
            return false;
        }

        return isset($_POST[$key]) ? true : (isset($_GET[$key]) ? true : false);
    }

    /**
     * Show the notices to WP
     *
     * @param $message
     * @param string $type
     * @return string
     */
    public static function showNotices($message, $type = '') {
        if (file_exists(_WSC_THEME_DIR_ . 'Notices.php')) {
            ob_start();
            include(_WSC_THEME_DIR_ . 'Notices.php');
            $message = ob_get_contents();
            ob_end_clean();
        }

        return $message;
    }

    /**
     * Connect remote with wp_remote_get
     *
     * @param $url
     * @param array $params
     * @param array $options
     * @return bool|string
     */
    public static function wsc_remote_get($url, $params = array(), $options = array()) {
        $options['method'] = 'GET';

        $parameters = '';
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if ($key <> '') $parameters .= ($parameters == "" ? "" : "&") . $key . "=" . $value;
            }

            if ($parameters <> '') {
                $url .= ((strpos($url, "?") === false) ? "?" : "&") . $parameters;
            }
        }
        //echo $url; exit();
        if (!$response = self::wsc_wpcall($url, $params, $options)) {
            return false;
        }

        return $response;
    }

    /**
     * Connect remote with wp_remote_get
     *
     * @param $url
     * @param array $params
     * @param array $options
     * @return bool|string
     */
    public static function wsc_remote_post($url, $params = array(), $options = array()) {
        $options['method'] = 'POST';
        if (!$response = self::wsc_wpcall($url, $params, $options)) {
            return false;
        }

        return $response;
    }

    /**
     * Use the WP remote call
     *
     * @param string $url
     * @param array $params
     * @param array $options
     * @return string
     */
    private static function wsc_wpcall($url, $params, $options) {
        $options['timeout'] = (isset($options['timeout'])) ? $options['timeout'] : 30;
        $options['sslverify'] = false;
        $options['httpversion'] = '1.0';

        if ($options['method'] == 'POST') {
            $options['body'] = $params;
            unset($options['method']);
            $response = wp_remote_post($url, $options);
        } else {
            unset($options['method']);
            $response = wp_remote_get($url, $options);
        }
        if (is_wp_error($response)) {
            WSC_Debug::dump($response);
            return false;
        }

        $response = self::cleanResponce(wp_remote_retrieve_body($response)); //clear and get the body
        WSC_Debug::dump('wsc_wpcall', $url, $options, $response); //output debug
        return $response;
    }

    /**
     * Get the Json from responce if any
     * @param string $response
     * @return string
     */
    private static function cleanResponce($response) {
        $response = trim($response, '()');
        return $response;
    }

    /**
     * Returns true if permalink structure
     *
     * @return boolean
     */
    public static function isPermalinkStructure() {
        return get_option('permalink_structure');
    }


    /**
     * Check if HTML Headers to prevent chenging the code for other file extension
     * @return bool
     */
    public static function isHtmlHeader() {
        $headers = headers_list();

        foreach ($headers as $index => $value) {
            if (strpos($value, ':') !== false) {
                $exploded = @explode(': ', $value);
                if (count($exploded) > 1) {
                    $headers[$exploded[0]] = $exploded[1];
                }
            }
        }

        if (isset($headers['Content-Type'])) {
            if (strpos($headers['Content-Type'], 'text/html') !== false ||
                strpos($headers['Content-Type'], 'text/xml') !== false) {
                return true;
            }
        } else {
            return false;
        }

        return false;
    }


    /**
     * Returns true if server is Apache
     *
     * @return boolean
     */
    public static function isApache() {
        global $is_apache;
        return $is_apache;
    }

    /**
     * Check if mode rewrite is on
     * @return bool
     */
    public static function isModeRewrite() {
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (!empty($modules))
                return in_array('mod_rewrite', $modules);
        }
        return true;
    }

    /**
     * Check whether server is LiteSpeed
     *
     * @return bool
     */
    public static function isLitespeed() {
        return (isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
    }

    /**
     * Check whether server is Lighthttp
     *
     * @return bool
     */
    public static function isLighthttp() {
        return (isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false);
    }

    /**
     * Check if multisites with path
     *
     * @return bool
     */
    public static function isMultisites() {
        if (!isset(self::$is_multisite)) {
            self::$is_multisite = (is_multisite() && ((defined('SUBDOMAIN_INSTALL') && !SUBDOMAIN_INSTALL) || (defined('VHOST') && VHOST == 'no')));
        }
        return self::$is_multisite;
    }

    /**
     * Returns true if server is nginx
     *
     * @return boolean
     */
    public static function isNginx() {
        global $is_nginx;
        return ($is_nginx || (isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false));
    }

    /**
     * Returns true if server is nginx
     *
     * @return boolean
     */
    public static function isWpengine() {
        return (isset($_SERVER['WPENGINE_PHPSESSIONS']));
    }

    public static function isInmotion() {
        return (isset($_SERVER['SERVER_ADDR']) && strpos(@gethostbyaddr($_SERVER['SERVER_ADDR']), 'inmotionhosting.com') !== false);
    }

    /**
     * Returns true if server is IIS
     *
     * @return boolean
     */
    public static function isIIS() {
        global $is_IIS, $is_iis7;
        return ($is_iis7 || $is_IIS || (isset($_SERVER['SERVER_SOFTWARE']) && stristr($_SERVER['SERVER_SOFTWARE'], 'microsoft-iis') !== false));
    }

    /**
     * Returns true if windows
     * @return bool
     */
    public static function isWindows() {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * Check if IIS has rewrite 2 structure enabled
     * @return bool
     */
    public static function isPHPPermalink() {
        if (get_option('permalink_structure')) {
            if (strpos(get_option('permalink_structure'), 'index.php') !== false || strpos(get_option('permalink_structure'), 'index.html') !== false || strpos(get_option('permalink_structure'), 'index.htm') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the plugin is active by checking the active_plugins list.
     *
     * @source wp-admin/includes/plugin.php
     *
     * @param string $plugin Plugin folder/main file.
     *
     * @return boolean
     */
    public static function isPluginActive($plugin) {
        if (empty(self::$active_plugins)) {
            self::$active_plugins = (array)get_option('active_plugins', array());

            if (is_multisite()) {
                self::$active_plugins = array_merge(array_values(self::$active_plugins), array_keys(get_site_option('active_sitewide_plugins')));
            }

            WSC_Debug::dump(self::$active_plugins);

        }
        return in_array($plugin, self::$active_plugins, true);
    }

    /**
     * Check whether the theme is active.
     *
     * @param string $theme Theme folder/main file.
     *
     * @return boolean
     */
    public static function isThemeActive($theme) {
        if (function_exists('wp_get_theme')) {
            $themes = wp_get_theme();
            if (isset($themes->name) && (strtolower($themes->name) == strtolower($theme) || strtolower($themes->name) == strtolower($theme) . ' child')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all the plugin names
     *
     * @return array
     */
    public static function getAllPlugins() {
        $all_plugins = (array)get_option('active_plugins', array());;

        if (is_multisite()) {
            $all_plugins = array_merge(array_values($all_plugins), array_keys(get_site_option('active_sitewide_plugins')));
        }

        return $all_plugins;
    }

    /**
     * Get all the themes names
     *
     * @return array
     */
    public static function getAllThemes() {
        return search_theme_directories();
    }

    /**
     * Get the absolute filesystem path to the root of the WordPress installation
     *
     * @return string Full filesystem path to the root of the WordPress installation
     */
    public static function getRootPath() {
        return ABSPATH;
    }

    /**
     * Get Relative path for the current blog in case of WP Multisite
     * @param $url
     * @return mixed|string
     */
    public static function getRelativePath($url) {
        $url = wp_make_link_relative($url);

        if ($url <> '') {
            $url = str_replace(wp_make_link_relative(get_bloginfo('url')), '', $url);

            if (WSC_Classes_Tools::isMultisites() && defined('PATH_CURRENT_SITE')) {
                $url = str_replace(rtrim(PATH_CURRENT_SITE, '/'), '', $url);
                $url = trim($url, '/');
                $url = $url . '/';
            } else {
                $url = trim($url, '/');
            }
        }

        WSC_Debug::dump($url);
        return $url;
    }

    /**
     * Empty the cache from other cache plugins when save the settings
     */
    public static function emptyCache() {
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }

        if (function_exists('w3tc_minify_flush')) {
            w3tc_minify_flush();
        }
        if (function_exists('w3tc_dbcache_flush')) {
            w3tc_dbcache_flush();
        }
        if (function_exists('w3tc_objectcache_flush')) {
            w3tc_objectcache_flush();
        }

        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }

        if (function_exists('rocket_clean_domain')) {
            // Remove all cache files
            rocket_clean_domain();
        }

        if (function_exists('opcache_reset')) {
            // Remove all opcache if enabled
            opcache_reset();
        }

        if (function_exists('apc_clear_cache')) {
            // Remove all apc if enabled
            apc_clear_cache();
        }

        if (class_exists('Cache_Enabler_Disk') && method_exists('Cache_Enabler_Disk', 'clear_cache')) {
            // clear disk cache
            Cache_Enabler_Disk::clear_cache();
        }

        //Clear the fastest cache
        global $wp_fastest_cache;
        if (isset($wp_fastest_cache) && method_exists($wp_fastest_cache, 'deleteCache')) {
            $wp_fastest_cache->deleteCache();
        }
    }

    /**
     * Flush the WordPress rewrites
     */
    public static function flushWPRewrites() {
        if (WSC_Classes_Tools::isPluginActive('woocommerce/woocommerce.php')) {
            update_option('woocommerce_queue_flush_rewrite_rules', 'yes');
        }

        flush_rewrite_rules();
    }

    /**
     * Called on plugin activation
     */
    public function wsc_activate() {
        set_transient('wsc_activate', true);

        $lastsafeoptions = self::getOptions(true);
        if (isset($lastsafeoptions['wsc_mode']) && ($lastsafeoptions['wsc_mode'] == 'ninja' || $lastsafeoptions['wsc_mode'] == 'lite')) {
            set_transient('wsc_restore', true);
        }

        self::$options = @array_merge(self::$init, self::$default);
        self::$options['wsc_ver'] = WSC_VERSION_ID;
        self::saveOptions();
    }

    /**
     * Called on plugin deactivation
     */
    public function wsc_deactivate() {
        $options = self::$default;
        //Prevent duplicates
        foreach ($options as $key => $value) {
            //set the default params from tools
            WSC_Classes_Tools::saveOptions($key, $value);
        }
    }

    /**
     * Check for updates
     * Called on activation
     */
    public static function checkUpgrade() {
        self::$options = self::getOptions();
        self::$options['wsc_ver'] = WSC_VERSION_ID;
        self::saveOptions();

    }

    /**
     * Check if new themes or plugins are added
     */
    public function checkWpUpdates() {
        $all_plugins = WSC_Classes_Tools::getAllPlugins();
        $dbplugins = WSC_Classes_Tools::getOption('wsc_plugins');
        foreach ($all_plugins as $plugin) {
            if (is_plugin_active($plugin) && isset($dbplugins['from']) && !empty($dbplugins['from'])) {
                if (!in_array(plugin_dir_path($plugin), $dbplugins['from'])) {
                    self::saveOptions('changes', true);
                }
            }
        }

        $all_themes = WSC_Classes_Tools::getAllThemes();
        $dbthemes = WSC_Classes_Tools::getOption('wsc_themes');
        foreach ($all_themes as $theme => $value) {
            if (is_dir($value['theme_root']) && isset($dbthemes['from']) && !empty($dbthemes['from'])) {
                if (!in_array($theme . '/', $dbthemes['from'])) {
                    self::saveOptions('changes', true);
                }
            }
        }

    }

    /**
     * Call API Server
     * @param null $email
     * @param string $redirect_to
     * @return array|bool|mixed|object
     */
    public static function checkApi($email = null, $redirect_to = '') {
        $check = array();
        if (isset($email) && $email <> '') {
            $args = array('email' => $email, 'url' => home_url(), 'howtolessons' => 0, 'source' => _WSC_PLUGIN_NAME_);
            $response = self::wsc_remote_get(_WSC_API_SITE_ . '/api/free/token', $args, array('timeout' => 10));
        } elseif (self::getOption('wsc_token')) {
            $args = array('token' => self::getOption('wsc_token'), 'url' => home_url(), 'howtolessons' => 0, 'source' => _WSC_PLUGIN_NAME_);
            $response = self::wsc_remote_get(_WSC_API_SITE_ . '/api/free/token', $args, array('timeout' => 10));
        } else {
            return $check;
        }
        if ($response && json_decode($response)) {
            $check = json_decode($response, true);

            WSC_Classes_Tools::saveOptions('wsc_token', (isset($check['token']) ? $check['token'] : 0));
            WSC_Classes_Tools::saveOptions('api_token', (isset($check['api_token']) ? $check['api_token'] : false));
            WSC_Classes_Tools::saveOptions('error', isset($check['error']));

            if (!isset($check['error'])) {
                if ($redirect_to <> '') {
                    wp_redirect($redirect_to);
                    exit();
                }
            } elseif (isset($check['message'])) {
                WSC_Classes_Error::setError($check['message']);
            }
        } else {
            //WSC_Classes_Tools::saveOptions('error', true);
            WSC_Classes_Error::setError(sprintf(__('CONNECTION ERROR! Make sure your website can access: %s', _WSC_PLUGIN_NAME_), '<a href="' . _WSC_SUPPORT_SITE_ . '" target="_blank">' . _WSC_SUPPORT_SITE_ . '</a>') . " <br /> ");
        }

        return $check;
    }

    /**
     * Set the content type to text/plain
     * @return string
     */
    public static function setContentType() {
        return "text/plain";
    }

    /**
     * Return false on hooks
     * @param string $param
     * @return bool
     */
    public static function returnFalse($param = null) {
        return false;
    }

    /**
     * Return true on hooks
     * @param string $param
     * @return bool
     */
    public static function returnTrue($param = null) {
        return true;
    }

}