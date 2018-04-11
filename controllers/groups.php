<?php

class SOMUSICAPI_CTRL_Groups extends GROUPS_CTRL_Groups {

    const PLUGIN_KEY_GROUPS  = SOMUSICAPI_BOL_GroupsService::PLUGIN_KEY;
    const ENTITY_TYPE_GROUPS = SOMUSICAPI_BOL_GroupsService::ENTITY_TYPE;

    public function create() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $res = GROUPS_BOL_Service::getInstance()->findByTitle($DATA['title']);
        if($res !== null) {
            exit(json_encode("Name already used."));
        }

        $group = new GROUPS_BOL_Group();
        $group->userId = $userId;
        $group->title = $DATA['title'];
        $group->description = $DATA['description'];
        $group->timeStamp = time();
        $group->whoCanView = GROUPS_BOL_Service::WCV_ANYONE;
        $group->whoCanInvite = GROUPS_BOL_Service::WCI_CREATOR;

        GROUPS_BOL_Service::getInstance()->saveGroup($group);
        GROUPS_BOL_Service::getInstance()->addUser($group->id, $userId);

        $response[] = array(
            'id'          => $group->id,
            'title'       => $group->title,
            'description' => $group->description,
            'adminId'     => $group->userId
        );
        exit(json_encode($response));
    }


    public function delete() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($DATA['groupId']);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }

        $isOwner = $userId == $groupDto->userId;
        if ( !$isOwner )
        {
            exit(json_encode("Error."));
        }

        GROUPS_BOL_Service::getInstance()->deleteGroup($groupDto->id);

        exit(json_encode(true));
    }


    public function myGroupList() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $dtoList = GROUPS_BOL_Service::getInstance()->findMyGroups($userId, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {

            $array['id']            = $element->id;
            $array['title']         = $element->title;
            $array['description']   = $element->description;
            $array['adminId']       = $element->userId;
            $array['isMember']      = 1;
            $response[] = $array;
        }

        exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'title',0)));
    }


    public function myAdminGroupList() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $dtoList = GROUPS_BOL_Service::getInstance()->findMyGroups($userId, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {
            if($element->userId == $userId){
                $array['id']            = $element->id;
                $array['title']         = $element->title;
                $array['description']   = $element->description;
                $array['adminId']       = $element->userId;
                $array['isMember']      = 1;
                $response[] = $array;
            }
        }

        exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'title',0)));
    }


    public function myUserGroupList() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $dtoList = GROUPS_BOL_Service::getInstance()->findMyGroups($userId, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {
            if($element->userId != $userId){
                $array['id']            = $element->id;
                $array['title']         = $element->title;
                $array['description']   = $element->description;
                $array['adminId']       = $element->userId;
                $array['isMember']      = 1;
                $response[] = $array;
            }
        }

        exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'title',0)));
    }


    public function userList($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $groupId = urldecode($params['groupId']);
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res )
        {
            exit(json_encode(SOMUSICAPI_BOL_GroupsService::getInstance()->usersList($groupId)));

        } else {
            exit(json_encode("Error."));
        }
    }


    public function join() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $groupId = (int)$DATA['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        if ( $groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE )
        {
            $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupDto->id, $userId);

            if ( $invite !== null )
            {
                GROUPS_BOL_Service::getInstance()->markInviteAsViewed($groupDto->id, $userId);
                GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);

                exit(json_encode(true));
            } else {
                exit(json_encode(false));
            }

        } else {

            $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupDto->id, $userId);
            if ( $invite !== null )
            {
                GROUPS_BOL_Service::getInstance()->markInviteAsViewed($groupDto->id, $userId);
            }
            GROUPS_BOL_Service::getInstance()->addUser($groupId, $userId);

            exit(json_encode(true));

        }
    }


    public function isJoin(){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $groupId = $DATA['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        if(SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $groupId)) {
            exit(json_encode(true));
        } else {
            exit(json_encode(false));
        }
    }


    public function leave($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $groupId = (int)$DATA['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        if($userId == $groupDto->userId){
            exit(json_encode("error"));
        }

        GROUPS_BOL_Service::getInstance()->deleteUser($groupId, $userId);

        exit(json_encode(true));
    }


    public function invite() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $groupId = $DATA['groupId'];
        $userToInvite = $DATA['userId'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("error"));
        }

        if ( $groupDto->whoCanInvite == GROUPS_BOL_Service::WCI_CREATOR ) {

            if($userId == $groupDto->userId){
                GROUPS_BOL_Service::getInstance()->inviteUser($groupDto->id, $userToInvite, $userId);
                exit(json_encode(true));
            } else {
                exit(json_encode(false));
            }

        } else {
            if(SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $groupId)) {
                GROUPS_BOL_Service::getInstance()->inviteUser($groupDto->id, $userToInvite, $userId);
            } else {
                exit(json_encode("Error."));
            }
        }
    }


    public function declineInvite()
    {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        $groupId = (int) $DATA['groupId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $group === null )
        {
            exit(json_encode("error"));
        }
        $invite = GROUPS_BOL_Service::getInstance()->findInvite($groupId,$userId);
        if ( $invite === null )
        {
            exit(json_encode("error"));
        }

        GROUPS_BOL_Service::getInstance()->deleteInvite($groupId, $userId);

        exit(json_encode(true));
    }


    public function inviteList() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        GROUPS_BOL_Service::getInstance()->markAllInvitesAsViewed($userId);
        $dtoList = GROUPS_BOL_Service::getInstance()->findInvitedGroups($userId, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {
            $array['id']            = $element->id;
            $array['title']         = $element->title;
            $array['description']   = $element->description;
            $array['adminId']       = $element->userId;
            $array['isMember']      = 1;
            $response[] = $array;
        }

        exit(json_encode($response));
    }


    public function allGroups() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $dtoList = GROUPS_BOL_Service::getInstance()->findGroupList(GROUPS_BOL_Service::LIST_ALL, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {
            if($element->whoCanView != GROUPS_BOL_Service::WCV_INVITE) {
                $array['id']          = $element->id;
                $array['title']       = $element->title;
                $array['description'] = $element->description;
                $array['adminId']     = $element->userId;
                $array['isMember']    = SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $element->id);
                $response[] = $array;
            }
        }

        exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'title',0)));
    }


    public function latestList() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $dtoList = GROUPS_BOL_Service::getInstance()->findGroupList(GROUPS_BOL_Service::LIST_LATEST, $first=null, $count=1000);
        $response = array();
        foreach ($dtoList as $element) {
            if($element->whoCanView != GROUPS_BOL_Service::WCV_INVITE) {
                $array['id'] = $element->id;
                $array['title'] = $element->title;
                $array['description'] = $element->description;
                $array['adminId'] = $element->userId;
                $response[] = $array;
            }
        }

        exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'title',0)));
    }


    public function addPostInGroup() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $userId = $DATA['userId'];
        $groupId = intval($DATA['groupId']);
        $msg = $DATA['msg'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }

        if ( SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $groupId) ) {

            $out = NEWSFEED_BOL_Service::getInstance()->addStatus( $userId, 'groups', $groupId, 15,$msg, array (
                "content" => array(),
                "attachmentId" => null
            ));
            exit(json_encode($out));
        } else {

            exit(json_encode("Error."));
        }
    }


    public function deletePostInGroup($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $postId = urldecode($params['postId']);

        $actionByDao = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $actionByDao === null )
        {
            exit(json_encode("error"));
        }
        $action = get_object_vars ($actionByDao);
        $data        = get_object_vars ( json_decode( $action['data']) );
        $data_data   = get_object_vars ( $data['data'] );
        $user_id     = $data_data['userId'];

        if($userId == $user_id){
            NEWSFEED_BOL_Service::getInstance()->removeAction(self::ENTITY_TYPE_GROUPS, $postId);
            exit(json_encode(true));
        }else{
            exit(json_encode(false));
        }
    }


    public function allPostsGroup($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $groupId = urldecode($params['groupId']);
        
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {
            exit(json_encode(SOMUSICAPI_BOL_GroupsService::getInstance()->allPostsOfGroup($groupId)));
        } else {
            exit(json_encode("Error."));
        }
    }


    public function getPostsByIntervalInGroup($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $groupId = intval(urldecode($params['groupId']));
        $index = intval(urldecode($params['index']));
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {

            $allPosts = SOMUSICAPI_BOL_GroupsService::getInstance()->allPostsOfGroup($groupId);
            $reverse = array_reverse($allPosts);
            $response = array();
            $range = $index+5;
            for($i = $index; $i < $range; $i++ ){

                if($reverse[$i] == null){
                    break;
                }
                $response[] = $reverse[$i];
            }
            exit(json_encode($response));
        } else {
            exit(json_encode("Error"));
        }
    }


    public function addCommentPost() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['entityId'];
        $groupId = $DATA['groupId'];
        $message = $DATA['msg'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {

            $comment = BOL_CommentService::getInstance()->addComment(self::ENTITY_TYPE_GROUPS, $postId, self::PLUGIN_KEY_GROUPS, $userId, $message, null);

            $eventParams = array(
                'entityType' => self::ENTITY_TYPE_GROUPS,
                'entityId' => $postId,
                'userId' => $userId,
                'pluginKey' => self::PLUGIN_KEY_GROUPS,
                'commentId' => $comment->id
            );
            OW::getEventManager()->trigger(new OW_Event('base_add_comment', $eventParams));

            exit(json_encode($comment));

        } else {
            exit(json_encode("Error"));
        }
    }


    public function deleteCommentPost($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $idComment = urldecode($params['commentId']);
        $groupId = urldecode($params['groupId']);

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }
        $comment = BOL_CommentService::getInstance()->findComment($idComment);
        if ( $comment === null )
        {
            exit(json_encode("Error"));
        }

        if($comment->userId == $userId){

            $res = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
            BOL_CommentService::getInstance()->deleteComment($idComment);
            $eventParams = array(
                'entityType' => $res->entityType,
                'entityId' => $res->entityId,
                'userId' => $userId,
                'pluginKey' => $res->pluginkey,
                'commentId' => $idComment
            );
            OW::getEventManager()->trigger(new OW_Event('base_delete_comment', $eventParams));
            exit(json_encode(true));

        } else {
            exit(json_encode(false));
        }
    }


    public function getCommentsPost($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $postId  = urldecode($params['postId']);
        $groupId = urldecode($params['groupId']);

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {
            exit(json_encode(BOL_CommentService::getInstance()->findFullCommentList(self::ENTITY_TYPE_GROUPS, $postId)));
        } else {
            exit(json_encode("Error"));
        }
    }


    public function like() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['postId'];
        $groupId = $DATA['groupId'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {

            $like = NEWSFEED_BOL_Service::getInstance()->addLike($userId, self::ENTITY_TYPE_GROUPS, $postId);

            $event = new OW_Event('feed.after_like_added', array(
                'entityType' => self::ENTITY_TYPE_GROUPS,
                'entityId' => $postId,
                'userId' => $userId
            ), array(
                'likeId' => $like->id
            ));
            OW::getEventManager()->trigger($event);

            exit(json_encode(true));
        } else {
            exit(json_encode("Error"));
        }
    }


    public function isLiked($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $postId = urldecode($params['postId']);
        $groupId = urldecode($params['groupId']);

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error."));
        }

        exit(json_encode(NEWSFEED_BOL_Service::getInstance()->isLiked($userId, self::ENTITY_TYPE_GROUPS, $postId)));
    }


    public function getLikesPost($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $postId = intval(urldecode($params['postId']));
        $groupId = intval(urldecode($params['groupId']));

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error."));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {
            exit(json_encode(SOMUSICAPI_BOL_GroupsService::getInstance()->getLikesPostOfGroup($postId)));

        } else {
            exit(json_encode("Error error"));
        }
    }


    public function unlike() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['postId'];
        $groupId = $DATA['groupId'];

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error."));
        }
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_GROUPS, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error."));
        }

        $res = $this->isAuthorized($groupDto->whoCanView, $userId, $groupId);
        if ( $res ) {

            NEWSFEED_BOL_Service::getInstance()->removeLike($userId, self::ENTITY_TYPE_GROUPS, $postId);

            $event = new OW_Event('feed.after_like_removed', array(
                'entityType' => self::ENTITY_TYPE_GROUPS,
                'entityId' => $postId,
                'userId' => $userId
            ));
            OW::getEventManager()->trigger($event);

            exit(json_encode(true));

        } else {
            exit(json_encode("Error"));
        }
    }


    public function invitationsLeft($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $groupId = urldecode($params['groupId']);

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if ( $groupDto === null )
        {
            exit(json_encode("Error"));
        }

        if(SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $groupId)) {

            if ( $groupDto->whoCanInvite == GROUPS_BOL_Service::WCI_CREATOR ) {
                if($userId == $groupDto->userId){
                    $flag = true;
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }

            if($flag){
                $friends = SOMUSICAPI_BOL_FriendsService::getInstance()->getFriends($userId);
                $response = array();
                foreach ($friends as $friend) {
                    $resp = GROUPS_BOL_Service::getInstance()->findInvite($groupId, $friend['id']);
                    if(!$resp &&  $friend['id']!=$userId && !SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($friend['id'], $groupId)){
                        $response[] =  SOMUSICAPI_BOL_UserService::getInstance()->userInfo1($friend['id']);
                    }

                }
                exit(json_encode(SOMUSICAPI_CLASS_Sort::sortArray($response,'realname',0)));
            } else {
                exit(json_encode("Error"));
            }

        } else {
            exit(json_encode("Error"));
        }
    }


    public function searchGroup($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $substring = urldecode($params['groupname']);

        exit(json_encode(SOMUSICAPI_BOL_GroupsService::getInstance()->searchGroup($userId, $substring)));
    }


    private function isAuthorized($whoCanView, $userId, $groupId) {

        if ( $whoCanView == GROUPS_BOL_Service::WCV_INVITE )
        {
            if(SOMUSICAPI_BOL_GroupsService::getInstance()->isMember($userId, $groupId)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }
}