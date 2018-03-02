<?php

include_once './classes/CriptoBotWorker.php';
//include_once './classes/voteBanCryptoBot.php';


$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$CrBot = new CriptoBotWorker($update);
//$voteBan = new voteBanCryptoBot($update);

// $CrBot->sendMessage($content);
// return;

if ($CrBot->workinf['callback_query_message'] != NULL) {
//    if ($CrBot->workinf['callback_query_message'] == '/tvoteban') {
//        $CrBot->sendCallBackMessage("Баним");
//        $CrBot->sendMessage($content);
//        return;
//    }
//    if ($CrBot->workinf['callback_query_message'] == '/fvoteban') {
//        $CrBot->sendCallBackMessage("Прощаем");
//        return;
//    }
    $val = mb_substr($CrBot->workinf['callback_query_message'], 1);

    $CrBot->getCurs($val);
}


if ($CrBot->workinf['command'] == "/start") { // @mcrpadm18_bot
    if ($CrBot->workinf['chatId'] < 0) {
        return;
    }  
    $CrBot->sendStartKeyboard();
}
if ($CrBot->workinf['command'] == "/help") {
    if ($CrBot->workinf['chatId'] < 0) {
        return;
    }
    $CrBot->sendMessage(HelpText);
}
if ($CrBot->workinf['command'] == "/getcur") { // @mcrpadm18_bot
    if ($CrBot->workinf['chatId'] < 0) {
        return;
    }
    $CrBot->sendMainKeyboard();  
}
//if ($CrBot->workinf['command'] == "/vote1") { // @mcrpadm18_bot
//    if ($CrBot->workinf['chatId'] > 0) {
//        return;
//    }
//   // $voteBan->startVote();
//    //$CrBot->sendMessage($content);
//}




