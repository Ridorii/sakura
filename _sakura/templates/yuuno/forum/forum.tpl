<div class="head">{{ title }}</div>
<div class="forumList">
    {% for forum in forum.forums %}
        {% if forum.type == 1 %}
            {% if forum.forums|length %}
            <div class="forumCategory">
                {% if forum.type != 1 %}Subforums{% else %}<a href="{{ urls.format('FORUM_SUB', [forum.id]) }}" class="clean">{{ forum.name }}</a>{% endif %}
            </div>
            {% for forum in forum.forums %}
                {% include 'forum/forumEntry.tpl' %}
            {% endfor %}
            {% endif %}
        {% else %}
            {% include 'forum/forumEntry.tpl' %}
        {% endif %}
    {% endfor %}
</div>
{% if not forum.type and forum.id > 0 %}
    {% include 'forum/forumBtns.tpl' %}
    {% if board.threads|length %}
        <table class="topicList">
            <thead>
                <tr>
                    <th></th>
                    <th>Topic</th>
                    <th>Author</th>
                    <th></th>
                    <th>Last post</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th>Topic</th>
                    <th>Author</th>
                    <th></th>
                    <th>Last post</th>
                </tr>
            </tfoot>
            <tbody>
                {% for thread in board.threads[currentPage] %}
                    {% include 'forum/topicEntry.tpl' %}
                {% endfor %}
            </tbody>
        </table>
    {% else %}
        <h1 class="stylised" style="margin: 2em auto; text-align: center;">There are no posts in this forum!</h1>
    {% endif %}
    {% include 'forum/forumBtns.tpl' %}
{% endif %}
