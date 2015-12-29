{% extends 'global/master.tpl' %}

{% set profileHidden = profile.permission(constant('Sakura\\Perms\\Site::DEACTIVATED')) or profile.password.password_algo == 'nologin' or (profile.permission(constant('Sakura\\Perms\\Site::RESTRICTED')) and (user.id != profile.id and not user.permission(constant('Sakura\\Perms\\Manage::USE_MANAGE'), constant('Sakura\\Perms::MANAGE')))) %}

{% set noUserpage = profile.userPage|length < 1 %}

{% set profileView = noUserpage and profileView == 'index' ? 'comments' : profileView %}

{% block title %}{% if profileHidden %}User not found!{% else %}Profile of {{ profile.username }}{% endif %}{% endblock %}

{% block content %}
    {% if profileHidden %}
        <div class="content standalone" style="padding: 20px;">
            <h1>The requested user does not exist!</h1>
            There are a few possible reasons for this:
            <ul style="padding-left: 40px;">
                <li>They changed their username.</li>
                <li>They may have been <a href="{{ urls.format('SITE_FAQ') }}#abyss" class="default">abyss'd</a>.</li>
                <li>You made a typo.</li>
                <li>They never existed.</li>
            </ul>
        </div>
    {% else %}
        <div class="content profile">
            <div class="content-right content-column">
                <div style="text-align: center;">
                    <img src="{{ urls.format('IMAGE_AVATAR', [profile.id]) }}" alt="{{ profile.username }}'s Avatar" class="default-avatar-setting" style="box-shadow: 0 3px 7px #{% if profile.checkOnline %}484{% else %}844{% endif %};" /><br />
                    {% if profile.mainRank > 1 and profile.checkBan|length < 1 %}
                        <span style="font-size: .8em;">{{ profile.userTitle }}</span>
                        <h1 style="color: {{ profile.colour }}; text-shadow: 0 0 7px {% if profile.colour != 'inherit' %}{{ profile.colour }}{% else %}#222{% endif %}; padding: 0 0 2px;"{% if profile.getUsernameHistory %} title="Known as {{ profile.getUsernameHistory[0]['username_old'] }} before {{ profile.getUsernameHistory[0]['change_time']|date(sakura.dateFormat) }}."{% endif %}>{{ profile.username }}</h1>
                            {% if profile.isPremium[0] %}<img src="{{ sakura.contentPath }}/images/tenshi.png" alt="Tenshi" style="vertical-align: middle;" /> {% endif %}<img src="{{ sakura.contentPath }}/images/flags/{{ profile.country.short|lower }}.png" alt="{{ profile.country.short }}" style="vertical-align: middle;" /> <span style="font-size: .8em; line-height: 11px;">{{ profile.country.long }}</span>
                        {% if session.checkLogin %}
                        <div class="user-actions">
                            {% if user.id == profile.id %}
                                <a class="fa fa-pencil-square-o" title="Edit your profile" href="{{ urls.format('SETTING_MODE', ['general', 'profile']) }}"></a>
                            {% else %}
                                {% if user.isFriends(profile.id) != 0 %}<a class="fa fa-{% if user.isFriends(profile.id) == 2 %}heart{% else %}star{% endif %}" title="You are friends"></a>{% endif %}
                                <a class="fa fa-user-{% if user.isFriends(profile.id) == 0 %}plus{% else %}times{% endif %}" title="{% if user.isFriends(profile.id) == 0 %}Add {{ profile.username }} as a friend{% else %}Remove friend{% endif %}" href="{% if user.isFriends(profile.id) == 0 %}{{ urls.format('FRIEND_ADD', [profile.id, php.sessionid, php.time, sakura.currentPage]) }}{% else %}{{ urls.format('FRIEND_REMOVE', [profile.id, php.sessionid, php.time, sakura.currentPage]) }}{% endif %}" id="profileFriendToggle"></a>
                                <a class="fa fa-exclamation-circle" title="Report {{ profile.username }}" href="{{ urls.format('USER_REPORT', [profile.id]) }}"></a>
                            {% endif %}
                            <hr class="default" />
                            <a class="fa fa-file-text-o" title="View {{ profile.username }}'s user page" href="{{ urls.format('USER_PROFILE', [profile.id]) }}"></a>
                            <a class="fa fa-list" title="View {{ profile.username }}'s threads" href="{{ urls.format('USER_THREADS', [profile.id]) }}"></a>
                            <a class="fa fa-reply" title="View {{ profile.username }}'s posts" href="{{ urls.format('USER_POSTS', [profile.id]) }}"></a>
                            <a class="fa fa-star" title="View {{ profile.username }}'s friends" href="{{ urls.format('USER_FRIENDS', [profile.id]) }}"></a>
                            {#<a class="fa fa-users" title="View {{ profile.username }}'s groups" href="{{ urls.format('USER_GROUPS', [profile.id]) }}"></a>#}
                            {% if not noUserpage %}
                                <a class="fa fa-comments-o" title="View {{ profile.username }}'s profile comments" href="{{ urls.format('USER_COMMENTS', [profile.id]) }}"></a>
                            {% endif %}
                        </div>
                        {% endif %}
                        <hr class="default" />
                        <b>Joined</b> <span title="{{ profile.dates.joined|date(sakura.dateFormat) }}">{{ profile.elapsed.joined }}</span>
                        <br />
                        {% if profile.dates.lastOnline < 1 %}
                            <b>{{ profile.username }} hasn't logged in yet.</b>
                        {% else %}
                            <b>Last online</b> <span title="{{ profile.dates.lastOnline|date(sakura.dateFormat) }}">{{ profile.elapsed.lastOnline }}</span>
                        {% endif %}
                        <br />
                        <b>{{ profile.username }} has {% if not profile.forumStats.posts %}no{% else %}{{ profile.forumStats.posts }}{% endif %} forum post{% if profile.forumStats.posts != 1 %}s{% endif %}.</b>
                        {% if profile.dates.birth != '0000-00-00' and profile.dates.birth|split('-')[0] > 0 %}
                            <br /><b>Age</b> <span title="{{ profile.dates.birth }}">{{ profile.elapsed(' old').birth }}</span>
                        {% endif %}
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
                                            <a href="https://youtube.com/{% if field.youtubetype == true %}channel{% else %}user{% endif %}/{{ field.value }}" class="default">{% if field.youtubetype == true %}{{ profile.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
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
                        <h1 style="color: #222; text-shadow: 0 0 7px #888; padding: 0 0 2px;">{{ profile.username }}</h1>
                    {% endif %}
                    <hr class="default" />
                    <b>Account Standing</b>
                    {% if profile.permission(constant('Sakura\\Perms\\Site::DEACTIVATED')) %}
                        <h2 style="color: #888; text-shadow: 0 0 7px #888; margin-top: 0;">Deactivated</h2>
                    {% elseif profile.checkBan %}
                        <h2 style="color: #222; text-shadow: 0 0 7px #222; margin-top: 0;">Banned</h2>
                    {% elseif profile.permission(constant('Sakura\\Perms\\Site::RESTRICTED')) %}
                        <h2 style="color: #800; text-shadow: 0 0 7px #800; margin-top: 0;">Restricted</h2>
                    {% elseif profile.getWarnings %}
                        <h2 style="color: #A00; text-shadow: 0 0 7px #A00; margin-top: 0;">Bad</h2>
                    {% else %}
                        <h2 style="color: #080; text-shadow: 0 0 7px #080; margin-top: 0;">Good</h2>
                    {% endif %}
                    {% if profile.getWarnings %}
                        <table class="panelTable">
                            <tr>
                                <th>Action</th>
                                <th>Duration</th>
                                <th>Reason</th>
                            </tr>
                            {% for warning in profile.getWarnings %}
                            <tr class="{{ warning.warning_action_text|lower }}">
                                <td>{{ warning.warning_action_text }}</td>
                                <td>{{ warning.warning_length }} minute{% if warning.warning_length != 1 %}s{% endif %}</td>
                                <td>{{ warning.warning_reason }}</td>
                            </tr>
                            {% endfor %}
                        </table>
                    {% endif %}
                </div>
            </div>
            <div class="content-left content-column">
                {% include 'profile/' ~ profileView ~ '.tpl' %}
            </div>
            <div class="clear"></div>
        </div>
    {% endif %}
{% endblock %}
