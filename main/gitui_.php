<?php
if(isset($_REQUEST['pull'])) {
    shell_exec('git -C /var/www/flashii.net pull > /var/www/autopull.log');
    print '<h2>Site should be up with the latest commit now.</h2>';
}
?>
<form method="post" action="">
    <input type="submit" name="pull" value="Pull from Repository" />
</form>
