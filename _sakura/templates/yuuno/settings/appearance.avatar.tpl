<form enctype="multipart/form-data" method="post" action="{{ setting.action }}">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="avatar" />
    <input type="hidden" name="MAX_FILE_SIZE" value="{{ avatar.max_size }}" />
    <div style="text-align: center;">
        <div>
            <img src="/a/{{ user.id }}" alt="Your Avatar" class="default-avatar-setting" />
        </div>
        <div>
            <input type="file" name="avatar" />
            <div style="font-size: .8em;">
                (Leave upload box empty to remove avatar)
            </div>
        </div>
        <div>
            <input type="submit" value="Submit" name="submit" class="inputStyling" />
        </div>
    </div>
</form>
