<?php

/**simpleServerMonitor - motinoring Load Average on nix servers**/

/*const Telegram*/
const TELEGRAM_TOKEN = '';
const TELEGRAM_CHATID = ''; //insert your Chat ID (get in show JSON bot)

$loadAverage = getLoadAverage();
$serverName = 'Leaseweb NL';
$uptime = getUpTime();
$message = "Load on server $serverName:\n" . $loadAverage['now'] . "\nFULL LA: " . $loadAverage['full'] . "\nUpTime: $uptime days";
$type = checkLoadAverage($loadAverage['now']); //get type alert or null

if (isNeedAlert($type)) {
    $message = "$type.$message"; //emoji + message
    sendAlertTelegram(TELEGRAM_TOKEN, TELEGRAM_CHATID, $message);
}

function getLoadAverage(): array
{
    $loadAverage = sys_getloadavg(); //get Load average on *Nix
    $loadAverage['now'] = $loadAverage[0];
    $loadAverage['full'] = implode(" ", $loadAverage); //join array to string
    
    return $loadAverage;
}

function isNeedAlert(?string $type): bool
{
    if (!is_null($type)) {
        return true;
    }
    
    return false;
}

function checkLoadAverage(int $loadAverageNow): ?string
{
    $type = ['warning' => "😡", 'critical' => "☠"];

    switch ($loadAverageNow) {
        case ($loadAverageNow >= 5 and $loadAverageNow < 10):
            return $type['warning'];
        case ($loadAverageNow >= 10):
            return $type['critical'];
        default:
            return null;
    }
}

function sendAlertTelegram(string $token, string $chatID, string $message): void
{
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . $token . '/sendMessage',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => $chatID,
                'text' => $message,
            ),
        )
    );
    curl_exec($ch);
}

function getUpTime(): int
{
    $uptime = @file_get_contents('/proc/uptime');
    $seconds = floatval($uptime);
    
    return round($seconds / 86400, 2);
}
