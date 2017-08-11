<?php

$code = $_GET['code'];

GetScorecardApi::updateOption(GetScorecardApi::OPTION_OAUTH_CODE,$code);

$client_id = GetScorecardApi::getOption(GetScorecardApi::OPTION_CLIENT_ID);
$client_secret = GetScorecardApi::getOption(GetScorecardApi::OPTION_CLIENT_SECRET);
$redirect_uri = GetScorecardApi::getOption(GetScorecardApi::OPTION_REDIRECT_URI);

$clientData = array(
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $code,
    'grant_type' => 'authorization_code',
    'redirect_uri' => $redirect_uri
);

$contents = GetScorecardApi::sendOauthRequest($clientData);
$contents = json_decode($contents);

GetScorecardApi::updateOption(GetScorecardApi::OPTION_ACCESS_TOKEN,$contents->access_token);
GetScorecardApi::updateOption(GetScorecardApi::OPTION_REFRESH_TOKEN,$contents->refresh_token);

GetScorecardApi::deleteOption(GetScorecardApi::OPTION_OAUTH_CODE);

$redirectTo = CF7_GS_ADMIN_AJAX_URL . 'admin.php?page=cf7-crm' . '&action=authSuccess';

header('Location: ' . $redirectTo);
die();