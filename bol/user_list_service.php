<?php

class SOMUSICAPI_BOL_UserListService {

    private static $classInstance;
    private $userDao;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct() {
        $this->userDao = BOL_UserDao::getInstance();
    }

    public function allUsers($userId){

        $users = $this->userDao->findAll();
        $result = array();
        foreach($users as $user){
            if($user->id != $userId) {
                $result[] = SOMUSICAPI_BOL_UserService::getInstance()->userInfo1(intval($user->id));
            }
        }
        return $result;
    }

    public function searchUser($userId, $substring){

        $users = $this->userDao->findAll();
        $res = array();
        foreach($users as $user){
            if($user->id != $userId) {
                $realname = BOL_UserService::getInstance()->getDisplayName(intval($user->id));
                if (stristr($realname, $substring)) {
                    $res[]= SOMUSICAPI_BOL_UserService::getInstance()->userInfo1(intval($user->id));
                }
            }
        }

        return SOMUSICAPI_CLASS_Sort::sortArray($res,'realname',0);
    }

}