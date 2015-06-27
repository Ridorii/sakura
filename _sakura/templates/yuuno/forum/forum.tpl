<div class="head">Forums{% if board.viewforum %} / {{ board.forums[0].forum.forum_name }}{% endif %}</div>
<table class="forumList">
    <tbody>
        {% for category in board.forums %}
            {% include 'forum/forumEntry.tpl' %}
        {% endfor %}
    </tbody>
</table>
{% if board.viewforum %}
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
{% endif %}
