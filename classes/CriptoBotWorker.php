<?php

define(CORE, "../../../coreBots/");
require_once CORE . "criptoBotCore.php";
require_once 'criptoBotBase.php';
ini_set('date.timezone', 'Europe/Moscow');

class CriptoBotWorker {

    private $deleteWords;
    private $admins;
    private $tags;
    public $workinf = [];

    public function __construct($inputParams) {

        $wordsPath = CORE . 'dWords.php';
        $this->deleteWords = include($wordsPath);
        $this->tags = include(CORE . "tagsList.php");
        $admins = CORE . 'cryptoAdmins.php';
        $this->admins = include($admins);
        $this->workinf = [
            //'inputJSON' => $content,
            'message' => isset($inputParams["message"])?$inputParams["message"]:NULL,
            'chatId' => isset($inputParams["message"]["chat"]["id"])?$inputParams["message"]["chat"]["id"]:NULL,
            'messageId' => isset($inputParams['message']['message_id'])?$inputParams['message']['message_id']:NULL,
            'userId' => isset($inputParams['message']['from']['id'])?$inputParams['message']['from']['id']:NULL,
            'command' => mb_strtolower(isset($inputParams['message']["text"])?$inputParams['message']["text"]:NULL),
            'isSticker' => isset($inputParams['message']['sticker']) ? true : false,
            'timeMessage' => isset($inputParams["message"]["date"])?$inputParams["message"]["date"]:NULL,
            'callback_query_message' => isset($inputParams['callback_query']['data']) ?
            $inputParams['callback_query']['data'] : NULL,
            'gif' => isset($inputParams['message']['document']['mime_type']) ?
            $inputParams['message']['document']['mime_type'] : NULL,
            'callback_messageId' => isset($inputParams['callback_query']['message']['message_id']) ?
            $inputParams['callback_query']['message']['message_id'] : NULL,
            'callback_chatId' => isset($inputParams['callback_query']['message']['chat']['id']) ?
            $inputParams['callback_query']['message']['chat']['id'] : NULL,
            'message_caption' => isset($inputParams['message']['caption'])?
            $inputParams['message']['caption']:NULL,
            'reply_to_messageId' => isset($inputParams['message']['reply_to_message']['message_id'])?
            $inputParams['message']['reply_to_message']['message_id']:NULL,
            'reply_to_from_fname' => isset($inputParams['message']['reply_to_message']['from']['first_name'])?
            $inputParams['message']['reply_to_message']['from']['first_name']:NULL,
            'reply_to_from_lname' => isset($inputParams['message']['reply_to_message']['from']['last_name'])?
            $inputParams['message']['reply_to_message']['from']['last_name']:NULL,
            'reply_to_text' => isset($inputParams['message']['reply_to_message']['text'])?
            $inputParams['message']['reply_to_message']['text']:NULL,
            'reply_to_from_userId' => isset($inputParams['message']['reply_to_message']['from']['id'])?
            $inputParams['message']['reply_to_message']['from']['id']:NULL,
            'entities' => isset($inputParams['message']['entities']) ?
            $inputParams['message']['entities'] : NULL, // это массив 
            'caption_entities' => isset($inputParams['message']['caption_entities']) ?
            $inputParams['message']['caption_entities'] : NULL, // это массив 
            'new_member_id' => isset($inputParams['message']['new_chat_member']['id'])?
            $inputParams['message']['new_chat_member']['id']:NULL,
            'is_bot' => isset($inputParams['message']['new_chat_member']['is_bot'])?
            $inputParams['message']['new_chat_member']['is_bot']:NULL,
            //'entities_spam' => $inputParams['message']['entities'][0]['type'],
            'join_group' => isset($inputParams['message']['new_chat_member']) ? true : false,
            'from_chat' => isset($inputParams['message']['forward_from_chat']['id'])?true:false,
            'from_bot' => isset($inputParams['message']['forward_from']['is_bot'])?
            $inputParams['message']['forward_from']['is_bot']:NULL
        ];

        $this->censoringMessage();

    }
    private function removeKeyboard(){
       $removeKeyboard = json_encode([
            'remove_keyboard' => true,
        ]);      
        $reply_markup = '&reply_markup='.$removeKeyboard;
        $this->writeLogDelMessage(BotToken . "/sendmessage?chat_id=" .
            $this->workinf['chatId'] . "&text=1".$reply_markup);      
    }

    private function keyboardinit() {
    $keyb = array('');    
    $keyboard = array($keyb);
    $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
    $reply = json_encode($resp);
    file_get_contents(BotToken . "/sendmessage?chat_id=" .
            $this->workinf['chatId'] . "&text=". urlencode('test')."&reply_markup=" . $reply);  

    return;
}
    public function sendMessage($message) {
        file_get_contents(BotToken . "/sendmessage?chat_id=" . $this->workinf['chatId'] .
                "&text=" . urlencode($message));
    }

    public function sendCallBackMessage($message) {
        file_get_contents(BotToken . "/sendmessage?chat_id=" . $this->workinf['callback_chatId'] .
                "&parse_mode=HTML&text=" . urlencode($message));
    }

    private function deleteMessage($chatID, $messageID) {
        file_get_contents(BotToken . "/deletemessage?chat_id=" . $chatID .
                "&message_id=" . $messageID);
    }

    private function restrictChatMember() {
        $blocktime = time() + 86400; // мут новым пользователям на сутки
        file_get_contents(BotToken . "/restrictChatMember?chat_id=" . $this->workinf['chatId'] .
                "&user_id=" . $this->workinf['new_member_id'] . "&until_date=" . $blocktime);
        $this->writeLogDelMessage("Бан новичка:  " . $this->workinf['new_member_id'] . " " . $this->workinf['chatId'] . "\n");
    }

    private function checkBots() {

        $this->writeLogDelMessage("Проверка на бота. Результат: " . $this->workinf['is_bot'] . "\n");
        if ($this->workinf['is_bot'] == "true") {
            file_get_contents(BotToken . "/kickChatMember?chat_id=" . $this->workinf['chatId'] .
                    "&user_id=" . $this->workinf['new_member_id']);
            $this->writeLogDelMessage("Удалили бота id:  " . $this->workinf['new_member_id'] . " Из чата: " . $this->workinf['chatId'] . "\n");
            //kick user
        }
    }

    private function censoringMessage() {

        if ($this->workinf['command'] == "/start@mcrpadm18_bot" || $this->workinf['command'] == "/help@mcrpadm18_bot" ||
                $this->workinf['command'] == "/stat@combot" || $this->workinf['command'] == "/getcur@mcrpadm18_bot") {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage($this->workinf['command'] . " : Удалена команда боту в чате.\n");
            return;
        } elseif (in_array($this->workinf['userId'], $this->admins)) {
            return;
        } elseif ($this->workinf['join_group']) {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("Новый пользователь. Удалено сервисное сообщение.\n");
            $this->restrictChatMember();
            $this->checkBots();
            return;
        }

        if ($this->workinf['entities'] != NULL) {
            foreach ($this->workinf['entities'] as $array) {
                foreach ($array as $key => $val) {
                    if ($key == "type" && ($val == "text_link" || $val == "url")) {
                        $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
                        $this->writeLogDelMessage($this->workinf['command'] . " : Удалена ссылка в entities\n");
                        return;
                    }
                }
            }
        }
        if ($this->workinf['caption_entities'] != NULL) {
            foreach ($this->workinf['caption_entities'] as $array) {
                foreach ($array as $key => $val) {
                    if ($key == "type" && ($val == "text_link" || $val == "url")) {
                        $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
                        $this->writeLogDelMessage($this->workinf['command'] . " : Удалена ссылка в caption_entities\n");
                        return;
                    }
                }
            }
        }

        if ($this->workinf['gif'] != NULL && $this->workinf['gif'] == 'video/mp4') {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("Анимация удалена\n");

            return;
        }
        if($this->workinf['from_chat']){
             $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("Удален репост из другой группы.\n");
        }
         if($this->workinf['from_bot']=='true'){
             $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("Удален репост от бота.\n");
        }
        foreach ($this->deleteWords as $item) {
            if (preg_match("/$item/", $this->workinf['command'])) {
                $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
                $this->writeLogDelMessage($this->workinf['command'] . " : Сообщение содежрит мат\n");
                return;
            }
        }
        if ($this->workinf['isSticker'] == 1) {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage($this->workinf['chatId']."Удален стикер\n");
            return;
        }


        $bd = new CryptoBotBase($this->workinf['userId'], $this->workinf['timeMessage']);
        if ($bd->checkLastMessageTime()) {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage( $this->workinf['command'] . " : Прошло менее 5 секунд. Прошло " . $bd->sec . "\n");
            return;
        }
    }

    private function writeLogDelMessage($logMessage) {
        $file = fopen("logDelNessage.txt", "a");
        fwrite($file, "[" . $this->getTime() . "] " . $logMessage);
        fclose($file);

        return;
    }

    private function getTime() {
        (int) $h = date('H');
        (int) $m = date('i');
        $str_h = "";
        $str_m = "";
        if ($h % 10 == 1 && ($h < 10 || $h > 20)) {
            $str_h = "час";
        } elseif (($h % 10 > 1 && $h < 5) || ($h > 20 && $h % 10 > 1)) {
            $str_h = "часа";
        } else {
            $str_h = "часов";
        }
        if ($m % 10 == 1 && $m != 11) {
            $str_m = "минута";
        } elseif (($m > 20 || $m < 10) && ($m % 10 > 1 && $m % 10 < 5)) {
            $str_m = "минуты";
        } else {
            $str_m = "минут";
        }
        $time = $h . " " . $str_h . " " . $m . " " . $str_m;
        return $time;
    }

}
