<?php
class SOMUSICAPI_CTRL_Preview extends SOMUSIC_CTRL_Preview {


    public function importMusicXML() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        if(!isset($DATA["file"]))
            exit(json_encode(false));
        $instrumentsName = array();
        $musicInstruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstruments();
        foreach ($musicInstruments as $mi)
            array_push($instrumentsName, $mi->name);
        $parser = new SOMUSIC_CLASS_MusicXmlParser($instrumentsName);
        $composition = $parser->parseMusicXML($DATA["file"]);

        $editor = new SOMUSIC_CTRL_Editor(false);
        $editor->setComposition($composition);

        /*$preview = unserialize(OW::getSession()->get("preview"));
        $preview->importedComposition = $composition;
        $preview->instrumentsTable = array();
        foreach ($composition->instrumentsUsed as $instrumentUsed)
            array_push($preview->instrumentsTable, array("name"=>$instrumentUsed->labelName, "type"=>$instrumentUsed->name, "user"=>-1));
        OW::getSession()->set("preview", serialize($preview));*/

        exit(json_encode(true));
    }


    public function commitPreview() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $DATA = json_decode(file_get_contents('php://input'), true);
        if(!isset($DATA["timeSignature"]) || !isset($DATA["keySignature"]) || !isset($DATA["instrumentsUsed"]))
            exit(json_encode(false));
        $preview = unserialize(OW::getSession()->get("preview"));
        $timeSignature = $DATA["timeSignature"];
        $keySignature = $DATA["keySignature"];
        $instrumentsUsed = $DATA["instrumentsUsed"];

        $editor = new SOMUSIC_CTRL_Editor(false);
        $editor->initEditor($instrumentsUsed, $timeSignature, $keySignature, $preview->name);

        exit(json_encode(true));
    }

}