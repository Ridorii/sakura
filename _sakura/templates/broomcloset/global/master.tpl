<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{% block title %}{{ sakura.siteName }}{% endblock %}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
{% if page.redirect %}
        <meta http-equiv="refresh" content="{{ page.redirectTimeout ? page.redirectTimeout : '3' }}; URL={{ page.redirect }}" />
{% endif %}
{{ block('meta') }}
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/broomcloset.css" />
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/bootstrap-theme.css" />
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
{{ block('css') }}
        <!-- JS -->
        <script type="text/javascript" charset="utf-8" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" charset="utf-8" src="{{ sakura.resources }}/js/bootstrap.js"></script>
{{ block('js') }}
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-static-top">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="{{ urls.format('MANAGE_INDEX') }}">The Broomcloset</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-left">
                        <li{% if manage.category == 'dashboard' %} class="active"{% endif %}><a href="{{ urls.format('MANAGE_INDEX') }}">Dashboard</a></li>
                        <li{% if manage.category == 'news' %} class="active"{% endif %}><a href="{{ urls.format('MANAGE_CAT', ['news']) }}">News</a></li>
                        <li class="dropdown{% if manage.category == 'configuration' %} active{% endif %}">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configuration <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('MANAGE_MODE', ['configuration', 'general']) }}">General</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['configuration', 'security']) }}">Security</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['configuration', 'authentication']) }}">Authentication</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['configuration', 'appearance']) }}">Appearance</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['configuration', 'performance']) }}">Performance</a></li>
                            </ul>
                        </li>
                        <li{% if manage.category == 'reports' %} class="active"{% endif %}><a href="{{ urls.format('MANAGE_CAT', ['reports']) }}">Reports</a></li>
                        <li{% if manage.category == 'infopages' %} class="active"{% endif %}><a href="{{ urls.format('MANAGE_CAT', ['infopages']) }}">Info pages</a></li>
                        <li class="dropdown{% if manage.category == 'users' %} active{% endif %}">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Users <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('MANAGE_MODE', ['users', 'manage']) }}">Manage users</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['users', 'ranks']) }}">Manage ranks</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['users', 'groups']) }}">Manage groups</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['users', 'warnings']) }}">Warnings</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['users', 'bans']) }}">Bans</a></li>
                            </ul>
                        </li>
                        <li class="dropdown{% if manage.category == 'forums' %} active{% endif %}">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Forums <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('MANAGE_MODE', ['forums', 'manage']) }}">Manage forums</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['forums', 'moderate']) }}">Moderate forums</a></li>
                            </ul>
                        </li>
                        <li class="dropdown{% if manage.category == 'permissions' %} active{% endif %}">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Permissions <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('MANAGE_MODE', ['permissions', 'global']) }}">Global permissions</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['permissions', 'forums']) }}">Forum permissions</a></li>
                            </ul>
                        </li>
                        <li class="dropdown{% if manage.category == 'logs' %} active{% endif %}">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Logs <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('MANAGE_MODE', ['logs', 'all']) }}">Full log</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['logs', 'management']) }}">Management logs</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['logs', 'errors']) }}">Error logs</a></li>
                                <li><a href="{{ urls.format('MANAGE_MODE', ['logs', 'user']) }}">User logs</a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color: {{ user.colour }};">{{ user.data.username }} <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ urls.format('USER_PROFILE', [user.data.id]) }}">View Profile</a></li>
                                <li><a href="{{ urls.format('SETTINGS_INDEX') }}">Site settings</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ urls.format('SITE_HOME') }}">Back to site</a></li>
                                <li><a href="{{ urls.format('USER_LOGOUT', [php.time, php.sessionid, urls.format('SITE_HOME')]) }}">Logout</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ urls.format('CHANGELOG') }}#r{{ sakura.versionInfo.version }}" style="color: {{ sakura.versionInfo.colour }};" title="{{ sakura.versionInfo.label }} {{ sakura.versionInfo.stable ? 'Stable' : 'Development' }}">Sakura r{{ sakura.versionInfo.version }}</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
{{ block('content') }}
        </div>
    </body>
</html>
