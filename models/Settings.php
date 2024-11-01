<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class WSC_Models_Settings {

    /**
     * Save the Values in database
     * @param $params
     */
    public function saveValues($params) {
        //Save the option values
        foreach ($params as $key => $value) {
            if (in_array($key, array_keys(WSC_Classes_Tools::$options))) {
                //Make sure is set in POST
                if (WSC_Classes_Tools::getIsset($key)) {
                    //sanitize the value first
                    $value = WSC_Classes_Tools::getValue($key);

                    //set the default value in case of nothing to prevent empty paths and errors
                    if ($value == '') {
                        if (isset(WSC_Classes_Tools::$default[$key])) {
                            $value = WSC_Classes_Tools::$default[$key];
                        } elseif (isset(WSC_Classes_Tools::$init[$key])) {
                            $value = WSC_Classes_Tools::$init[$key];
                        }
                    }
                    WSC_Classes_Tools::saveOptions($key, $value);
                }
            }
        }
    }

}
