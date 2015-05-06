{% include 'global/header.tpl' %}
    <div class="content homepage forum">
        <div class="content-right content-column">
            {% include 'elements/indexPanel.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">Forums</div>
            <table class="forumList">
                <tbody>
                {% for category in page.boards %}
                    {% if category.forums|length %}
                    <tr class="forumCategory">
                        <td class="forumCategoryTitleColumn" colspan="4"><a href="//{{ sakura.urls.forum }}/{{ category.data.forum_id }}/" class="clean">{{ category.data.forum_name }}</a></td>
                    </tr>
                    {% for forum in category.forums %}
                    <tr class="forumForum">
                        <td class="forumIconColumn">
                            <div class="forumIcon read fa fa-3x {% if forum.forum_icon %}{{ forum.forum_icon }}{% else %}{% if forum.forum_type %}fa-chevron-circle-right{% else %}fa-comments{% endif %}{% endif %}"></div>
                        </td>
                        <td class="forumTitleColumn"{% if forum.forum_type == 2 %} colspan="3"{% endif %}>
                            <div class="name"><a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}" target="_blank"{% else %}//{{ sakura.urls.forum }}/{{ forum.forum_id }}/"{% endif %} class="default">{{ forum.forum_name }}</a></div>
                            <div class="desc">{{ forum.forum_desc }}</div>
                        </td>
                        {% if forum.forum_type != 2 %}
                        <td class="forumCountColumn">
                            <div class="topics" title="Amount of topics in this forum.">{{ forum.forum_topics }}</div>
                            <div class="posts" title="Amount of posts in this forum.">{{ forum.forum_posts }}</div>
                        </td>
                        <td class="forumLastColumn">
                            <div>
                                {% if forum.forum_last_post_id %}
                                    Last post in <a href="//{{ sakura.urls.forum }}/thread/{{ forum.forum_last_post_id }}" class="default">Thread with an obnoxiously long fucking title</a><br />12 years ago by <a href="//{{ sakura.urls.main }}/u/{{ forum.last_poster_data.id }}" class="default" style="color: {% if forum.last_poster_data.name_colour %}{{ forum.last_poster_data.name_colour }}{% else %}{{ forum.last_poster_rank.colour }}{% endif %};">{{ forum.last_poster_data.username }}</a>
                                {% else %}
                                    There are no posts in this forum.<br />&nbsp;
                                {% endif %}
                            </div>
                        </td>
                        {% endif %}
                    </tr>
                    {% endfor %}
                    {% endif %}
                {% endfor %}
                </tbody>
            </table>
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
{% include 'global/footer.tpl' %}
