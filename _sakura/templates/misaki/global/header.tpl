<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{{ page.title }}</title>
        <meta name="description" content="{{ sakura.sitedesc }}" />
        <meta name="keywords" content="{{ sakura.sitetags }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        <meta name="msapplication-TileColor" content="#fbeeff" />
        <meta name="msapplication-TileImage" content="/content/images/icons/ms-icon-144x144.png" />
        <meta name="theme-color" content="#9475B2" />
        {% if page.redirect %}
            <meta http-equiv="refresh" content="3; URL={{ page.redirect }}" />
        {% endif %}
        <link rel="apple-touch-icon" sizes="57x57" href="/content/images/icons/apple-icon-57x57.png" />
        <link rel="apple-touch-icon" sizes="60x60" href="/content/images/icons/apple-icon-60x60.png" />
        <link rel="apple-touch-icon" sizes="72x72" href="/content/images/icons/apple-icon-72x72.png" />
        <link rel="apple-touch-icon" sizes="76x76" href="/content/images/icons/apple-icon-76x76.png" />
        <link rel="apple-touch-icon" sizes="114x114" href="/content/images/icons/apple-icon-114x114.png" />
        <link rel="apple-touch-icon" sizes="120x120" href="/content/images/icons/apple-icon-120x120.png" />
        <link rel="apple-touch-icon" sizes="144x144" href="/content/images/icons/apple-icon-144x144.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="/content/images/icons/apple-icon-152x152.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="/content/images/icons/apple-icon-180x180.png" />
        <link rel="icon" type="image/png" sizes="192x192"  href="/content/images/icons/android-icon-192x192.png" />
        <link rel="icon" type="image/png" sizes="32x32" href="/content/images/icons/favicon-32x32.png" />
        <link rel="icon" type="image/png" sizes="96x96" href="/content/images/icons/favicon-96x96.png" />
        <link rel="icon" type="image/png" sizes="16x16" href="/content/images/icons/favicon-16x16.png" />
        <link rel="manifest" href="/manifest.json" />
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
        <script type="text/javascript">

            // Create an object so we can access certain settings from remote JavaScript files
            var sakuraVars = {

                "cookie": {

                    "prefix":   "{{ sakura.cookie.prefix }}",
                    "domain":   "{{ sakura.cookie.domain }}",
                    "path":     "{{ sakura.cookie.path }}"

                },

                "urlMain":          "{{ sakura.urlMain }}",
                "content":          "{{ sakura.contentPath }}",
                "resources":        "{{ sakura.resources }}",
                "recaptchaEnabled": "{{ sakura.recaptchaEnabled }}",

                "minUserLen":       {{ sakura.minUsernameLength }},
                "maxUserLen":       {{ sakura.maxUsernameLength }},
                "minPwdEntropy":    {{ sakura.minPwdEntropy }},
                "checkLogin":       {% if session.checkLogin %}true{% else %}false{% endif %}

            };

            // Space for things that need to happen onload
            window.addEventListener("load", function() {

                {% if php.self == '/profile.php' ? (profile.data.userData.profileBackground and not profile.data.userData.userOptions.disableProfileParallax) : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.data.userData.userOptions.profileBackgroundSiteWide and user.data.userData.profileBackground and not user.data.userData.userOptions.disableProfileParallax) %}
                    initialiseParallax('userBackground');
                {% endif %}

            });

        </script>
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header-fade"></div>
            <div id="notifications"></div>
            <div id="header">
                <a href="/">
                    <div class="logo"></div>
                </a>
                <div class="social">
                    <ul>
                        <li><a target="_blank" title="Flashii on Twitter" class="fa fa-twitter" href="https://twitter.com/_flashii"></a></li>
                        <li><a target="_blank" title="Flashii on YouTube" class="fa fa-youtube-play" href="https://youtube.com/user/flashiinet"></a></li>
                        <li><a target="_blank" title="Flashii on Steam" class="fa fa-steam" href="https://steamcommunity.com/groups/flashiinet"></a></li>
                        <li><a target="_blank" title="Circlestorm on BitBucket" class="fa fa-bitbucket" href="https://bitbucket.org/circlestorm"></a></li>
                        <li><a target="_blank" title="Flashii on osu!" class="fa fa-dot-circle-o" href="https://osu.ppy.sh/#"></a></li>
                        <li><a title="Subscribe to the news page feed" class="fa fa-rss" href="/news.xml"></a></li>
                    </ul>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="wrapper">
                {% if php.self == '/profile.php' ? profile.data.userData.profileBackground : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.data.userData.userOptions.profileBackgroundSiteWide and user.data.userData.profileBackground) %}
                    <div id="userBackground" style="background-image: url('/bg/{{ (php.self == '/profile.php' ? profile : user).data.id }}');"></div>
                {% endif %}
                <div id="content">
                    <div id="navigation">
                        <ul class="nav-left floatLeft">
                            <li><a href="/">Home</a></li>
                            <li><a href="/news">News</a></li>
                            <li><a href="//chat.{{ sakura.urlMain }}/">Chat</a></li>
                            <li><a href="/forum">Forum</a></li>
                            <li><a href="/members">Members</a></li>
                            <li><a href="/search">Search</a></li>
                            <li><a href="/support">Support us</a></li>
                        </ul>
                        <ul class="nav-right floatRight">
                            <li class="nav-usermenu">
                                <a href="#"{% if session.checkLogin %} style="color: {{ user.colour }};"{% endif %}>{% if session.checkLogin %}{{ user.data.username }}{% else %}Guest{% endif %}</a>
                                <ul>
                                {% if session.checkLogin %}
                                    <li><a href="/u/{{ user.data.id }}">My Profile</a></li>
                                    <li><a href="/messages">Private Messages</a></li>
                                    <li><a href="/settings">User Settings</a></li>
                                    <li><a href="/manage">Site Management</a></li>
                                    <li><a href="/logout?mode=logout&amp;time={{ php.time }}&amp;session={{ php.sessionid }}&amp;redirect={{ sakura.currentPage }}">Logout</a></li>
                                {% else %}
                                    <li><a href="/login">Login</a></li>
                                    <li><a href="/register">Register</a></li>
                                {% endif %}
                                </ul>
                            </li>
                            <li><a href="/u/{{ user.data.id }}" class="ignore"><img src="{{ sakura.contentPath }}/pixel.png" alt="{{ user.data.username }}" style="background-image: url('/a/{{ user.data.id }}');" class="nav-avatar" /></a></li>
                        </ul>
                    </div>
