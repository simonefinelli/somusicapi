<?php

class SOMUSICAPI_BOL_UserService {

    private static $classInstance;
    private $question_data_dao;
    private $avatar_dao;
    private $user_dao;


    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct() {
        $this->question_data_dao = BOL_QuestionDataDao::getInstance();
        $this->avatar_dao        = BOL_AvatarDao::getInstance();
        $this->user_dao          = BOL_UserDao::getInstance();
    }

    public function userInfo1($param_id) {

        $user_byDao = $this->findUserByIdOrUsernameOrEmail($param_id);

        if (is_null($user_byDao)) {
            $user_toReturn = [];
        } else {

            // get user array from object
            $user = get_object_vars($user_byDao);

            // get data from user array
            $id = $user['id'];
            $email = $user['email'];
            $username = $user['username'];
            $joinDate = date('Y/m/d', $user['joinStamp']);

            // join with question_data (to get realname, sex, birthdate)
            $question_data_byDao = $this->question_data_dao->findByQuestionsNameList(array('realname', 'sex', 'birthdate'), $id);

            $questions = array();
            foreach ($question_data_byDao as $key => $value) {
                $question = get_object_vars($value);
                $questions[$question['questionName']] = $question;
            }

            if (isset($questions['realname'])) {
                $realname = $questions['realname']['textValue'];
            } else {
                $realname = '';
            }

            if (isset($questions['sex'])) {

                if ($questions['sex']['intValue'] = 1) {
                    $sex = 'male';
                } else {
                    $sex = 'female';
                }

            } else {
                $sex = '';
            }

            if (isset($questions['birthdate'])) {
                $birthdate = date('Y/m/d', strtotime($questions['birthdate']['dateValue']));
            } else {
                $birthdate = '';
            }

            // join with avatar (to get avatar)
            $avatar_byDao = $this->avatar_dao->findByUserId($id);

            if (isset($avatar_byDao)) {
                $avatarObj = array();
                foreach ($avatar_byDao as $key => $value) {
                    $avatarObj[$key] = $value;
                }

                $baseUrl = 'http://';
                $origin = str_replace('index.php', '', $_SERVER['PHP_SELF']);
                $baseUrl = $baseUrl . $_SERVER['SERVER_NAME'] . $origin;

                $avatar = $baseUrl . '/ow_userfiles/plugins/base/avatars/avatar_' . $id  . '_' . $avatarObj['hash'] . '.jpg';
                $avatar_big = $baseUrl . '/ow_userfiles/plugins/base/avatars/avatar_big_' . $id  . '_' . $avatarObj['hash'] . '.jpg';
                $avatar_original = $baseUrl . '/ow_userfiles/plugins/base/avatars/avatar_original_' . $id  . '_' . $avatarObj['hash'] . '.jpg';
            } else {
                $avatar = '';
                $avatar_big = '';
                $avatar_original = '';
            }

            // add entry to users array
            $user_toReturn = array(
                'id'              => $id,
                'email'           => $email,
                'username'        => $username,
                'realname'        => $realname,
                'sex'             => $sex,
                'birthdate'       => $birthdate,
                'joinDate'        => $joinDate,
                'avatar'          => $avatar,
                'avatar_big'      => $avatar_big,
                'avatar_original' => $avatar_original
            );

        }

        return ($user_toReturn);
    }

    private function findUserByIdOrUsernameOrEmail($var) {

        $example = new OW_Example();
        $example->andFieldEqual('id', trim($var));

        $result = $this->user_dao->findObjectByExample($example);

        if( $result !== null ) {
            return $result;
        }

        $example = new OW_Example();
        $example->andFieldEqual('username', trim($var));

        $result = $this->user_dao->findObjectByExample($example);

        if( $result !== null ) {
            return $result;
        }

        $example = new OW_Example();
        $example->andFieldEqual('email', trim($var));

        $result = $this->user_dao->findObjectByExample($example);

        return $result;
    }

}