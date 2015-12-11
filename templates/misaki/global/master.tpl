<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{% block title %}{{ sakura.siteName }}{% endblock %}</title>
        <meta name="description" content="{{ sakura.siteDesc }}" />
        <meta name="keywords" content="{{ sakura.siteTags }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        <meta name="msapplication-TileColor" content="#9475b2" />
        <meta name="msapplication-TileImage" content="/content/images/icons/ms-icon-144x144.png" />
        <meta name="theme-color" content="#9475B2" />
{% if page.redirect %}
        <meta http-equiv="refresh" content="{{ page.redirectTimeout ? page.redirectTimeout : '3' }}; URL={{ page.redirect }}" />
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
{{ block('meta') }}
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/misaki.css" />
{{ block('css') }}
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

                "siteName":         "{{ sakura.siteName }}",
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

                {% if php.self == '/profile.php' ? (profile.userData.profileBackground and not profile.optionFields.disableProfileParallax) : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.optionFields.profileBackgroundSiteWide and user.userData.profileBackground and not user.optionFields.disableProfileParallax) %}
                    initialiseParallax('userBackground');
                {% endif %}

            });

        </script>
{{ block('js') }}
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header-fade"></div>
            <div id="notifications"></div>
            <div id="wrapper">
                {% if php.self == '/profile.php' ? profile.userData.profileBackground : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.optionFields.profileBackgroundSiteWide and user.userData.profileBackground) %}
                    <div id="userBackground" style="background-image: url('{{ urls.format('IMAGE_BACKGROUND', [(php.self == '/profile.php' ? profile : user).id]) }}');"></div>
                {% endif %}
                <div id="navigation">
                    <ul class="site-menu">
                        <li title="Home" class="logo"><a href="{{ urls.format('SITE_HOME') }}"></a></li>
                        <li title="News"><a href="{{ urls.format('SITE_NEWS') }}" class="fa fa-newspaper-o"></a></li>
                        <li title="Chat"><a href="//chat.{{ sakura.urlMain }}/" class="fa fa-commenting"></a></li>
                        <li title="Forums"><a href="{{ urls.format('FORUM_INDEX') }}" class="fa fa-comments"></a></li>
                        <li title="Members"><a href="{{ urls.format('MEMBERLIST_INDEX') }}" class="fa fa-users"></a></li>
                        <li title="Search"><a href="{{ urls.format('SITE_SEARCH') }}" class="fa fa-search"></a></li>
                        <li title="Support us"><a href="{{ urls.format('SITE_PREMIUM') }}" class="fa fa-heart"></a></li>
                    </ul>
                    <ul class="user-menu">
                        <li class="nav-usermenu">
                            <a href="{% if session.checkLogin %}javascript:void(0);{% else %}{{ urls.format('SITE_LOGIN') }}{% endif %}">
                                <div>
                                    <div class="nav-username"{% if session.checkLogin %} style="color: {{ user.colour }};"{% endif %}>
                                        {% if session.checkLogin %}{{ user.username }} <span class="nav-user-dropdown"></span>{% else %}Guest{% endif %}
                                    </div>
                                    <div class="nav-userstats">
                                        {% if session.checkLogin %}<span class="fa fa-envelope"></span> 0 / <span class="fa fa-user-plus"></span> 0 / <span class="fa fa-warning"></span> 0 / <span class="fa fa-reply"></span> 0{% else %}Please log in to proceed!{% endif %}
                                    </div>
                                </div>
                            </a>
                            {% if session.checkLogin %}
                                <ul>
                                    <li><a href="{{ urls.format('USER_PROFILE', [user.id]) }}">My Profile</a></li>
                                    <li><a href="{{ urls.format('SETTING_CAT', ['messages']) }}">Private Messages</a></li>
                                    <li><a href="{{ urls.format('SETTINGS_INDEX') }}">User Settings</a></li>
                                    <li><a href="{{ urls.format('MANAGE_INDEX') }}">Site Management</a></li>
                                    <li><a href="{{ urls.format('USER_LOGOUT', [php.time, php.sessionid, sakura.currentPage]) }}">Logout</a></li>
                                </ul>
                            {% endif %}
                        </li>
                        <li><a href="{% if session.checkLogin %}{{ urls.format('USER_PROFILE', [user.id]) }}{% else %}{{ urls.format('SITE_LOGIN') }}{% endif %}"><img src="{{ sakura.contentPath }}/pixel.png" alt="{{ user.username }}" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.id]) }}');" class="nav-avatar" /></a></li>
                    </ul>
                </div>

                {% if sakura.siteLogo or sakura.announcementImage %}
                <div id="banner" style="background: url('{% if sakura.announcementImage %}{{ sakura.announcementImage }}{% else %}{{ sakura.siteLogo }}{% endif %}');">
                    <a href="{% if sakura.announcementImage and sakura.announcementLink %}{{ sakura.announcementLink }}{% else %}{{ urls.format('SITE_HOME') }}{% endif %}"></a>
                </div>
                {% endif %}

                <div id="content">
{% block content %}
                    <div class="platform">
                        <div style="text-align: center; font-family: 'Exo2-0-LightItalic', sans-serif; font-size: 3em; line-height: 1.5em; margin: 10px auto">This template doesn't exist (yet)!<br />Switch back to Yuuno via the User Settings to view this page!</div>
                    </div>
{% endblock %}
                </div>
            </div>
            <div id="footer">
                <div class="inner">
                    <div class="ft-logo"></div>
                    <div class="ft-text">
                        <div>Powered by <a href="https://github.com/flashwave/sakura/" target="_blank">Sakura</a>, <a href="https://flash.moe/" target="_blank">Flashwave</a> 2013-2015</div>
                        <div><a href="{{ urls.format('INFO_PAGE', ['terms']) }}">Terms of Service</a> | <a href="{{ urls.format('INFO_PAGE', ['contact']) }}">Contact</a> | <a href="{{ urls.format('SITE_FAQ') }}">FAQ</a> | <a href="{{ urls.format('INFO_PAGE', ['rules']) }}">Rules</a> | <a href="https://sakura.flash.moe/">Changelog</a> | <a href="https://fiistat.us/">Status</a></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
