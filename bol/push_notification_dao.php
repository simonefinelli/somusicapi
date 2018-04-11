<?php

class SOMUSICAPI_BOL_PushNotificationDao extends OW_BaseDao {

    private static $classInstance;

    protected function __construct() {
        parent::__construct ();
    }

    public static function getInstance() {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    public function getDtoClassName() {
        return 'SOMUSICAPI_BOL_PushNotification';
    }

    public function getTableName() {
        return OW_DB_PREFIX.'somusicapi_push_notification';
    }

    public function getRegistrationIdsByUserId($userId) {
        $example = new OW_Example();
        $example->andFieldEqual("userId", $userId);
        $elements = $this->findListByExample($example);

        $registrationIds = array();
        foreach ($elements as $element) {
            $registrationIds[] = $element->registrationId;
        }

        return $registrationIds;
    }

    public function insertRegistrationId($userId, $registrationId){

        $query = 'INSERT INTO '.$this->getTableName(). ' (`registrationId`, `userId`) VALUES (:registrationId, :userId)';
        return $this->dbo->query($query, array(
            "registrationId" => $registrationId,
            "userId" => $userId
        ));
    }

    public function deleteRegistrationId($registrationId){

        $query = 'DELETE FROM '.$this->getTableName(). ' WHERE registrationId= :registrationId';
        return $this->dbo->query($query, array(
            "registrationId" => $registrationId
        ));
    }

}
