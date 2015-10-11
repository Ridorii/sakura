{% include 'global/header.tpl' %}
    {% if profile.checkPermission('SITE', 'DEACTIVATED') or profile.data.password_algo == 'nologin' or (profile.checkPermission('SITE', 'RESTRICTED') and (user.data.id != profile.data.id and not user.checkPermission('MANAGE', 'USE_MANAGE'))) %}
    <div class="userNotFound">
        <h1 class="sectionHead">user not found!</h1>
        There are a few possible reasons for this:
        <ul>
            <li>They changed their username.</li>
            <li>They may have been <a href="/faq#abyss" class="default">abyss'd</a>.</li>
            <li>You made a typo.</li>
            <li>They never existed.</li>
        </ul>
    </div>
    {% else %}
        <div class="profile" id="u{{ profile.data.user_id }}">
            <div class="profileHeader" style="background-image: url('/u/{{ profile.data.user_id }}/header');">
                <div class="profileFade"></div>
                <div class="headerLeft">
                    <img class="userAvatar" src="/a/{{ profile.data.user_id }}" alt="{{ profile.data.username }}'s Avatar" />
                    <div class="userData">
                        <div class="profileUsername" style="color: {{ profile.colour }};">
                            {{ profile.data.username }}
                        </div>
                        <div class="profileUsertitle">
                            {{ profile.userTitle }}
                        </div>
                    </div>
                </div>
                <div class="joinedLast">
                    <div>Joined {{ profile.data.regdate|date(sakura.dateFormat) }}</div>
                    <div>{% if profile.data.lastdate == 0 %}User hasn't logged in yet.{% else %}Last Active {{ profile.data.lastdate|date(sakura.dateFormat) }}{% endif %}</div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="profileContent">
                <div class="userDataBar">
                {% if not profile.checkPermission('SITE', 'DEACTIVATED') and (profile.checkPremium[0] or profile.checkPermission('MANAGE', 'USE_MANAGE')) %}
                    <div class="profilePlatform hierarchyContainer">
                        <div class="inner">
                            <ul class="hierarchies">
                                {% if profile.checkPremium[0] %}
                                    <li class="tenshi">Tenshi</li>
                                {% endif %}
                                {% if profile.checkPermission('MANAGE', 'USE_MANAGE') %}
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
                    <div class="profilePlatform userAccounts">
                        <div class="inner">
                            {% if session.checkLogin %}
                                {% if profile.profileFields %}
                                    {% for name,field in profile.profileFields %}
                                    <div class="field">
                                        <div>{{ field.name }}</div>
                                        <div>
                                        {% if name == 'youtube' %}
                                            <a href="https://youtube.com/{% if field.youtubetype == 'true' %}channel{% else %}user{% endif %}/{{ field.value }}" class="default">{% if field.youtubetype == 'true' %}{{ profile.data.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
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
                            {% if profile.checkPermission('SITE', 'DEACTIVATED') %}
                                <div class="standing" style="color: #800;">Deactivated</div>
                            {% elseif profile.checkBan %}
                                <h2 class="standing" style="color: #222;">Banned</h2>
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
                <div class="userPage">
                {% if not profile.checkPermission('SITE', 'DEACTIVATED') %}
                    <div class="statsRow">
                        <div class="profilePlatform">
                            <a class="inner" href="/u/{{ profile.data.user_id }}/friends">
                                <div>Friends</div>
                                <div class="count">{{ profile.getFriends|length }}</div>
                            </a>
                        </div>
                        <div class="profilePlatform">
                            <a class="inner" href="/u/{{ profile.data.user_id }}/groups">
                                <div>Groups</div>
                                <div class="count">n/a</div>
                            </a>
                        </div>
                        <div class="profilePlatform forumStats">
                            <div class="inner">
                                <div class="forumStatTitle">Forum stats</div>
                                <div class="forumStatCount">
                                    <a class="posts" href="/u/{{ profile.data.user_id }}/posts">{{ profile.forumStats.posts }} post{% if profile.forumStats.posts != 1 %}s{% endif %}</a>
                                    <a class="threads" href="/u/{{ profile.data.user_id }}/threads">{% if profile.forumStats.topics %}{{ profile.forumStats.topics }}{% else %}0{% endif %} thread{% if profile.forumStats.topics != 1 %}s{% endif %}</a>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="userPage profilePlatform markdown{% if profile.userPage|length < 1 %} hidden{% endif %}">
                        <div class="inner">{{ profile.userPage|raw }}</div>
                    </div>
                {% endif %}
                </div>
                <div class="clear"></div>
            </div>
        </div>
    {% endif %}
{% include 'global/footer.tpl' %}
