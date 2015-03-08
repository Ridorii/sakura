<?php
$userFile   = file_get_contents('passwd');
$usersTemp  = explode("\r\n", $userFile);
$users      = array();

foreach($usersTemp as $user) {
    $users[] = explode(":", $userFile);
}

print_r($users);
