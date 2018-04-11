<?php

class SOMUSICAPI_CTRL_Editor extends OW_ActionController {

    private $id;
    private $userId;
    private $cache;
    private $composition;
    private $instrumentsScore;
    private $assignment;


    public function __construct($loadData=true, $id=null) {
        $this->userId = OW::getUser()->getId();
        $this->cache = new Memcached();
        $this->cache->addServer("localhost", 11211);
        if(isset($id)) {
            $this->id = $id;
            OW::getSession()->set("editorId", $this->id);
        }
        else $this->id = OW::getSession()->get("editorId");
        if(!isset($this->id))
            $this->id = "userId#".$this->userId;
        if($loadData)
            $this->loadDataFromCache();
    }


    public function __destruct() {
        if(!isset($this->composition) || !isset($this->instrumentsScore))
            return;
        for($i=0; $i<count($this->instrumentsScore); $i++) {
            if($this->instrumentsScore[$i]->user == $this->userId || $this->instrumentsScore[$i]->user==-1) {
                $this->cache->set($this->id."#instrumentScore#".$i, $this->instrumentsScore[$i], time()+60*60);
                $this->composition->instrumentsScore[$i] = $this->instrumentsScore[$i];
            }
            else $this->composition->instrumentsScore[$i] = $this->cache->get($this->id."#instrumentScore#".$i);
        }
        OW::getSession()->set($this->id, serialize($this->composition));

    }


    public function loadDataFromCache($composition = null) {
        $this->instrumentsScore = array();
        if(isset($composition))
            $this->composition = $composition;
        else
            $this->composition = unserialize(OW::getSession()->get($this->id));
        $nMeasures = 0;
        for($i=0; $i<count($this->composition->instrumentsScore); $i++) {
            array_push($this->instrumentsScore, $this->cache->get($this->id."#instrumentScore#".$i));
            $nMeasures = max(array($nMeasures, count($this->instrumentsScore[$i]->measures)));
        }
        for($i=0; $i<count($this->composition->instrumentsScore); $i++) {
            $lastMeasure = $this->instrumentsScore[$i]->measures[count($this->instrumentsScore[$i]->measures)-1];
            $clef = $lastMeasure->clef;
            $timeSign = $lastMeasure->timeSignature;
            $keySign = $lastMeasure->keySignature;
            while(count($this->instrumentsScore[$i]->measures)<$nMeasures)
                array_push($this->instrumentsScore[$i]->measures, $this->newMeasure($clef, explode ("/", $timeSign), $keySign));
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
    }


    public function initEditor() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $instrumentsUsed = json_decode($DATA['instrumentsUsed'], true);
        $timeSignature = $DATA['timeSignature'];
        $keySignature = $DATA['keySignature'];
        $name = $DATA['name'];

        // TODO: bloccare chiamata rest
        $this->composition = new SOMUSIC_CLASS_Composition (-1, $name, $this->userId, -1, $this->userId, -1, array(), $instrumentsUsed);
        $this->instrumentsScore = array();
        foreach($instrumentsUsed as $instrument) {
            for($i = 0; $i<count($instrument["scoresClef"]); $i++) {
                $is = new SOMUSIC_CLASS_InstrumentScore($instrument["scoresClef"][$i], $instrument["labelName"]."#score".$i, array(), array(), $instrument["name"], $instrument["user"]);
                array_push($this->instrumentsScore, $is);
                array_push($is->measures, $this->newMeasure($instrument["scoresClef"][$i], explode("/", $timeSignature), $keySignature));
                $this->cache->set($this->id."#instrumentScore#".$i, $is, time()+60*60);	//TODO: rimuovere?
            }
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        for($i=0; $i<count($this->instrumentsScore); $i++) {
            $this->cache->set($this->id."#instrumentScore#".$i, $this->instrumentsScore[$i], time()+60*60);
            $this->composition->instrumentsScore[$i] = $this->instrumentsScore[$i];
        }
        OW::getSession()->set($this->id, serialize($this->composition), time()+60*60);
        exit(json_encode($this->composition));
    }


    public function setCompositionAjax() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);
        $composition = json_decode($DATA['composition']);

        //$this->composition = new SOMUSIC_CLASS_Composition ($composition->id, $composition->name, $composition->user_c,
        //$composition->timestamp_c, $composition->user_m, $composition->timestamp_m, $composition->instrumentsScore, $composition->instrumentsUsed);

        $this->composition = $this->getCompositionObjectAjax($composition);

        /**if(!is_object($composition))
        $this->composition = $this->getCompositionObject($composition);
        else $this->composition = $composition;*/
        //$composition = $this->checkComposition($composition);

        $this->instrumentsScore = $this->composition->instrumentsScore;
        for($i=0; $i<count($this->instrumentsScore); $i++)
            $this->cache->set($this->id."#instrumentScore#".$i, $this->instrumentsScore[$i], time()+60*60);

        exit(json_encode($this->composition));
    }


    public function setComposition($composition) {
        // TODO: bloccare chiamata rest
        if(!is_object($composition))
            $this->composition = $this->getCompositionObject($composition);
        else $this->composition = $composition;
        //$composition = $this->checkComposition($composition);
        $this->instrumentsScore = $this->composition->instrumentsScore;
        for($i=0; $i<count($this->instrumentsScore); $i++)
            $this->cache->set($this->id."#instrumentScore#".$i, $this->instrumentsScore[$i], time()+60*60);
    }


    public function addNote() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["staveIndex"]) || !isset($DATA["measureIndex"]) || !isset($DATA["noteIndex"])
            || !isset($DATA["newNote"]) || !isset($DATA["duration"]) || !isset($DATA["accidental"]))
            $this->error("error insertion note");
        $staveIndex = intval($DATA["staveIndex"]);
        $measureIndex = intval($DATA["measureIndex"]);
        $noteIndex = intval($DATA["noteIndex"]);
        $newNote = explode("/", $DATA["newNote"]);
        $duration = 64/intval($DATA["duration"]);
        $voiceIndex = (isset($DATA["voiceIndex"])?$DATA["voiceIndex"]:0);
         //////////////////////////////////////////////////////////
        $instrumentScore = $this->instrumentsScore[$staveIndex];
        if($instrumentScore->user!=$userId && $instrumentScore->user!=-1)
            $this->error("permission denied");
        $measure = $instrumentScore->measures[$measureIndex];
        if(count($measure->voices)==$voiceIndex)
            $measure->voices[] = $this->newVoice(explode("/", $measure->timeSignature));
        $note = $measure->voices[$voiceIndex][$noteIndex];
        if ($duration > $note->duration) {
            /*$ni = $noteIndex+1;
            $duration1 = $note->duration;
            while(count($note->step)==0 && $ni<count($measure->voices[$voiceIndex]) && count($measure->voices[$voiceIndex][$ni]->step)==0) {
                $duration1 += $measure->voices[$voiceIndex][$ni]->duration;
                $ni++;
            }
            if($duration<=$duration1) {
                $note->duration = $duration1;
                array_splice($measure->voices[$voiceIndex], $noteIndex+1, ($ni-$noteIndex));
            }*/
            if(!$this->mergeRests($measure->voices[$voiceIndex], $noteIndex, $duration))
                $this->error("error note duration");
        }
        $durationDif = $note->duration - $duration;
        if (count($note->step)==0) {
            $toAdd = array();
            while ($durationDif>0) {
                $max = $this->getMax2Pow($durationDif);
                array_unshift($toAdd, new SOMUSIC_CLASS_Note($max, array(), array(), array()));
                $durationDif -= $max;
            }
            if ($DATA["isPause"] == "true")
                array_unshift($toAdd, new SOMUSIC_CLASS_Note($duration, array(), array(), array()));
            else array_unshift($toAdd, new SOMUSIC_CLASS_Note($duration, array($newNote[0]), array($newNote[1]), array($DATA["accidental"]), -1, -1, 0, null, (isset($DATA["color"]))?$DATA["color"]:"black"));
            for($i = $noteIndex+1; $i<count($measure->voices[$voiceIndex]); $i++) {
                $n = $measure->voices[$voiceIndex][$i];
                if($n->isTieStart!=-1)
                    $instrumentScore->ties[$n->isTieStart]->firstNote += count($toAdd)-1;
                if($n->isTieEnd!=-1)
                    $instrumentScore->ties[$n->isTieEnd]->lastNote += count($toAdd)-1;
            }
            array_splice($measure->voices[$voiceIndex], $noteIndex, 1, $toAdd);
        }
        else if ($duration == ($note->dots==0?$note->duration:$note->duration*(2*$note->dots)/(pow(2, $note->dots+1)-1)) && $DATA["isPause"] == "false") {
            array_push($note->step, $newNote[0]);
            array_push($note->octave, $newNote[1]);
            array_push($note->accidental, $DATA["accidental"]);
            $this->sortNote($note);
            if(isset($DATA["color"]) && (!isset($note->color) || $note->color!=""))
                $note->color = $DATA["color"];
        }
        $this->instrumentsScore[$staveIndex] = $instrumentScore;
        if ($measureIndex == count($this->instrumentsScore[0]->measures)-1) {
            for($i=0; $i<count($this->instrumentsScore); $i++) {
                $lastMeasure = $this->instrumentsScore[$i]->measures[$measureIndex];
                $clef = $lastMeasure->clef;
                $timeSign = $lastMeasure->timeSignature;
                $keySign = $lastMeasure->keySignature;
                array_push($this->instrumentsScore[$i]->measures, $this->newMeasure($clef, explode("/", $timeSign), $keySign));
            }
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit(json_encode($this->composition));
    }


    public function deleteNotes() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["toRemove"]))
            $this->error("error notes deletion");
        $arr = json_decode($DATA["toRemove"]);
        foreach ($arr as $obj) {
            $is = $this->instrumentsScore[$obj->staveIndex];
            if($is->user!=$this->userId && $is->user!=-1)
                continue;
            $m = $is->measures [$obj->measureIndex];
            $note = $m->voices [0] [$obj->noteIndex];
            $note->step = array ();
            $note->octave = array ();
            $note->accidental = null;
            if($note->isTieStart!=-1) {
                $tie = $is->ties [$note->isTieStart];
                $found = false;
                $alreadyTied = false;
                for($i = $tie->firstMeasure; $i <= $tie->lastMeasure && ! $found; $i ++) {
                    $firstMeasure = $is->measures [$i];
                    for($j = $tie->firstNote + 1; $j < count ( $firstMeasure->voices [0] ) && ! $found; $j ++) {
                        $n = $firstMeasure->voices [0] [$j];
                        if (count($n->step)>0 && ! ($tie->lastMeasure == $i && $tie->lastNote == $j)) {
                            $found = true;
                            if (! $this->areTied ( $is, $i, $j, $tie->lastMeasure, $tie->lastNote )) {
                                $tie->firstMeasure = $i;
                                $tie->firstNote = $j;
                                $is->measures[$i]->voices[0][$j]->isTieStart = $note->isTieStart;
                            } else $alreadyTied = true;
                        }
                    }
                }
                if (! $found || $alreadyTied)
                    $this->removeTie($is, $note->isTieStart);
            }
            if($note->isTieEnd!=-1) {
                $tie = $is->ties[$note->isTieEnd];
                $found = false;
                $alreadyTied = false;
                for($i = $tie->lastMeasure; $i >= $tie->lastMeasure && ! $found; $i --) {
                    $lastMeasure = $is->measures [$i];
                    for($j = $tie->lastNote - 1; $j >= 0 && ! $found; $j --) {
                        $n = $lastMeasure->voices [0] [$j];
                        if (count($n->step)>0 && ! ($tie->firstMeasure == $i && $tie->firstNote == $j)) {
                            $found = true;
                            if (! $this->areTied ( $is, $tie->firstMeasure, $tie->firstNote, $i, $j )) {
                                $tie->lastMeasure = $i;
                                $tie->lastNote = $j;
                                $is->measures[$i]->voices[0][$j]->isTieEnd = $note->isTieEnd;
                            } else $alreadyTied = true;
                        }
                    }
                }
                if (! $found || $alreadyTied)
                    $this->removeTie($is, $note->isTieEnd);
            }
            $note->isTieStart = -1;
            $note->isTieEnd = -1;
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit(json_encode($this->composition));
    }


    public function addTie() {

        $userId = SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA ["toTie"]))
            $this->error("error insertion tie");
        $toTie = json_decode($DATA ["toTie"]);
        $score = $toTie [0] -> voiceName;
         /////////////////////////////////////////////////////
        for($i = 1; $i < count ( $toTie ); $i ++)
            if ($toTie [$i] -> voiceName != $score)
                $this->error("error voice");
        $firstMeasure = INF;
        $lastMeasure = - INF;
        for($i = 0; $i < count ( $toTie ); $i ++) {
            if ($toTie [$i] -> measureIndex < $firstMeasure) {
                $firstMeasure = $toTie [$i] -> measureIndex;
                $firstNote = $toTie [$i] -> noteIndex;
            }
            else if ($toTie [$i] -> measureIndex == $firstMeasure) {
                $pos = $toTie [$i] -> noteIndex;
                if ($pos < $firstNote)
                    $firstNote = $pos;
            }
            if ($toTie [$i] -> measureIndex > $lastMeasure) {
                $lastMeasure = $toTie [$i] -> measureIndex;
                $lastNote = $toTie [$i] -> noteIndex;
            }
            else if ($toTie [$i] -> measureIndex == $lastMeasure) {
                $pos = $toTie [$i] -> noteIndex;
                if ($pos > $lastNote)
                    $lastNote = $pos;
            }
        }
        $instrumentScore = $this->instrumentsScore[$toTie[0] -> staveIndex];
        if($instrumentScore->user!=$userId && $instrumentScore->user!=-1)
            exit(json_encode($this->composition));
        $tieIndex = $this->areTied ( $instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote );
        $startNote = $instrumentScore->measures [$firstMeasure]->voices [0] [$firstNote];
        $endNote = $instrumentScore->measures [$lastMeasure]->voices [0] [$lastNote];
        if ($tieIndex < 0) {
            $pos = array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($firstMeasure, $firstNote, $lastMeasure, $lastNote));
            $startNote->isTieStart = $pos-1;
            $endNote->isTieEnd = $pos-1;
        } else $this->removeTie ( $instrumentScore, $tieIndex );
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit ( json_encode ( $this->composition ) );
    }


    public function getComposition() {
        /*if (isset($_REQUEST ["id"])) {
            $id = intval($_REQUEST["id"]);
            //$this->composition = json_decode ( SOMUSIC_BOL_Service::getInstance ()->getScoreByPostId ( $id ) ["data"] );
            $this->composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getScoreByPostId($id));
            $this->instrumentsScore = $this->composition->instrumentsScore;
            for($i=0; $i<count($this->instrumentsScore); $i++)
                $this->cache->set($this->id."#instrumentScore#".$i, $this->instrumentsScore[$i], time()+60*60);
        }*/
        return $this->composition;
    }


    public function getCompositionById($params){

        $composition=null;
        $id = intval(urldecode($params["id"]));
        $composition = SOMUSIC_CLASS_Composition::getCompositionObject(SOMUSIC_BOL_Service::getInstance()->getComposition($id));

        exit(json_encode($composition));
    }


    public function reset() {
        //TODO: bloccare chiamata rest
        $composition = $this->composition;
        $composition->instrumentsScore = $this->instrumentsScore;
        for($i=0; $i<count($this->instrumentsScore); $i++)
            $this->cache->delete($this->id."#instrumentScore#".$i);
        OW::getSession()->delete("editorId");
        $this->instrumentsScore = null;
        $this->composition = null;
        return $composition;
    }


    public function accidentalUpdate() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["toUpdate"]) || !isset($DATA["accidental"]))
            $this->error("error accidental update");
        $arr = json_decode($DATA["toUpdate"]);
        foreach ($arr as $obj) {
            $is = $this->instrumentsScore[$obj->staveIndex];
            if($is->user!=$this->userId && $is->user!=-1)
                continue;
            $m = $is->measures [$obj->measureIndex];
            $note = $m->voices [0] [$obj->noteIndex];
            for($i=0; $i<count($note->accidental); $i++)
                $note->accidental[$i] = $DATA["accidental"];
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit(json_encode($this->composition));
    }


    public function dotsUpdate() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["toUpdate"]) || !isset($DATA["dotValue"]))
            $this->error("error dot update");

        $arr = json_decode($DATA["toUpdate"]);
        
        foreach ($arr as $obj) {
            $is = $this->instrumentsScore[$obj -> staveIndex];
            if($is->user!=$this->userId && $is->user!=-1)
                continue;
            $m = $is->measures[$obj->measureIndex];
            $note = $m->voices[0][$obj->noteIndex];
            if($note->dots>0) {
                $duration = $note->duration*(2*$note->dots)/(pow(2, $note->dots+1)-1);
                if(intval($DATA["dotValue"])>$note->dots)
                    $dots = $note->dots+(intval($DATA["dotValue"])-$note->dots);
                else $dots = $note->dots-intval($DATA["dotValue"]);
            }
            else{
                $duration = $note->duration;
                $dots = $note->dots+intval($DATA["dotValue"]);
            }
            if($dots<0)
                continue;
            $noteValue = $duration;
            for($i=0; $i<$dots; $i++)
                $noteValue += $duration/(2*pow(2, $i));
            if($note->duration == $duration || ($DATA["dotValue"]==2 && $note->duration==($duration+$duration/2))) {
                $restIndex = null;
                for($i=intval($obj->noteIndex)+1; $i<count($m->voices[0]) && !isset($restIndex); $i++) {
                    if(count($m->voices[0][$i]->step)==0)
                        $restIndex = $i;
                }
                $diff = $noteValue-$note->duration;
                if($m->voices[0][$restIndex]->duration<$diff)
                    $this->mergeRests($m->voices[0], $restIndex, $diff);
                if(!isset($restIndex) || $m->voices[0][$restIndex]->duration<$diff)
                    continue;
                $durationDif = $m->voices[0][$restIndex]->duration - $diff;
                $toAdd = array ();
                while($durationDif>0) {
                    $max = $this->getMax2Pow($durationDif);
                    array_unshift($toAdd, new SOMUSIC_CLASS_Note($max, array(), array(), array()));
                    $durationDif -= $max;
                }
                array_splice($m->voices[0], $restIndex, 1, $toAdd);
            }
            else {
                $toAdd = array();
                $diff = $note->duration - $noteValue;
                while($diff>0) {
                    $max = $this->getMax2Pow($diff);
                    array_unshift($toAdd, new SOMUSIC_CLASS_Note($max, array(), array(), array()));
                    $diff -= $max;
                }
                array_splice($m->voices[0], intval($obj->noteIndex)+1, 0, $toAdd);
            }
            $note->dots = $dots;
            $note->duration = $noteValue;
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit(json_encode($this->composition));
    }


    public function isCompositionInCache($composition) {
        foreach ($composition->instrumentsScore as $i=>$instrumentScore) {
            if(is_bool($this->cache->get($this->id."#instrumentScore#".$i)))
                return false;
            $is = $this->cache->get($this->id."#instrumentScore#".$i);
            if($instrumentScore->name!=$is->name || $instrumentScore->user!=$is->user || $instrumentScore->instrument!=$is->instrument)
                return false;
        }
        //TODO: controllare che non ci sono più strumenti dei necessari in cache
        //return is_bool($this->cache->get($this->id."#instrumentScore#".count($composition->istrumentsScore)));
        return true;
    }


    public function getInstrumentsUsed() {
        return $this->composition->instrumentsUsed;
    }


    public function removeInstrument() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["index"]) || count($this->composition->instrumentsUsed)<=1)
            exit(json_encode(false));
        $index = intval($DATA["index"]);
        $nScore = 0;
        $scoreStart = -1;
        $length = -1;
        for ($i=0; $i<count($this->composition->instrumentsUsed); $i++) {
            if($i==$index) {
                $scoreStart = $nScore;
                $length = count($this->composition->instrumentsUsed[$i]["scoresClef"]);
                array_splice($this->instrumentsScore, $nScore, $length);
                array_splice($this->composition->instrumentsScore, $nScore, $length);
                array_splice($this->composition->instrumentsUsed, $index, 1);
                $nScore += $length;
                $index = -1;
                $i--;
            }
            else $nScore += count($this->composition->instrumentsUsed[$i]["scoresClef"]);
        }
        $scoreEnd = $nScore-$length;
        for($i=$scoreStart; $i<$scoreEnd; $i++)
            $this->cache->set($this->id."#instrumentScore#".$i, $this->cache->get($this->id."#instrumentScore#".($i+$length)));
        //exit(json_encode(array($length, $scoreStart, $scoreEnd, count($this->composition->instrumentsUsed))));
        for($i=$scoreEnd; $i<$nScore; $i++)
            $this->cache->delete($this->id."#instrumentScore#".$i);
        exit(json_encode(true));
    }


    public function close() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        if(!isset($this->assignment) || $this->assignment->close==0)
            exit(json_encode($this->composition));
        $composition = $this->reset();
        exit(json_encode($composition));
    }


    public function exportMusicXML() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        $instrumentsName = array();
        $musicInstruments = SOMUSIC_BOL_Service::getInstance()->getMusicInstruments();
        foreach ($musicInstruments as $mi)
            array_push($instrumentsName, $mi->name);
        $parser = new SOMUSIC_CLASS_MusicXmlParser($instrumentsName);
        //exit(json_encode($parser->parseComposition($this->composition)));
        exit($parser->parseComposition($this->composition));
    }


    public function getId() {
        exit(json_encode($this->id));
    }


    public function getJSONComposition() {

        SOMUSICAPI_CLASS_Jwt::checkToken();
        exit(json_encode($this->composition));
    }


    public function moveNotes() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["toUpdate"]) || !isset($DATA["value"]))
            $this->error("error move update");
        $noteNames = array("C", "D", "E", "F", "G", "A", "B");
        $value = intval($DATA["value"]);
        $arr = json_decode($DATA["toUpdate"]);
        foreach ($arr as $obj) {
            $is = $this->instrumentsScore[$obj->staveIndex];
            if($is->user!=$this->userId && $is->user!=-1)
                continue;
            $m = $is->measures[$obj->measureIndex];
            $note = $m->voices[0][$obj->noteIndex];
            for($i=0; $i<count($note->step); $i++) {
                $step = strtoupper($note->step[$i]);
                $octave = intval($note->octave[$i]);
                $stepValue = array_search($step, $noteNames);
                if($value>0 && $stepValue==6) {
                    $note->step[$i] = strtolower($noteNames[0]);
                    $note->octave[$i]++;
                }
                else if($value<0 && $stepValue==0) {
                    $note->step[$i] = strtolower($noteNames[6]);
                    $note->octave[$i]--;
                }
                else $note->step[$i] = strtolower($noteNames[$stepValue+$value]);
            }
        }
        $this->composition->instrumentsScore = $this->instrumentsScore;
        exit(json_encode($this->composition));
    }


    public function setNoteAnnotationText() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["measureIndex"]) || !isset($DATA["staveIndex"]) ||
            !isset($DATA["noteIndex"]) || !isset($DATA["text"]))
            $this->error("error note annotation");
        $measureIndex = intval($DATA["measureIndex"]);
        $staveIndex = intval($DATA["staveIndex"]);
        $noteIndex = intval($DATA["noteIndex"]);
        $note = $this->instrumentsScore[$staveIndex]->measures[$measureIndex]->voices[0][$noteIndex];
        $note->text = $DATA["text"];
        exit(json_encode(true));
    }


    public function changeNoteDuration() {

        SOMUSICAPI_CLASS_Jwt::checkToken();

        $DATA = json_decode(file_get_contents('php://input'), true);

        if(!isset($DATA["toChange"]) || !isset($DATA["duration"]))
            $this->error("error change note duration");
        $duration = 64/intval($DATA["duration"]);
        $toChange = json_decode($DATA["toChange"]);
        foreach ($toChange as $obj) {
            $is = $this->instrumentsScore[$obj->staveIndex];
            if($is->user!=$this->userId && $is->user!=-1)
                continue;
            $m = $is->measures[$obj->measureIndex];
            $note = $m->voices[0][$obj->noteIndex];
            if($note->duration<$duration) {
                $restIndex = null;
                for($i=intval($obj->noteIndex)+1; $i<count($m->voices[0]) && !isset($restIndex); $i++) {
                    if(count($m->voices[0][$i]->step)==0)
                        $restIndex = $i;
                }
                $diff = $duration - $note->duration;
                if(isset($restIndex) && $m->voices[0][$restIndex]->duration<$diff)
                    $this->mergeRests($m->voices[0], $restIndex, $diff);
                if(!isset($restIndex) || $m->voices[0][$restIndex]->duration<$diff)
                    continue;
                $note->duration += $diff;
                $m->voices[0][$restIndex]->duration -= $diff;
                //$this->error($diff." ".$note->duration." ".$m->voices[0][$restIndex]->duration);
                if($m->voices[0][$restIndex]->duration>0) {
                    /*$toAdd = array ();
                    while($diff>0) {
                        $max = $this->getMax2Pow($diff);
                        array_unshift($toAdd, new SOMUSIC_CLASS_Note($max, array(), array(), array()));
                        $diff -= $max;
                    }
                    array_splice($m->voices[0], $restIndex, 1, $toAdd);*/
                }
                else array_splice($m->voices[0], $restIndex, 1);
            }
            else {
                $diff = $note->duration - $duration;
                $note->duration = $duration;
                $toAdd = array();
                while($diff>0) {
                    $max = $this->getMax2Pow($diff);
                    array_unshift($toAdd, new SOMUSIC_CLASS_Note($max, array(), array(), array()));
                    $diff -= $max;
                }
                array_splice($m->voices[0], intval($obj->noteIndex)+1, 0, $toAdd);
            }
        }
        exit(json_encode($this->composition));
    }


    private function mergeRests(&$voice, $index, $duration) {
        $i = $index+1;
        $note = $voice[$index];
        $duration1 = $note->duration;
        while(count($note->step)==0 && $i<count($voice) && count($voice[$i]->step)==0) {
            $duration1 += $voice[$i]->duration;
            $i++;
        }
        if($duration<=$duration1) {
            $note->duration = $duration1;
            array_splice($voice, $index+1, ($i-$index-1));
            return true;
        }
        return false;
    }


    //TODO: controllare se serve
    private function getCompositionObject($compositionArray) {
        $this->composition = new SOMUSIC_CLASS_Composition($compositionArray["id"], $compositionArray["name"], $compositionArray["user_c"], $compositionArray["timestamp_c"], $compositionArray["user_m"], $compositionArray["timestamp_m"], array(), $compositionArray["instrumentsUsed"]);
        foreach ($compositionArray["instrumentsScore"] as $instrumentScoreArray) {
            $instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray["default_clef"], $instrumentScoreArray["name"], array(), array(), $instrumentScoreArray["instrument"], $instrumentScoreArray["user"]);
            foreach ($instrumentScoreArray["measures"] as $measureArray) {
                $voices = array();
                foreach ($measureArray["voices"] as $voiceArray) {
                    $voice = array();
                    foreach ($voiceArray as $noteArray)
                        array_push($voice, new SOMUSIC_CLASS_Note($noteArray["duration"], $noteArray["step"], $noteArray["octave"], $noteArray["accidental"], $noteArray["isTieStart"], $noteArray["isTieEnd"]));
                    array_push($voices, $voice);
                }
                $measure = new SOMUSIC_CLASS_Measure($measureArray["clef"], $measureArray["keySignature"], $measureArray["timeSignature"], $voices);
                array_push($instrumentScore->measures, $measure);
            }
            foreach ($instrumentScoreArray["ties"] as $tieArray)
                array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray["firstMeasure"], $tieArray["firstNote"], $tieArray["lastMeasure"], $tieArray["lastNote"]));
            array_push($this->composition->instrumentsScore, $instrumentScore);
        }
        return $this->composition;
    }


    //TODO: controllare se serve
    private function getCompositionObjectAjax($compositionArray) {
        $this->composition = new SOMUSIC_CLASS_Composition($compositionArray->id, $compositionArray->name, $compositionArray->user_c, $compositionArray->timestamp_c, $compositionArray->user_m, $compositionArray->timestamp_m, array(), $compositionArray->instrumentsUsed);
        foreach ($compositionArray->instrumentsScore as $instrumentScoreArray) {
            $instrumentScore = new SOMUSIC_CLASS_InstrumentScore($instrumentScoreArray->default_clef, $instrumentScoreArray->name, array(), array(), $instrumentScoreArray->instrument, $instrumentScoreArray->user);
            foreach ($instrumentScoreArray->measures as $measureArray) {
                $voices = array();
                foreach ($measureArray->voices as $voiceArray) {
                    $voice = array();
                    foreach ($voiceArray as $noteArray)
                        array_push($voice, new SOMUSIC_CLASS_Note($noteArray->duration, $noteArray->step, $noteArray->octave, $noteArray->accidental, $noteArray->isTieStart, $noteArray->isTieEnd));
                    array_push($voices, $voice);
                }
                $measure = new SOMUSIC_CLASS_Measure($measureArray->clef, $measureArray->keySignature, $measureArray->timeSignature, $voices);
                array_push($instrumentScore->measures, $measure);
            }
            foreach ($instrumentScoreArray->ties as $tieArray)
                array_push($instrumentScore->ties, new SOMUSIC_CLASS_Tie($tieArray->firstMeasure, $tieArray->firstNote, $tieArray->lastMeasure, $tieArray->lastNote));
            array_push($this->composition->instrumentsScore, $instrumentScore);
        }
        return $this->composition;
    }

    //TODO: non finito
    /*private function checkComposition($composition) {
        $instrumentsUsed = $composition->instrumentsUsed;
        $counter = 0;
        $maxMeasure = 0;
        foreach ($instrumentsUsed as $instrument) {
            for($i = 0; $i<count($instrument->scoresClef); $i++) {
                if(count($composition->instrumentsScore)<$counter) {
                    $is = $composition->instrumentsScore[$couter];
                    if($is->name != $instrument->labelName."#score".$i) {
                        array_splice($composition->instrumentsScore, $counter, 1);
                    }
                    else $maxMeasure = max($maxMeasure, count($is->measures));
                }
                else {
                    $is = new SOMUSIC_CLASS_InstrumentScore($instrument->scoresClef[$i], $instrument->labelName."#score".$i, array(), array(), $instrument->name, $instrument->user);
                    array_push($composition->instrumentsScore, $is);
                }
                //array_push ( $is->measures, $this->newMeasure ( $instrument ["scoresClef"] [$i], explode ( "/", $timeSignature ), $keySignature ) );
                $counter++;
            }
        }
        $timeSignature = $composition->instrumentsScore[0]->measures[0]->timeSignature;
        $keySignature = $composition->instrumentsScore[0]->measures[0]->keySignature;
        foreach ($composition->instrumentsScore as $is) {
            while(count($is->measures)<$maxMeasure)
                array_push($is->measures, $this->newMeasure($instrument["scoresClef"][$i], explode("/", $timeSignature), $keySignature));
        }
        return $composition;
    }*/


    private function removeTie($instrumentScore, $tieIndex) {
        $tie = $instrumentScore->ties [$tieIndex];
        $firstNote = $instrumentScore->measures [$tie->firstMeasure]->voices [0] [$tie->firstNote];
        $lastNote = $instrumentScore->measures [$tie->lastMeasure]->voices [0] [$tie->lastNote];
        array_splice ( $instrumentScore->ties, $tieIndex, 1 );
        if($firstNote->isTieStart == $tieIndex)
            $firstNote->isTieStart = -1;
        if($lastNote->isTieEnd == $tieIndex)
            $lastNote->isTieEnd = -1;
        for($i = 0; $i < count ( $instrumentScore->measures ); $i ++) {
            $m = $instrumentScore->measures [$i];
            for($j = 0; $j < count ( $m->voices [0] ); $j ++) {
                $note = $m->voices [0] [$j];
                if($note->isTieStart != -1 && $note->isTieStart > $tieIndex)
                    $note->isTieStart--;
                if($note->isTieEnd != -1 && $note->isTieEnd > $tieIndex)
                    $note->isTieEnd--;
            }
        }
    }


    private function areTied($instrumentScore, $firstMeasure, $firstNote, $lastMeasure, $lastNote) {
        foreach ( $instrumentScore->ties as $i => $tie ) {
            if ($tie->firstMeasure == $firstMeasure && $tie->firstNote == $firstNote && $tie->lastMeasure == $lastMeasure && $tie->lastNote == $lastNote)
                return $i;
        }
        return - 1;
    }


    private function newMeasure($clef, $timeSign, $keySign) {
        $measure = new SOMUSIC_CLASS_Measure($clef, $keySign, implode("/", $timeSign), array());
        array_push($measure->voices, $this->newVoice($timeSign));
        return $measure;
    }


    private function newVoice($timeSign) {
        $voice = array();
        for($j=0; $j<intval($timeSign[0]); $j++) {
            $pause = new SOMUSIC_CLASS_Note(64/intval($timeSign[1]), array(), array(), array());
            array_push($voice, $pause);
        }
        return $voice;
    }


    private function getMax2Pow($num) {
        $max = 1;
        for($i = 0, $pow = 1; $pow <= $num; $i ++, $pow = pow ( 2, $i ))
            $max = $pow;
        return $max;
    }


    private function error($errorMsg) {
        $composition = (array)$this->composition;
        $composition["error"] = $errorMsg;
        exit(json_encode((object)$composition));
    }


    private function sortNote(&$note) {
        $n = count($note->octave);
        for($i=0; $i<$n-1; $i++) {
            for($j=$i+1; $j<$n; $j++) {
                if(intval($note->octave[$j])<intval($note->octave[$i]) ||
                    ($note->octave[$j]==$note->octave[$i] && $this->stepToInt($note->step[$j])<$this->stepToInt($note->step[$i]))) {
                    $temp = $note->octave[$j];
                    $note->octave[$j] = $note->octave[$i];
                    $note->octave[$i] = $temp;
                    $temp = $note->step[$j];
                    $note->step[$j] = $note->step[$i];
                    $note->step[$i] = $temp;
                    $temp = $note->accidental[$j];
                    $note->accidental[$j] = $note->accidental[$i];
                    $note->accidental[$i] = $temp;
                }
            }
        }
    }


    private function stepToInt($step) {
        switch($step) {
            case "c":
                return 0;
            case "d":
                return 1;
            case "e":
                return 2;
            case "f":
                return 3;
            case "g":
                return 4;
            case "a":
                return 5;
            case "b":
                return 6;
        }
        return -1;
    }

}

