<?php

class SOMUSICAPI_BOL_FriendsService {

    private static $classInstance;
    private $friendship_dao;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->friendship_dao = FRIENDS_BOL_FriendshipDao::getInstance();
    }

    public function getFriends($param_id) {

        $friendships_byDao = $this->friendship_dao->findFriendshipListByUserId($param_id);
        $friendships_toReturn = array();

        foreach($friendships_byDao as $friendship_byDao) {

            // find Friendship By Id to get Timestamp
            $friendshipFind_byDao = $this->friendship_dao->findById($friendship_byDao->id);

            // get actions array from object
            $friendship = get_object_vars($friendshipFind_byDao);
            //$user_id   = intval($friendship['userId']);
            $friend_id = intval($friendship['friendId']);

            // check if the friendship is reverse
            if($param_id == $friend_id) {
                //$user_id   = intval($friendship['friendId']);
                $friend_id = intval($friendship['userId']);
            }
            //$timestamp = date('Y/m/d H:s', $friendship['timeStamp']);

            if($friend_id != $param_id){
                $friendships_toReturn[] = SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($friend_id);
            }
        }

        return SOMUSICAPI_CLASS_Sort::sortArray($friendships_toReturn,'realname',0);
    }

    public function delete($userId, $friendId) {

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('friendId', $friendId);
        return $this->friendship_dao->deleteByExample($example);

    }
}