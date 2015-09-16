<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="changePasswordForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="password" />
    <div class="profile-field">
        <div><h2>Current Password</h2></div>
        <div><input type="password" name="currentpw" placeholder="Enter your current password for verification." class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>New Password</h2></div>
        <div><input type="password" name="newpw" placeholder="Enter your new password." class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>Confirmation</h2></div>
        <div><input type="password" name="conpw" placeholder="Enter your new password again to make sure you didn't fuck up." class="inputStyling" /></div>
    </div>
    <div class="profile-save">
        <input type="submit" value="Save" name="submit" class="inputStyling" />
        <input type="reset" value="Reset" name="reset" class="inputStyling" />
    </div>
</form>
<script type="text/javascript">
window.addEventListener("load", function() {

    prepareAjaxForm('changePasswordForm', 'Changing password...');

});
</script>
