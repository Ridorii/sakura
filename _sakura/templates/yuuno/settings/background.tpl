<form enctype="multipart/form-data" method="post" action="{{ setting.action }}">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="background" />
    <input type="hidden" name="MAX_FILE_SIZE" value="{{ background.max_size }}" />
    <div style="text-align: center;">
        <div>
            <img src="/bg/{{ user.data.id }}" alt="Your Background" class="default-avatar-setting" style="max-width: 90%; max-height: 90%;" />
        </div>
        <div>
            <input type="file" name="background" />
        </div>
        <div>
            <input type="submit" value="Submit" name="submit" class="inputStyling" />
        </div>
    </div>
</form>
