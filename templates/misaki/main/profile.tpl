{% extends 'global/master.tpl' %}

{% set profileHidden = profile.permission(constant('Sakura\\Perms\\Site::DEACTIVATED')) or (profile.permission(constant('Sakura\\Perms\\Site::RESTRICTED')) and (user.id != profile.id and not user.permission(constant('Sakura\\Perms\\Manage::USE_MANAGE'), constant('Sakura\\Perms::MANAGE')))) %}

{% set noUserpage = profile.userPage|length < 1 %}

{% set profileView = profileHidden ? 'hidden' : (noUserpage and profileView == 'index' ? 'comments' : profileView) %}

{% block title %}{% if profileHidden %}User not found!{% else %}Profile of {{ profile.username }}{% endif %}{% endblock %}

{% block css %}
    <style type="text/css">
        #profileHeader {
            background-image: linear-gradient(0deg, transparent 0%, transparent 12%, rgba(0, 0, 0, .7) 30%,
                transparent 76%, transparent 100%), url('{{ urls.format('IMAGE_HEADER', [profile.id]) }}');
        }

        #profileHeader.floating {
            background-image: linear-gradient(90deg, transparent 0%, transparent 40%, #3A2E44 45%), url('{{ urls.format('IMAGE_HEADER', [profile.id]) }}');
            background-size: auto 130px;
            background-repeat: no-repeat;
            background-position: left top;
        }
    </style>
{% endblock %}

{% block js %}
    <script type="text/javascript">
        // Header
        window.addEventListener("scroll", function(e) {
            if(window.scrollY > 170) {
                var profileHeader = document.getElementById('profileHeader');
                var profileContent = document.getElementById('profileContent');
                profileHeader.className = 'profileHeaderContent floating';
                profileContent.className = 'profileContainer headerFloating';
            } else {
                var profileHeader = document.getElementById('profileHeader');
                var profileContent = document.getElementById('profileContent');
                profileHeader.className = 'profileHeaderContent';
                profileContent.className = 'profileContainer';
            }
        });
    </script>
{% endblock %}

{% block content %}
    <div class="profile" id="u{{ profile.id }}">
        <div class="profileHeaderContent" id="profileHeader">
            <div id="userAvatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [profile.id]) }}');">{{ profile.username }}'s Avatar</div>
            <div class="userData">
                <div class="headerLeft">
                    <div class="profileUsername" style="color: {{ profile.colour }};"{% if profile.getUsernameHistory %} title="Known as {{ profile.getUsernameHistory[0]['username_old'] }} before {{ profile.getUsernameHistory[0]['change_time']|date(sakura.dateFormat) }}."{% endif %}>
                        {% if profileHidden %}Unknown user{% else %}{{ profile.username }}{% endif %}
                    </div>
                    <div class="profileUserTitle">
                        {% if profileHidden %}The requested user does not exist!{% else %}{{ profile.userTitle }}{% endif %}
                    </div>
                </div>
                <div class="headerRight">
                {% if not profileHidden %}
                    <div>Joined <span title="{{ profile.dates.joined|date(sakura.dateFormat) }}">{{ profile.elapsed.joined }}</span></div>
                    <div>{% if profile.dates.lastOnline < 1 %}User hasn't logged in yet.{% else %}Last Active <span title="{{ profile.dates.lastOnline|date(sakura.dateFormat) }}">{{ profile.elapsed.lastOnline }}</span>{% endif %}</div>
                {% endif %}
                </div>
            </div>
        </div>
        <div class="profileContainer" id="profileContent">
            <div class="userDataBar">
            {% if not profileHidden %}
                {% if (profile.checkPremium[0] or profile.permission(constant('Sakura\\Perms\\Manage::USE_MANAGE'), constant('Sakura\\Perms::MANAGE'))) %}
                    <div class="profilePlatform hierarchyContainer">
                        <div class="inner">
                            <ul class="hierarchies">
                                {% if profile.checkPremium[0] %}
                                    <li class="tenshi">Tenshi</li>
                                {% endif %}
                                {% if profile.permission(constant('Sakura\\Perms\\Manage::USE_MANAGE'), constant('Sakura\\Perms::MANAGE')) %}
                                    <li class="staff">Staff</li>
                                {% endif %}
                                {% if false %}
                                    <li class="developer">Developer</li>
                                {% endif %}
                                {% if false %}
                                    <li class="alumnii">Alumnii</li>
                                {% endif %}
                            </ul>
                        </div>
                    </div>
                    {% endif %}
                    {% if session.checkLogin %}
                        <div class="profilePlatform userActions">
                            <div class="inner">
                                <ul class="actions">
                                    {% if user.id == profile.id %}
                                        <li class="edit"><a title="Edit your profile" href="{{ urls.format('SETTING_MODE', ['general', 'profile']) }}">Edit</a></li>
                                        <li class="settings"><a title="Change your settings" href="{{ urls.format('SETTINGS_INDEX') }}">Settings</a></li>
                                    {% else %}
                                        <li class="{% if user.checkFriends(profile.id) == 2 %}mutualFriend{% elseif user.checkFriends(profile.id) == 1 %}pendingFriend{% else %}addFriend{% endif %}"><a href="{% if user.checkFriends(profile.id) == 0 %}{{ urls.format('FRIEND_ADD', [profile.id, php.sessionid, php.time, sakura.currentPage]) }}{% else %}{{ urls.format('FRIEND_REMOVE', [profile.id, php.sessionid, php.time, sakura.currentPage]) }}{% endif %}">{% if user.checkFriends(profile.id) == 0 %}Add friend{% else %}Friends{% endif %}</a></li>
                                        <li class="report"><a href="{{ urls.format('USER_REPORT', [profile.id]) }}">Report</a></li>
                                    {% endif %}
                                </ul>
                            </div>
                        </div>
                    {% endif %}
                    <div class="profilePlatform userAccounts">
                        <div class="inner">
                            {% if session.checkLogin %}
                                {% if profile.profileFields %}
                                    {% for name,field in profile.profileFields %}
                                    <div class="field">
                                        <div>{{ field.name }}</div>
                                        <div>
                                        {% if name == 'youtube' %}
                                            <a href="https://youtube.com/{% if field.youtubetype == 'true' %}channel{% else %}user{% endif %}/{{ field.value }}" class="default">{% if field.youtubetype == 'true' %}{{ profile.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
                                        {% else %}
                                            {% if field.islink %}
                                                <a href="{{ field.link }}">
                                            {% endif %}
                                            {{ field.value }}
                                            {% if field.islink %}
                                                </a>
                                            {% endif %}
                                        {% endif %}
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    {% endfor %}
                                {% else %}
                                    <div class="noAccounts">
                                        <div class="fa fa-question"></div>
                                        <div class="notif">This user has not set any links yet.</div>
                                    </div>
                                {% endif %}
                            {% else %}
                                <div class="noAccounts">
                                    <div class="fa fa-exclamation-circle"></div>
                                    <div class="notif">Log in to view the full profile.</div>
                                </div>
                            {% endif %}
                        </div>
                    </div>
                {% endif %}
                <div class="profilePlatform accountStanding">
                    <div class="inner">
                        <div class="title">Account Standing</div>
                        {% if profileHidden %}
                            <div class="standing" style="color: #444;">Unknown</div>
                        {% elseif profile.checkBan %}
                            <h2 class="standing" style="color: #811;">Banned</h2>
                        {% elseif profile.permission(constant('Sakura\\Perms\\Site::RESTRICTED')) %}
                            <h2 class="standing" style="color: #811;">Restricted</h2>
                        {% else %}
                            {% if profile.getWarnings %}
                                <div class="standing" style="color: #A22;">Bad</div>
                            {% else %}
                                <div class="standing" style="color: #2A2;">Good</div>
                            {% endif %}
                        {% endif %}
                        {% if profile.getWarnings %}
                            <div>This user has <b>{{ profile.getWarnings|length }}</b> warning{% if profile.getWarnings|length != 1 %}s{% endif %}!</div>
                            <div>After <b>10 warnings</b> a user may be permanently banned.</div>
                        {% endif %}
                    </div>
                </div>
            </div>
            <div class="profileMain">
                {% if not profileHidden %}
                <div class="statsRow">
                    {% if profileView != (noUserpage ? 'comments' : 'index') %}
                        <div class="profilePlatform">
                            <a class="inner" title="Userpage" href="{{ urls.format('USER_PROFILE', [profile.id]) }}">
                                <div class="fa fa-user"></div>
                            </a>
                        </div>
                    {% endif %}
                    {% if profileView != 'friends' %}
                    <div class="profilePlatform">
                        <a class="inner" title="Friends" href="{{ urls.format('USER_FRIENDS', [profile.id]) }}">
                            <div class="fa fa-user-plus"></div>
                            <div class="count">{{ profile.getFriends|length }}</div>
                        </a>
                    </div>
                    {% endif %}
                    {% if profileView != 'groups' %}
                    <div class="profilePlatform">
                        <a class="inner" title="Groups" href="{{ urls.format('USER_GROUPS', [profile.id]) }}">
                            <div class="fa fa-users"></div>
                            <div class="count">0</div>
                        </a>
                    </div>
                    {% endif %}
                    {% if profileView != 'comments' %}
                    <div class="profilePlatform">
                        <a class="inner" title="Comments" href="{{ urls.format('USER_COMMENTS', [profile.id]) }}">
                            <div class="fa fa-comments"></div>
                            <div class="count">{{ profile.profileComments.count }}</div>
                        </a>
                    </div>
                    {% endif %}
                    {% if profileView != 'threads' %}
                    <div class="profilePlatform">
                        <a class="inner" title="Threads" href="{{ urls.format('USER_THREADS', [profile.id]) }}">
                            <div class="fa fa-list"></div>
                            <div class="count">{{ profile.forumStats.topics }}</div>
                        </a>
                    </div>
                    {% endif %}
                    {% if profileView != 'posts' %}
                    <div class="profilePlatform">
                        <a class="inner" title="Posts" href="{{ urls.format('USER_POSTS', [profile.id]) }}">
                            <div class="fa fa-reply"></div>
                            <div class="count">{{ profile.forumStats.posts }}</div>
                        </a>
                    </div>
                    {% endif %}
                </div>
                {% endif %}
                <div class="userPage profilePlatform">
                    <div class="inner">
                        {% include 'profile/' ~ profileView ~ '.tpl' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
