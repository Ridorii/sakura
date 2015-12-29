<div class="head">{{ title }}</div>
<div class="forumList">
    {% for forum in forum.forums %}
        {% if forum.type == 1 %}
            {% if forum.forums|length and forum.permission(constant('Sakura\\Perms\\Forum::VIEW'), user.id) %}
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
    {% set threads = forum.threads|batch(25) %}

    {% set paginationPages = threads %}
    {% set paginationUrl %}{{ urls.format('FORUM_SUB', [forum.id]) }}{% endset %}

    {% include 'forum/forumBtns.tpl' %}
    {% if forum.threads %}
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
                {% for thread in threads[get.page|default(1) - 1] %}
                    {% include 'forum/topicEntry.tpl' %}
                {% endfor %}
            </tbody>
        </table>
    {% else %}
        <h1 class="stylised" style="margin: 2em auto; text-align: center;">There are no posts in this forum!</h1>
    {% endif %}
    {% include 'forum/forumBtns.tpl' %}
{% endif %}
