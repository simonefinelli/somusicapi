<?php

class SOMUSICAPI_BOL_PushNotificationsService {

    private static $classInstance;
    private $pushNotificationDao;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->pushNotificationDao = SOMUSICAPI_BOL_PushNotificationDao::getInstance();
    }

    public function saveRegistrationId($userId, $registrationId) {

        $example = new OW_Example();
        $example->andFieldEqual("registrationId", $registrationId);
        $result = $this->pushNotificationDao->findObjectByExample($example);

        if(empty($result)) {
            return $this->pushNotificationDao->insertRegistrationId($userId, $registrationId);
        } else if ($result->userId != $userId) {
            return 2;
        } else {
            return 0;
        }

    }

    public function deleteRegistrationId($userId, $registrationId) {

        $example = new OW_Example();
        $example->andFieldEqual("registrationId", $registrationId);
        $example->andFieldEqual("userId", $userId);
        $result = $this->pushNotificationDao->findObjectByExample($example);

        if(!empty($result)) {
            return $this->pushNotificationDao->deleteRegistrationId($registrationId);
        } else {
            return 0;
        }

    }

    public function getRegistrationIdsByUserId($userId) {

        return $this->pushNotificationDao->getRegistrationIdsByUserId($userId);
    }
}