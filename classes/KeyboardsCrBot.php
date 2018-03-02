<?php
//define(CORE, "../../../coreBots/");
//require_once CORE . "criptoBotCore.php";

class KeyboardsCrBot extends CriptoBotWorker{
    public function __construct($inputParams) {
        parent::__construct($inputParams);
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
}
