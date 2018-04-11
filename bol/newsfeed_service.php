<?php

class SOMUSICAPI_BOL_NewsfeedService {

    const PLUGIN_KEY  = 'newsfeed';
    const ENTITY_TYPE = 'user-status';

    private static $classInstance;
    private $action_dao;
    private $activity_dao;
    private $like_dao;
    private $commentService;
    private $somusicPostDao;
    private $compositionDao;
    private $serviceFriends;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->action_dao        = NEWSFEED_BOL_ActionDao::getInstance();
        $this->activity_dao      = NEWSFEED_BOL_ActivityDao::getInstance();
        $this->like_dao          = NEWSFEED_BOL_LikeDao::getInstance();
        $this->commentService    = BOL_CommentService::getInstance();
        $this->somusicPostDao    = SOMUSIC_BOL_SomusicPostDao::getInstance();
        $this->compositionDao    = SOMUSIC_BOL_CompositionDao::getInstance();
        $this->serviceFriends    = FRIENDS_BOL_Service::getInstance();

    }

    public function allPosts() {

        $example = new OW_Example();
        $example->andFieldEqual('pluginKey', SOMUSICAPI_BOL_NewsfeedService::PLUGIN_KEY);
        $example->andFieldEqual('entityType', SOMUSICAPI_BOL_NewsfeedService::ENTITY_TYPE);

        $actions_byDao = $this->action_dao->findListByExample($example);

        $status = array();

        foreach ($actions_byDao as $action_byDao) {

            // get action array from object
            $action = get_object_vars ($action_byDao);

            // get data from action array
            $id_action   = $action['id'];
            $id_status   = $action['entityId'];

            $data        = get_object_vars ( json_decode( $action['data']) );
            $data_data   = get_object_vars ( $data['data'] );

            $status_text = $data_data['status'];
            $user_id     = $data_data['userId'];

            // join with activity (to get timestamp)
            $example = new OW_Example();
            $example->andFieldEqual('actionId', $id_action);
            $example->andFieldEqual('activityType', 'create');

            $activity_byDao = $this->activity_dao->findObjectByExample($example);
            $activity = get_object_vars($activity_byDao);

            $timestamp = date('Y/m/d H:s', $activity['timeStamp']);

            $realname = BOL_UserService::getInstance()->getDisplayName($user_id);

            $likesNumber = $this->like_dao->findCountByEntity("user-status", $id_status);

            $result = $this->commentService->findFullCommentList("user-status", $id_status);
            if($result == []){
                $bool = false;
            } else {
                $bool = true;
            }

            // add entry to status array
            $status[] = array(
                'id'           => $id_status,
                'status'       => $status_text,
                'user_id'      => $user_id,
                'realname'     => $realname,
                'likes_number' => $likesNumber,
                'timestamp'    => $timestamp,
                'comments'     => $bool
            );

        }

        return $status;
    }

    public function allPostsOfFriends($userId) {

        $example = new OW_Example();
        $example->andFieldEqual('pluginKey', SOMUSICAPI_BOL_NewsfeedService::PLUGIN_KEY);
        $example->andFieldEqual('entityType', SOMUSICAPI_BOL_NewsfeedService::ENTITY_TYPE);

        $actions_byDao = $this->action_dao->findListByExample($example);

        $status = array();

        foreach ($actions_byDao as $action_byDao) {

            // get action array from object
            $action = get_object_vars ($action_byDao);

            // get data from action array
            $id_action   = $action['id'];
            $id_status   = $action['entityId'];

            $data        = get_object_vars ( json_decode( $action['data']) );
            $data_data   = get_object_vars ( $data['data'] );

            $status_text = $data_data['status'];
            $user_id     = $data_data['userId'];

            $friendship = $this->serviceFriends->findFriendship($userId, $user_id);
            if($friendship->status == 'active' || $userId == $user_id) {

                // join with activity (to get timestamp)
                $example = new OW_Example();
                $example->andFieldEqual('actionId', $id_action);
                $example->andFieldEqual('activityType', 'create');

                $activity_byDao = $this->activity_dao->findObjectByExample($example);
                $activity = get_object_vars($activity_byDao);

                $timestamp = date('Y/m/d H:s', $activity['timeStamp']);

                $realname = BOL_UserService::getInstance()->getDisplayName($user_id);

                $likesNumber = $this->like_dao->findCountByEntity("user-status", $id_status);

                $result = $this->commentService->findFullCommentList("user-status", $id_status);
                if ($result == []) {
                    $bool = false;
                } else {
                    $bool = true;
                }

                // add entry to status array
                $status[] = array(
                    'id' => $id_status,
                    'status' => $status_text,
                    'user_id' => $user_id,
                    'realname' => $realname,
                    'likes_number' => $likesNumber,
                    'timestamp' => $timestamp,
                    'comments' => $bool
                );
            }
        }
        return $status;
    }

    public function getUserPosts($userId) {

        $actions_byDao = $this->action_dao->findListByUserId($userId);
        $status_toReturn = array();

        foreach($actions_byDao as $action_byDao) {

            // get actions array from object
            $action = get_object_vars ($action_byDao);

            $entityType = 'user-status';
            $pluginKey  = 'newsfeed';

            if( (strcmp($action['entityType'], $entityType) == 0) && (strcmp($action['pluginKey'], $pluginKey) == 0) ) {

                // get data from action array
                $id_action   = $action['id'];
                $id_status   = $action['entityId'];
                $data        = get_object_vars ( json_decode( $action['data']) );
                $data_data   = get_object_vars ( $data['data'] );
                $status_text = $data_data['status'];
                $user_id     = $data_data['userId'];

                // join with activity (to get timestamp)
                $example = new OW_Example();
                $example->andFieldEqual('actionId', $id_action);
                $example->andFieldEqual('activityType', 'create');

                $activity_byDao = $this->activity_dao->findObjectByExample($example);
                $activity       = get_object_vars($activity_byDao);
                $timestamp      = date('Y/m/d H:s', $activity['timeStamp']);

                $user_name = BOL_UserService::getInstance()->getDisplayName($user_id);

                $likesNumber = $this->like_dao->findCountByEntity("user-status", $id_status);


                $result = $this->commentService->findFullCommentList("user-status", $id_status);
                if($result == []){
                    $bool = false;
                } else {
                    $bool = true;
                }

                // add entry to status array
                $status_toReturn[] = array(
                    'id'        => $id_status,
                    'status'    => $status_text,
                    'user_id'   => $user_id,
                    'user_name' => $user_name,
                    'likes_number' =>$likesNumber,
                    'timestamp' => $timestamp,
                    'comments' => $bool
                );

            }

        }

        return $status_toReturn;
    }

    public function getPostsWithComposition() {

        return $this->somusicPostDao->findAll();
    }

    public function getScoreByPostId($postId) {

        $somusicPost = $this->somusicPostDao->findByPostId($postId);
        if(!isset($somusicPost)) {
            return null;
        }
        $result=$this->compositionDao->findById($somusicPost->id_melody);

        return SOMUSIC_CLASS_Composition::getCompositionObject($result);
    }

    public function getLikesPost($entityId) {

        $likes_byDao = $this->like_dao->findByEntity(SOMUSICAPI_BOL_NewsfeedService::ENTITY_TYPE, $entityId);

        $likes = array();

        foreach ($likes_byDao as $like_byDao) {

            // get action array from object
            $like = get_object_vars ($like_byDao);

            // get data from action array
            $id_like     = $like['id'];
            $id_entity   = $like['entityId'];
            $user_id     = $like['userId'];
            $timestamp   = date('Y/m/d H:s', $like['timeStamp']);

            // add entry to status array
            $likes[] = array(
                'id'          => $id_like,
                'realname'    => BOL_UserService::getInstance()->getDisplayName($user_id),
                'postId'     => $id_entity,
                'userId'     => $user_id,
                'timestamp'   => $timestamp
            );

        }

        return $likes;
    }

}