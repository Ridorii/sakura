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

                {% if php.self == '/profile.php' ? (profile.data.user_data.profileBackground and not profile.data.user_data.userOptions.disableProfileParallax) : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.data.user_data.userOptions.profileBackgroundSiteWide and user.data.user_data.profileBackground and not user.data.user_data.userOptions.disableProfileParallax) %}
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
                {% if php.self == '/profile.php' ? profile.data.user_data.profileBackground : (user.checkPermission('SITE', 'CREATE_BACKGROUND') and user.data.user_data.userOptions.profileBackgroundSiteWide and user.data.user_data.profileBackground) %}
                    <div id="userBackground" style="background-image: url('{{ urls.format('IMAGE_BACKGROUND', [(php.self == '/profile.php' ? profile : user).data.user_id]) }}');"></div>
                {% endif %}
                <div id="navigation">
                    <ul class="nav-left floatLeft">
                        <li class="logo"><a href="{{ urls.format('SITE_HOME') }}"><div {% if sakura.siteLogo %} style="background-image: url('{{ sakura.siteLogo }}');"{% endif %}></div><div>Home</div></a></li>
                        <li><a href="{{ urls.format('SITE_NEWS') }}"><div class="fa fa-newspaper-o"></div><div>News</div></a></li>
                        <li><a href="//chat.{{ sakura.urlMain }}/"><div class="fa fa-commenting"></div><div>Chat</div></a></li>
                        <li><a href="{{ urls.format('FORUM_INDEX') }}"><div class="fa fa-comments"></div><div>Forum</div></a></li>
                        <li><a href="{{ urls.format('MEMBERLIST_INDEX') }}"><div class="fa fa-users"></div><div>Members</div></a></li>
                        <li><a href="{{ urls.format('SITE_SEARCH') }}"><div class="fa fa-search"></div><div>Search</div></a></li>
                        <li><a href="{{ urls.format('SITE_PREMIUM') }}"><div class="fa fa-heart"></div><div>Support us</div></a></li>
                    </ul>
                    <ul class="nav-right floatRight">
                        <li class="nav-usermenu">
                            <a href="#"{% if session.checkLogin %} style="color: {{ user.colour }};"{% endif %}>{% if session.checkLogin %}{{ user.data.username }}{% else %}Guest{% endif %}</a>
                            <ul>
                            {% if session.checkLogin %}
                                <li><a href="{{ urls.format('USER_PROFILE', [user.data.user_id]) }}">My Profile</a></li>
                                <li><a href="{{ urls.format('SETTING_CAT', ['messages']) }}">Private Messages</a></li>
                                <li><a href="{{ urls.format('SETTINGS_INDEX') }}">User Settings</a></li>
                                <li><a href="{{ urls.format('MANAGE_INDEX') }}">Site Management</a></li>
                                <li><a href="{{ urls.format('USER_LOGOUT', [php.time, php.sessionid, sakura.currentPage]) }}">Logout</a></li>
                            {% else %}
                                <li><a href="{{ urls.format('SITE_LOGIN') }}">Login or Register</a></li>
                            {% endif %}
                            </ul>
                        </li>
                        <li><a href="{{ urls.format('USER_PROFILE', [user.data.user_id]) }}" class="ignore"><img src="{{ sakura.contentPath }}/pixel.png" alt="{{ user.data.username }}" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.user_id]) }}');" class="nav-avatar" /></a></li>
                    </ul>
                </div>
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
                        <div>Copyright &copy; 2013-2015 <a href="http://flash.moe/" target="_blank">Flashwave</a>, <a href="http://circlestorm.net/" target="_blank">et al</a>.</div>
                        <div><a href="{{ urls.format('INFO_PAGE', ['terms']) }}">Terms of Service</a> | <a href="{{ urls.format('INFO_PAGE', ['contact']) }}">Contact</a> | <a href="{{ urls.format('SITE_FAQ') }}">FAQ</a> | <a href="{{ urls.format('INFO_PAGE', ['rules']) }}">Rules</a> | <a href="https://sakura.flash.moe/">Changelog</a> | <a href="https://fiistat.us/">Status</a></div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
