<!DOCTYPE html>
<html>
    <head>
        <!-- META -->
        <meta charset="{{ sakura.charset }}" />
        <title>{{ page.title }}</title>
        <meta name="description" content="Any community that gets its laughs by pretending to be idiots will eventually be flooded by actual idiots who mistakenly believe that they're in good company. Welcome to Flashii." />
        <meta name="keywords" content="Flashii, Media, Flashwave, Circle, Zeniea, MalwareUp, Cybernetics, Saibateku, Community" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        {% if page.redirect %}
            <meta http-equiv="refresh" content="3; URL={{ page.redirect }}" />
        {% endif %}
        <!-- CSS -->
        <link rel="stylesheet" type="text/css" href="//{{ sakura.urls.content }}/global.css?s={{ php.time }}" />
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/yuuno.css?s={{ php.time }}" />
        <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
        <!-- JS -->
        <script type="text/javascript" src="{{ sakura.resources }}/js/yuuno.js?s={{ php.time }}"></script>
        <script type="text/javascript">
        {% if not user.checklogin %}

            // Setting the shit so clicking the login link doesn't redirect to /login
            function initHeaderLoginForm() {

                var headerLoginForm = document.getElementById('headerLoginForm');
                var headerLoginLink = document.getElementById('headerLoginLink');
                var createInput     = document.createElement('input');

                createInput.setAttribute('name', 'ajax');
                createInput.setAttribute('value', 'true');
                createInput.setAttribute('type', 'hidden');
                headerLoginForm.appendChild(createInput);

                headerLoginLink.setAttribute('href', 'javascript:void(0);');
                headerLoginLink.setAttribute('onclick', 'toggleLoginForm();');

            }

            // Toggling the dynamic login form
            function toggleLoginForm() {

                var headerLoginForm = document.getElementById('headerLoginForm');

                headerLoginForm.className = (headerLoginForm.className == 'hidden' ? '' : 'hidden');

            }

        {% else %}

            // Prepare header logout stuff
            function initHeaderLoginForm() {

                var headerLogoutLink = document.getElementById('headerLogoutLink');

                headerLogoutLink.setAttribute('href', 'javascript:void(0);');
                headerLogoutLink.setAttribute('onclick', 'doHeaderLogout();');

            }
            
            function doHeaderLogout() {

                generateForm("headerLogoutForm", {"class":"hidden","method":"post","action":"//{{ sakura.urls.main }}/logout"},{"mode":"logout","ajax":"true","time":"{{ php.time }}","session":"{{ php.sessionid }}","redirect":"{{ sakura.currentpage }}"},"contentwrapper");

                setTimeout(function(){
                    submitPost("headerLogoutForm", true, "Logging out...")
                }, 10);

            }

        {% endif %}
        // Space for things that need to happen onload
        window.onload = function() {

            // Login form under header and ajax logout
            initHeaderLoginForm();

            {% if php.self == '/authenticate.php' %}
            // AJAX Form Submission            
            var forms = {
                "loginForm": 'Logging in...',
                {% if not sakura.disableregister %}"registerForm": 'Processing registration...',{% endif %}
                {% if not sakura.requireactive %}"resendForm": 'Attempting to resend activation...',{% endif %}
                "passwordForm": 'Sending password recovery mail...'
            };

            for(var i in forms) {
                var form    = document.getElementById(i);
                var submit  = form.querySelector('[type="submit"]');

                // TODO: Make hitting the enter key submit forms
                //form.setAttribute('onkeypress', '');

                submit.setAttribute('href',     'javascript:void(0);');
                submit.setAttribute('onclick',  'submitPost(\''+ i +'\', true, \''+ forms[i] +'\');');
                submit.setAttribute('type',     'button');

                var createInput = document.createElement('input');
                createInput.setAttribute('name', 'ajax');
                createInput.setAttribute('value', 'true');
                createInput.setAttribute('type', 'hidden');
                form.appendChild(createInput);
            }
            {% endif %}

        };
        </script>
    </head>
    <body>
        <div id="container">
            <span id="top"></span>
            <div class="header" id="header">
                <a class="logo" href="/">{{ sakura.sitename }}</a>
                <div class="menu">
                    <div class="menu-nav" id="navMenuSite">
                        <!-- Navigation menu, displayed on left side of the bar. -->
                        <a class="menu-item" href="//{{ sakura.urls.main }}/" title="Return to the front page of Flashii">Home</a>
                        <a class="menu-item" href="//{{ sakura.urls.main }}/news" title="Here you can read updates on Flashii">News</a>
                        <a class="menu-item" href="//{{ sakura.urls.chat }}/" title="Chat with other Flashii members">Chat</a>
                        {% if user.checklogin %}
                            <a class="menu-item" href="//{{ sakura.urls.main }}/members" title="View a list with all the activated user accounts">Members</a>
                            <a class="menu-item menu-donate" href="//{{ sakura.urls.main }}/donate" title="Give us money to keep the site (and other services) up and running">Donate</a>
                        {% endif %}
                    </div>
                    <div class="menu-ucp" id="navMenuUser">
                        <!-- User menu, displayed on right side of the bar. -->
                        {% if user.checklogin %}
                            <a class="menu-item avatar" href="//{{ sakura.urls.main }}/u/{{ user.data.id }}" title="View and edit your own profile" style="background-image: url('//{{ sakura.urls.main }}/a/{{ user.data.id }}'); width: auto; color: {{ user.rank.colour }}; font-weight: 700;">{{ user.data.username }}</a>
                            <a class="menu-item" href="//{{ sakura.urls.main }}/settings" title="Change your settings">Settings</a>
                            <a class="menu-item" href="//{{ sakura.urls.main }}/logout?mode=logout&time={{ php.time }}&session={{ php.sessionid }}&redirect={{ sakura.currentpage }}" title="End your login session" id="headerLogoutLink">Logout</a>
                        {% else %}
                            <a class="menu-item" id="headerLoginLink" href="//{{ sakura.urls.main }}/login" title="Login to Flashii">Login</a>
                            <a class="menu-item" href="//{{ sakura.urls.main }}/register" title="Create an account">Register</a>
                        {% endif %}
                    </div>
                    <div class="menu-mob">
                        <a class="menu-item" id="mobileNavToggle" href="javascript:;" onclick="mobileMenu(true);">Open Menu</a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="contentwrapper">
                {% if not user.checklogin %}
                    <form method="post" action="/authenticate" class="hidden" id="headerLoginForm">
                        <input type="hidden" name="redirect" value="{{ sakura.currentpage }}" />
                        <input type="hidden" name="session" value="{{ php.sessionid }}" />
                        <input type="hidden" name="time" value="{{ php.time }}" />
                        <input type="hidden" name="mode" value="login" />
                        <label for="headerLoginUserName">Username:</label>
                        <input type="text" id="headerLoginUserName" name="username" class="inputStyling" placeholder="Username" />
                        <label for="headerLoginPassword">Password:</label>
                        <input type="password" id="headerLoginPassword" name="password" class="inputStyling" placeholder="Password" />
                        <input type="checkbox" name="remember" id="headerLoginRemember" />
                        <label for="headerLoginRemember">Remember me</label>
                        <input type="button" onclick="submitPost(this.parentNode.id, true, 'Logging in...');" id="headerLoginButton" name="submit" class="inputStyling small" value="Login" />
                    </form>
                {% endif %}
