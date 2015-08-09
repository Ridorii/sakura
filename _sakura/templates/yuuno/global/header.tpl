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
        <link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/yuuno.css" />
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
        <script type="text/javascript" src="{{ sakura.resources }}/js/yuuno.js"></script>
        <script type="text/javascript">

            // Create an object so we can access certain settings from remote JavaScript files
            var sakuraVars = {

                "cookie": {

                    "prefix":   "{{ sakura.cookieprefix }}",
                    "domain":   "{{ sakura.cookiedomain }}",
                    "path":     "{{ sakura.cookiepath }}"

                },

                "url_main":     "{{ sakura.url_main }}",
                "content":      "{{ sakura.content_path }}",
                "resources":    "{{ sakura.resources }}",

                "minUserLen":       {{ sakura.minusernamelength }},
                "maxUserLen":       {{ sakura.maxusernamelength }},
                "minPwdEntropy":    {{ sakura.minpwdentropy }},
                "checklogin":       {% if user.checklogin %}true{% else %}false{% endif %}

            };

        {% if not user.checklogin and not sakura.lockauth %}

            // Setting the shit so clicking the login link doesn't redirect to /login
            function initHeaderLoginForm() {

                var headerLoginForm = document.getElementById('headerLoginForm');
                var createInput     = document.createElement('input');
                var submit          = headerLoginForm.querySelector('[type="submit"]');

                createInput.setAttribute('name', 'ajax');
                createInput.setAttribute('value', 'true');
                createInput.setAttribute('type', 'hidden');
                headerLoginForm.appendChild(createInput);
                
                submit.setAttribute('type', 'button');
                submit.setAttribute('onclick', 'submitPost(\'headerLoginForm\', true, \'Logging in...\');');

            }

        {% elseif user.checklogin %}

            // Prepare header logout stuff
            function initHeaderLoginForm() {

                var headerLogoutLink = document.getElementById('headerLogoutLink');

                headerLogoutLink.setAttribute('href', 'javascript:void(0);');
                headerLogoutLink.setAttribute('onclick', 'doHeaderLogout();');

            }

            function doHeaderLogout() {

                generateForm("headerLogoutForm", {
                    "class":    "hidden",
                    "method":   "post",
                    "action":   "//{{ sakura.url_main }}/logout"
                },
                {
                    "mode":     "logout",
                    "ajax":     "true",
                    "time":     "{{ php.time }}",
                    "session":  "{{ php.sessionid }}",
                    "redirect": "{{ sakura.currentpage }}"
                }, "contentwrapper");

                setTimeout(function(){
                    submitPost("headerLogoutForm", true, "Logging out...")
                }, 10);

            }

        {% endif %}

        {% if php.self == '/profile.php' and user.checklogin and user.data.id != profile.user.id %}

            // Prepare friend toggle
            function initFriendToggle() {

                var profileFriendToggle = document.getElementById('profileFriendToggle');

                profileFriendToggle.setAttribute('href', 'javascript:void(0);');
                profileFriendToggle.setAttribute('onclick', 'doFriendToggle();');

            }

            function doFriendToggle() {

                generateForm("doFriendToggle", {
                    "class":    "hidden",
                    "method":   "post",
                    "action":   "//{{ sakura.url_main }}/friends"
                },
                {
                    "{% if profile.friend == 0 %}add{% else %}remove{% endif %}": "{{ profile.user.id }}",
                    "ajax":     "true",
                    "time":     "{{ php.time }}",
                    "session":  "{{ php.sessionid }}",
                    "redirect": "{{ sakura.currentpage }}"
                }, "contentwrapper");

                setTimeout(function(){
                    submitPost("doFriendToggle", true, "{% if profile.friend == 0 %}Adding{% else %}Removing{% endif %} friend...")
                }, 10);

            }

        {% endif %}

        // Space for things that need to happen onload
        window.onload = function() {

            // Alter the go to top button
            var gotop = document.getElementById('gotop');
            gotop.setAttribute('href',      'javascript:void(0);');
            gotop.setAttribute('onclick',   'scrollToTop();');

            // Login form under header and ajax logout
            initHeaderLoginForm();

            {% if user.checklogin %}
            // Make notification requests (there's a seperate one to make it happen before the first 60 seconds)
            notifyRequest('{{ php.sessionid }}');
            setInterval(function(){notifyRequest('{{ php.sessionid }}');}, 60000);
            {% endif %}

            {% if php.self == '/profile.php' and user.checklogin and user.data.id != profile.user.id %}
            initFriendToggle();
            {% endif %}

            {% if php.self == '/authenticate.php' and not sakura.lockauth %}
            // AJAX Form Submission            
            var forms = {
                {% if not auth.changingPass %}
                "loginForm": 'Logging in...',
                {% if not sakura.disableregister %}"registerForm": 'Processing registration...',{% endif %}
                {% if not sakura.requireactive %}"resendForm": 'Attempting to resend activation...',{% endif %}
                "passwordForm": 'Sending password recovery mail...'
                {% else %}
                "passwordForm": 'Changing password...'
                {% endif %}
            };

            for(var i in forms) {
                var form    = document.getElementById(i);
                var submit  = form.querySelector('[type="submit"]');

                form.setAttribute('onkeydown', 'formEnterCatch(event, \''+ submit.id +'\');');

                submit.setAttribute('href',     'javascript:void(0);');
                submit.setAttribute('onclick',  'submitPost(\''+ i +'\', true, \''+ forms[i] +'\', '+ (i == 'registerForm' ? 'true' : 'false') +');');
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
                <a class="logo" href="//{{ sakura.url_main }}/">{{ sakura.sitename }}</a>
                <div class="menu">
                    <div class="menu-nav" id="navMenuSite">
                        <!-- Navigation menu, displayed on left side of the bar. -->
                        <a class="menu-item" href="/" title="Return to the front page of Flashii">Home</a>
                        <a class="menu-item" href="/news" title="Here you can read updates on Flashii">News</a>
                        <a class="menu-item" href="//chat.{{ sakura.url_main }}/" title="Chat with other Flashii members">Chat</a>
                        <a class="menu-item" href="/forum" title="Discuss things with other members but static">Forums</a>
                        <a class="menu-item" href="/search" title="Search on Flashii">Search</a>
                        {% if user.checklogin %}
                            <a class="menu-item" href="/members" title="View a list with all the activated user accounts">Members</a>
                            <a class="menu-item menu-donate" href="/support" title="Give us money to keep the site (and other services) up and running">Support us</a>
                        {% endif %}
                    </div>
                    <div class="menu-ucp" id="navMenuUser">
                        <!-- User menu, displayed on right side of the bar. -->
                        {% if user.checklogin %}
                            <a class="menu-item avatar" href="/u/{{ user.data.id }}" title="View and edit your own profile" style="background-image: url('/a/{{ user.data.id }}'); width: auto; color: {{ user.colour }}; font-weight: 700;">{{ user.data.username }}</a>
                            <a class="menu-item" href="/messages" title="Read your private message">Messages</a>
                            <a class="menu-item" href="/manage" title="Manage the site">Manage</a>
                            <a class="menu-item" href="/settings" title="Change your settings">Settings</a>
                            <a class="menu-item" href="/logout?mode=logout&amp;time={{ php.time }}&amp;session={{ php.sessionid }}&amp;redirect={{ sakura.currentpage }}" title="End your login session" id="headerLogoutLink">Logout</a>
                        {% else %}
                            {% if sakura.lockauth %}
                            <div class="menu-item" style="padding-left: 10px; padding-right: 10px;">Authentication is locked</div>
                            {% else %}
                            <a class="menu-item" href="/authenticate" title="Login to Flashii">Login or Register</a>
                            {% endif %}
                        {% endif %}
                    </div>
                    <div class="menu-mob">
                        <a class="menu-item" id="mobileNavToggle" href="javascript:;" onclick="mobileMenu(true);">Open Menu</a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div id="contentwrapper">
                <div id="notifications"></div>
                {% if not user.checklogin and php.self != '/authenticate.php' %}
                    <form method="post" action="/authenticate" id="headerLoginForm" onkeydown="formEnterCatch(event, 'headerLoginButton');">
                        <input type="hidden" name="redirect" value="{{ sakura.currentpage }}" />
                        <input type="hidden" name="session" value="{{ php.sessionid }}" />
                        <input type="hidden" name="time" value="{{ php.time }}" />
                        <input type="hidden" name="mode" value="login" />
                        <div>
                            <label for="headerLoginUserName">Username:</label>
                            <input type="text" id="headerLoginUserName" name="username" class="inputStyling" placeholder="Username" />
                        </div>
                        <div>
                            <label for="headerLoginPassword">Password:</label>
                            <input type="password" id="headerLoginPassword" name="password" class="inputStyling" placeholder="Password" />
                        </div>
                        <div>
                            <input type="checkbox" name="remember" id="headerLoginRemember" />
                            <label for="headerLoginRemember">Remember me</label>
                        </div>
                        <div>
                            <input type="submit" id="headerLoginButton" name="submit" class="inputStyling small" value="Login" />
                        </div>
                    </form>
                {% endif %}
                <noscript>
                    <div class="headerNotify">
                        <h1>You have JavaScript disabled!</h1>
                        <p>A lot of things on this site require JavaScript to be enabled (e.g. the chat), we try to keep both sides happy but it is highly recommended that you enable it (you'll also have to deal with this message being here if you don't enable it).</p>
                    </div>
                </noscript>
