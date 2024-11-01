<div id="wsc_wrap" class="d-flex flex-row my-3 bg-light">
    <div class="wsc_row d-flex flex-row bg-white px-3">
        <?php do_action('wsc_notices'); ?>
        <div class="wsc_col flex-grow-1 mr-3">
            <form method="POST">
                <?php wp_nonce_field('wsc_connect', 'wsc_nonce') ?>
                <input type="hidden" name="action" value="wsc_connect"/>

                <?php do_action('wsc_form_notices'); ?>
                <div class="card p-0 col-sm-12 tab-panel">
                    <h3 class="card-title bg-brown text-white p-2"><?php _e('Activate the Plugin', _WSC_PLUGIN_NAME_); ?></h3>
                    <div class="card-body">

                        <div class="col-sm-12 row border-bottom border-light py-3 mx-0 my-3">
                            <div class="col-sm-4 p-1 font-weight-bold">
                                <?php _e('Email Address', _WSC_PLUGIN_NAME_); ?>:
                                <div class="small text-black-50"><?php echo __('Enter your email address, connect to WPPlugins and get a free Token', _WSC_PLUGIN_NAME_); ?></div>
                            </div>
                            <div class="col-sm-8 p-0 input-group ">
                                <?php
                                global $current_user;
                                $email = $current_user->user_email;
                                ?>
                                <input type="text" class="form-control" name="wsc_email" value="<?php echo $email ?>" placeholder="<?php echo $email ?>"/>
                            </div>
                        </div>

                        <div class="col-sm-12 border-bottom border-light py-3 mx-0 my-3 text-right">
                            <button type="button" class="btn btn-link text-info" onclick="jQuery('#wsc_token_div').toggle();"><?php _e("I have a token already", _WSC_PLUGIN_NAME_); ?></button>
                        </div>
                        <div id="wsc_token_div" class="col-sm-12 row border-bottom border-light py-3 mx-0 my-3" style="display: none">
                            <div class="col-sm-4 p-1 font-weight-bold">
                                <?php _e('Enter the Token', _WSC_PLUGIN_NAME_); ?>:
                                <div class="small text-black-50"><?php echo sprintf(__('Enter the Token from your %sWPPlugins account%s ', _WSC_PLUGIN_NAME_), '<a href="' . _WSC_ACCOUNT_SITE_ . '/user/auth/licenses" target="_blank">', '</a>'); ?></div>
                            </div>
                            <div class="col-sm-8 p-0 input-group">
                                <input type="text" class="form-control" name="wsc_token" value=""/>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="col-sm-12 my-3 p-0">
                    <button type="submit" class="btn rounded-0 btn-success btn-lg px-5 save"><?php _e('Activate', _WSC_PLUGIN_NAME_); ?></button>
                </div>
            </form>
            <form method="POST">
		        <?php wp_nonce_field( 'wsc_dont_connect', 'wsc_nonce' ) ?>
                <input type="hidden" name="action" value="wsc_dont_connect"/>
                <button type="submit" class="btn rounded-0 float-left btn-link btn-lg px-5" style="position: relative;margin-top: -65px;margin-left: 164px;"><?php _e( 'Skip Activation', _WSC_PLUGIN_NAME_ ); ?></button>
            </form>
        </div>
        <div class="wsc_col wsc_col_side col-3">
            <div class="card col-sm-12 p-0">
                <div class="card-body f-gray-dark text-left border-bottom">
                    <h3 class="card-title"><?php _e('Activate Plugin', _WSC_PLUGIN_NAME_); ?></h3>
                    <div class="text-info">
                        <?php echo sprintf(__("By activating the plugin you agree with our %sTerms of Use%s and %sPrivacy Policy%s", _WSC_PLUGIN_NAME_), '<a href="https://wpplugins.tips/terms-of-use/" target="_blank">','</a>','<a href="https://wpplugins.tips/privacy-policy/" target="_blank">','</a>'); ?>
                    </div>
                    <div class="text-info mt-3">
                        <?php echo __('Note! If you add your email you will receive a free token which will activate the plugin.', _WSC_PLUGIN_NAME_); ?>
                    </div>
                </div>
            </div>

            <?php echo $view->getView('Support') ?>

        </div>
    </div>

</div>