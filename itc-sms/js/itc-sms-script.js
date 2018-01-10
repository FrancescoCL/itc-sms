/** *********************************** **/
/**  itc-sms.php  JAVASCRIPT FUNCTIONS  **/
/**   Author: Francesco Casadei Lelli   **/
/** *********************************** **/

/** *********** **/
/**  FUNCTIONS  **/
/** *********** **/

/** BULK ACTIONS FUNCTIONS **/
function bulk_actions(js_selected){
    if(js_selected === 'delete'){
        jQuery("input:checkbox:checked").each(function(){
            var js_check_ID = jQuery(this).val();
            if(js_check_ID !== undefined) del_user(js_check_ID);
        });
    }
    jQuery(document).ready(function(){
        location.reload();
    });
}

/** SWITCH FROM TABLE TO USER MODIFY **/
function mod_user(id){
    jQuery('#display_user').hide();
    jQuery('#user_id').val(id);
    jQuery('#itc_sms_mod_name').val(jQuery('#name-'+id).val());
    jQuery('#itc_sms_mod_surname').val(jQuery('#surname-'+id).val());
    jQuery('#itc_sms_mod_telephone').val(jQuery('#telephone-'+id).val());

    var mail = jQuery('#email-'+id).val();
    if(mail === '-') jQuery('#itc_sms_mod_email').val('');
    else jQuery('#itc_sms_mod_email').val(mail);

    jQuery('#modify_user').show();
}

/** REMOVE USER **/
function del_user(id){
    var data = {'action' : 'delete_data','del_id' : id};
    jQuery.post(ajaxurl, data, function(){});
}

/** CLIENT SIDE MAIL CHECK FUNCTION **/
function email_check(email){
    var espressione = /^[_a-z0-9+-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$/;
    if(espressione.test(email)) return 1;
    else return 0;
}


/** ********************** **/
/**  RESPONSIVE FUNCTIONS  **/
/** ********************** **/

/** CLICK RESPONSE ON DOCUMENT READY **/
jQuery(document).ready(function(){

    /** BULK ACTIONS TOP OR BOTTOM SELECTOR **/
    jQuery('#doaction').click(function(){
        var js_selected = jQuery('#bulk-action-selector-top').val();
        bulk_actions(js_selected);
    });

    jQuery('#doaction2').click(function(){
        var js_selected = jQuery('#bulk-action-selector-bottom').val();
        bulk_actions(js_selected);
    });

    /** REGISTER USER **/
    jQuery('#itc_register_user').click(function(){
        var js_name = jQuery('#itc_sms_user_name').val();
        var js_surname = jQuery('#itc_sms_user_surname').val();
        var js_telephone = jQuery('#itc_sms_user_telephone').val();
        var js_email = jQuery('#itc_sms_user_email').val();

        jQuery('#user_saved').hide('fast');

        if(js_name === ''){
            jQuery('#itc_sms_user_name').focus();
            return '';
        }
        else if(js_surname === ''){
            jQuery('#itc_sms_user_surname').focus();
            return '';
        }
        else if(js_telephone === ''){
            jQuery('#itc_sms_user_telephone').focus();
            return '';
        }
        else if(js_email !== '' && !email_check(js_email)){
            jQuery('#email_error').show('fast');
            jQuery('#itc_sms_user_email').focus();
            return '';
        }
        else{
            jQuery('#email_error').hide('fast');
            jQuery('#telephone_error').hide('fast');
            var data = {
                "action": "send_data",
                "name": js_name,
                "surname": js_surname,
                "telephone": js_telephone,
                "email": js_email
            };
            jQuery.post(
                ajaxurl,
                data,
                function(){
                    jQuery('#user_saved').show('fast');
                    jQuery('#itc_sms_user_name').val('');
                    jQuery('#itc_sms_user_surname').val('');
                    jQuery('#itc_sms_user_telephone').val('');
                    jQuery('#itc_sms_user_email').val('');
                }
            );
        }
    });

    /** MODIFY USER **/
    jQuery('#itc_modify_user').click(function (){
        jQuery('#user_mod').hide('fast');
        var js_mod_name = jQuery('#itc_sms_mod_name').val();
        var js_mod_surname = jQuery('#itc_sms_mod_surname').val();
        var js_mod_telephone = jQuery('#itc_sms_mod_telephone').val();
        var js_mod_email = jQuery('#itc_sms_mod_email').val();
        var js_user_id = jQuery('#user_id').val();

        if(js_mod_name === ''){
            jQuery('#itc_sms_mod_name').focus();
            //return '';
        }
        else if(js_mod_surname === ''){
            jQuery('#itc_sms_mod_surname').focus();
            //return '';
        }
        else if(js_mod_telephone === ''){
            jQuery('#itc_sms_mod_telephone').focus();
            //return '';
        }
        else if(js_mod_email !== '' && !email_check(js_mod_email)){
            jQuery('#mod_email_error').show('fast');
            jQuery('#itc_sms_mod_email').focus();
            //return '';
        }
        else{
            jQuery('#mod_email_error').hide('fast');
            var data = {
                'action': 'modify_data',
                'mod_id': js_user_id,
                'new_name': js_mod_name,
                'new_surname': js_mod_surname,
                'new_telephone': js_mod_telephone,
                'new_email': js_mod_email
            };
            jQuery.post(
                ajaxurl,
                data,
                function(){
                    jQuery('#user_mod').show('fast');
                }
            );
        }
    });

    /** DATA EXPORT **/
    jQuery("#start_export").click(function () {
        jQuery("#export_status").hide();
        jQuery("#export_error").hide();
        var sub_flag = jQuery("input[name=yn]:checked", "#radio_in").val();
        var data = {
            "action": "export_data",
            "sub_flag": sub_flag
        };
        jQuery.post(
            ajaxurl,
            data,
            function(result){
                // Errors check
                if (!result) jQuery("#export_error").show('fast');
                else jQuery("#export_status").show('fast');

                // Start download
                var location = jQuery('#file').attr('href');
                document.location = location;
            }
        );

    });

    /** FILE UPLOAD **/
    jQuery(':file').on('change', function () {
        // Save uploaded file
        jQuery('#import_status').hide('fast');
        cells = [];
        var fileUpload = document.getElementById("fileUpload");
        var regex = /^([a-zA-Z0-9\s_\\.\-:(0-9)])+(.csv)$/;
        if(regex.test(fileUpload.value.toLowerCase())){
            if(typeof (FileReader) !== "undefined"){
                var reader = new FileReader();
                reader.onload = function(rd){
                    rows = rd.target.result.split("\n");
                    for(var i = 0; i < rows.length - 1; i++){
                        cells[i] = rows[i].split(",");
                    }
                };
                reader.readAsText(fileUpload.files[0]);
            }
        }
    });

    /** DATA IMPORT **/
    jQuery('#start_import').on('click', function(){
        jQuery('#import_status').hide('fast');
        var data = {
            'action': 'import_data',
            'file': cells.toString()
        };
        jQuery.post(
            ajaxurl,
            data,
            function(){
                jQuery('#import_status').show('fast');
            }
        );
    });

    /** SEARCH BOX INSTRUCTIONS **/
    jQuery("#search-search-input").attr("placeholder", "nome o cognome");

});