<?php

include_once './classes/CriptoBotWorker.php';


$content = file_get_contents("php://input");
$update = json_decode($content, TRUE);
$CrBot = new CriptoBotWorker($update);


if ($CrBot->workinf['command'] == "/start") { 
    if ($CrBot->workinf['chatId'] < 0) {
        return;
    }  
    return;
}


