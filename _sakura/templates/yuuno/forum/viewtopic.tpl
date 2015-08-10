{% include 'global/header.tpl' %}
    <div class="content homepage forum viewtopic">
        <div class="content-column">
            <div class="head">{{ forum.forum.forum_name }} / {{ topic.topic_title }}</div>
            {% include 'forum/forumBtns.tpl' %}
            <table class="posts">
                {% for post in posts %}
                    <tr class="post" id="p{{ post.post_id }}">
                        <td class="userpanel">
                            {% if post.user.rank_main > 1 %}<a href="/u/{{ post.user.id }}" class="default username" style="color: {% if post.user.name_colour %}{{ post.user.name_colour }}{% else %}{{ post.rank.colour }}{% endif %};" title="Go to {{ post.user.username }}'s profile">{{ post.user.username }}</a>
                            <img src="/a/{{ post.user.id }}" alt="{{ post.user.username }}" class="avatar" style="box-shadow: 0 3px 7px #{% if post.is_online %}484{% else %}844{% endif %};" />
                            {% else %}
                            <a class="username">[deleted user]</a>
                            {% endif %}
                            <div class="userdata">
                                <div class="usertitle">{% if not post.user.usertitle %}{{ post.rank.title }}{% else %}{{ post.user.usertitle }}{% endif %}</div>
                                <img src="{{ sakura.content_path }}/images/tenshi.png" alt="Tenshi"{% if not post.is_premium %} style="opacity: 0;"{% endif %} /> <img src="{{ sakura.content_path }}/images/flags/{% if post.user.country|lower == 'eu' %}europeanunion{% else %}{{ post.user.country|lower }}{% endif %}.png" alt="{{ post.country }}" />
                                {% if user.checklogin %}
                                <div class="actions">
                                    {% if user.data.id == post.user.id %}
                                    <a class="fa fa-pencil-square-o" title="Edit this post" href="/forum/post/{{ post.post_id }}/edit"></a>
                                    <a class="fa fa-trash" title="Delete this post" href="/forum/post/{{ post.post_id }}/delete"></a>
                                    {% elseif post.user.rank_main > 1 %}
                                    {% if post.is_friend != 0 %}<a class="fa fa-{% if post.is_friend == 2 %}heart{% else %}star{% endif %}" title="You are friends"></a>{% endif %}
                                    <a class="fa fa-user-{% if post.is_friend == 0 %}plus{% else %}times{% endif %} forum-friend-toggle" title="{% if post.is_friend == 0 %}Add {{ post.user.username }} as a friend{% else %}Remove friend{% endif %}" href="/friends?{% if post.is_friend == 0 %}add{% else %}remove{% endif %}={{ post.user.id }}&amp;session={{ php.sessionid }}&amp;time={{ php.time }}&amp;redirect=/forum/post/{{ post.post_id }}&amp;direct=true"></a>
                                    <a class="fa fa-flag" title="Report {{ post.user.username }}" href="/u/{{ post.user.id }}/report"></a>
                                    {% endif %}
                                    <a class="fa fa-reply" title="Quote this post" href="/forum/post/{{ post.post_id }}/quote"></a>
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
                                    <a href="/forum/post/{{ post.post_id }}#p{{ post.post_id }}" class="clean" title="{{ post.post_time|date(sakura.date_format) }}">{{ post.time_elapsed }}</a>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="post-text markdown">
                                {{ post.parsed_post|raw }}
                            </div>
                            {% if post.enable_sig and post.signature %}
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
{% include 'global/footer.tpl' %}
