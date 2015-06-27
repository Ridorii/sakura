{% if category.forums|length and category.forum|length %}
    <tr class="forumCategory">
        <td class="forumCategoryTitleColumn" colspan="4">{% if category.forum.forum_type != 1 %}Subforums{% else %}<a href="//{{ sakura.urls.main }}/forum/{{ category.forum.forum_id }}/" class="clean">{{ category.forum.forum_name }}</a>{% endif %}</td>
    </tr>
    {% for forum in category.forums %}
        <tr class="forumForum">
            <td class="forumIconColumn">
                <div class="forumIcon read fa fa-3x {% if forum.forum_icon %}{{ forum.forum_icon }}{% else %}{% if forum.forum_type == 2 %}fa-chevron-circle-right{% elseif forum.forum_type == 1 %}fa-folder{% else %}fa-comments{% endif %}{% endif %}"></div>
            </td>
            <td class="forumTitleColumn"{% if forum.forum_type == 2 %} colspan="3"{% endif %}>
                <div class="name"><a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}" target="_blank"{% else %}//{{ sakura.urls.main }}/forum/{{ forum.forum_id }}/"{% endif %} class="default">{{ forum.forum_name }}</a></div>
                <div class="desc">
                    {{ forum.forum_desc }}
                    {% if board.forums[forum.forum_id]|length %}
                        <div class="subforums" style="margin-top: 3px; margin-left: -5px;">
                            Subforums:
                            {% for forum in board.forums[forum.forum_id].forums %}
                                <a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}" target="_blank"{% else %}//{{ sakura.urls.main }}/forum/{{ forum.forum_id }}/"{% endif %}" class="default">{{ forum.forum_name }}</a>
                            {% endfor %}
                        </div>
                    {% endif %}
                </div>
            </td>
            {% if forum.forum_type != 2 %}
            <td class="forumCountColumn">
                <div class="topics" title="Amount of topics in this forum.">{{ forum.forum_topics }}</div>
                <div class="posts" title="Amount of posts in this forum.">{{ forum.forum_posts }}</div>
            </td>
            <td class="forumLastColumn">
                <div>
                    {% if forum.forum_last_post_id %}
                        Last post in <a href="//{{ sakura.urls.main }}/forum/thread/{{ forum.forum_last_post_id }}" class="default">Thread with an obnoxiously long fucking title</a><br />12 years ago by <a href="//{{ sakura.urls.main }}/u/{{ forum.last_poster.user.id }}" class="default" style="color: {% if forum.last_poster.user.name_colour %}{{ forum.last_poster.user.name_colour }}{% else %}{{ forum.last_poster.rank.colour }}{% endif %};">{{ forum.last_poster.user.username }}</a> <a href="/forum/post/{{ forum.forum_last_post_id }}" class="default fa fa-tag"></a>
                    {% else %}
                        There are no posts in this forum.<br />&nbsp;
                    {% endif %}
                </div>
            </td>
            {% endif %}
        </tr>
    {% endfor %}
{% endif %}
