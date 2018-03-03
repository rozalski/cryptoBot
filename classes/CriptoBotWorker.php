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
            'message' => $inputParams["message"],
            'chatId' => $inputParams["message"]["chat"]["id"],
            'messageId' => $inputParams['message']['message_id'],
            'userId' => $inputParams['message']['from']['id'],
            'command' => mb_strtolower($inputParams['message']["text"]),
            'isSticker' => isset($inputParams['message']['sticker']) ? true : false,
            'timeMessage' => $inputParams["message"]["date"],
            'callback_query_message' => isset($inputParams['callback_query']['data']) ?
            $inputParams['callback_query']['data'] : NULL,
            'gif' => isset($inputParams['message']['document']['mime_type']) ?
            $inputParams['message']['document']['mime_type'] : NULL,
            'callback_messageId' => $inputParams['callback_query']['message']['message_id'],
            'callback_chatId' => $inputParams['callback_query']['message']['chat']['id'],
            'message_caption' => $inputParams['message']['caption'],
            'reply_to_messageId' => $inputParams['message']['reply_to_message']['message_id'],
            'reply_to_from_fname' => $inputParams['message']['reply_to_message']['from']['first_name'],
            'reply_to_from_lname' => $inputParams['message']['reply_to_message']['from']['last_name'],
            'reply_to_text' => $inputParams['message']['reply_to_message']['text'],
            'reply_to_from_userId' => $inputParams['message']['reply_to_message']['from']['id'],
            'entities' => $inputParams['message']['entities'], // это массив 
            'caption_entities' => $inputParams['message']['caption_entities'], // это массив 
            'new_member_id' => $inputParams['message']['new_chat_member']['id'],
            'is_bot' => $inputParams['message']['new_chat_member']['is_bot'],
            //'entities_spam' => $inputParams['message']['entities'][0]['type'],
            'join_group' =>isset($inputParams['message']['new_chat_member'])?true:false
        ];
        $this->censoringMessage();
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
    private function restrictChatMember(){
        $blocktime = time()+86400; // мут новым пользователям на сутки
        file_get_contents(BotToken . "/restrictChatMember?chat_id=".$this->workinf['chatId'].
                "&user_id=".$this->workinf['new_member_id']."&until_date=".$blocktime);
        $this->writeLogDelMessage("Бано новичка:  ".$this->workinf['new_member_id']." ".$this->workinf['chatId']."\n");
    }
     private function checkBots(){
          
       $this->writeLogDelMessage("[" . $this->getTime() . "] check bots".$this->workinf['is_bot'] . "\n");
       if($this->workinf['is_bot']=="true"){
             file_get_contents(BotToken . "/kickChatMember?chat_id=".$this->workinf['chatId'].
                "&user_id=".$this->workinf['new_member_id']);
        $this->writeLogDelMessage("Удалили бота id:  ".$this->workinf['new_member_id']." Из чата: ".$this->workinf['chatId'] . "\n");
            //kick user
       }
    }
    
    private function censoringMessage() { 

        if ($this->workinf['command'] == "/start@mcrpadm18_bot" || $this->workinf['command'] == "/help@mcrpadm18_bot" ||
                $this->workinf['command'] == "/stat@combot" || $this->workinf['command'] == "/getcur@mcrpadm18_bot") {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Удалена команда боту в чате.\n");
            return;
            }elseif (in_array($this->workinf['userId'], $this->admins)) {
            return;
        }
        elseif($this->workinf['join_group']){
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . "Новый пользователь\n");
            $this->restrictChatMember();
            $this->checkBots();
            return;
        }
        
        
        foreach($this->workinf['entities'] as $array){
            foreach ($array as $key => $val){
                if($key == "type" && ($val=="text_link" || $val == "url")){
                   $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Удалена ссылка в entities\n");
            return;
                }
            }
        }      
        foreach($this->workinf['caption_entities'] as $array){
            foreach ($array as $key => $val){
                if($key == "type" && ($val=="text_link" || $val == "url")){
                   $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Удалена ссылка в caption_entities\n");     
            return;
            
                }
            }
        }    

        if ($this->workinf['gif'] != NULL && $this->workinf['gif'] == 'video/mp4') {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Анимация удалена\n");
        
            return;
        }
        foreach ($this->deleteWords as $item) {
            if (preg_match("/$item/", $this->workinf['command'])) {
                $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
                $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Сообщение содежрит мат\n");
                return;
                
            }
        }
        if ($this->workinf['isSticker'] == 1) {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "]  : Удален стикер\n");
            return;
        }


        $bd = new CryptoBotBase($this->workinf['userId'], $this->workinf['timeMessage']);
        if ($bd->checkLastMessageTime()) {
            $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
            $this->writeLogDelMessage("[" . $this->getTime() . "] " . $this->workinf['command'] . " : Прошло менее 5 секунд. Прошло " . $bd->sec . "\n");
            return;
            
        }
        
    }

    private function writeLogDelMessage($logMessage) {
        $file = fopen("logDelNessage.txt", "a");
        fwrite($file, $logMessage);
        fclose($file);

        return;
    }

    public function sendMainKeyboard() {

        $inline_button1 = array("text" => "BTC/USD", "callback_data" => "/btc-usd");
        $inline_button2 = array("text" => "ETH/USD", "callback_data" => '/eth-usd');
        $inline_button3 = array("text" => "ETC/USD", "callback_data" => '/etc-usd');
        $inline_button4 = array("text" => "XRP/USD", "callback_data" => '/xrp-usd');
        $inline_button5 = array("text" => "BCH/USD", "callback_data" => '/bch-usd');
        $inline_button6 = array("text" => "ADA/USD", "callback_data" => '/ada-usd');
        $inline_button7 = array("text" => "LTC/USD", "callback_data" => '/ltc-usd');
        $inline_button8 = array("text" => "XEM/USD", "callback_data" => '/xem-usd');
        $inline_button9 = array("text" => "NEO/USD", "callback_data" => '/neo-usd');
        $inline_button10 = array("text" => "XLM/USD", "callback_data" => '/xlm-usd');
        $inline_keyboard = [array($inline_button1, $inline_button2),
            array($inline_button3, $inline_button4), array($inline_button5,
                $inline_button6), array($inline_button7, $inline_button8),
            array($inline_button9, $inline_button10)];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard);
        $message = "Выбери валютную пару";
        file_get_contents(BotToken . "/sendmessage?chat_id=" . $this->workinf['chatId'] .
                "&text=" . urlencode($message) . '&reply_markup=' . $replyMarkup);
    }

    public function sendStartKeyboard() {
        $inline_button1 = array("text" => "\xF0\x9F\x9A\x80 Основной чат", "url" => "https://t.me/traders_crypto");
        $inline_button2 = array("text" => "\xE2\x98\x95 Флудлка", "url" => 'https://t.me/fortraders_flood');

        $inline_keyboard = [[$inline_button1], [$inline_button2]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard);
        $message = HelpText;

        file_get_contents(BotToken . "/sendmessage?chat_id=" . $this->workinf['chatId'] .
                "&parse_mode=HTML&text=" . urlencode($message) . '&reply_markup=' . $replyMarkup . "&resize_keyboard=true");
    }

    public function getCurs($val) {
        $content = file_get_contents("https://api.cryptonator.com/api/full/" . $val);
        $res = json_decode($content, true);
        foreach ($res['ticker']['markets'] as $market) {
            $marketprices .= "На <b>" . $market['market'] . "</b> торгуется по: " .
                    substr($market['price'], 0, -6) . "\r\n";
        }
        $curs = "<b>Валютная пара:</b> " . $res['ticker']['base'] . " / " . $res['ticker']['target'] .
                "\r\n<b>Цена:</b> " . substr($res['ticker']['price'], 0, -6) .
                "\n<b>Изменение:</b> " . substr($res['ticker']['change'], 0, -6) .
                "\n" . $marketprices;
        $this->sendCallBackMessage($curs);
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
