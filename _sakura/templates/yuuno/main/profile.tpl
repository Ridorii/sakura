{% include 'global/header.tpl' %}
    {% if profile.notset or profile.user.id == 0 or profile.user.password_algo == 'nologin' %}
    <div class="content standalone" style="padding: 20px;">
        <h1>The requested user does not exist!</h1>
        There are a few possible reasons for this:
        <ul style="padding-left: 40px;">
            <li>They changed their username.</li>
            <li>They may have been <a href="/faq#abyss" class="default">abyss'd</a>.</li>
            <li>You made a typo.</li>
            <li>They never existed.</li>
        </ul>
    </div>
    {% else %}
    <div id="userBackground"></div>
	<div class="content profile">
        <div class="{% if profile.profpage|length > 1 %}content-right {% endif %}content-column">
            <div style="text-align: center;">
                <img src="/a/{{ profile.user.id }}" alt="{{ profile.user.username }}'s Avatar" class="default-avatar-setting" style="box-shadow: 0 3px 7px #{% if profile.online %}484{% else %}844{% endif %};" />
                <br /><span style="font-size: .8em;">{{ profile.ranktitle }}</span>
                <h1 style="color: {{ profile.colour }}; text-shadow: 0 0 7px #888; padding: 0 0 2px;">{{ profile.user.username }}</h1>
                {% if profile.user.rank_main > 1 %}
                {% if profile.istenshi %}<img src="//{{ sakura.urls.content }}/images/tenshi.png" alt="Tenshi" /> {% endif %}<img src="//{{ sakura.urls.content }}/images/flags/{% if profile.user.country|lower == 'eu' %}europeanunion{% else %}{{ profile.user.country|lower }}{% endif %}.png" alt="{{ profile.user.country }}" /> <span style="font-size: .9em; line-height: 11px;">{{ profile.country }}</span>
                {% if user.checklogin %}
                <div class="user-actions">
                {% if user.data.id == profile.user.id %}
                <a class="fa fa-pencil-square" title="Edit your profile" href="//{{ sakura.urls.main }}/settings/profile"></a>
                {% else %}
                <a class="fa fa-user-plus" title="Add {{ profile.user.username }} as a friend" href="//{{ sakura.urls.main }}/friends?add={{ profile.user.id }}&session={{ php.sessionid }}&time={{ php.time }}&return={{ sakura.currentpage }}"></a>
                <a class="fa fa-flag" title="Report {{ profile.user.username }}" href="//{{ sakura.urls.main }}/report/{{ profile.user.id }}"></a>
                {% endif %}
                </div>
                {% endif %}
                <hr class="default" />
                <b>Joined</b> {{ profile.user.regdate|date("l Y-m-d H:i T") }}<br />
                {% if profile.user.lastdate == 0 %}
                <b>User hasn't logged in yet.</b>
                {% else %}
                <b>Last Seen on</b> {{ profile.user.lastdate|date("l Y-m-d H:i T") }}
                {% endif %}<br />
                <b>User has {% if not profile.user.posts %}no{% else %}{{ profile.user.posts }}{% endif %} forum post{% if profile.user.posts != 1 %}s{% endif %}.</b>
                {% if profile.data is not null %}
                <hr class="default" />
                {% if user.checklogin %}
                <table style="width: 100%;">
                {% for name,field in profile.data %}
                <tr>
                    <td style="text-align: left; font-weight: bold;">
                        {{ field.name }}
                    </td>
                    <td style="text-align: right;">
                        {% if name == 'youtube' %}
                            <a href="https://youtube.com/{% if field.youtubetype == 1 %}channel{% else %}user{% endif %}/{{ field.value }}" class="default">{% if field.youtubetype == 1 %}{{ profile.user.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
                        {% else %}
                            {% if field.islink %}
                            <a href="{{ field.link }}" class="default">
                            {% endif %}
                            {{ field.value }}
                            {% if field.islink %}
                            </a>
                            {% endif %}
                        {% endif %}
                    </td>
                </tr>
                {% endfor %}
                </table>
                {% else %}
                <b>Log in to view the full profile!</b>
                {% endif %}
                {% endif %}
                {% endif %}
                <hr class="default" />
                <b>Account Standing</b>
                {% if profile.user.rank_main < 2 %}
                <h2 style="color: #888; text-shadow: 0 0 7px #888; margin-top: 0;">Deactivated</h2>
                {% else %}
                    {% if profile.warnings %}
                    <h2 style="color: red; text-shadow: 0 0 7px #888; margin-top: 0;">Bad</h2>
                    <span style="font-size: 10px; line-height: 10px;">This user has <b>{{ profile.warnings|length }} warning{% if profile.warnings|length != 1 %}s{% endif %}</b>.<br />After 5 to 10 warnings (depending on what they are for) this user may be permanently banned.</span>
                    {% else %}
                    <h2 style="color: green; text-shadow: 0 0 7px #888; margin-top: 0;">Good</h2>
                    {% endif %}
                {% endif %}
            </div>
        </div>
        <div class="content-left content-column markdown{% if profile.profpage|length < 1 %} hidden{% endif %}">
            {{ profile.profpage|raw }}
        </div>
        <div class="clear"></div>
    </div>
    {% if profile.user.background_url %}
    <script type="text/javascript">
        initialiseParallax('userBackground');
    </script>
    {% endif %}
    {% endif %}
{% include 'global/footer.tpl' %}
