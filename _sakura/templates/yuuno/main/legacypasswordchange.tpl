{% include 'global/header.tpl' %}
    <div class="content news settings">
        <div class="head">Changing Password</div>
        <div class="settings-explanation">
            Because of a change in the way Flashii handles authentication you are required to change your password.
        </div>
        <form method="post" action="/authenticate">
            <input type="hidden" name="redirect" value="//iihsalf.net/" />
            <input type="hidden" name="session" value="{{ php.sessionid }}" />
            <input type="hidden" name="time" value="{{ php.time }}" />
            <input type="hidden" name="mode" value="legacypwchange" />
            <div class="profile-field">
                <div><h2>Old Password</h2></div>
                <div style="text-align: center;"><input type="password" name="oldpw" placeholder="Your current password for verification" class="inputStyling" /></div>
            </div>
            <div class="profile-field">
                <div><h2>New Password</h2></div>
                <div style="text-align: center;"><input type="password" name="newpw" placeholder="Your new password, can be the same but that's not a good idea" class="inputStyling" /></div>
            </div>
            <div class="profile-field">
                <div><h2>Verify Password</h2></div>
                <div style="text-align: center;"><input type="password" name="verpw" placeholder="Your new password again to make sure you didn't typo anything" class="inputStyling" /></div>
            </div>
            <div class="profile-save">
                <input type="submit" value="Save" name="submit" class="inputStyling" /> <input type="reset" value="Reset" name="reset" class="inputStyling" />
            </div>
        </form>
    </div>
{% include 'global/footer.tpl' %}
