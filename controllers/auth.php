<?php
if(isset($_GET['page']) && $_GET['page'] == 'cf7-crm') {
    if (isset($_GET['action']) && $_GET['action'] == 'auth_callback') {
        include(dirname(dirname(__FILE__)) . '/auth/callback.php');
    }

    if (isset($_GET['action']) && $_GET['action'] == 'auth_redirect') {
        include(dirname(dirname(__FILE__)) . '/auth/oauth_redirect.php');
    }
}