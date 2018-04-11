<?php

class SOMUSICAPI_CLASS_Sort {

    public static function sortArray($arrayToSort, $field, $mode) {

        $array = array();
        foreach ($arrayToSort as $key => $row)
        {
            $array[$key] = $row[$field];
        }

        switch ($mode) {
            case 0:
                $arg1 = 4; //SORT_ASC
                $arg2 = 6; //SORT_NATURAL
                $arg3 = 8; //SORT_FLAG_CASE
                break;
            case 1:
                $arg1 = 3; //SORT_DESC
                $arg2 = 6; //SORT_NATURAL
                $arg3 = 8; //SORT_FLAG_CASE
                break;
            case 2:
                $arg1 = 4; //SORT_ASC
                $arg2 = 0;
                $arg3 = 0;
                break;
            case 3:
                $arg1 = 3; //SORT_DESC
                $arg2 = 0;
                $arg3 = 0;
                break;
            default:
                $arg1 = 4; //SORT_ASC
                $arg2 = 0;
                $arg3 = 0;
        }

        array_multisort($array, $arg1|$arg2|$arg3, $arrayToSort);
        return $arrayToSort;
    }

}