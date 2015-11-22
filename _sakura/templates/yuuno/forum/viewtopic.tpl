{% extends 'global/master.tpl' %}

{% set forumBackLink %}{{ urls.format('FORUM_SUB', [forum.id]) }}{% endset %}
{% set forumReplyLink %}{{ urls.format('FORUM_REPLY', [thread.id]) }}{% endset %}

{% set posts = thread.posts|batch(10) %}

{% set paginationPages = posts %}
{% set paginationUrl %}{{ urls.format('FORUM_THREAD', [thread.id]) }}{% endset %}

{% block title %}{{ thread.title }}{% endblock %}

{% block content %}
    <div class="content homepage forum viewtopic">
        <div class="content-column">
            <div class="head">{{ forum.name }} / {{ thread.title }}</div>
            {% include 'forum/forumBtns.tpl' %}
            <table class="posts">
                {% for post in posts[get.page|default(1) - 1] %}
                    <tr class="post" id="p{{ post.id }}">
                        <td class="userpanel">
                            {% if not post.poster.checkPermission('SITE', 'DEACTIVATED') or post.poster.checkPermission('SITE', 'RESTRICTED') %}<a href="{{ urls.format('USER_PROFILE', [post.poster.id]) }}" class="default username" style="color: {{ post.poster.colour }}; text-shadow: 0 0 5px {% if post.poster.colour != 'inherit' %}{{ post.poster.colour }}{% else %}#222{% endif %};" title="Go to {{ post.poster.username }}'s profile">{{ post.poster.username }}</a>
                            <img src="{{ urls.format('IMAGE_AVATAR', [post.poster.id]) }}" alt="{{ post.poster.username }}" class="avatar" style="box-shadow: 0 3px 7px #{% if post.poster.isOnline %}484{% else %}844{% endif %};" />
                            {% else %}
                            <a class="username">[deleted user]</a>
                            {% endif %}
                            <div class="userdata">
                                <div class="usertitle">{{ post.poster.userTitle }}</div>
                                <img src="{{ sakura.contentPath }}/images/tenshi.png" alt="Tenshi"{% if not post.poster.isPremium[0] %} style="opacity: 0;"{% endif %} /> <img src="{{ sakura.contentPath }}/images/flags/{{ post.poster.country.short|lower }}.png" alt="{{ post.poster.country.long }}" />{% if post.poster.id == (thread.posts|first).poster.id %} <img src="{{ sakura.contentPath }}/images/op.png" alt="OP" title="Original Poster" />{% endif %}
                                {% if session.checkLogin %}
                                <div class="actions">
                                    {% if user.id == post.poster.id %}
                                        <a class="fa fa-pencil-square-o" title="Edit this post" href="{{ urls.format('FORUM_EDIT_POST', [post.id]) }}"></a>
                                        <a class="fa fa-trash" title="Delete this post" href="{{ urls.format('FORUM_DELETE_POST', [post.id]) }}"></a>
                                    {% elseif not post.poster.checkPermission('SITE', 'DEACTIVATED') or post.poster.checkPermission('SITE', 'RESTRICTED') %}
                                    {% if user.isFriends(post.poster.id) != 0 %}
                                        <a class="fa fa-{% if user.isFriends(post.poster.id) == 2 %}heart{% else %}star{% endif %}" title="You are friends"></a>
                                    {% endif %}
                                    <a class="fa fa-user-{% if user.isFriends(post.poster.id) == 0 %}plus{% else %}times{% endif %} forum-friend-toggle" title="{% if user.isFriends(post.poster.id) == 0 %}Add {{ post.poster.username }} as a friend{% else %}Remove friend{% endif %}" href="{% if user.isFriends(post.poster.id) == 0 %}{{ urls.format('FRIEND_ADD', [post.poster.id, php.sessionid, php.time, sakura.currentPage]) }}{% else %}{{ urls.format('FRIEND_REMOVE', [post.poster.id, php.sessionid, php.time, sakura.currentPage]) }}{% endif %}"></a>
                                    <a class="fa fa-flag" title="Report {{ post.poster.username }}" href="{{ urls.format('USER_REPORT', [post.poster.id]) }}"></a>
                                    {% endif %}
                                    <a class="fa fa-reply" title="Quote this post" href="{{ urls.format('FORUM_QUOTE_POST', [post.id]) }}"></a>
                                </div>
                                {% endif %}
                            </div>
                        </td>
                        <td class="post-content">
                            <div class="details">
                                <div class="subject">
                                    <a href="#p{{ post.id }}" class="clean">{{ post.subject }}</a>
                                </div>
                                <div class="date">
                                    <a href="{{ urls.format('FORUM_POST', [post.id]) }}#p{{ post.id }}" class="clean" title="{{ post.time|date(sakura.dateFormat) }}">{{ post.timeElapsed }}</a>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="post-text markdown">
                                {{ post.parsed|raw }}
                            </div>
                            {% if post.poster.signature and post.signature %}
                            <div class="clear"></div>
                            <div class="signature">
                                {{ post.poster.signature|raw|nl2br }}
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
