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
                <div>The background that is displayed on your profile.</div>
                <div>Maximum image size is {{ background.max_width }}x{{ background.max_height }}, minimum image size is {{ background.min_width }}x{{ background.min_height }}, maximum file size is {{ background.max_size_view }}.</div>
            </div>
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
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
