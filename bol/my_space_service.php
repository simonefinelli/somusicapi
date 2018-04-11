<?php

class SOMUSICAPI_BOL_MySpaceService {

    private static $classInstance;
    private $compositionDao;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self ();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        $this->compositionDao = SOMUSIC_BOL_CompositionDao::getInstance();
    }

    public function addComposition($userId, $name, $instrumentsScore, $instrumentsUsed) {
        $composition = new SOMUSIC_BOL_Composition();
        $composition->name = $name;
        $composition->user_c = $userId;
        $composition->user_m = $userId;
        $composition->instrumentsScore = $instrumentsScore;
        $composition->instrumentsUsed = $instrumentsUsed;
        $this->compositionDao->save($composition);
    }

}