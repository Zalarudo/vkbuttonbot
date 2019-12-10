<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once './vendor/autoload.php';
$token = 'mySecureToken';
//подключение вк
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;
//обработчик ошибок
function myLog($str)
{
    file_put_contents("php://stdout", "$str\n");
}
//цвета и данные для кнопок
const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';
const CMD_ID = 'ID';
const CMD_NAME = 'NAME';
const CMD_NEXT = 'NEXT';
const CMD_TYPING = 'TYPING';
const CMD_JOKE = 'JOKE';
//функция создания кнопок
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

//обработка входящих запросов
if ($type === 'message_new') {
    //получение данных от запроса
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
    //здесь и далее - создание клавиатуры
    $kbd = [
        'one_time' => false,
        'buttons' => [
            [getBtn("Как меня зовут и какой у меня ID?", COLOR_DEFAULT, CMD_NAME)],
            [getBtn("Далее", COLOR_PRIMARY, CMD_NEXT)],
            [getBtn("Расскажи анекдот", COLOR_POSITIVE, CMD_JOKE)],
            [getBtn("Пришли котика", COLOR_PRIMARY, 'cat')]
        ]
    ];
    //стандартное сообщение и вложение
    $msg = "Привет я бот!";
    $attach='';
    if ($payload === CMD_NAME) {
        $msg = "Твое имя " . $user_name . ", а твой ID: " . $userId;
        $kbd = [
            'one_time' => false,
            'buttons' => [
                [getBtn("Сделай как будто печатаешь", COLOR_POSITIVE, CMD_TYPING)],
                [getBtn("Назад", COLOR_NEGATIVE)],
            ]
        ];
    }
    
    if($payload === 'cat'){
        $msg = 'Вот изящный котик';
        $attach = 'photo-189680340_457239019';
        $kbd = [
            'one_time' => false,
            'buttons' => [
                [getBtn("Как меня зовут и какой у меня ID?", COLOR_PRIMARY, CMD_NAME)],
                [getBtn("Назад", COLOR_NEGATIVE)],
            ]
        ];

    }
    if($payload === CMD_JOKE){
        $msg = "Ну что ". $user_name . " хочешь посмеяться, да? Чувак, который меня делает, не спит уже 2-й день и пытается понять, какого хрена я дублирую ответы, а ведь это так просто) И ВК тут не причем, просто он не очень умен";
        $kbd = [
            'one_time' => false,
            'buttons' => [
                [getBtn("Как меня зовут и какой у меня ID?", COLOR_PRIMARY, CMD_NAME)],
                [getBtn("Назад", COLOR_NEGATIVE)],
            ]
        ];

    }    
    if ($payload === CMD_NEXT) {
        $kbd = [
            'one_time' => false,
            'buttons' => [
                [getBtn("Сделай как будто печатаешь", COLOR_POSITIVE, CMD_TYPING)],
                [getBtn("Назад", COLOR_NEGATIVE)],
            ]
        ];
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
    //ответ в вк с помощью метода message.send
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
    //обязательный ответ в вк, чтобы он не спамил запросами
    echo ("ok");

}


