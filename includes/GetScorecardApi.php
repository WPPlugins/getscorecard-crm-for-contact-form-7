<?php
/**
 * Created by PhpStorm.
 * User: Antonshell
 * Date: 06.03.2015
 * Time: 9:55
 */

/**
 * Class GetScorecardApi
 */
class GetScorecardApi{
    const OPTION_PREFIX = 'cf7_gs';

    const OPTION_CLIENT_ID = 'client_id';
    const OPTION_CLIENT_SECRET = 'client_secret';
    const OPTION_USER_ID = 'user_id';
    const OPTION_REDIRECT_URI = 'redirect_uri';
    const OPTION_ACCESS_TOKEN = 'access_token';
    const OPTION_REFRESH_TOKEN = 'refresh_token';

    const OPTION_OAUTH_CODE = 'oauth_code';

    const API_URL  = '/api/public';

    protected $apiUrl = null;
    protected $accessToken = null;

    public $authorized = false;

    public function __construct(){
        $this->authorized = $this->checkIfAuthorized();

        //refresh access token
        if(!$this->authorized){
            $this->refreshAccessToken();
            $this->authorized = $this->checkIfAuthorized();
        }

        $this->apiUrl = $this->getApiUrl();
        $this->accessToken = self::getOption(self::OPTION_ACCESS_TOKEN);
    }

    /**
     * @return string
     */
    public static function getApiUrl(){
        $baseUrl = CF7_GS_GETSCORECARD_BASE_URL;
        return $baseUrl . self::API_URL;
    }

    public static function deleteAuthData(){
        self::deleteOption(self::OPTION_CLIENT_ID);
        self::deleteOption(self::OPTION_CLIENT_SECRET);
        self::deleteOption(self::OPTION_USER_ID);
        self::deleteOption(self::OPTION_ACCESS_TOKEN);
        self::deleteOption(self::OPTION_REFRESH_TOKEN);
        self::deleteOption(self::OPTION_REDIRECT_URI);
    }

    public static function refreshAccessToken(){
        $refreshToken = self::getOption(self::OPTION_REFRESH_TOKEN);
        $clientId = self::getOption(self::OPTION_CLIENT_ID);
        $clientSecret = self::getOption(self::OPTION_CLIENT_SECRET);

        $data = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $refreshToken,
            "client_id" => $clientId,
            "client_secret" => $clientSecret
        );

        $result = self::sendOauthRequest($data);
        $result = json_decode($result);

        if(isset($result->access_token)){
            self::updateOption(self::OPTION_ACCESS_TOKEN,$result->access_token);
        }
    }

    /**
     * @return bool
     */
    public static function checkIfAuthorized(){
        $accessToken = self::getOption(self::OPTION_ACCESS_TOKEN);

        if(!$accessToken){
            return false;
        }

        $userId = self::getOption(self::OPTION_USER_ID);

        $ch = curl_init(self::getApiUrl() . '/users/'. $userId);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'GetScorecard-Client-Type: wordpress_plugin',
                'GetScorecard-Client-Version: 1'
            ]
        );

        $userInfo = curl_exec($ch);

        $userInfo = json_decode($userInfo);

        if($userInfo->status == 403){
            return false;
        }

        $userInfo = get_object_vars($userInfo);
        $userInfo = $userInfo[0];
        $userInfo = get_object_vars($userInfo);

        if(!is_array($userInfo)){
            return false;
        }

        return true;
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function sendGetRequest($url){
        $ch = curl_init($this->apiUrl . '/'.$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->accessToken,
                'GetScorecard-Client-Type: woocommerce_plugin',
                'GetScorecard-Client-Version: 1'
            ]
        );

        $result = curl_exec($ch);
        return $result;
    }

    /**
     * @param $clientData
     * @param $url
     * @return mixed
     */
    protected function sendPostRequest($clientData,$url){

        $data_string = json_encode($clientData);

        $ch = curl_init($this->apiUrl . '/'.$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/vnd.getscorecard.v1+json',
                'Accept: application/vnd.getscorecard.v1+json',
                'Authorization: Bearer ' . $this->accessToken,
                'GetScorecard-Client-Type: woocommerce_plugin',
                'GetScorecard-Client-Version: 1'
            ]
        );

        $result = curl_exec($ch);
        return $result;
    }

    /**
     * @param $data
     * @return mixed
     */
    public static function sendOauthRequest($data){
        $url = self::getApiUrl().'/oauth';
        $fields_string = '';
        //url-ify the data for the POST
        foreach($data as $key=>$value)
        {
            $fields_string .= $key.'='.urlencode($value).'&';
        }
        rtrim($fields_string, '&');

        $curl = curl_init();
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

        curl_setopt($curl,CURLOPT_URL,$url);		//The URL to fetch. This can also be set when initializing a session with curl_init().
        curl_setopt($curl,CURLOPT_POST, count($data));    //set POST data
        curl_setopt($curl,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);	//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,5);	//The number of seconds to wait while trying to connect.

        curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);	//The contents of the "User-Agent: " header to be used in a HTTP request.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	//To follow any "Location: " header that the server sends as part of the HTTP header.
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);	//To automatically set the Referer: field in requests where it follows a Location: redirect.
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);	//The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);	//To stop cURL from verifying the peer's certificate.

        $contents = curl_exec($curl);
        curl_close($curl);

        return $contents;
    }

    /**
     * @param $option
     * @param $value
     */
    public static function updateOption($option,$value){
        update_option( self::OPTION_PREFIX . $option, $value);
    }

    /**
     * @param $option
     * @return mixed|void
     */
    public static function getOption($option){
        return get_option(self::OPTION_PREFIX . $option);
    }

    /**
     * @param $option
     */
    public static  function deleteOption($option){
        delete_option(self::OPTION_PREFIX . $option);
    }
}