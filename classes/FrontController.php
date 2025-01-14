<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * The main class for controllers
 *
 */
class WSC_Classes_FrontController {

    /** @var object of the model class */
    public $model;

    /** @var object of the view class */
    public $view;

    /** @var string name of theclass */
    protected $name;

    public function __construct() {

        /* Load error class */
        WSC_Classes_ObjController::getClass('WSC_Classes_Error');

        /* Load Tools */
        WSC_Classes_ObjController::getClass('WSC_Classes_Tools');

        /* get the name of the current class */
        $this->name = get_class($this);

        /* load the model and hooks here for wordpress actions to take efect */
        /* create the model and view instances */
        $model_classname = str_replace('Controllers', 'Models', $this->name);
        if(WSC_Classes_ObjController::getClassPath($model_classname)) {
            $this->model = WSC_Classes_ObjController::getClass($model_classname);
        }

        //IMPORTANT TO LOAD HOOKS HERE
        /* check if there is a hook defined in the controller clients class */
        WSC_Classes_ObjController::getClass('WSC_Classes_HookController')->setHooks($this);

        /* Load the Main classes Actions Handler */
        WSC_Classes_ObjController::getClass('WSC_Classes_Action');
        WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController');

    }

    /**
     * load sequence of classes
     * Function called usualy when the controller is loaded in WP
     *
     * @return WSC_Classes_FrontController
     */
    public function init() {
        return $this;
    }

    /**
     * Get the block view
     *
     * @param mixed $view
     * @param mixed $obj
     * @return string HTML
     */
    public function getView($view = null, $obj = null) {
        if(!isset($obj)){
            $obj = $this;
        }
        if (!isset($view)) {
            if ($class = WSC_Classes_ObjController::getClassPath($this->name)) {
                $view = $class['name'];
            }
        }

        if (isset($view)) {
            $this->view = WSC_Classes_ObjController::getClass('WSC_Classes_DisplayController');
            return $this->view->getView($view, $obj);
        }

        return '';
    }

    /**
     * Called as menu callback to show the block
     *
     */
    public function show() {
        echo $this->init()->getView();
    }

    /**
     * first function call for any class
     *
     */
    protected function action() {
        // generated nonce we created
    }


    /**
     * initialize settings
     * Called from index
     *
     * @return void
     */
    public function hookInit() {
        WSC_Classes_ObjController::getClass('WSC_Controllers_Menu')->hookInit();
    }


    /**
     * Called on frontend. For disconnected users
     */
    public function hookFrontinit() { }

    /**
     * Hook the admin head
     * This function will load the media in the header for each class
     *
     * @return void
     */
    public function hookHead() { }

}
