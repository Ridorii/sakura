<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{{ page.title }}</title>
        <meta name="description" content="Any community that gets its laughs by pretending to be idiots will eventually be flooded by actual idiots who mistakenly believe that they're in good company. Welcome to Flashii." />
        <meta name="keywords" content="Flashii, Media, Flashwave, Murasaki, Misaka, Circle, Zeniea, MalwareUp, Cybernetics, Saibateku, Community" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="//{{ sakura.urls.content }}/global.css" />
        <link rel="stylesheet" type="text/css" href="//{{ sakura.urls.content }}/css/yuuno/yuuno.css" />
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
        <!-- JS -->
        <script type="text/javascript" src="//{{ sakura.urls.content }}/js/yuuno.js"></script>
        <script type="text/javascript">
        {% if user.loggedin != true %}
            // Setting the shit so clicking the login link doesn't redirect to /login
            function initLoginForm() {

                var headerLoginLink = document.getElementById('headerLoginLink');

                headerLoginLink.setAttribute('href', 'javascript:;');
                headerLoginLink.setAttribute('onclick', 'toggleLoginForm();');

            }

            // Toggling the dynamic login form
            function toggleLoginForm() {

                var headerLoginForm = document.getElementById('headerLoginForm');

                headerLoginForm.className = (headerLoginForm.className == 'hidden' ? '' : 'hidden');

            }

            // Execute initLoginForm() on load
            window.onload = function(){initLoginForm();};
        {% endif %}
        </script>
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header" id="header">
                <a class="logo" href="/"></a>
                <div class="menu">
                    <div class="menu-nav" id="navMenuSite">
                        <!-- Navigation menu, displayed on left side of the bar. -->
                        <a class="menu-item" href="http://{{ sakura.urls.main }}/" title="Return to the front page of Flashii">Home</a>
                        <a class="menu-item" href="http://{{ sakura.urls.main }}/news" title="Here you can read updates on Flashii">News</a>
                    </div>
                    <div class="menu-ucp" id="navMenuUser">
                        <!-- User menu, displayed on right side of the bar. -->
                        <a class="menu-item" id="headerLoginLink" href="http://{{ sakura.urls.main }}/login" title="Login to Flashii">Login</a>
                        <a class="menu-item" href="http://{{ sakura.urls.main }}/register" title="Create an account">Register</a>
                    </div>
                    <div class="menu-mob">
                        <a class="menu-item" id="mobileNavToggle" href="javascript:;" onclick="mobileMenu(true);">Open Menu</a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="contentwrapper">
                {% if user.loggedin != true %}
                    <div class="hidden" id="headerLoginForm">
                        <form method="post" action="/login">
                            <input type="hidden" name="redirect" value="{{ sakura.currentpage }}" />
                            <input type="hidden" name="session" value="{{ php.sessionid }}" />
                            <input type="hidden" name="time" value="{{ php.time }}" />
                            <label for="headerLoginUserName">Username:</label>
                            <input type="text" id="headerLoginUserName" name="username" class="inputStyling" placeholder="Username" />
                            <label for="headerLoginPassword">Username:</label>
                            <input type="password" id="headerLoginPassword" name="password" class="inputStyling" placeholder="Password" />
                            <input type="submit" id="headerLoginButton" name="submit" class="inputStyling small" value="Login" />
                        </form>
                    </div>
                {% endif %}
