<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class WSC_Controllers_Widget extends WSC_Classes_FrontController {

    public $riskreport = array();
    public $risktasks;

    public function dashboard() {
        $this->risktasks = WSC_Classes_ObjController::getClass('WSC_Controllers_SecurityCheck')->getRiskTasks();
        $this->riskreport =WSC_Classes_ObjController::getClass('WSC_Controllers_SecurityCheck')->getRiskReport();

        echo $this->getView('Dashboard');
    }

    public function action() {
        parent::action();
        if (!current_user_can('manage_options')) {
            return;
        }

        switch (WSC_Classes_Tools::getValue('action')) {
            case 'wsc_widget_securitycheck':
                WSC_Classes_ObjController::getClass('WSC_Controllers_SecurityCheck')->doSecurityCheck();

                ob_start();
                $this->dashboard();
                $output = ob_get_clean();

                WSC_Classes_Tools::setHeader('json');
                echo json_encode(array('data' => $output));
                exit;

        }
    }
}
