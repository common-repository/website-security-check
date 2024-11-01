<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class WSC_Controllers_Menu extends WSC_Classes_FrontController {

	public $alert = '';

	/**
	 * Hook the Admin load
	 */
	public function hookInit() {
		/* add the plugin menu in admin */
		if ( current_user_can( 'manage_options' ) ) {
			//check if activated
			if ( get_transient( 'wsc_activate' ) == 1 ) {
				// Delete the redirect transient
				delete_transient( 'wsc_activate' );

				//Check if there are expected upgrades
				WSC_Classes_Tools::checkUpgrade();
			}

			//Show Admin Toolbar
			add_action( 'admin_bar_menu', array( $this, 'hookTopmenu' ), 999 );
			add_action( 'wp_dashboard_setup', array( $this, 'hookDashboardSetup' ) );

			//Set the alert if security wasn't check
			if ( ! get_option( 'wsc_securitycheck' ) ) {
				$this->alert = " <span class='awaiting-mod count-errors_count' style='display: inline-block; vertical-align: middle; margin: -2px 0 0 2px; padding: 0 5px; min-width: 8px; height: 18px; border-radius: 11px; background-color: #ca4a1f; color: #fff; font-size: 9px; line-height: 18px; text-align: center;'> <span class='sq_count pending-count' style='line-height: 17px;font-size: 11px;'>1</span> </span>";
			} elseif ( $securitycheck_time = get_option( 'wsc_securitycheck_time' ) ) {
				if ( isset( $securitycheck_time['timestamp'] ) && time() - $securitycheck_time['timestamp'] > ( 3600 * 24 * 7 ) ) {
					$this->alert = " <span class='awaiting-mod count-errors_count' style='display: inline-block; vertical-align: middle; margin: -2px 0 0 2px; padding: 0 5px; min-width: 8px; height: 18px; border-radius: 11px; background-color: #ca4a1f; color: #fff; font-size: 9px; line-height: 18px; text-align: center;'> <span class='sq_count pending-count' style='line-height: 17px;font-size: 11px;'>1</span> </span>";
				}
			}
		}

	}

	/**
	 * Creates the Setting menu in WordPress
	 */
	public function hookMenu() {
		if ( current_user_can( 'manage_options' ) ) {
			$this->model->addMenu( array(
				__( 'Website Security Check - WPPlugins', _WSC_PLUGIN_NAME_ ),
				'Security Check' . $this->alert,
				'manage_options',
				'wsc_securitycheck',
				array( WSC_Classes_ObjController::getClass( 'WSC_Controllers_Settings' ), 'init' ),
				_WSC_THEME_URL_ . 'img/logo_16.png'
			) );

		}

	}

	/**
	 * Add a menu in Admin Bar
	 *
	 * @param $wp_admin_bar
	 *
	 * @return mixed
	 */
	public function hookTopmenu( $wp_admin_bar ) {
		$wp_admin_bar->add_node( array(
			'id'     => 'wsc_securitycheck',
			'title'  => '<img src="' . _WSC_THEME_URL_ . 'img/logo_16.png' . '" style="height: 15px; vertical-align: text-bottom; display: inline-block; margin-right: 3px;" />' . __( 'Security Check', _WSC_PLUGIN_NAME_ ) . $this->alert,
			'href'   => WSC_Classes_Tools::getSettingsUrl( 'wsc_securitycheck' ),
			'parent' => false
		) );

		return $wp_admin_bar;
	}

	public function hookDashboardSetup() {
		wp_add_dashboard_widget(
			'wsc_dashboard_widget',
			__( 'Website Security Check', _WSC_PLUGIN_NAME_ ),
			array( WSC_Classes_ObjController::getClass( 'WSC_Controllers_Widget' ), 'dashboard' )
		);

		// Move our widget to top.
		global $wp_meta_boxes;

		$dashboard                                    = $wp_meta_boxes['dashboard']['normal']['core'];
		$ours                                         = array( 'wsc_dashboard_widget' => $dashboard['wsc_dashboard_widget'] );
		$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( $ours, $dashboard );
	}

	/**
	 * Creates the Setting menu in Multisite WordPress
	 */
	public function hookMultisiteMenu() {

		$this->model->addMenu( array(
			__( 'Website Security Check - WPPlugins', _WSC_PLUGIN_NAME_ ),
			'Security Check' . $this->alert,
			'manage_options',
			'wsc_settings',
			array( WSC_Classes_ObjController::getClass( 'WSC_Controllers_Settings' ), 'init' ),
			_WSC_THEME_URL_ . 'img/logo_16.png'
		) );
	}
}