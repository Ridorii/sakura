{% extends 'global/master.tpl' %}

{% set bbcode = {'b': ['Bold', 'bold'], 'i': ['Italic', 'italic'], 'u': ['Underline', 'underline'], 's': ['Strikethrough', 'strikethrough'], 'header': ['Header', 'header'], 'url': ['URL', 'chain'], 'code': ['Code', 'code'], 'spoiler': ['Spoiler', 'minus'], 'box': ['Spoiler box', 'folder', true], 'list': ['List', 'list-ul'], 'img': ['Image', 'picture-o'], 'youtube': ['YouTube video', 'youtube-play']} %}

{% set cancelTarget = 'history.go(-1);' %}

{% set editorFormId = 'forumPostingForm' %}

{% block title %}Posting{% endblock %}

{% block content %}
<div class="content">
    <div class="content-column forum posting">
        {% include 'elements/editor.tpl' %}
    </div>
</div>
{% endblock %}
