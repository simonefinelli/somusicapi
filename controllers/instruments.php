<?php

class SOMUSICAPI_CTRL_Instruments extends OW_ActionController {


    public function getInstrumentsGroup(){

        SOMUSICAPI_CLASS_Jwt::checkToken();

        exit(json_encode(SOMUSIC_BOL_Service::getInstance()->getInstrumentGroups()));
    }


    public function getInstruments(){

        SOMUSICAPI_CLASS_Jwt::checkToken();

        exit(json_encode(SOMUSIC_BOL_Service::getInstance()->getMusicInstruments()));
    }

}