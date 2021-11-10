<?php
/*****simpleServerMonitor - motinoring Load Average on nix servers****/

/*const Telegram*/
define('TELEGRAM_TOKEN', '');
define('TELEGRAM_CHATID', ''); // insert your Chat ID (get in show JSON bot)


$loadAverage = sys_getloadavg(); //get Load average on *Nix
$loadAverageStr = implode(" ", $loadAverage); //join array to string
$loadAverageNow = $loadAverage[0];
$message = "Нагрузка на сервере: $loadAverage[0]\nFULL LA: $loadAverageStr";
$type = checkLoadAverage($message, $loadAverage); //get type alert or null

if(isNeedAlert($type)) {
    $message = $type.$message; //emoji + message
    sendAlertTelegram(TELEGRAM_TOKEN, TELEGRAM_CHATID, $message);
}


function isNeedAlert($type)
{
    if(!is_null($type)) {
        return true;
    }
}

function checkLoadAverage($message, $loadAverageNow) {
    $type = ['warning'=> "😡", 'critical' => "💀"];

    switch ($loadAverageNow) {
        case ($loadAverageNow >= 5 and $loadAverageNow < 10):
            return $type['warning'];
        case ($loadAverageNow >= 10):
            return $type['critical'];
        default:
            return null;
    }
}

function sendAlertTelegram($token, $chatID, $message)
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => $chatID,
                'text' => $message,
            ),
        )
    );
    curl_exec($ch);
}
