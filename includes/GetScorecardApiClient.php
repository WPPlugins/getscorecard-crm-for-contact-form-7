<?php
/**
 * Created by PhpStorm.
 * User: Antonshell
 * Date: 04.03.2015
 * Time: 11:21
 */

/**
 * Class GetScorecardApiClient
 */
class GetScorecardApiClient extends GetScorecardApi{

    /**
     * @param $id
     * @return mixed
     */
    public function getUserById($id){
        $userInfo = $this->sendGetRequest('users/' . (int)$id);
        $userInfo = json_decode($userInfo,true);
        return $userInfo[0];
    }

    /**
     * @return array
     */
    public function getUserInfoLabels(){
        return array(
            'id' => 'GetScorecard ID',
            'fullname' => 'Full Name',
            'email' => 'Email'
        );
    }

    /**
     * @param $apiFields
     * @return array
     */
    public static function formatModules($apiFields){
        $result = array();
        foreach($apiFields as $item){
            $result[$item['moduleid']] = $item['label'];
        }

        return $result;
    }

    /**
     * @param $apiFields
     * @param $module
     * @return array
     * @throws Exception
     */
    public static function formatModuleFields($apiFields,$moduleId){
        foreach($apiFields as $module){
            if($module['moduleid'] == $moduleId){
                $result = array();

                foreach($module['fields'] as $item){
                    $result[$item['columnname']] = $item['label'];
                }

                return $result;
            }
        }

        throw new Exception('Wrong module selected');
    }

    /**
     * get modules and associated fields to map with cf7
     *
     * @return mixed
     * @throws Exception
     */
    public function getModulesFields(){
        $result = $this->sendGetRequest('custom?action=contactForm7getModulesFields&XDEBUG_SESSION_START=123');
        $result = json_decode($result,true);

        if(!isset($result['_embedded']['custom'])){
            throw new Exception('Can\'t get data. Unexpected server error');
        }

        return $result['_embedded']['custom'];
    }


    /**
     * @param $cf7Data
     * @param $posted_data
     * @return array|mixed
     */
    public function processCF7FormData($cf7Data,$posted_data){
        $post = array();
        $customs = $cf7Data['customs'];
        $customs_modules = $cf7Data['customs_modules'];
        $keys = array_keys($customs);

        foreach($keys as $key){
            $module = $customs_modules[$key];
            $field = $customs[$key];
            $value = $posted_data[$key];

            /* concat fields */
            if(isset($post[$module][$field])){
                $post[$module][$field] .= '<br>';
                $post[$module][$field] .= $value;
            }
            else{
                $post[$module][$field] = $value;
            }
            /**/
        }

        $result = $this->sendPostRequest($post,'custom?action=contactForm7ProcessFormData&XDEBUG_SESSION_START=123');
        $result = json_decode($result);
        $result = $this->processResultPost($result);
        return $result;
    }

    /**
     * @param $data
     * @param $module
     * @return array
     */
    protected function processResultGet($data,$module){
        $result = array();

        if(is_array($data['_embedded']['columns'])){

            if($module){
                $result['data'] = array();

                foreach($data['_embedded']['columns'] as $key=>$item){
                    if($item['moduleid'] == $module){
                        $result['data'][] = $item;
                    }
                }
            }

            $result['statusCode'] = 200;
            $result['statusText'] = 'mail_sent';
            $result['message'] = 'mail_sent_ok';
        }
        else{
            $result = array(
                'statusCode' => 400,
                'statusText' => 'error',
                'message' => 'can\'t get data'
            );
        }

        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    protected function processResultPost($result){
        $response = array(
            'statusCode' => 400,
            'statusText' => 'mail_failed',
            'message' => 'access_token_not_exist'
        );

        if(!isset($result->status)){
            $response['statusCode'] = 200;
            $response['statusText'] = 'mail_sent';
            $response['message'] = 'mail_sent_ok';
        }
        elseif($result->status == 422){
            $invalid_fields = array();

            foreach($result->validation_messages as $key => $value) {
                $invalid_fields[$key] = array(
                    'reason' => '',
                    'idref' => ''
                );

                foreach($value as $errorMessage) {
                    $invalid_fields[$key]['reason'] .= $errorMessage;
                }
            }

            $response['statusCode'] = 422;
            $response['statusText'] = 'validation_failed';
            $response['message'] = 'validation_error';
            $response['invalid_fields'] = $invalid_fields;
        }
        elseif($result->status == 403){
            $response['statusCode'] = 403;
            $response['statusText'] = 'mail_failed';
            $response['message'] = 'api_access_denied';
        }

        return $response;
    }
}