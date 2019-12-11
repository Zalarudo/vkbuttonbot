<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once './vendor/autoload.php';



$datakonv = file_get_contents("https://konverbot.net/chatbot/novyj-chat-bot-72/?skey=b929a3af31a2c4d3ab72be8706f2ab46&test_export_json");


$decdata = json_decode($datakonv);

$quest = $decdata -> konverbot_questions;


$token = '61ca346dfccd75afa801ae21bb6991023248bb225c1d1b9b1606affc1f19329b250bb73dd1901dc8dc2aa';
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;
function myLog($str)
{
    file_put_contents("php://stdout", "$str\n");
}
const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';
const CMD_ID = 'ID';
const CMD_NAME = 'NAME';
const CMD_NEXT = 'NEXT';
const CMD_TYPING = 'TYPING';
const CMD_JOKE = 'JOKE';
const CMD_MUSIC = 'MUSIC';
function getBtn($label, $color, $payload = '')
{
    return [
        'action' => [
            'type' => 'text',
            "payload" => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'label' => $label
        ],
        'color' => $color
    ];
}

$json = file_get_contents('php://input');
//myLog($json);
$data = json_decode($json, true);
$type = $data['type'] ?? '';
$vk = new VKApiClient('5.85', VKLanguage::RUSSIAN);
if($type === 'confirmation'){
    echo ' 92d1037e';

}

if ($type === 'message_new') {


    $message = $data['object'] ?? [];
    $userId = $message['user_id'] ?? $message['peer_id'];
    $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userId}&access_token={$token}&v=5.85"));
    $user_name = $userInfo->response[0]->first_name;
    $body = $message['body'] ?? '';
    $payload = $message['payload'] ?? '';


    if ($payload) {
        $payload = json_decode($payload, true);
    }

    myLog("MSG: " . $body . " PAYLOAD:" . $payload);

    $kbd = [
        'one_time' => false,
        'buttons' => [
            [getBtn($quest[0]->wpchatbot_answers[0]->wpchatbot_answer, COLOR_DEFAULT, $quest[0]->wpchatbot_answers[0]->wpchatbot_goto)],
            [getBtn($quest[0]->wpchatbot_answers[1]->wpchatbot_answer, COLOR_DEFAULT, $quest[0]->wpchatbot_answers[1]->wpchatbot_goto)],
            [getBtn($quest[0]->wpchatbot_answers[2]->wpchatbot_answer, COLOR_DEFAULT, $quest[0]->wpchatbot_answers[2]->wpchatbot_goto)]
        ]
    ];
    $msg = strip_tags($quest[0]->wpchatbot_question);
    $attach='';
    $i = 1;


//        if (strripos($body, "Марка") == true || mb_strtolower($body) == "Марка") {
//            $msg = strip_tags($quest[2]->wpchatbot_question);
//        }
//

    foreach ($quest as $value){

        if ($payload == $i){

            $for = $i-1;
            $msg = strip_tags($quest[$for]->wpchatbot_question);


            if($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer_type =='input'){

                if($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer_input_label =='Введите марку'){

                $msg = $msg . " Пожалуйста напишите ответ в формате: Марка - ваша марка";

                }else if($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer_input_label =='Введите имя'){
                    $msg = $msg . " Пожалуйста напишите ответ в формате: Имя - ваше имя";
                } else if($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer_input_label =='Укажите удобную дату'){
                    $msg = $msg . " Пожалуйста напишите ответ в формате: Дата - удобная дата";
                }else if($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer_input_label =='Введите номер телефона'){
                    $msg = $msg . " Пожалуйста напишите ответ в формате: Номер - ваш номер телефона";
                }

                $kbd = [
                    'one_time' => true,
                    'buttons' => [
                        [getBtn('Отправить ответ', COLOR_DEFAULT, $quest[$for]->wpchatbot_answers[0]->wpchatbot_goto)]
                    ]
                ];

                break;
            }else {
                $kbd = [
                    'one_time' => false,
                    'buttons' => [
                        [getBtn($quest[$for]->wpchatbot_answers[0]->wpchatbot_answer, COLOR_DEFAULT, $quest[$for]->wpchatbot_answers[0]->wpchatbot_goto)],
                        [getBtn($quest[$for]->wpchatbot_answers[1]->wpchatbot_answer, COLOR_DEFAULT, $quest[$for]->wpchatbot_answers[1]->wpchatbot_goto)],
                        [getBtn($quest[$for]->wpchatbot_answers[2]->wpchatbot_answer, COLOR_DEFAULT, $quest[$for]->wpchatbot_answers[2]->wpchatbot_goto)]
                    ]
                ];
                break;
            }
        }
        $i++;
    }

    if ($payload === CMD_TYPING) {
        try {
            $res = $vk->messages()->setActivity($token, [
                'peer_id' => $userId,
                'type' => 'typing'
            ]);
            $msg = null;
        } catch (\Exception $e) {
            myLog($e->getCode() . ' ' . $e->getMessage());
        }
    }
    try {
        if ($msg !== null) {
            $response = $vk->messages()->send($token, [
                'peer_id' => $userId,
                'message' => $msg,
                'keyboard' => json_encode($kbd, JSON_UNESCAPED_UNICODE),
                'attachment'=> $attach
            ]);
        }
    } catch (\Exception $e) {
        myLog($e->getCode() . ' ' . $e->getMessage());
    }
    echo ("ok");

}


