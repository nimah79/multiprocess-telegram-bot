<?php

/* Multiprocess Telegram Bot
 * By NimaH79
 * NimaH79.ir
 * @NimaH79
*/

set_time_limit(0);

define('TOKEN', 'XXXXXXXX:XXXXXXXXXXXXXXXXXXXXXXXXXXXX');

function apiRequest($method, $parameters = [])
{
    foreach ($parameters as $key => &$val) {
        if (is_array($val)) {
            $val = json_encode($val);
        }
    }
    $ch = curl_init('https://api.telegram.org/bot'.TOKEN.'/'.$method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

if (file_exists('updatesOffset')) {
    file_put_contents('updatesOffset', 0);
}

$offset = @file_get_contents('updatesOffset');

while (true) {
    $updates = apiRequest('getUpdates', ['offset' => $offset]);
    $updates = json_decode($updates, true);
    $updates = $updates['result'];

    foreach ($updates as $update) {
        if ($update['update_id'] > $offset) {
            $offset = $update['update_id'] + 1;
        }
        $pid = pcntl_fork();
        if ($pid === -1) {
            die();
        } elseif ($pid) {
            // Created child with PID $pid
        } else {
            if (isset($update['message'])) {
                $message = $update['message'];
                $message_id = $message['message_id'];
                $chat_id = $message['chat']['id'];
                if (isset($message['text'])) {
                    $text = $message['text'];
                    if ($text == '/start') {
                        apiRequest('sendMessage', ['chat_id' => $chat_id, 'text' => 'Hi! I am a miltiprocess Telegram bot!', 'reply_to_message_id' => $message_id]);
                    }
                }
            }
            die();
        }
    }

    file_put_contents('updatesOffset', $offset);
}
