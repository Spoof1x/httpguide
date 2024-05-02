<?php
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
?>
