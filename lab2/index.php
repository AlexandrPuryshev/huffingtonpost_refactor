<?php

include("SendMessage.class.php");

const LOGIN         =   "Kronos0041";
const PASSWORD      =   "723248636291";

$text   =   "Hi! How are you doing? Do you wanna chat with me?";

error_reporting( E_ERROR );

$data = "user=Kronos004&password=723248636291&rem=1&a=2&ajax=2&_tp_=xml";

// Для проверки отправить forzahar1 пароль randki
/*
$data = array(
    'http://znajomi.interia.pl/wiadomosci/nowa?usr=4866468'
);
*/

$send = new SendMessage($data, $text);
$send->send();


echo "\n\nTHE WORK DONE!\n\n";
