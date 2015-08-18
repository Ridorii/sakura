<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{{ page.title }}</title>
        <meta name="description" content="{{ sakura.sitedesc }}" />
        <meta name="keywords" content="{{ sakura.sitetags }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        {% if page.redirect %}
            <meta http-equiv="refresh" content="3; URL={{ page.redirect }}" />
        {% endif %}
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/misaki.css" />
        {% if page.style %}
        <style type="text/css">
            {% for element,properties in page.style %}
                {{ element|raw }} {
                    {% for property,value in properties %}
                        {{ property|raw }}: {{ value|raw }};
                    {% endfor %}
                }
            {% endfor %}
        </style>
        {% endif %}
        <!-- JS -->
        <script type="text/javascript" src="{{ sakura.resources }}/js/misaki.js"></script>
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header-fade"></div>
            <div id="notifications"></div>
            <div id="header">
                <div class="logo"></div>
                <div class="text"></div>
                <div class="floatRight">
                    <div class="social">
                        <ul>
                            <li><a target="_blank" title="Flashii on Twitter" class="fa fa-twitter" href="https://twitter.com/_flashii"></a></li>
                            <li><a target="_blank" title="Flashii on YouTube" class="fa fa-youtube-play" href="https://youtube.com/user/flashiinet"></a></li>
                            <li><a target="_blank" title="Flashii on Steam" class="fa fa-steam" href="https://steamcommunity.com/groups/flashiinet"></a></li>
                            <li><a title="Subscribe to the news page feed" class="fa fa-rss" href="/news.xml"></a></li>
                        </ul>
                        <div class="clear"></div>
                    </div>
                    <div class="image"></div>
                </div>
            </div>
            <div id="wrapper">
                {% if profile.user.userData.profileBackground %}
                <div id="userBackground"></div>
                {% endif %}
                <div id="content">
                    <div id="navigation">
                        <ul class="nav-left floatLeft">
                            <li><a href="/">Home</a></li>
                            <li><a href="/news">News</a></li>
                            <li><a href="//chat.{{ sakura.url_main }}/">Chat</a></li>
                            <li><a href="/forum">Forum</a></li>
                            <li><a href="/members">Members</a></li>
                            <li><a href="/search">Search</a></li>
                            <li><a href="/donate">Donate</a></li>
                        </ul>
                        <ul class="nav-right floatRight">
                            <li class="nav-usermenu">
                                <a href="#"{% if user.checklogin %} style="color: {{ user.colour }};"{% endif %}>{% if user.checklogin %}{{ user.data.username }}{% else %}Guest{% endif %}</a>
                                <ul>
                                {% if user.checklogin %}
                                    <li><a href="/u/{{ user.data.id }}">My Profile</a></li>
                                    <li><a href="/messages">View Messages</a></li>
                                    <li><a href="/settings">User Settings</a></li>
                                    <li><a href="/manage">Site Management</a></li>
                                    <li><a href="/logout?mode=logout&amp;time={{ php.time }}&amp;session={{ php.sessionid }}&amp;redirect={{ sakura.currentpage }}">Logout</a></li>
                                {% else %}
                                    <li><a href="/login">Login</a></li>
                                    <li><a href="/register">Register</a></li>
                                {% endif %}
                                </ul>
                            </li>
                            <li><a href="#" class="ignore"><img src="{{ sakura.content_path }}/pixel.png" alt="{{ user.data.username }}" style="background-image: url('/a/{{ user.data.id }}');" class="nav-avatar" /></a></li>
                        </ul>
                        <div class="clear"></div>
                    </div>
