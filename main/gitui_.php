<?php
if(isset($_REQUEST['pull'])) {
    header('Content-Type: text/plain');
    print shell_exec('git -C /var/www/flashii.net pull > /var/www/autopull.log');
}
?>
<form method="post" action="">
    <input type="submit" name="pull" value="Pull from Repository" />
</form>
