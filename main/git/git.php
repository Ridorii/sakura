<?php
$userFile   = file_get_contents('passwd');
$usersTemp  = explode("\r\n", $userFile);
$users      = array();

foreach($usersTemp as $user) {
    $userTemp = explode(":", $userFile);
    $users[$userTemp[0]] = $userTemp[1];
    unset($userTemp);
}

print_r($users);
