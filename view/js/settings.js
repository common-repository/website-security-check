(function ($) {
    "use strict";

    $.fn.wsc_loading = function (state) {
        var $this = this;
        var loading = '<i class="fa fa-circle-o-notch fa-spin mr-1 wsc_loading"></i>';
        $this.find('i').remove()
        if (state) {
            $this.prepend(loading);
        } else {
            $('.wsc_loading').remove();
        }

        return $this;
    };

    $.fn.wsc_fixSettings = function (name, value) {
        var $div = $('#wsc_wrap');
        var $this = this;
        $this.wsc_loading(true);
        $.post(
            wscQuery.ajaxurl,
            {
                action: 'wsc_fixsettings',
                name: name,
                value: value,
                wsc_nonce: wscQuery.nonce
            }
        ).done(function (response) {
            $this.wsc_loading(false);
            if (typeof response.success !== 'undefined' && typeof response.message !== 'undefined') {
                if (response.success) {
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-success text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                    $this.hide();
                } else {
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                }
            }
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }).error(function () {
            $this.wsc_loading(false);
            $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>Ajax Error.</strong></div>');
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }, 'json');
    };

    $.fn.wsc_fixConfig = function (name, value) {
        var $div = $('#wsc_wrap');
        var $this = this;
        $this.wsc_loading(true);
        $.post(
            wscQuery.ajaxurl,
            {
                action: 'wsc_fixconfig',
                name: name,
                value: value,
                wsc_nonce: wscQuery.nonce
            }
        ).done(function (response) {
            $this.wsc_loading(false);
            if (typeof response.success !== 'undefined' && typeof response.message !== 'undefined') {
                if (response.success) {
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-success text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                    $this.hide();
                } else {
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                }
            }
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }).error(function () {
            $this.wsc_loading(false);
            $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>Ajax Error.</strong></div>');
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }, 'json');
    };

    $.fn.wsc_securityExclude = function (name) {
        var $div = $('#wsc_wrap');
        var $this = this;
        $.post(
            wscQuery.ajaxurl,
            {
                action: 'wsc_securityexclude',
                name: name,
                wsc_nonce: wscQuery.nonce
            }
        ).done(function (response) {
            if (typeof response.success !== 'undefined' && typeof response.message !== 'undefined') {
                if (response.success) {
                    $this.parents('tr:last').fadeOut();
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-success text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                } else {
                    $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                }
            }
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }).error(function () {
            $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>Ajax Error.</strong></div>');
            setTimeout(function () {
                $('.wsc_alert').remove();
            }, 5000)
        }, 'json');
    };


    $.fn.wsc_settingsListen = function () {
        var $this = this;

        $this.find('.start_securitycheck').find('button').on('click', function () {
            var $div = $this.find('.start_securitycheck');
            $div.after('<div class="wp_loading"></div>');
            $div.hide();
            $.post(
                wscQuery.ajaxurl,
                {
                    action: 'wsc_securitycheck',
                    wsc_nonce: wscQuery.nonce
                }
            ).done(function (response) {
                location.reload();
            }).error(function () {
                location.reload();
            });
            return false;
        });

        $this.find('button.wsc_resetexclude').on('click', function () {
            var $div = $this.find('.start_securitycheck');
            $.post(
                wscQuery.ajaxurl,
                {
                    action: 'wsc_resetexclude',
                    wsc_nonce: wscQuery.nonce
                }
            ).done(function (response) {
                if (typeof response.success !== 'undefined' && typeof response.message !== 'undefined') {
                    if (response.success) {
                        $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-success text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                    } else {
                        $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>' + response.message + '</strong></div>');
                    }
                }
                setTimeout(function () {
                    $('.wsc_alert').remove();
                }, 5000)
            }).error(function () {
                $div.prepend('<div class="fixed-top mt-4 pt-4 alert alert-danger text-center wsc_alert" role="alert"><strong>Ajax Error.</strong></div>');
                setTimeout(function () {
                    $('.wsc_alert').remove();
                }, 5000)
            });
            return false;
        });

    };

    $(document).ready(function () {
        $('#wsc_wrap').wsc_settingsListen();

        $(function () {
            $('[data-toggle="popover"]').popover()
        })

    });
})(jQuery);




