<?php

class SOMUSICAPI_CTRL_PushNotificationsManager extends OW_ActionController {


    public function savePushToken() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $registrationId = $DATA['registrationId'];
        if(empty($registrationId)){
            exit(json_encode("Error."));
        }
        $res = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->saveRegistrationId($userId, $registrationId);

        if($res == 1) {
            $msg = true;
        } else if ($res == 0) {
            $msg = "RegistrationId not changed.";
        } else {
            $msg = "RegistrationId already exist.";
        }
        exit(json_encode($msg));
    }
    
}