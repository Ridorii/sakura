{% include 'global/header.tpl' %}
{% if profile.notset or profile.user.id == 0 or profile.user.password_algo == 'nologin' %}
user not found, don't forget to make this sexy
{% else %}
<div class="profile" id="u{{ profile.user.id }}">
    <div class="profileHeader" style="background-image: url('/u/{{ profile.user.id }}/header');">
        <div class="profileFade"></div>
        <div class="headerLeft">
            <img class="userAvatar" src="/a/{{ profile.user.id }}" alt="{{ profile.user.username }}'s Avatar" />
            <div class="userData">
                <div class="profileUsername" style="color: {{ profile.colour }};">
                    {{ profile.user.username }}
                </div>
                <div class="profileUsertitle">
                    {{ profile.ranktitle }}
                </div>
            </div>
        </div>
        <div class="joinedLast">
            <div>Joined {{ profile.user.regdate|date("l Y-m-d H:i T") }}</div>
            <div>{% if profile.user.lastdate == 0 %}User hasn't logged in yet.{% else %}Last Active {{ profile.user.lastdate|date("l Y-m-d H:i T") }}{% endif %}</div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="profileContent">
        <div class="userDataBar">   
        {% if profile.user.rank_main > 1 %}
            <div class="profilePlatform hierarchyContainer">
                <div class="inner">
                    <ul class="hierarchies">
                        <li class="tenshi">Tenshi</li>
                        <li class="staff">Staff</li>
                        <li class="developer">Developer</li>
                        <li class="alumnii">Alumnii</li>
                    </ul>
                </div>
            </div>
            <div class="profilePlatform userAccounts">
                <div class="inner">
                    {% if user.checklogin %}
                    {% if profile.fields %}
                        {% for name,field in profile.fields %}
                        <div class="field">
                            <div>{{ field.name }}</div>
                            <div>
                            {% if name == 'youtube' %}
                                <a href="https://youtube.com/{% if field.youtubetype == 1 %}channel{% else %}user{% endif %}/{{ field.value }}">{% if field.youtubetype == 1 %}{{ profile.user.username }}'s Channel{% else %}{{ field.value }}{% endif %}</a>
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
                            <div class="notif">This user has not set any accounts yet.</div>
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
                    <div class="standing" style="color:
                    {% if profile.user.rank_main < 2 %}
                        #888;">Deactivated
                    {% else %}
                        {% if profile.warnings %}
                            #F22;">Bad
                        {% else %}
                            #2F2;">Good
                        {% endif %}
                    {% endif %}
                    </div>
                    {% if profile.warnings %}
                        <div>This user has <b>{{ profile.warnings|length }}</b> warning{% if profile.warnings|length != 1 %}s{% endif %}!</div>
                        <div>After <b>10 warnings</b> a user may be permanently banned.</div>
                    {% endif %}
                </div>
            </div>
        </div>
        <div class="userPage">
        {% if profile.user.rank_main > 1 %}
            <div class="statsRow">
                <div class="profilePlatform">
                    <a class="inner" href="/u/{{ profile.user.id }}/friends">
                        <div>Friends</div>
                        <div class="count">n/a</div>
                    </a>
                </div>
                <div class="profilePlatform">
                    <a class="inner" href="/u/{{ profile.user.id }}/groups">
                        <div>Groups</div>
                        <div class="count">n/a</div>
                    </a>
                </div>
                <div class="profilePlatform forumStats">
                    <div class="inner">
                        <div class="forumStatTitle">Forum stats</div>
                        <div class="forumStatCount">
                            <a class="posts" href="/u/{{ profile.user.id }}/posts">{% if profile.data.forum.posts %}{{ profile.data.forum.posts }}{% else %}0{% endif %} post{% if profile.data.forum.posts != 1 %}s{% endif %}</a>
                            <a class="threads" href="/u/{{ profile.user.id }}/threads">{% if profile.data.forum.threads %}{{ profile.data.forum.threads }}{% else %}0{% endif %} thread{% if profile.data.forum.threads != 1 %}s{% endif %}</a>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="profilePage profilePlatform markdown{% if profile.profilePage|length < 1 %} hidden{% endif %}">
                <div class="inner">{{ profile.profilePage|raw }}</div>
            </div>
        {% endif %}
        </div>
        <div class="clear"></div>
    </div>
</div>
{% if profile.user.userData.profileBackground %}
<script type="text/javascript">
    initialiseParallax('userBackground');
</script>
{% endif %}
{% endif %}
{% include 'global/footer.tpl' %}
