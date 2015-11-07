{% if category.forums|length and category.forum|length %}
    <tr class="forumCategory">
        <td class="forumCategoryTitleColumn" colspan="4">{% if category.forum.forum_type != 1 %}Subforums{% else %}<a href="{{ urls.format('FORUM_SUB', [category.forum.forum_id]) }}" class="clean">{{ category.forum.forum_name }}</a>{% endif %}</td>
    </tr>
    {% for forum in category.forums %}
        <tr class="forumForum">
            <td class="forumIconColumn">
                <div class="forumIcon read fa fa-3x {% if forum.forum_icon %}{{ forum.forum_icon }}{% else %}{% if forum.forum_type == 2 %}fa-chevron-circle-right{% elseif forum.forum_type == 1 %}fa-folder{% else %}fa-comments{% endif %}{% endif %}"></div>
            </td>
            <td class="forumTitleColumn"{% if forum.forum_type == 2 %} colspan="3"{% endif %}>
                <div class="name"><a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}{% else %}{{ urls.format('FORUM_SUB', [forum.forum_id]) }}{% endif %}" class="default">{{ forum.forum_name }}</a></div>
                <div class="desc">
                    {{ forum.forum_desc }}
                    {% if board.forums[forum.forum_id]|length %}
                        <div class="subforums" style="margin-top: 3px; margin-left: -5px; font-weight: bold;">
                            Subforums:
                            {% for forum in board.forums[forum.forum_id].forums %}
                                <a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}{% else %}{{ urls.format('FORUM_SUB', [forum.forum_id]) }}{% endif %}" class="default">{{ forum.forum_name }}</a>
                            {% endfor %}
                        </div>
                    {% endif %}
                </div>
            </td>
            {% if forum.forum_type != 2 %}
            <td class="forumCountColumn">
                <div class="topics" title="Amount of topics in this forum.">{{ forum.topic_count }}</div>
                <div class="posts" title="Amount of posts in this forum.">{{ forum.post_count }}</div>
            </td>
            <td class="forumLastColumn">
                <div>
                    {% if forum.last_post.post_id %}
                        <a href="{{ urls.format('FORUM_THREAD', [forum.last_post.topic_id]) }}" class="default">{{ forum.last_post.post_subject }}</a><br />
                        <span title="{{ forum.last_post.post_time|date(sakura.dateFormat) }}">{{ forum.last_post.elapsed }}</span> by {% if forum.last_poster.id %}<a href="{{ urls.format('USER_PROFILE', [forum.last_poster.id]) }}" class="default" style="color: {{ forum.last_poster.colour }}; text-shadow: 0 0 5px {% if forum.last_poster.colour != 'inherit' %}{{ forum.last_poster.colour }}{% else %}#222{% endif %};">{{ forum.last_poster.username }}</a>{% else %}[deleted user]{% endif %} <a href="{{ urls.format('FORUM_POST', [forum.last_post.post_id]) }}#p{{ forum.last_post.post_id }}" class="default fa fa-tag"></a>
                    {% else %}
                        There are no posts in this forum.<br />&nbsp;
                    {% endif %}
                </div>
            </td>
            {% endif %}
        </tr>
    {% endfor %}
{% endif %}
