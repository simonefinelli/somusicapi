<?php

class SOMUSICAPI_CTRL_Notifications extends NOTIFICATIONS_CTRL_Notifications {


    public function allNotificationsNotViewed($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $id_user = intval(urldecode($params['userId']));

        if($userId != $id_user){
            exit(json_encode("Error."));
        }

        exit(json_encode(SOMUSICAPI_BOL_NotificationsService::getInstance()->allNotificationsNotViewed($id_user)));

    }


    public function allNotificationsViewed($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $id_user = intval(urldecode($params['userId']));

        if($userId != $id_user){
            exit(json_encode("Error."));
        }

        exit(json_encode(SOMUSICAPI_BOL_NotificationsService::getInstance()->allNotificationsViewed($id_user)));

    }


    public function setNotificationsAsViewed() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $ids = json_decode(urldecode($DATA['ids']));

        SOMUSICAPI_BOL_NotificationsService::getInstance()->setNotificationsAsViewed($ids);
        exit(json_encode(true));
    }

}