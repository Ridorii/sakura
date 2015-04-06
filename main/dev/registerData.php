<?php
require_once '../../_sakura/sakura.php';

if(isset($_POST['submit'])) {

    header('Content-Type: text/plain;charset=utf-8');

    $pass = Sakura\Hashing::create_hash($_POST['password']);

    $regData = [
        'username' => $_POST['username'],
        'username_clean' => Sakura\Main::cleanString($_POST['username'], true),
        'password_hash' => $pass[3],
        'password_salt' => $pass[2],
        'password_algo' => $pass[0],
        'password_iter' => $pass[1],
        'password_chan' => time(),
        'email' => Sakura\Main::cleanString($_POST['email'], true),
        'group_main' => '1',
        'groups' => json_encode([1]),
        'register_ip' => $_SERVER['REMOTE_ADDR'],
        'last_ip' => $_SERVER['REMOTE_ADDR'],
        'regdate' => time(),
        'lastdate' => time(),
        'lastunamechange' => time(),
        'profile_data' => json_encode([])
    ];
    
    print_r($regData);
    exit;

}
?>
<form method="post" action="<?=$_SERVER['PHP_SELF'];?>">
    username: <input type="text" name="username" /><br />
    password: <input type="password" name="password" /><br />
    email: <input type="text" name="email" /><br />
    <input type="submit" name="submit" value="Submit" />
</form>
