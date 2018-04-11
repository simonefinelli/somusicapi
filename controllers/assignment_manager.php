<?php

class SOMUSICAPI_CTRL_AssignmentManager extends SOMUSIC_CTRL_AssignmentManager {

    public function __construct() {
        parent::__construct();
    }


    public function newAssignment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["groupId"]) || !isset($DATA["name"]) || !isset($DATA["isMultiUser"]))
            exit(json_encode($this->error("incorrect arguments")));
        if(strlen($DATA["name"])==0)
            exit(json_encode($this->error("incorrect name")));
        if(SOMUSIC_BOL_Service::getInstance()->isGroupNameUsed($DATA["groupId"], $DATA["name"]))
            exit(json_encode($this->error("name already used")));
        $assignment = array("group_id"=>$DATA["groupId"], "name"=>$DATA["name"], "is_multi_user"=>intval($DATA["isMultiUser"]));
        OW::getSession()->set("newAssignment", json_encode($assignment));
        exit(json_encode($assignment));
    }


    public function saveNewAssignment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        parent::saveNewAssignment();
    }


    public function commitExecution() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $assignmentId = intval($DATA['assignmentId']);

        if(!isset($assignmentId))
            exit(json_encode($this->error("incorrect arguments")));
        $assignment = SOMUSIC_BOL_Service::getInstance()->getAssignment($assignmentId);
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($assignment->group_id);

        if($assignment->mode==1 && $userId!=$group->userId)
            exit(json_encode((object)array("status"=>true)));		//multi-user mode
        $editor = new SOMUSIC_CTRL_Editor();
        $composition = $editor->getComposition();
        if($assignment->mode==1 && $userId==$group->userId) {
            SOMUSIC_BOL_Service::getInstance()->closeAssignment($assignmentId);		//multi-user mode
            $editor->reset();
        }
        $oldExecution = SOMUSIC_BOL_Service::getInstance()->getExecutionByAssignmentAndUser($assignmentId, $userId);
        if(!isset($oldExecution))
            SOMUSIC_BOL_Service::getInstance()->addAssignmentExecution($assignmentId, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
        else SOMUSIC_BOL_Service::getInstance()->updateComposition($oldExecution->composition_id, json_encode($composition->instrumentsScore), json_encode($composition->instrumentsUsed));
        OW::getSession()->delete("assignmentId");
        exit(json_encode((object)array("status"=>true)));
    }


    public function editExecution() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        parent::editExecution();
    }


    public function removeAssignment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        if(!isset($DATA["id"]))
            exit(json_encode($this->error("incorrect arguments")));
        SOMUSIC_BOL_Service::getInstance()->removeAssignment($DATA["id"]);
        exit(json_encode((object)array("status"=>true)));
    }


    public function closeAssignment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        if(!isset($DATA["id"]))
            exit(json_encode($this->error("incorrect arguments")));
        SOMUSIC_BOL_Service::getInstance()->closeAssignment($DATA["id"]);
        exit(json_encode((object)array("status"=>true)));
    }


    public function saveComment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        if(!isset($DATA["id"]) || !isset($DATA["comment"]))
            exit(json_encode($this->error("incorrect arguments")));
        SOMUSIC_BOL_Service::getInstance()->setExecutionComment($DATA["id"], $DATA["comment"]);
        exit(json_encode((object)array("status"=>true)));
    }


    public function completeAssignment() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["assignmentId"]))
            exit(json_encode($this->error("incorrect arguments")));
        $assignmnet = SOMUSIC_BOL_Service::getInstance()->getAssignment($DATA["assignmentId"]);
        if(isset($DATA["executionId"]) && strlen($DATA["executionId"])>0) {
            $execution = SOMUSIC_BOL_Service::getInstance()->getExecution($DATA["executionId"]);
            $compositionId = $execution->composition_id;
            OW::getSession()->set("executionId", $DATA["executionId"]);
        }
        else $compositionId = $assignmnet->composition_id;
        $composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($compositionId));
        $id = null;
        if($assignmnet->mode==1) {
            $id = "groupId#".$assignmnet->group_id;
            OW::getSession()->set("isClose", $assignmnet->close);
        }
        $editor = new SOMUSIC_CTRL_Editor(false, $id);
        if($assignmnet->mode==1 && $editor->isCompositionInCache($composition))
            $editor->loadDataFromCache($composition);
        else $editor->setComposition($composition);
        OW::getSession()->set("assignmentId", $DATA["assignmentId"]);
        exit(json_encode((object)array("status"=>true)));

    }


    public function makeCorrection() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["executionId"]))
            exit(json_encode($this->error("incorrect arguments")));
        $executionId = $DATA["executionId"];
        $execution = SOMUSIC_BOL_Service::getInstance()->getExecution($executionId);
        $composition = SOMUSIC_BOL_Service::getInstance()->getComposition($execution->composition_id);
        $editor = new SOMUSIC_CTRL_Editor();
        $newComposition = $editor->getComposition();
        SOMUSIC_BOL_Service::getInstance()->updateComposition($composition->id, json_encode($newComposition->instrumentsScore), json_encode($newComposition->instrumentsUsed));
        exit(json_encode((object)array("status"=>true)));
    }


    public function assignments($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $groupId = intval(urldecode($params['groupId']));

        $service = SOMUSIC_BOL_Service::getInstance();
        $assignments = SOMUSIC_BOL_Service::getInstance()->getAssignmentsByGroupId($groupId);

        $assignment1 = array();
        foreach ($assignments as $a) {
            $execution = $service->getExecutionByAssignmentAndUser($a->id, $userId);
            if(isset($execution->id))
                $composition = SOMUSIC_CLASS_Composition::getCompositionObject($service->getComposition($execution->composition_id));
            else $composition = SOMUSIC_CLASS_Composition::getCompositionObject($service->getComposition($a->composition_id));

            array_push($assignment1, array("id"=>$a->id,
                "isMultiUser"=>$a->mode,
                "name"=>$a->name,
                "compositionId"=>$composition->getId(),
                "close"=>$a->close,
                "executionId"=>(isset($execution->id)?$execution->id:-1),
                "timestamp_c"  =>str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->getTimestampC()))),
                "timestamp_m"  =>str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->timestamp_m))),
            ));
        }

        exit(json_encode(array_reverse($assignment1)));
    }


    public function allParticipantsAssignment($params){

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();
        $assignmentId = intval(urldecode($params['assignmentId']));

        $service = SOMUSIC_BOL_Service::getInstance();
        $assignment = $service->getAssignment($assignmentId);
        $executions = $service->getExecutionsByAssignmentId($assignmentId);
        $usersId = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($assignment->group_id);
        $users = array();

        foreach ($usersId as $id)
            if($userId!=$id)
                array_push($users, BOL_UserService::getInstance()->findByIdWithoutCache($id));
        usort($users, array($this, "cmpUser"));
        $compositions = array();
        foreach ($executions as $ex) {
            $composition = $service->getComposition($ex->composition_id);
            $composition = SOMUSIC_CLASS_Composition::getCompositionObject($composition);
            $compositions[] = array(
                "userId"        =>$ex->user_id,
                "realname"      =>BOL_UserService::getInstance()->getDisplayName($ex->user_id),
                "executionId"   =>$ex->id,
                "timestamp_c"   =>str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->getTimestampC()))),
                "timestamp_m"   =>str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->timestamp_m))),
                "compositionId" =>$composition->getId(),
                "comment"       =>$ex->comment
            );
        }
        exit(json_encode($compositions));
    }


    public function getExecutionByAssignmentAndUser($params) {

        $id_user = SOMUSICAPI_CLASS_Jwt::checkToken();

        $assignmentId = intval(urldecode($params['assignmentId']));
        $userId = intval(urldecode($params['userId']));

        if($id_user != $userId){
            exit("Error.");
        }

        $response = SOMUSIC_BOL_Service::getInstance()->getExecutionByAssignmentAndUser($assignmentId, $userId);

        $composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($response->composition_id));

        $response1 = array(
            'compositionId' => $response->composition_id,
            'assignmentId'  => $response->assignment_id,
            'userId'        => $response->user_id,
            'realname'      => BOL_UserService::getInstance()->getDisplayName($response->user_id),
            'comment'       => $response->comment,
            'id'            => $response->id,
            'timestamp_c'   => str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->getTimestampC()))),
            'timestamp_m'   => str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->timestamp_m)))
        );

        exit(json_encode($response1));
    }


    public function getExecutionsByAssignmentId($params) {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $assignmentId = intval(urldecode($params['assignmentId']));
        $service = SOMUSIC_BOL_Service::getInstance();
        $executions = $service->getExecutionsByAssignmentId($assignmentId);

        $response = array();
        foreach ($executions as $item) {
            $composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($item->composition_id));
            $response[] = array(
                'compositionId' => $item->composition_id,
                'assignmentId'  => $item->assignment_id,
                'userId'        => $item->user_id,
                'realname'      => BOL_UserService::getInstance()->getDisplayName($item->user_id),
                'comment'       => $item->comment,
                'id'            => $item->id,
                'timestamp_c'   => str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->getTimestampC()))),
                'timestamp_m'   => str_replace(" ", " ",date("H:i d/m/Y",strtotime($composition->timestamp_m)))
            );
        }

        exit(json_encode($response));
    }


    public function getAssignmentById($params) {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $assignmentId = intval(urldecode($params['assignmentId']));
        $assignment = SOMUSIC_BOL_Service::getInstance()->getAssignment($assignmentId);

        exit(json_encode($assignment));
    }


    ///////////////////////////////////////////////////////////
    /// Private functions
    ///////////////////////////////////////////////////////////

    private function error($errorMsg) {
        $toReturn = array();
        $toReturn["status"] = false;
        $toReturn["message"] = $errorMsg;
        exit(json_encode((object)$toReturn));
    }

}