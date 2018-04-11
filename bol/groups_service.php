<?php

class SOMUSICAPI_BOL_GroupsService {

    const PLUGIN_KEY  = 'newsfeed';
    const ENTITY_TYPE = 'groups-status';

    private static $classInstance;
    private $action_dao;
    private $activity_dao;
    private $actionFeed_dao;
    private $like_dao;
    private $commentService;
    private $friendship_dao;
    private $groupsDao;
    private $service;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->action_dao       = NEWSFEED_BOL_ActionDao::getInstance();
        $this->activity_dao     = NEWSFEED_BOL_ActivityDao::getInstance();
        $this->actionFeed_dao   = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $this->like_dao         = NEWSFEED_BOL_LikeDao::getInstance();
        $this->commentService   = BOL_CommentService::getInstance();
        $this->friendship_dao   = FRIENDS_BOL_FriendshipDao::getInstance();
        $this->groupsDao        = GROUPS_BOL_GroupDao::getInstance();
        $this->service          = GROUPS_BOL_Service::getInstance();
    }

    public function allPostsOfGroup($id_group) {

        $example = new OW_Example();
        $example->andFieldEqual('pluginKey', SOMUSICAPI_BOL_GroupsService::PLUGIN_KEY);
        $example->andFieldEqual('entityType', SOMUSICAPI_BOL_GroupsService::ENTITY_TYPE);

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


            $activityId = $activity['id'];
            // join with action-feed
            $example = new OW_Example();
            $example->andFieldEqual('activityId', $activityId);
            $example->andFieldEqual('feedType', 'groups');

            $activity_byDao = $this->actionFeed_dao->findObjectByExample($example);
            $actionFeed = get_object_vars($activity_byDao);


            $realname = BOL_UserService::getInstance()->getDisplayName($user_id);

            $likesNumber = $this->like_dao->findCountByEntity(SOMUSICAPI_BOL_GroupsService::ENTITY_TYPE, $id_status);

            $result = $this->commentService->findFullCommentList(SOMUSICAPI_BOL_GroupsService::ENTITY_TYPE, $id_status);

            if($result == []){
                $bool = false;
            } else {
                $bool = true;
            }

            // add entry to status array
            $status[] = array(
                'id'        => $id_status,
                'status'    => $status_text,
                'userId'   => $user_id,
                'realname' => $realname,
                'likes_number' =>$likesNumber,
                'timestamp' => $timestamp,
                'comments' => $bool,
                'groupId' => $actionFeed['feedId']
            );

        }

        $result = array();
        foreach ($status as $post) {
            if ($post['groupId'] == $id_group) {
                $result[] = array(
                    'id'           => $post['id'],
                    'status'       => $post['status'],
                    'user_id'      => $post['userId'],
                    'realname'     => $post['realname'],
                    'likes_number' => $post['likes_number'],
                    'timestamp'    => $post['timestamp'],
                    'comments'     => $post['comments'],
                    'groupId'      => $post['groupId'],
                );
            }
        }

        return($result);
    }

    public function getLikesPostOfGroup($entityId) {

        $likes_byDao = $this->like_dao->findByEntity(SOMUSICAPI_BOL_GroupsService::ENTITY_TYPE, $entityId);

        $likes = array();

        foreach ($likes_byDao as $like_byDao) {

            // get action array from object
            $like = get_object_vars ($like_byDao);

            // get data from action array
            $id_like     = $like['id'];
            //$entity_type = $like['entityType'];
            $id_entity   = $like['entityId'];
            $user_id     = $like['userId'];
            $timestamp   = date('Y/m/d H:s', $like['timeStamp']);

            // add entry to status array
            $likes[] = array(
                'id'          => $id_like,
                'realname'    => BOL_UserService::getInstance()->getDisplayName($user_id),
                'postId'      => $id_entity,
                'userId'      => $user_id,
                'timestamp'   => $timestamp
            );

        }
        return $likes;
    }

    public function searchGroup($userId, $substring){

        $groups = $this->groupsDao->findAll();
        $res = array();

        $flag = false;
        foreach($groups as $group){
            if($group->whoCanView == GROUPS_BOL_Service::WCV_INVITE) {
                if($this->isMember($userId,$group->id )){
                    $flag = true;
                } else {
                    $flag = false;
                }
            } else {
                if (stristr($group->title, $substring)) {
                    $flag = true;
                }
            }

            if($flag){
                if (stristr($group->title, $substring)) {
                    $array['id']            = $group->id;
                    $array['title']         = $group->title;
                    $array['description']   = $group->description;
                    $array['adminId']       = $group->userId;
                    $array['isMember']      = SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $group->id);
                    $res[]= $array;
                }
            }
        }

        return SOMUSICAPI_CLASS_Sort::sortArray($res,'title',0);
    }

    public function usersList($groupId) {

        $groupDto = $this->service->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        $dtoList = $this->service->findUserList($groupId, 0, 1000);
        $response = array();
        foreach ($dtoList as $element) {
            $response[] = SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($element->id);
        }
        return SOMUSICAPI_CLASS_Sort::sortArray($response,'realname',0);
    }

    public function isMember($userId, $groupId){

        $list = $this->usersList($groupId);
        foreach ($list as $elem) {
            if($userId == $elem['id']){
                return true;
            }
        }
        return false;
    }

}