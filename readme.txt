сайт для mysql - 'http://127.0.0.1/openserver/phpmyadmin/index.php'
Всем привет , форумчанцам , вообщем видел что ктото да объснял насчет своей апи, но все равно подытожу , сделаю небольшой гайдик(ну как небольшой) 
Разделю этот гайд на 2 части ( собственно сама апишка(самая простая из всех наверно) и сам код луа(получился небольшой). Дело в том , что у доты своих вебсокетов для каждой кастомной игры, приходится вручную каждую секунду отправлять запрос на свою апи, лично я в гайде буду использовать OpenServer(локальный хостинг) , всем рекомендую , потом перейдете на хост за 10 рублей в год и вам хватать будет.
Что вооьще я делаю и зачем , в примере я покажу как можно сделать функцию для сохранения и загрузки инвенторя, на самом деле здесь уже будет все что нужно.
[SPOILER="API"] вообще нужно для начала понять что такое api собсвенна , апи это своего рода штука , которая служит для передачи инфы между чем-то и чем-то(в нашем случае между доткой и нашей базой данных, либо хз что вы там собираетесь делать, с ней можно много разных штук сделать) 
Я буду писать на пхп, наверно самое быстрое на чем можно развернуть свое апи. для начала запустим хост( повторюсь я делаю на локаььном хостинге) 
Сразу сделаю путь api/… чтобы было легче работать в будущем. В директории создаем 3 файлика( вообще можно 1 , но просто так намного понятнее и удобнее) 1) index.php
2)functions.php
3)connect.php
Пока не будем вдаваться в подробности , что и зачем.
Заходим в index.php и прописываем
[CODE=php]<?php
//Устанавливаем заголовки для ответов
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// конектим наши 2 файла, в котором будут подключение к бд и функции соотвественно
require 'connect.php';
require 'functions.php';
 
//Определяем метод запроса
$method = $_GET["method"];

// Получаем payload запроса
$payload = json_decode(file_get_contents("php://input"), true);

// Явно определяем значение параметра steamid
$steamid = $_GET['steamid'];

if ($method === "load") {
    getInventory($mysqli, $steamid);
} elseif ($method === "save"){
    setInventory($mysqli, $steamid, $payload);
}
?>[/CODE]
Ну проходится по коду я не буду, единственное что скажу, что по факту запрос будет Post, но мы также можем брать данные из get.
Собственно в гет параметре мы укажем стим айди и метод, в пост параметре предметы для сохранения( именно названия) 
Далее код для самих функций 
[CODE=php]<?php

function getInventory($mysqli, $steamid) {
    // Запрос на выборку 6 слотов по параметру steamid
    $query = "SELECT `slot0`, `slot1`, `slot2`, `slot3`, `slot4`, `slot5` FROM `inventory` WHERE `steamid` = '$steamid'";
    $result = $mysqli->query($query);
    
    if (!$result) {
        echo "Ошибка запроса: " . $mysqli->error;
        return;
    }
    
    // Извлекаем данные из результата запроса и помещаем их в ассоциативный массив
    $inventory = $result->fetch_assoc();
    
    // Конвертируем массив в JSON и возвращаем в качестве ответа
    echo json_encode($inventory);
}

function setInventory ($mysqli, $steamid, $body) {
    //Пытаемся найти уже существующую запись для данного steamid
    $query = "SELECT * FROM `inventory` WHERE `steamid` = '$steamid'";
    $inventory = $mysqli->query($query)->fetch_assoc();

    //Записываем в переменные значения полей из пейлоада (вовсе не обязательно и body можно использовать напрямую)
    $slot0 = $body[0];
    $slot1 = $body[1];
    $slot2 = $body[2];
    $slot3 = $body[3];
    $slot4 = $body[4];
    $slot5 = $body[5];

    //Проверяем существует ли в базе данных запись с этим steamid
    if (is_null($inventory)){
        //Если не существует осуществляем вставку
        $query = "INSERT INTO `inventory`(`steamid`, `slot0`, `slot1`, `slot2`, `slot3`, `slot4`, `slot5`)
                  VALUES ('$steamid','$slot0','$slot1','$slot2','$slot3','$slot4','$slot5')";
        $mysqli->query($query);
    } else {
        //Если существует осуществляем обновление
        $query = "UPDATE `inventory`
                  SET `slot0`='$slot0',`slot1`='$slot1',`slot2`='$slot2',`slot3`='$slot3',`slot4`='$slot4',`slot5`='$slot5'
                  WHERE `steamid` = '$steamid'";
        $mysqli->query($query);
    }
    $query = "SELECT `slot0`, `slot1`, `slot2`, `slot3`, `slot4`, `slot5` FROM `inventory` WHERE `steamid` = '$steamid'";
    $result = $mysqli->query($query);
    $inventory = $result->fetch_assoc();
    
    // Конвертируем массив в JSON и возвращаем в качестве ответа
    echo json_encode($inventory);
}[/CODE]
Объяснять тут не буду, если хотите вставьте нейронке , может пояснит , что и как. Последний файл самый такой выжненький , подлючегие к бд
[CODE=php]<?php
// Устанавливаем параметры подключения
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'inventory');

// Создаем объект mysqli для подключения к базе данных
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Проверяем соединение на наличие ошибок
if ($mysqli->connect_errno) {
    echo "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}[/CODE]
Фух, но вроде , осталась бд и можно переходить к луа, вообщем бд создаете( смотрите гайды, раписывать слишком долго) , создаете в таблице 7 стлбоцов, 1 для steamid, 6 для вот таких названий slot0,slot1,slot2 и так далее до 5.
Так ну вроде бы закончили.
[/SPOILER]
[SPOILER="Lua"]
Так ну начнем с того что будем использовать слушатель для сообщения в чате  ListenToGameEvent("player_chat", Dynamic_Wrap(self,"OnChat"), self)
Также создайте файл webtool и импортируете его 
В нем напишите:
[CODE=lua]
requests = {}

function requests:goto(link, body, callback)
    local req
    if body then
        req = CreateHTTPRequestScriptVM("POST", link)
        local pl = json.encode(body)
        req:SetHTTPRequestRawPostBody('application/json', pl)
    else
        req = CreateHTTPRequestScriptVM("GET", link)
    end
    req:SetHTTPRequestAbsoluteTimeoutMS(5000)
    req:SetHTTPRequestHeaderValue("Dedicated-Server-Key", "test")
    req:Send(function(result)
        info = {statuscode = result.StatusCode}
        if result.StatusCode == 200 then
            local data = json.decode(result.Body)
            if data then info.data = data else info.data = "nil" end
        end
        if callback then callback(info) end
    end)
end[/CODE] код для самого запроса и получение данных 
Дальше просто осталось сделать функцию которая срабатывает когда плеер пишет в чат ,помните мы писали слушатель, так вот он для чего.
[CODE=lua]
function CAddonTemplateGameMode:OnChat(keys)
    local text = string.lower(keys.text)
    local playerID = keys.playerid
    local hero = PlayerResource:GetSelectedHeroEntity(playerID)


    if text == "-save" then
        local steamid = PlayerResource:GetSteamAccountID(playerID)
        items = GetItemList(hero)
        requests:goto( link.."/api/inventory/index.php?steamid="..steamid .. "&method=save", items, function (result)
            DeepPrintTable(result)
        end)
    elseif text == '-load' then
        local steamid = PlayerResource:GetSteamAccountID(playerID)
        -- send GET request withcallback processing 
        local body = requests:goto(link.."/api/inventory/index.php?steamid="..steamid.. "&method=load", nil, function(result)
        -- callback processing (data - response object)
        local data = result.data
        local eslot = nil
        for slot=0,5 do
            print(data['slot'..slot])
            -- remove anything in current slot
            local iItem = hero:GetItemInSlot(slot)
            if iItem then hero:RemoveItem(iItem) end

            -- add item to slot
            local item = hero:AddItemByName(data['slot'..tostring(slot)])

            -- rearrange slot
            if item then
                if eslot and eslot~=slot then    
                    hero:SwapItems( eslot, slot )
                end
            elseif not eslot then
                eslot = slot
            end
        end
        end)
    end
end
[/CODE]
[/SPOILER]
Вроде все, вас осталось только при загрузке выдать предмет , написать в чат -save , выкинуть предмет и написать -load, вуаля