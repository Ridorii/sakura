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
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/bootstrap.css" />
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/bootstrap-theme.css" />
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
{{ block('css') }}
        <!-- JS -->
        <script type="text/javascript" src="{{ sakura.contentPath }}/libraries/jquery.js"></script>
        <script type="text/javascript" src="{{ sakura.resources }}/js/bootstrap.js"></script>
{{ block('js') }}
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="logo">
                <a href="/manage/">Broom Closet</a> /
                <a href="/manage/{{ page.activepage }}/">{{ page.pages[page.activepage].desc }}</a> /
                <a href="/manage/{{ page.activepage }}/{{ page.activesub }}/">{{ page.pages[page.activepage].subs[page.activesub].desc }}</a>
            </div>
            <div class="nav">
                <div class="menu" id="siteNav">
                    <div style="color: {{ user.colour }};">{{ user.data.username }}</div>
                    <a href="/">Return to Site Index</a>
                    <a href="/logout?mode=logout&amp;time={{ php.time }}&amp;session={{ php.sessionid }}&amp;redirect=/">Logout</a>
                </div>
                <div class="menu" id="pageNav">
                    <div>Navigation</div>
                    {% for short,page in page.pages %}
                        <a href="/manage/{{ short }}/">{{ page.desc }}</a>
                    {% endfor %}
                </div>
                <div class="menu" id="subNav">
                    <div>{{ page.pages[page.activepage].desc }}</div>
                    {% for short,sub in page.pages[page.activepage].subs %}
                        <a href="/manage/{{ page.activepage }}/{{ short }}/">{{ sub.desc }}</a>
                    {% endfor %}
                </div>
            </div>
        </nav>
        <div id="contentwrapper">
{{ block('content') }}
        </div>
        <div class="footer">
            <div style="color: {{ sakura.versionInfo.colour }};">Sakura b{{ sakura.versionInfo.version }} ({{ sakura.versionInfo.label }}/{{ sakura.versionInfo.stable ? 'Stable' : 'Development' }})</div>
        </div>
    </body>
</html>
