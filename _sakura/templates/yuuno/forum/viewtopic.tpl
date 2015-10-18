{% extends 'global/master.tpl' %}

{% block title %}{{ topic.topic_title }}{% endblock %}

{% block content %}
    <div class="content homepage forum viewtopic">
        <div class="content-column">
            <div class="head">{{ forum.forum.forum_name }} / {{ topic.topic_title }}</div>
            {% include 'forum/forumBtns.tpl' %}
            <table class="posts">
                {% for post in posts %}
                    <tr class="post" id="p{{ post.post_id }}">
                        <td class="userpanel">
                            {% if not post.user.checkPermission('SITE', 'DEACTIVATED') or post.user.checkPermission('SITE', 'RESTRICTED') %}<a href="{{ urls.format('USER_PROFILE', [post.user.data.user_id]) }}" class="default username" style="color: {{ post.user.colour }}; text-shadow: 0 0 5px {% if post.user.colour != 'inherit' %}{{ post.user.colour }}{% else %}#222{% endif %};" title="Go to {{ post.user.data.username }}'s profile">{{ post.user.data.username }}</a>
                            <img src="{{ urls.format('IMAGE_AVATAR', [post.user.data.user_id]) }}" alt="{{ post.user.data.username }}" class="avatar" style="box-shadow: 0 3px 7px #{% if post.user.checkOnline %}484{% else %}844{% endif %};" />
                            {% else %}
                            <a class="username">[deleted user]</a>
                            {% endif %}
                            <div class="userdata">
                                <div class="usertitle">{% if not post.user.usertitle %}{{ post.rank.title }}{% else %}{{ post.user.user_title }}{% endif %}</div>
                                <img src="{{ sakura.contentPath }}/images/tenshi.png" alt="Tenshi"{% if not post.user.checkPremium[0] %} style="opacity: 0;"{% endif %} /> <img src="{{ sakura.contentPath }}/images/flags/{{ post.user.country.short|lower }}.png" alt="{{ post.user.country.long }}" />
                                {% if session.checkLogin %}
                                <div class="actions">
                                    {% if user.data.user_id == post.user.data.user_id %}
                                        <a class="fa fa-pencil-square-o" title="Edit this post" href="{{ urls.format('FORUM_EDIT_POST', [post.post_id]) }}"></a>
                                        <a class="fa fa-trash" title="Delete this post" href="{{ urls.format('FORUM_DELETE_POST', [post.post_id]) }}"></a>
                                    {% elseif not post.user.checkPermission('SITE', 'DEACTIVATED') or post.user.checkPermission('SITE', 'RESTRICTED') %}
                                    {% if post.user.checkFriends(user.data.user_id) != 0 %}
                                        <a class="fa fa-{% if post.user.checkFriends(user.data.user_id) == 2 %}heart{% else %}star{% endif %}" title="You are friends"></a>
                                    {% endif %}
                                    <a class="fa fa-user-{% if post.user.checkFriends(user.data.user_id) == 0 %}plus{% else %}times{% endif %} forum-friend-toggle" title="{% if post.user.checkFriends(user.data.user_id) == 0 %}Add {{ post.user.data.username }} as a friend{% else %}Remove friend{% endif %}" href="{% if post.user.checkFriends(user.data.user_id) == 0 %}{{ urls.format('FRIEND_ADD', [post.user.data.user_id, php.sessionid, php.time, sakura.currentPage]) }}{% else %}{{ urls.format('FRIEND_REMOVE', [post.user.data.user_id, php.sessionid, php.time, sakura.currentPage]) }}{% endif %}"></a>
                                    <a class="fa fa-flag" title="Report {{ post.user.data.username }}" href="{{ urls.format('USER_REPORT', [post.user.data.user_id]) }}"></a>
                                    {% endif %}
                                    <a class="fa fa-reply" title="Quote this post" href="{{ urls.format('FORUM_QUOTE_POST', [post.post_id]) }}"></a>
                                </div>
                                {% endif %}
                            </div>
                        </td>
                        <td class="post-content">
                            <div class="details">
                                <div class="subject">
                                    <a href="#p{{ post.post_id }}" class="clean">{{ post.post_subject }}</a>
                                </div>
                                <div class="date">
                                    <a href="{{ urls.format('FORUM_POST', [post.post_id]) }}#p{{ post.post_id }}" class="clean" title="{{ post.post_time|date(sakura.dateFormat) }}">{{ post.elapsed }}</a>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="post-text markdown">
                                {{ post.parsed_post|raw }}
                            </div>
                            {% if post.post_signature and post.signature %}
                            <div class="clear"></div>
                            <div class="signature">
                                {{ post.signature|raw }}
                            </div>
                            {% endif %}
                        </td>
                    </tr>
                {% endfor %}
            </table>
            {% include 'forum/forumBtns.tpl' %}
        </div>
    </div>
{% endblock %}
