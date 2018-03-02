<?php
define(CORE, "../../../coreBots/");
require_once CORE . "criptoBotCore.php";

class voteBanCryptoBot extends CriptoBotWorker{
    public function __construct($inputParams) {
        parent::__construct($inputParams);
    }
    public function startVote(){
        if(isset($this->workinf['reply_to_message'])){
             $this->sendVoteKeyboard();          
              return; 
        }

    else{
        $this->deleteMessage($this->workinf['chatId'], $this->workinf['messageId']);
        return;
    }
           
    }
    private function sendVoteKeyboard(){
        $inline_button1 = array("text" => "\xF0\x9F\x98\xA1 Удалить этот спам!", "callback_data" => "/tvoteban");
        $inline_button2 = array("text" => "\xF0\x9F\x98\x9C Не удалять...", "callback_data" => '/fvoteban');
        
        $inline_keyboard = [[$inline_button1], [$inline_button2]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $replyMarkup = json_encode($keyboard);
        $message = "\xE2\x80\xBC Начато голосование \xE2\x80\xBC \r\n"
                . "Сообщение от:\r\n <b>".$this->workinf['reply_to_from_fname']. " ".
                $this->workinf['reply_to_from_lname']."</b>\r\n"
                . "Текст сообщения:\r\n<b>"
                .$this->workinf['reply_to_text']. "</b>\r\n \xE2\x81\x89 <b>Это спам? Удалить?</b> \xE2\x81\x89";      
        $sendkeyvote = file_get_contents(BotToken . "/sendmessage?chat_id=" . $this->workinf['chatId'] .
                "&parse_mode=HTML&text=" . urlencode($message) . '&reply_markup=' . $replyMarkup . "&resize_keyboard=true");
        $this->sendMessage($sendkeyvote); // тут надо взять id сообщения с голосовалкой
        return;
   }
}
