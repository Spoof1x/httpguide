<?php
//Устанавливаем заголовки для ответов
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php';
require 'functions.php';

//Перед всеми действиями валидация ключа сервера  

//Определяем метод запроса
$method = $_GET["method"];

// Получаем payload запроса
$payload = json_decode(file_get_contents("php://input"), true);

// Явно определяем значение параметра steamid
$steamid = $_GET['steamid'];

// В зависимости от метода и роута вызываем нужную функцию
if ($method === "load") {
    getInventory($mysqli, $steamid);
} elseif ($method === "save"){
    setInventory($mysqli, $steamid, $payload);
}
?>
