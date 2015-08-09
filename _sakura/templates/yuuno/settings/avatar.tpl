{% include 'global/header.tpl' %}
    <div class="content settings messages">
        <div class="content-right content-column">
            {% include 'elements/settingsNav.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">
                {{ page.title }}
            </div>
            <div class="settings-explanation">
                <div>Your avatar which is displayed all over the site and on your profile.</div>
                <div>Maximum image size is {{ avatar.max_width }}x{{ avatar.max_height }}, minimum image size is {{ avatar.min_width }}x{{ avatar.min_height }}, maximum file size is {{ avatar.max_size_view }}.</div>
            </div>
            <form enctype="multipart/form-data" method="post" action="{{ setting.action }}">
                <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
                <input type="hidden" name="timestamp" value="{{ php.time }}" />
                <input type="hidden" name="mode" value="avatar" />
                <input type="hidden" name="MAX_FILE_SIZE" value="{{ avatar.max_size }}" />
                <div style="text-align: center;">
                    <div>
                        <img src="/a/{{ user.data.id }}" alt="Your Avatar" class="default-avatar-setting" />
                    </div>
                    <div>
                        <input type="file" name="avatar" />
                    </div>
                    <div>
                        <input type="submit" value="Submit" name="submit" class="inputStyling" />
                    </div>
                </div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
