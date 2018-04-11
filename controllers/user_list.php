<?php

class SOMUSICAPI_CTRL_UserList extends BASE_CTRL_UserList {


    public function allUsers(){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        exit(json_encode(SOMUSICAPI_BOL_UserListService::getInstance()->allUsers($userId)));
    }


    public function searchUser($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $substring = urldecode($params['realname']);

        exit(json_encode(SOMUSICAPI_BOL_UserListService::getInstance()->searchUser($userId, $substring)));
    }
}

