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
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/mio.css" />
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
        <script type="text/javascript" src="{{ sakura.resources }}/js/mio.js"></script>
	</head>
    <body>
        <div class="flashii-bar">
            <a href="//{{ sakura.urls.main }}/login"<!-- onclick="flashii_login(true);"-->Login</a> |
            <a href="//{{ sakura.urls.main }}/register">Register</a>
        </div>
		<a href="//{{ sakura.urls.main }}/">
			<img class="logo" src="//{{ sakura.urls.content }}/pixel.png" alt="{{ sakura.sitename }}" />
		</a>
		<br />
