<?php

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
}