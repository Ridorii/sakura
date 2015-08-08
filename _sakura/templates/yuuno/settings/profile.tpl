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
                These are the external account links etc. on your profile, shouldn't need any additional explanation for this one.
            </div>
            <form enctype="multipart/form-data" method="post" action="{{ sakura.currentpage }}">
                <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
                <input type="hidden" name="timestamp" value="{{ php.time }}" />
                <input type="hidden" name="mode" value="profile" />
                {% for field in profile.fields %}
                    <div class="profile-field">
                        <div>
                            <h2>{{ field.name }}</h2>
                        </div>
                        <div>
                            <input type="{{ field.formtype }}" name="profile_{{ field.ident }}" class="inputStyling" placeholder="{{ field.description }}"{% if profile.user[field.ident].value %} value="{{ profile.user[field.ident].value }}"{% endif %} />
                        </div>
                        {% if field.addit %}
                            {% for id,addit in field.addit %}
                                <div>
                                    <input type="{{ addit[0] }}" id="{{ id }}" name="profile_additional_{{ id }}" />
                                    <label for="{{ id }}" style="font-size: 10px;">{{ addit[1]|raw }}</label>
                                </div>
                            {% endfor %}
                        {% endif %}
                    </div>
                {% endfor %}
                <div class="profile-save">
                    <input type="submit" value="Save" name="submit" class="inputStyling" />
                    <input type="reset" value="Reset" name="reset" class="inputStyling" />
                </div>
            </form>
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
