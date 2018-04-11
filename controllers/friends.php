<?php

class SOMUSICAPI_CTRL_Friends extends FRIENDS_CTRL_Action {


    public function accept() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $requesterId = $DATA['requesterId'];

        $frendshipDto =  FRIENDS_BOL_Service::getInstance()->accept($userId, $requesterId);
        if ( !empty($frendshipDto) )
        {
            FRIENDS_BOL_Service::getInstance()->onAccept($userId, $requesterId, $frendshipDto);
        }
        if(isset($frendshipDto)){
            exit(json_encode($frendshipDto));
        }else{
            exit(json_encode("Error."));
        }
    }


    public function request() {

        $requesterId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $userId = $DATA['userId'];

        if( $requesterId == $userId ){
            exit(json_encode(false));
        }

        $friendship = FRIENDS_BOL_Service::getInstance()->findFriendship($requesterId, $userId);
        if ( !empty($friendship) )
        {
            exit(json_encode(false));
        }

        FRIENDS_BOL_Service::getInstance()->request($requesterId, $userId);
        FRIENDS_BOL_Service::getInstance()->onRequest($requesterId, $userId);

        exit(json_encode(true));
    }


    public function ignore() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $requesterId = $DATA['requesterId'];

        FRIENDS_BOL_Service::getInstance()->ignore($requesterId, $userId);

        exit(json_encode(true));
    }


    public function cancel() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $requesterId = $DATA['requesterId'];

        $event = new OW_Event('friends.cancelled', array(
            'senderId' => $requesterId,
            'recipientId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        exit(json_encode(true));
    }


    public function delete($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $friendId = intval(urldecode($params['friendId']));

        $friendsService = SOMUSICAPI_BOL_FriendsService::getInstance();
        if ($friendsService->delete($userId, $friendId) == 1 || $friendsService->delete($friendId, $userId) == 1) {
            NEWSFEED_BOL_Service::getInstance()->removeFollow($userId, 'user', $friendId);
            NEWSFEED_BOL_Service::getInstance()->removeFollow($friendId, 'user', $userId);
            $flag = true;
        } else {
            $flag = false;
        }

        exit(json_encode($flag));
    }


    public function findFriendship($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $user2Id = intval(urldecode($params['userId']));

        $result = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $user2Id);
        if(!isset($result)){
            $arr['userId'] = $userId;
            $arr['friendId'] = $user2Id;
            $arr['status'] = "notFriends";
            $arr['timeStamp'] = "";
            $arr['viewed'] = 0;
            $arr['active'] = 0;
            $arr['notificationSent'] = 0;
            $arr['id'] = 0;
            $arr['realname']="";
            exit(json_encode($arr));
        } else {
            $arr['userId'] = $userId;
            $arr['friendId'] = $user2Id;
            $arr['status'] = $result->status;
            $arr['timeStamp'] = $result->timeStamp;
            $arr['viewed'] = $result->viewed;
            $arr['active'] = $result->active;
            $arr['notificationSent'] = $result->notificationSent;
            $arr['id'] = $result->id;
            $arr['realname']= BOL_UserService::getInstance()->getDisplayName($userId);
            exit(json_encode($arr));
        }

    }


    public function getInfo($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $serviceUser = BOL_UserService::getInstance();
        $listType = (string)urldecode($params['request']); //'sent-requests' [richieste inviate] -- 'got-requests' [richieste ricevute]
        
        $first = intval(0);
        $count = intval(100);
        $idList = FRIENDS_BOL_Service::getInstance()->findFriendIdList($userId, $first, $count, $listType);
        $list = $serviceUser->findUserListByIdList($idList);
        $response = array();
        foreach ($list as $element){

            $userId = $element->id;
            $response[] = SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($userId);
        }
        exit (json_encode($response));

    }

    public function getFriends() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $friends = SOMUSICAPI_BOL_FriendsService::getInstance()->getFriends($userId);

        exit(json_encode($friends));
    }


    public function isFriend($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $user2Id = urldecode($params['userId']);

        $result = FRIENDS_BOL_Service::getInstance()->findFriendship($userId, $user2Id);
        if($result->status == 'active') {
            exit(json_encode(true));
        } else {
            exit(json_encode(false));
        }
    }

}