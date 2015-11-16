<div class="forumForum">
    <div class="forumIcon {% if forum.unread(user.id) %}unread {% endif %}fa fa-3x {% if forum.icon %}{{ forum.icon }}{% else %}{% if forum.type == 2 %}fa-chevron-circle-right{% elseif forum.type == 1 %}fa-folder{% else %}fa-comments{% endif %}{% endif %}"></div>
    <div class="forumTitle">
        <div class="name"><a href="{% if forum.type == 2 %}{{ forum.link }}{% else %}{{ urls.format('FORUM_SUB', [forum.id]) }}{% endif %}" class="default">{{ forum.name }}</a></div>
        <div class="desc">
            {{ forum.description }}
            {% if forum.forums|length %}
                <div class="subforums">
                    Subforums:
                    {% for forum in forum.forums %}
                        <a href="{% if forum.type == 2 %}{{ forum.link }}{% else %}{{ urls.format('FORUM_SUB', [forum.id]) }}{% endif %}" class="default">{{ forum.name }}</a>
                    {% endfor %}
                </div>
            {% endif %}
        </div>
    </div>
    {% if forum.type != 2 %}
    <div class="forumCount">
        <div class="topics" title="Amount of threads in this forum.">{{ forum.threadCount }}</div>
        <div class="posts" title="Amount of posts in this forum.">{{ forum.postCount }}</div>
    </div>
    <div class="forumLastPost">
        <div>
            {% if forum.lastPost.id %}
                <a href="{{ urls.format('FORUM_THREAD', [forum.lastPost.thread]) }}" class="default">{{ forum.lastPost.subject }}</a><br />
                <span title="{{ forum.lastPost.time|date(sakura.dateFormat) }}">{{ forum.lastPost.timeElapsed }}</span> by {% if forum.lastPost.poster.id %}<a href="{{ urls.format('USER_PROFILE', [forum.lastPost.poster.id]) }}" class="default" style="color: {{ forum.lastPost.poster.colour }}; text-shadow: 0 0 5px {% if forumlastPost.poster.colour != 'inherit' %}{{ forum.lastPost.poster.colour }}{% else %}#222{% endif %};">{{ forum.lastPost.poster.username }}</a>{% else %}[deleted user]{% endif %} <a href="{{ urls.format('FORUM_POST', [forum.lastPost.id]) }}#p{{ forum.lastPost.id }}" class="default fa fa-tag"></a>
            {% else %}
                There are no posts in this forum.<br />&nbsp;
            {% endif %}
        </div>
    </div>
    {% endif %}
</div>

