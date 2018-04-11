<?php

class SOMUSICAPI_CTRL_Newsfeed extends OW_ActionController {

    const PLUGIN_KEY_NEWSFEED  = SOMUSICAPI_BOL_NewsfeedService::PLUGIN_KEY;
    const ENTITY_TYPE_NEWSFEED = SOMUSICAPI_BOL_NewsfeedService::ENTITY_TYPE;

    public function addPost() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $msg = $DATA['msg'];

        $out = NEWSFEED_BOL_Service::getInstance()->addStatus( $userId, 'user', $userId, 15,$msg, array (
            "content" => array(),
            "attachmentId" => null
        ));
        exit(json_encode($out));
    }


    public function deletePost($params)
    {
        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $postId = urldecode($params['postId']);

        $actionByDao = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $actionByDao === null )
        {
            exit(json_encode("error"));
        }
        $action = get_object_vars ($actionByDao);
        $data        = get_object_vars ( json_decode( $action['data']) );
        $data_data   = get_object_vars ( $data['data'] );
        $user_id     = $data_data['userId'];

        if($userId == $user_id){
            NEWSFEED_BOL_Service::getInstance()->removeAction(self::ENTITY_TYPE_NEWSFEED, $postId);
            exit(json_encode(true));
        }else{
            exit(json_encode(false));
        }
    }


    public function like() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['postId'];
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        $like = NEWSFEED_BOL_Service::getInstance()->addLike($userId, self::ENTITY_TYPE_NEWSFEED, $postId);

        $event = new OW_Event('feed.after_like_added', array(
            'entityType' => self::ENTITY_TYPE_NEWSFEED,
            'entityId' => $postId,
            'userId' => $userId
        ), array(
            'likeId' => $like->id
        ));
        OW::getEventManager()->trigger($event);

        exit(json_encode(true));
    }


    public function isLiked($params) {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $postId = urldecode($params['postId']);
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error."));
        }

        exit(json_encode(NEWSFEED_BOL_Service::getInstance()->isLiked($userId, self::ENTITY_TYPE_NEWSFEED, $postId)));
    }


    public function unlike() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['postId'];
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        NEWSFEED_BOL_Service::getInstance()->removeLike($userId, self::ENTITY_TYPE_NEWSFEED, $postId);

        $event = new OW_Event('feed.after_like_removed', array(
            'entityType' => self::ENTITY_TYPE_NEWSFEED,
            'entityId' => $postId,
            'userId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        exit(json_encode(true));
    }


    public function allPosts(){

        SOMUSICAPI_CLASS_Jwt::checkToken();

        exit(json_encode(SOMUSICAPI_BOL_NewsfeedService::getInstance()->allPosts()));
    }


    public function getPostsByInterval($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $index = intval(urldecode($params['index']));

        $allPosts = SOMUSICAPI_BOL_NewsfeedService::getInstance()->allPostsOfFriends($userId);
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
    }


    public function getUserPosts($params) {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $userId = urldecode($params['userId']);

        exit(json_encode(SOMUSICAPI_BOL_NewsfeedService::getInstance()->getUserPosts($userId)));

    }


    public function getPostsComposition() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        exit(json_encode(SOMUSICAPI_BOL_NewsfeedService::getInstance()->getPostsWithComposition()));

    }


    public function getScoreByPostId($params) {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $postId = urldecode($params['postId']);
        $actionByDao = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( empty($actionByDao) )
        {
            exit(json_encode("Error."));
        }

        exit(json_encode(SOMUSICAPI_BOL_NewsfeedService::getInstance()->getScoreByPostId($postId)));
    }


    public function getLikesPost($params) {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $postId = intval(urldecode($params['postId']));
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        exit(json_encode(SOMUSICAPI_BOL_NewsfeedService::getInstance()->getLikesPost($postId)));
    }


    public function addCommentPost() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $postId = $DATA['entityId'];
        $massage = $DATA['msg'];

        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        $comment = BOL_CommentService::getInstance()->addComment(self::ENTITY_TYPE_NEWSFEED, $postId, self::PLUGIN_KEY_NEWSFEED, $userId, $massage, null);

        $eventParams = array(
            'entityType' => self::ENTITY_TYPE_NEWSFEED,
            'entityId' => $postId,
            'userId' => $userId,
            'pluginKey' => self::PLUGIN_KEY_NEWSFEED,
            'commentId' => $comment->id
        );

        OW::getEventManager()->trigger(new OW_Event('base_add_comment', $eventParams));

        exit(json_encode($comment));
    }


    public function deleteCommentPost($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $idComment = urldecode($params['commentId']);

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

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $postId = urldecode($params['idPost']);
        $newsfeedDto = NEWSFEED_BOL_Service::getInstance()->findAction(self::ENTITY_TYPE_NEWSFEED, $postId);
        if ( $newsfeedDto === null )
        {
            exit(json_encode("Error"));
        }

        exit(json_encode(BOL_CommentService::getInstance()->findFullCommentList(self::ENTITY_TYPE_NEWSFEED, $postId)));
    }

}