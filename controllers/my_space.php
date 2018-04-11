<?php

class SOMUSICAPI_CTRL_MySpace extends SOMUSIC_CTRL_MySpace {


    public function addScore() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $editor = new SOMUSIC_CTRL_Editor();
        $composition = $editor->reset();
        if(intval($composition->getId())!=-1)
            SOMUSIC_BOL_Service::getInstance()->updateComposition($composition->getId(), json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
        else SOMUSICAPI_BOL_MySpaceService::getInstance()->addComposition($userId, $composition->name, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));

        exit(json_encode(true));
    }


    public function removeScore() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $id = $DATA['scoreId'];

        $composition = SOMUSIC_BOL_Service::getInstance()->getComposition(intval($id));
        if($composition === null){
            exit(json_encode("Error."));
        }
        if($composition->user_c != $userId){
            exit(json_encode("Error."));
        }
        if(!isset($id))
            exit(json_encode(false));

        SOMUSIC_BOL_Service::getInstance()->removeComposition(intval($id));

        exit(json_encode(true));
    }


    public function shareScore() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        $id = $DATA['scoreId'];

        $composition = SOMUSIC_BOL_Service::getInstance()->getComposition(intval($id));
        if($composition === null){
            exit(json_encode("Error."));
        }
        if($composition->user_c != $userId){
            exit(json_encode("Error."));
        }
        if(!isset($id))
            exit(json_encode(false));

        $composition = SOMUSIC_BOL_Service::getInstance()->getComposition(intval($id));
        $out = NEWSFEED_BOL_Service::getInstance ()->addStatus ( $userId, "user", $userId, 15, $composition->name, array (
            "content" => array(),
            "attachmentId" => null,
            "isInMySpace" => 1
        ));
        SOMUSIC_BOL_Service::getInstance ()->addMelodyOnPost1(intval($id), $out['entityId']);
        exit(json_encode(true));
    }


    public function getCompositions($params) {

        $idUser = SOMUSICAPI_CLASS_Jwt::checkToken();
        $userId = urldecode($params['idUser']);

        if($idUser != $userId){
            exit(json_encode("Error."));
        }
        $compositions = SOMUSIC_BOL_Service::getInstance()->getAllCompositions($userId);
        $response= $compositions;

        exit(json_encode($response));

    }

}