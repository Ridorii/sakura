{% extends 'global/master.tpl' %}

{% set title %}Forums / {{ forum.name }}{% endset %}

{% set forumBackLink %}{{ urls.format('FORUM_INDEX') }}{% endset %}
{% set forumNewLink %}{{ urls.format('FORUM_NEW_THREAD', [forum.id]) }}{% endset %}
{% set forumMarkRead %}{{ urls.format('FORUM_MARK_READ', [forum.id, php.sessionid]) }}{% endset %}

{% block title %}{{ title }}{% endblock %}

{% block content %}
    <div class="content homepage forum viewforum">
        <div class="content-column">
            {% include 'forum/forum.tpl' %}
        </div>
    </div>
{% endblock %}
