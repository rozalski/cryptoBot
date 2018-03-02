<?php

define(CORE, "../../../coreBots/");

class CryptoBotBase {

    private $baseObj;
    private $baseMessageParams;
    public  $sec;

    public function __construct($userId, $timeMessage) {
        $this->baseObj = $this->getConnection();
        $this->baseMessageParams = array(
            'userId' => $userId,
            'timeMessage' => $timeMessage
        );
    }

    public static function getConnection() {

        $params = require_once CORE . 'config/cryptoBotBaseParams.php';
        $mysqli = new mysqli($params['host'], $params['user'], $params['password'], $params['dbname']);
        return $mysqli;
    }
//    public function retSec(){
//        return $this->seconds;
//    }
//    public function saveentryuser(){
//        $query = "INSERT INTO blacklist (new_member_id, date_time_entry) "
//                . "VALUE ('" . $this->baseMessageParams['userId'] . "',"
//                . " '" . $this->baseMessageParams['timeMessage'] . "')";
//        $this->baseObj->query($query);
//        return;
//    }
//    public function checkBlackList(){
//        $query = "SELECT * FROM blacklist WHERE new_member_id = '" . $this->baseMessageParams['userId'] . "'";
//        $result = $this->baseObj->query($query);
//        if ($result) {
//             $row = $result->fetch_assoc();
//             if($row['date_time_entry']>time()){
//                 return true;
//             }
//             else{
//                 return false;
//             }
//         }
//    }

    public function checkLastMessageTime() {
        $query = "SELECT timeMessage FROM tmpMsg WHERE userId = '" . $this->baseMessageParams['userId'] . "'";

        if ($result = $this->baseObj->query($query)) {
            $row = $result->fetch_assoc();
        } else {
            $this->saveLastMessageTime();
            return false;
        }
        $timestamp2 = time();
        $timestamp1 = $row['timeMessage'];
        $diff = $timestamp2 - $timestamp1;
        $this->sec = $timestamp2 - $timestamp1; // мониторим секунды в логе, удалить после
        //$seconds = $diff - (int) ($diff / 60) * 60; // Разница между (секунды)
        //return $seconds;
        if ($diff < 5) {
            
            $result->free();
            $this->closeConnect();
            return true;
        } else {         
            $this->saveLastMessageTime();
             $result->free();
            $this->closeConnect();
            return false;
        }
    }

    private function saveLastMessageTime() {
         $this->deleteLastMessageTime();
        $query = "INSERT INTO tmpMsg (userId, timeMessage) "
                . "VALUE ('" . $this->baseMessageParams['userId'] . "',"
                . " '" . $this->baseMessageParams['timeMessage'] . "')";
        $this->baseObj->query($query);
        return;
    }

    private function deleteLastMessageTime() {
        $query = "DELETE FROM tmpMsg WHERE userId = '" . $this->baseMessageParams['userId'] . "'";
        $this->baseObj->query($query);
        return;
    }
    
//    public function setVote(){
//       $query = "INSERT INTO voteT (ban_user_id, vote_user_id, vote_stat) "
//                . "VALUE ('" . $this->baseMessageParams['reply_to_from_userId'] . "',"
//                . " '" . $this->baseMessageParams['timeMessage'] . "', '".$this->baseMessageParams['timeMessage']."')";
//        $this->baseObj->query($query);
//        return;
//    }
    

    private function closeConnect() {
        $this->baseObj->close();
    }

}
