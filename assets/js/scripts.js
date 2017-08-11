jQuery(document).ready(function($) {

    //$('#toplevel_page_cf7-crm > .wp-first-item').text('123');
    //$('#toplevel_page_cf7-crm').find('.wp-first-item').find('a').text('Settings');

    /* validate register form */
    $( "#gs_registerform" ).validate({
        errorClass: "form-field-error",
        rules: {
            fullname: {
                required: true
            },
            emailaddress: {
                required: true,
                email: true
            },
            password: {
                required: true
            }
        }
    });
    /**/

    $( "#gs_registerform").submit(function( event ) {
        $(".loader").show();
    });

    var cf7Gs_myjq = jQuery.noConflict();

    //cf7Gs_myjq(window).unbind('beforeunload');

    /* map cf7 fields to GetScorecard */
    cf7Gs_myjq('.custom_field_module').change(function(){
        var element = $(this);

        var module = element.attr('value');
        var row = element.attr('data-row');

        cf7Gs_myjq('.select_field_' + row).each(function(){
            var select = $(this);
            var selectModule = select.attr('data-module');

            if(selectModule == module){
                select.attr('name','cf7gs_custom_field_select[]');
                select.show();
            }
            else{
                select.attr('name','unused');
                select.hide();
            }
        });
    });
    /**/

    cf7Gs_myjq('.button_redirect').on('click', function() {
        window.location.href = CF7_GS_ADMIN_AJAX_URL + "admin.php?page=wpcf7";
    });

    cf7Gs_myjq('#cf7_gs_button_yes').click(function() {
        //cf7Gs_myjq('#login_form').slideDown('slow');
        cf7Gs_myjq('#login_form').slideToggle('slow');
        cf7Gs_myjq('#register_form').slideUp('slow');
    });

    cf7Gs_myjq('#cf7_gs_button_no').click(function() {
        //cf7Gs_myjq('#login_form').slideDown('slow');
        cf7Gs_myjq('#register_form').slideToggle('slow');
        cf7Gs_myjq('#login_form').slideUp('slow');
    });

    cf7Gs_myjq('#cf7_gs_fields_map_save_changes').children().attr('value', 'Save Changes');

    function checkRegexp(o, regexp, n) {
        if (!(regexp.test(o))) {
            return false;
        } else {
            return true;
        }
    }

    function checkURL(url) {
        return /^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/.test(url);
    }

    function str_clean(str) {

        str = str.replace("'", " ");
        str = str.replace(",", "");
        str = str.replace("\"", "");
        str = str.replace("/", "");

        return str;
    }

    //UNLINK ACCOUNT AND DELETE PLUGIN OPTIONS AND SETTINGS
    cf7Gs_myjq('.LogoutUser').click(function(){

        if( confirm("Do you want to unlink your account?") ){

                $(".loader").show();

                cf7Gs_myjq('.loading').fadeIn();
                cf7Gs_myjq.ajax({ type: "POST", url: CF7_GS_ADMIN_AJAX_URL + 'admin-ajax.php', data: {action:'getScorecard_logoutUser'},
                    success: function(data) {
                        cf7Gs_myjq('.loading').fadeOut();
                        location.reload();
                    }
                });
        }

    });

    cf7Gs_myjq('.generateForm').click(function(){

        //if( confirm("Do you want to unlink your account?") ){

            $(".loader").show();

            cf7Gs_myjq('.loading').fadeIn();
            cf7Gs_myjq.ajax({ type: "POST", url: CF7_GS_ADMIN_AJAX_URL + 'admin-ajax.php', data: {action:'getScorecard_generateForm'},
                success: function(data) {
                    $(".loader").hide();

                    var postId = parseInt(data);

                    if(postId != 0){
                        location.href = CF7_GS_ADMIN_AJAX_URL + 'admin.php?page=wpcf7&post=' + postId;
                    }
                    else{
                        alert('Error. Can\'t create form');
                    }
                }
            });
        //}

    });

    function DisableOptions() {
        var arr = [];
        cf7Gs_myjq("#cf7_gs_table select option:selected").each(function() {
            arr.push(cf7Gs_myjq(this).val());
        });

        cf7Gs_myjq("#cf7_gs_table select option").filter(function() {
            return cf7Gs_myjq.inArray(cf7Gs_myjq(this).val(), arr) > -1;
        }).attr("disabled", "disabled");

    }

    $('#wpcf7-cf7gs-active').on('click', function() {
        if (jQuery('#wpcf7-cf7gs-active').is(':checked')) {
            jQuery('#cf7gs-formdata').show('fast');
        } else {
            jQuery('#cf7gs-formdata').hide('fast');
        }
    });
});

  