<?php

class SOMUSICAPI_BOL_NotificationsService {

    private static $classInstance;
    private $notificationDao;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->notificationDao   = NOTIFICATIONS_BOL_NotificationDao::getInstance();
    }

    public function allNotificationsNotViewed($id_user) {

        return $this->allNotifications($id_user, 0);
    }

    public function allNotificationsViewed($id_user){

        return $this->allNotifications($id_user, 1);
    }

    public function setNotificationsAsViewed($ids) {

        $this->notificationDao->markViewedByIds($ids);
    }

    private function allNotifications($id_user, $viewed) {

        $example = new OW_Example();
        $example->andFieldEqual('userId', $id_user);
        $notifications_byDao = $this->notificationDao->findListByExample($example);

        $status = array();

        foreach ($notifications_byDao as $notification_byDao) {

            // get action array from object
            $notification = get_object_vars ($notification_byDao);

            if($notification['viewed'] == $viewed) {

                // get data from action array
                $id_status   = $notification['entityId'];

                $data          = get_object_vars ( json_decode( $notification['data']) );
                $data_avatar   = get_object_vars ($data['avatar']);
                $data_string   = get_object_vars($data['string']);
                $data_vars     = get_object_vars($data_string['vars']);

                $user = SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($data_avatar['userId']);
                $url_avatar = $user['avatar'];

                $message="";
                switch ($data_string['key']) {
                    case 'newsfeed+email_notifications_status_like':
                        $message = $data_avatar['title'] . " likes your status " . "\"" . $data_vars['status'] . "\".";
                        break;
                    case 'newsfeed+email_notifications_status_comment':
                        $message = $data_avatar['title'] . " commented on your status " . "\"" . $data_vars['status'] . "\".";
                        break;
                    case 'friends+notify_accept':
                        $message = $data_avatar['title'] . " accepted you as a friend.";
                        break;
                }

                if ($message != "") {

                    $status[] = array(
                        'notificationId' => $notification['id'],
                        'idStatus' => $id_status,
                        'key' => $data_string['key'],
                        'statusName' => $data_vars['status'],
                        'userId' => $data_avatar['userId'],
                        'realname' => $data_avatar['title'],
                        'msg' => $message,
                        'userAvatar' => $url_avatar
                    );
                }
            }
        }

        return SOMUSICAPI_CLASS_Sort::sortArray($status,'notificationId',3);

    }

}