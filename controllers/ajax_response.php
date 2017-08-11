<?php

add_action('wp_ajax_getScorecard_logoutUser', 'getScorecard_logoutUser_callback');

function getScorecard_logoutUser_callback() {
    delete_option('cf7_gs_settings_form_key');
    delete_option('cf7_gs_settings_form_keys');
    delete_option('cf7_gs_database_active');
    delete_option('cf7_gs_settings_userCredentials');
    delete_option('cf7_gs_settings_default_deep_link_view');

    GetScorecardApi::deleteAuthData();

    echo 'Deleted.... User data'; //none list

    die();
}

add_action('wp_ajax_getScorecard_updateFields', 'getScorecard_updateFields_callback');

function getScorecard_updateFields_callback(){
    $apiClient = new GetScorecardApiClient();
    $fields = $apiClient->getModulesFields();

    update_option('cf7_gs_settings_api_fields', $fields);

    $redirectUrl = CF7_GS_ADMIN_AJAX_URL . 'admin.php?page=wpcf7&post=5&action=edit#cf7gs';
    header('Location: ' . $redirectUrl);
    die();
}

add_action('wp_ajax_getScorecard_generateForm', 'getScorecard_generateForm_callback');

function getScorecard_generateForm_callback(){

    $gsForm = new GetScorecardForm();

    $postId = $gsForm->createForm();

    echo $postId;
    die();
}

/* end file ajax_response.php */