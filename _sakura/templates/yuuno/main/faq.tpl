{% extends 'global/master.tpl' %}

{% block title %}Frequently Asked Questions{% endblock %}

{% block content %}
    <div class="content settings">
        <div class="content-right content-column">
            <div class="head">
                Frequently Asked Questions
            </div>
            <div class="right-menu-nav">
            {% for question in page.questions %}
            <a href="#{{ question.short }}" class="default">{{ question.question }}</a>
            {% endfor %}
            </div>
        </div>
        <div class="content-left content-column">
            {% for question in page.questions %}
            <div class="head" id="{{ question.short }}">
                {{ question.question }}
                <a href="#{{ question.short }}" class="fa fa-quote-right news-rss default"></a>
            </div>
            <p>{{ question.answer }}</p>
            {% endfor %}
        </div>
        <div class="clear"></div>
    </div>
{% endblock %}
