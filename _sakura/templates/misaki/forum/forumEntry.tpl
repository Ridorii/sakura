{% if category.forums|length and category.forum|length %}
    <div class="forumCategory">
        <div class="forumCategoryHead">
            <div class="forumCategoryTitle">{% if category.forum.forum_type != 1 %}Subforums{% else %}<a href="{{ urls.format('FORUM_SUB', [category.forum.forum_id]) }}">{{ category.forum.forum_name }}</a>{% endif %}</div>
            <div class="forumCategoryDescription">{{ category.forum.forum_desc }}</div>
        </div>
        {% for forum in category.forums %}
            <div class="forumSubEntry">
                <a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}{% else %}{{ urls.format('FORUM_SUB', [forum.forum_id]) }}{% endif %}" class="forumSubIcon">
                    <div class="forumIcon fa fa-3x {% if forum.forum_icon %}{{ forum.forum_icon }}{% else %}{% if forum.forum_type == 2 %}fa-chevron-circle-right{% elseif forum.forum_type == 1 %}fa-folder{% else %}fa-comments{% endif %}{% endif %}"></div>
                </a>
                <div class="forumSubTitle">
                    <a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}{% else %}{{ urls.format('FORUM_SUB', [forum.forum_id]) }}{% endif %}" class="forumSubName">{{ forum.forum_name }}</a>
                    <div class="forumSubDesc">
                        {{ forum.forum_desc }}
                        {% if board.forums[forum.forum_id]|length %}
                            <ul class="forumSubSubforums">
                                {% for forum in board.forums[forum.forum_id].forums %}
                                    <li><a href="{% if forum.forum_type == 2 %}{{ forum.forum_link }}{% else %}{{ urls.format('FORUM_SUB', [forum.forum_id]) }}{% endif %}" class="default">{{ forum.forum_name }}</a></li>
                                {% endfor %}
                            </ul>
                        {% endif %}
                    </div>
                </div>
                {% if forum.forum_type != 2 %}
                <div class="forumSubStats">
                    <div class="forumSubTopics" title="Amount of topics in this forum.">{{ forum.topic_count }}</div>
                    <div class="forumSubStatsSeperator">/</div>
                    <div class="forumSubPosts" title="Amount of posts in this forum.">{{ forum.post_count }}</div>
                </div>
                <div class="forumSubReplies">
                    <div>
                        {% if forum.last_post.post_id %}
                            <a href="{{ urls.format('FORUM_THREAD', [forum.last_post.topic_id]) }}" class="default">{{ forum.last_post.post_subject }}</a><br />
                            <span title="{{ forum.last_post.post_time|date(sakura.dateFormat) }}">{{ forum.last_post.elapsed }}</span> by {% if forum.last_poster.data.user_id %}<a href="{{ urls.format('USER_PROFILE', [forum.last_poster.data.user_id]) }}" class="default" style="color: {{ forum.last_poster.colour }}; text-shadow: 0 0 5px {% if forum.last_poster.colour != 'inherit' %}{{ forum.last_poster.colour }}{% else %}#222{% endif %};">{{ forum.last_poster.data.username }}</a>{% else %}[deleted user]{% endif %} <a href="{{ urls.format('FORUM_POST', [forum.last_post.post_id]) }}#p{{ forum.last_post.post_id }}" class="default fa fa-tag"></a>
                        {% else %}
                            There are no posts in this forum.
                        {% endif %}
                    </div>
                </div>
                {% endif %}
            </div>
        {% endfor %}
    </div>
{% endif %}
