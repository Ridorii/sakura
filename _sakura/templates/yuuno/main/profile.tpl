{% include 'global/header.tpl' %}
    {% if profile.notset or profile.user.id == 0 %}
    <div class="content standalone" style="padding: 20px;">
        <h1>The requested user does not exist!</h1>
        There are a few possible reasons for this:
        <ul style="padding-left: 40px;">
            <li>They changed their username.</li>
            <li>They may have been abyss'd.</li>
            <li>You made a typo.</li>
            <li>They never existed.</li>
        </ul>
    </div>
    {% else %}
	<div class="content profile">
        <div class="{% if profile.profpage|length > 1 %}content-right {% endif %}content-column">
            <div style="text-align: center;">
                <img src="/a/{{ profile.user.id }}" alt="{{ profile.user.username }}'s Avatar" class="default-avatar-setting" />
                <br /><span style="font-size: .8em;">{{ profile.user.usertitle }}</span>
                <h1 style="color: {{ profile.colour }}; text-shadow: 0 0 7px #888; padding: 0 0 10px;">{{ profile.user.username }}</h1>
                <hr class="default" />
                <b>Joined</b> {{ profile.user.regdate|date("l Y-m-d H:i") }}<br />
                <b>Last Seen on</b> {{ profile.user.lastdate|date("l Y-m-d H:i") }}
                <hr class="default" />
                <b>Account Standing</b>
                <h2 style="color: green; text-shadow: 0 0 7px #888; margin-top: 0;">Good</h2>
            </div>
        </div>
        <div class="content-left content-column markdown{% if profile.profpage|length < 1 %} hidden{% endif %}">
            {{ profile.profpage|raw }}
        </div>
        <div class="clear"></div>
    </div>
    {% endif %}
{% include 'global/footer.tpl' %}
