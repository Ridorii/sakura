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
                <div class="logo">
                    <a href="/">Broom Closet</a> /
                    <a href="/{{ page.activepage }}/">{{ page.pages[page.activepage].desc }}</a> /
                    <a href="/{{ page.activepage }}/{{ page.activesub }}/">{{ page.pages[page.activepage].subs[page.activesub].desc }}</a>
                </div>
                <div class="nav">
                    <div class="menu" id="siteNav">
                        <div style="color: {{ user.colour }};">{{ user.data.username }}</div>
                        <a href="//{{ sakura.urls.main }}/">Return to Site Index</a>
                        <a href="//{{ sakura.urls.main }}/logout?mode=logout&time={{ php.time }}&session={{ php.sessionid }}&redirect=//{{ sakura.urls.main }}/">Logout</a>
                    </div>
                    <div class="menu" id="pageNav">
                        <div>Navigation</div>
                        {% for short,page in page.pages %}
                            <a href="/{{ short }}/">{{ page.desc }}</a>
                        {% endfor %}
                    </div>
                    <div class="menu" id="subNav">
                        <div>{{ page.pages[page.activepage].desc }}</div>
                        {% for short,sub in page.pages[page.activepage].subs %}
                            <a href="/{{ page.activepage }}/{{ short }}/">{{ sub.desc }}</a>
                        {% endfor %}
                    </div>
                </div>
            </div>
            <div id="contentwrapper">
