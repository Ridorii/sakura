{% include 'global/header.tpl' %}
    {% if profile.data.id < 1 or profile.data.password_algo == 'nologin' %}
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
    	<div class="content profile">
            <div class="{% if profile.userPage|length > 1 %}content-right {% endif %}content-column">
                <div style="text-align: center;">
                    <img src="/a/{{ profile.data.id }}" alt="{{ profile.data.username }}'s Avatar" class="default-avatar-setting" style="box-shadow: 0 3px 7px #{% if profile.checkOnline %}484{% else %}844{% endif %};" /><br />
                    {% if profile.data.rank_main > 1 and profile.checkBan|length < 1 %}
                        <span style="font-size: .8em;">{{ profile.userTitle }}</span>
                        <h1 style="color: {{ profile.colour }}; text-shadow: 0 0 7px {% if profile.colour != 'inherit' %}{{ profile.colour }}{% else %}#222{% endif %}; padding: 0 0 2px;">{{ profile.data.username }}</h1>
                            {% if profile.checkPremium[0] %}<img src="{{ sakura.content_path }}/images/tenshi.png" alt="Tenshi" /> {% endif %}<img src="{{ sakura.content_path }}/images/flags/{% if profile.country.short|lower == 'eu' %}europeanunion{% else %}{{ profile.country.short|lower }}{% endif %}.png" alt="{{ profile.country.short }}" /> <span style="font-size: .9em; line-height: 11px;">{{ profile.country.long }}</span>
                        {% if session.checkLogin %}
                        <div class="user-actions">
                            {% if user.data.id == profile.data.id %}
                                <a class="fa fa-pencil-square-o" title="Edit your profile" href="/settings/profile"></a>
                            {% else %}
                                {% if profile.checkFriends(user.data.id) != 0 %}<a class="fa fa-{% if profile.checkFriends(user.data.id) == 2 %}heart{% else %}star{% endif %}" title="You are friends"></a>{% endif %}
                                <a class="fa fa-user-{% if profile.checkFriends(user.data.id) == 0 %}plus{% else %}times{% endif %}" title="{% if profile.checkFriends(user.data.id) == 0 %}Add {{ legacyprofile.data.username }} as a friend{% else %}Remove friend{% endif %}" href="/friends?{% if profile.checkFriends(user.data.id) == 0 %}add{% else %}remove{% endif %}={{ profile.data.id }}&amp;session={{ php.sessionid }}&amp;time={{ php.time }}&amp;redirect={{ sakura.currentpage }}" id="profileFriendToggle"></a>
                                <a class="fa fa-flag" title="Report {{ profile.data.username }}" href="/u/{{ profile.data.id }}/report"></a>
                            {% endif %}
                        </div>
                        {% endif %}
                        <hr class="default" />
                        <b>Joined</b> {{ profile.data.regdate|date(sakura.date_format) }}
                        <br />
                        {% if profile.data.lastdate < 1 %}
                            <b>{{ profile.data.username }} hasn't logged in yet.</b>
                        {% else %}
                            <b>Last Seen on</b> {{ profile.data.lastdate|date(sakura.date_format) }}
                        {% endif %}
                        <br />
                        <b>{{ profile.data.username }} has {% if not profile.forumStats.posts %}no{% else %}{{ profile.forumStats.posts }}{% endif %} forum post{% if profile.forumStats.posts != 1 %}s{% endif %}.</b>
                        {% if profile.profileFields %}
                            <hr class="default" />
                            {% if session.checkLogin %}
                                <table style="width: 100%;">
                                {% for name,field in profile.profileFields %}
                                <tr>
                                    <td style="text-align: left; font-weight: bold;">
                                        {{ field.name }}
                                    </td>
                                    <td style="text-align: right;">
                                        {% if name == 'youtube' %}
                                            <a href="https://youtube.com/{% if field.youtubetype == 1 %}channel{% else %}user{% endif %}/{{ field.value }}" class="default">{% if field.youtubetype == 1 %}{{ profile.data.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
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
                    {% else %}
                        <h1 style="color: #222; text-shadow: 0 0 7px #888; padding: 0 0 2px;">{{ profile.data.username }}</h1>
                    {% endif %}
                    <hr class="default" />
                    <b>Account Standing</b>
                    {% if profile.data.rank_main < 2 %}
                    <h2 style="color: #888; text-shadow: 0 0 7px #888; margin-top: 0;">Deactivated</h2>
                    {% elseif profile.checkBan %}
                    <h2 style="color: #222; text-shadow: 0 0 7px #222; margin-top: 0;">Banned</h2>
                    {% else %}
                        {% if profile.getWarnings %}
                            <h2 style="color: red; text-shadow: 0 0 7px #888; margin-top: 0;">Bad</h2>
                            <span style="font-size: 10px; line-height: 10px;">This user has <b>{{ profile.getWarnings|length }} warning{% if profile.getWarnings|length != 1 %}s{% endif %}</b>.<br />After 5 to 10 warnings (depending on what they are for) this user may be permanently banned.</span>
                        {% else %}
                            <h2 style="color: green; text-shadow: 0 0 7px #888; margin-top: 0;">Good</h2>
                        {% endif %}
                    {% endif %}
                </div>
            </div>
            <div class="content-left content-column markdown{% if profile.userPage|length < 1 %} hidden{% endif %}">
                {{ profile.userPage|raw }}
            </div>
            <div class="clear"></div>
        </div>
    {% endif %}
{% include 'global/footer.tpl' %}
