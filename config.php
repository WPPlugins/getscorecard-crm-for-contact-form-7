<?php
global $wpdb;

/* envirenment config */
switch($_SERVER['SERVER_ADDR']) {
    case '127.0.0.21':
        $environment = 'local';
        break;
    case '127.0.0.22':
        $environment = 'local2';
        break;
    case '46.101.26.74':
        $environment = 'dev';
        break;
    default:
        $environment = 'production';
        break;
}
/**/

$app_config = array();

$app_config['production'] = array(
    'getscorecardBaseUrl' => 'https://app.getscorecard.com'
);

$app_config['dev'] = array(
    'getscorecardBaseUrl' => 'http://46.101.26.74:8001'
);

$app_config['local'] = array(
    'getscorecardBaseUrl' => 'http://127.0.0.1:8001'
);

$app_config['local2'] = array(
    'getscorecardBaseUrl' => 'http://127.0.0.7'
);

$config = $app_config[$environment];
/**/

define('WPGETSCORECARD7_GS_BASE_URL', $config['getscorecardBaseUrl'] );

// define a list of constants used along.
define('CF7_GS_CLOUD_PATH', realpath(dirname(__FILE__) ) );
define('CF7_GS_ADMIN_AJAX_URL',  get_admin_url());

define('CF7_GS_FACEBOOK_URL', 'https://www.facebook.com/getscorecard');
define('CF7_GS_GOOGLE_PLUS_URL', 'https://plus.google.com/+GetScorecard/videos');
define('CF7_GS_LINKED_IN_URL', 'https://www.linkedin.com/company/getscorecard');
define('CF7_GS_TWITTER_URL', 'https://twitter.com/get_scorecard');
define('CF7_GS_YOUTUBE_URL', 'https://www.youtube.com/user/getscorecard');

define('CF7_GS_GETSCORECARD_BASE_URL', $config['getscorecardBaseUrl'] );

define('CF7_GS_GETSCORECARD_WEBSITE_URL', 'http://www.getscorecard.com/' );
define('CF7_GS_GETSCORECARD_WEBSITE_LABEL', 'GetScorecard.com' );
define('CF7_GS_GETSCORECARD_PROJECT_NAME', 'GetScorecard' );

define('CF7_GS_PLUGIN_NAME', 'GetScorecard CRM for Contact Form 7' );
define('CF7_GS_PLUGIN_VERSION', '1.0.6' );