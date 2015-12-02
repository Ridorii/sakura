<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="changePasswordForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="deactivate" />
    <div class="profile-field">
        <div><h2>Username</h2></div>
        <div><input type="text" name="username" placeholder="Case sensitive" class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>Password</h2></div>
        <div><input type="password" name="password" placeholder="Security" class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>E-mail address</h2></div>
        <div><input type="text" name="email" placeholder="More security" class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>Type &quot;I am one hundred percent sure that I want to deactivate my account.&quot; without the quotes.</h2></div>
        <div><input type="text" name="sensitive" placeholder="Are you 100% case sensitively sure?" class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>Verification</h2></div>
        <div style="text-align: center;">{% include 'elements/captcha.tpl' %}</div>
    </div>
    <div class="profile-save">
        <input type="submit" value="Goodbye!" name="submit" class="inputStyling" />
    </div>
</form>
<script type="text/javascript">
window.addEventListener("load", function() {
    prepareAjaxForm('changePasswordForm', 'Changing password...');
});
</script>
