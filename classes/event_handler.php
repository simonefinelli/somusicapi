<?php

class SOMUSICAPI_CLASS_EventHandler {

    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    // Handle event and route
    public function init()
    {

        OW::getEventManager()->bind('friends.request-sent',     array($this, 'onSendFriendRequest'));

        OW::getEventManager()->bind('feed.after_status_update', array($this, 'onShareScore'));

        OW::getEventManager()->bind('feed.after_like_added',    array($this, 'onLike'));

        OW::getEventManager()->bind('feed.after_comment_add',   array($this, 'onComment'));

        OW::getEventManager()->bind('groups.invite_user',       array($this, 'onInviteGroup'));

    }


    //Friend request push notification
    public function onSendFriendRequest(OW_Event $event) {

        $params = $event->getParams();
        $recipientId = $params['recipientId'];
        $senderId = $params['senderId'];
        $realNameSender = BOL_UserService::getInstance()->getDisplayName($senderId);

        //Targets
        $registrationIds = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->getRegistrationIdsByUserId($recipientId);
        if(!empty($registrationIds)) {
            //Data
            $messageData['title']    = "Friend request";
            $messageData['message']  = $realNameSender . " send you a friend request.";

            //Send notification
            $this->sendPushNotification($messageData, $registrationIds);
        }

    }

    //Share composition push notification
    public function onShareScore(OW_Event $event) {

        $params = $event->getParams();
        $userId = $params['userId'];
        $realNameSender = BOL_UserService::getInstance()->getDisplayName($userId);
        $info = $event->getData();
        $compositionName = $info['status'];
        $flag = $info['isInMySpace'];

        if($flag == 1) {
            //Targets
            $friends = SOMUSICAPI_BOL_FriendsService::getInstance()->getFriends($userId);
            $targets = array();
            foreach ($friends as $friend) {
                $registrationIds = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->getRegistrationIdsByUserId($friend['id']);
                if (!empty($registrationIds)) {
                    foreach ($registrationIds as $registrationId) {
                        $targets[] = $registrationId;
                    }
                }
            }

            if (!empty($targets)) {
                //Data
                $messageData['title'] = "Share composition";
                $messageData['message'] = $realNameSender . " share the composition " . "\"" . $compositionName . "\".";

                //Send notification
                $this->sendPushNotification($messageData, $targets);
            }
        }

    }

    //Like push notification
    public function onLike(OW_Event $event) {

        $params = $event->getParams();
        $entityId = $params['entityId'];
        $entityType = $params['entityType'];
        if( $entityType == 'groups-status') {
            return;
        }
        $userId = $params['userId'];
        $realNameSender = BOL_UserService::getInstance()->getDisplayName($userId);

        $actionDto = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
        $action = get_object_vars ($actionDto);
        $data = get_object_vars ( json_decode( $action['data']) );
        $status = $data['status'];
        $dataCreator = get_object_vars($data['data']);
        $creatorPostId = $dataCreator['userId'];

        if($userId != $creatorPostId) {
            //Targets
            $registrationIds = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->getRegistrationIdsByUserId($creatorPostId);
            if(!empty($registrationIds)) {
                //Data
                $messageData['title']    = "Like post";
                $messageData['message']  = $realNameSender . " likes your status " . "\"" . $status . "\".";

                //Send notification
                $this->sendPushNotification($messageData, $registrationIds);
            }
        }

    }

    //Comment push notification
    public function onComment(OW_Event $event) {

        $params = $event->getParams();
        $entityId = intval($params['entityId']);
        $entityType = $params['entityType'];
        if( $entityType == 'groups-status') {
            return;
        }
        $userId = $params['userId'];
        $realNameSender = BOL_UserService::getInstance()->getDisplayName($userId);

        $actionDto = NEWSFEED_BOL_Service::getInstance()->findAction($entityType, $entityId);
        $action = get_object_vars ($actionDto);
        $data = get_object_vars ( json_decode( $action['data']) );
        $status = $data['status'];
        $dataCreator = get_object_vars($data['data']);
        $creatorPostId = $dataCreator['userId'];

        if($userId != $creatorPostId) {
            //Targets
            $registrationIds = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->getRegistrationIdsByUserId($creatorPostId);
            if(!empty($registrationIds)) {
                //Data
                $messageData['title']    = "Comment post";
                $messageData['message']  = $realNameSender . " comment your status " . "\"" . $status . "\".";

                //Send notification
                $this->sendPushNotification($messageData, $registrationIds);
            }
        }

    }

    //Join group push notification
    public function onInviteGroup(OW_Event $event) {

        $params = $event->getParams();
        $groupId = intval($params['groupId']);
        $userId = $params['userId'];
        $inviterId = $params['inviterId'];
        $realnameInviter = BOL_UserService::getInstance()->getDisplayName($inviterId);
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        //Targets
        $registrationIds = SOMUSICAPI_BOL_PushNotificationsService::getInstance()->getRegistrationIdsByUserId($userId);
        if(!empty($registrationIds)) {
            //Data
            $messageData['title']    = "Join group";
            $messageData['message']  = $realnameInviter . " invites you to \"" . $group->title . "\" group.";

            //Send notification
            $this->sendPushNotification($messageData, $registrationIds);
        }
        
    }

    private function sendPushNotification($messageData, $targets) {

        $title        = $messageData['title'];
        $subtitle     = "";
        $message      = $messageData['message'];
        $tickerText   = "";
        $vibrate      = 1;
        $sound        = 1;
        $largeIcon    = 'large_icon';
        $smallIcon    = 'small_icon';

        $notificationToSend = new SOMUSICAPI_CLASS_MobileNotification($title, $subtitle, $message, $tickerText, $vibrate, $sound, $largeIcon, $smallIcon);

        $notificationToSend->send($targets);
    }

}
