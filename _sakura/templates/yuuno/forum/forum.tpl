<div class="head">Forums{% if board.viewforum %} / {{ board.forums[0].forum.forum_name }}{% endif %}</div>
<table class="forumList">
    <tbody>
        {% for category in board.forums %}
            {% include 'forum/forumEntry.tpl' %}
        {% endfor %}
    </tbody>
</table>
{% if board.viewforum and not board.forums[0].forum.forum_type %}
    {% include 'forum/forumBtns.tpl' %}
    {% if board.topics|length %}
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
                {% for topic in board.topics %}
                    {% include 'forum/topicEntry.tpl' %}
                {% endfor %}
            </tbody>
        </table>
    {% else %}
        <h1 class="stylised" style="margin: 2em auto; text-align: center;">There are no posts in this forum!</h1>
    {% endif %}
    {% include 'forum/forumBtns.tpl' %}
{% endif %}
