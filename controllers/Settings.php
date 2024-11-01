<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class WSC_Controllers_Settings extends WSC_Classes_FrontController {

    public $tabs;
    public $logout = false;
    public $show_token = false;
    public $plugins;

    public function init() {
        //Load the css for Settings

        WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController')->loadMedia('bootstrap.min');
        WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController')->loadMedia('font-awesome.min');
        WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController')->loadMedia('settings');


        //Show connect for activation
        if (!WSC_Classes_Tools::getOption('wsc_token')) {
            echo $this->getView('Connect');
            return;
        }


        //Show Hide My WP CTA
        //WSC_Classes_Error::setError(sprintf(__('Protect your website with %sHide My WP Ghost%s plugin. %sDownload it now%s', _WSC_PLUGIN_NAME_), '<strong style="color: red">', '</strong>',  '<a href="https://wordpress.org/plugins/hide-my-wp/" target="_blank" style="font-weight: bold">', '</a>'));

        //Show errors on top
        WSC_Classes_ObjController::getClass('WSC_Classes_Error')->hookNotices();

        echo '<script>var wscQuery = {"ajaxurl": "' . admin_url('admin-ajax.php') . '","nonce": "' . wp_create_nonce(_WSC_NONCE_ID_) . '"}</script>';

        //Show Security Check
        echo WSC_Classes_ObjController::getClass('WSC_Controllers_SecurityCheck')->show();
    }


    /**
     * Called when an action is triggered
     *
     * @return void
     */
    public function action() {
        parent::action();

        if (!current_user_can('manage_options')) {
            return;
        }

        switch (WSC_Classes_Tools::getValue('action')) {
            case 'wsc_connect':
                //Connect to API with the Email
                $email = sanitize_email(WSC_Classes_Tools::getValue('wsc_email', ''));
                $token = WSC_Classes_Tools::getValue('wsc_token', '');

                $redirect_to = WSC_Classes_Tools::getSettingsUrl();
                if ($token <> '') {
                    if (preg_match('/^[a-z0-9\-]{32}$/i', $token)) {
                        WSC_Classes_Tools::saveOptions('wsc_token', $token);
                        WSC_Classes_Tools::saveOptions('error', false);
                        WSC_Classes_Tools::checkApi();
                    } else {
                        WSC_Classes_Error::setError(__('ERROR! Please make sure you use a valid token to connect the plugin with WPPlugins', _WSC_PLUGIN_NAME_) . " <br /> ");
                    }
                } elseif ($email <> '') {
                    WSC_Classes_Tools::checkApi($email, $redirect_to);
                } else {
                    WSC_Classes_Error::setError(__('ERROR! Please make sure you use an email address to connect the plugin with WPPlugins', _WSC_PLUGIN_NAME_) . " <br /> ");
                }
                break;

	        case 'wsc_dont_connect':
		        $redirect_to = WSC_Classes_Tools::getSettingsUrl();

		        WSC_Classes_Tools::saveOptions('wsc_token', md5(home_url()));
		        WSC_Classes_Tools::saveOptions('error', false);

		        wp_redirect($redirect_to);
		        exit();

        }
    }

}
