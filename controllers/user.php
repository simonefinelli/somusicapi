<?php

class SOMUSICAPI_CTRL_User extends BASE_CTRL_User{


    public function login(){

        if ( OW::getRequest()->isPost() )
        {
            try {
                $identity = $_REQUEST['username'];
                $password = $_REQUEST['password'];
                $rememberMe = (bool)$_REQUEST['rememberMe'];

                $result = $this->processSignIn($identity, $password, $rememberMe);
                $userID = $result->getUserId();
            }
            catch ( LogicException $e ) {

                $response= array('result' => false, 'message' => 'Error!');
                exit(json_encode($response));
            }

            $message = '';

            foreach ( $result->getMessages() as $value ) {
                $message .= $value;
            }

            if ( $result->isValid() ) {

                $jwt = new SOMUSICAPI_CLASS_Jwt();
                $token = array();
                $token['iss'] = $id ='ServerSoMusic';
                $token['exp'] = 1300819380;
                $token['data'] = ['userId'=>$userID];
                $token = $jwt->encode($token, 'secret_server_key');
                header("Authorization:".$token);

                $cookie = (array)BOL_UserService::getInstance()->findLoginCookieByUserId($userID);
                $response= array('result' => true, 'userID' => $userID, 'message' => $message, 'cookie' => $cookie['cookie'], 'token' => $token);
                echo json_encode($response);

                exit();
            }
            else {

                $response = array('result' => false, 'message' => $message);
                exit(json_encode($response));
            }

        }

        exit(json_encode(array()));

    }


    public function logout(){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $registrationId = $DATA['registrationId'];
        if(!empty($registrationId)){
            SOMUSICAPI_BOL_PushNotificationsService::getInstance()->deleteRegistrationId($userId, $registrationId);
        }

        OW::getUser()->logout();
        if ( isset($_COOKIE['ow_login']) ) {
            setcookie('ow_login', '', time() - 3600, '/');
        }
        OW::getSession()->set('no_autologin', true);

        header_remove("Authorization:" );

        exit(json_encode(true));

    }


    public function userInfo($params){

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $param_id = urldecode($params['id']);

        exit(json_encode(SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($param_id)));
    }


    private function processSignIn($identity1, $password1, $rememberMe1)
    {
        $data['identity']=$identity1;
        $data['password']=$password1;
        $data['remember']=$rememberMe1;
        return BOL_UserService::getInstance()->processSignIn($data['identity'], $data['password'], isset($data['remember']));
    }
}

