{% if (user.userData.profileBackground is defined and user.checkPermission('SITE', 'CHANGE_BACKGROUND')) or user.checkPermission('SITE', 'CREATE_BACKGROUND') %}
    <form enctype="multipart/form-data" method="post" action="{{ setting.action }}">
        <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
        <input type="hidden" name="timestamp" value="{{ php.time }}" />
        <input type="hidden" name="mode" value="background" />
        <input type="hidden" name="MAX_FILE_SIZE" value="{{ background.max_size }}" />
        <div style="text-align: center;">
            <div>
                <img src="/bg/{{ user.id }}" alt="Your Background" class="default-avatar-setting" style="max-width: 90%; max-height: 90%;" />
            </div>
            <div>
                <input type="file" name="background" />
                <div style="font-size: .8em;">
                    (Leave upload box empty to remove background)
                </div>
            </div>
            <div>
                <input type="submit" value="Submit" name="submit" class="inputStyling" />
            </div>
        </div>
    </form>
{% else %}
    <h1 class="stylised" style="margin: 2em auto; text-align: center;">You do not have the permission to change your background.</h1>
{% endif %}
