<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{{ page.title }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        {% if page.redirect %}
            <meta http-equiv="refresh" content="3; URL={{ page.redirect }}" />
        {% endif %}
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="//{{ sakura.urls.content }}/global.css" />
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/manage.css" />
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
        <!-- JS -->
        <script type="text/javascript" src="{{ sakura.resources }}/js/manage.js"></script>
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header" id="header">
                <a class="logo" href="/">
                    Broom Closet{{ page.activepage }} {{ page.activesub }}
                </a>
                <div class="nav">
                    {% for short,mode in page.pages %}
                        <div class="menu" id="{{ short }}Nav">
                        <div>{{ mode.desc|raw }}</div>
                        {% for short,page in mode.pages %}
                            <a href="/{{ short }}/">{{ page.title }}</a>
                        {% endfor %}
                        </div>
                    {% endfor %}
                    <div class="menu" id="subNav">
                        <div>Section title here</div>
                        <a href="#">Front page</a>
                    </div>
                </div>
            </div>
            <div id="contentwrapper">
