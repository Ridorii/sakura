{% include 'global/header.tpl' %}
    <div class="content homepage">
        <div class="content-right content-column">
            {% if user.checklogin %}
                <div class="head">Hi, {{ user.data.username }}!</div>
                <img src="//{{ sakura.urls.main }}/a/{{ user.data.id }}" class="default-avatar-setting homepage-menu-avatar" />
                <ul>
                    <li><a href="//{{ sakura.urls.main }}/settings/profile" class="underline">Edit profile</a></li>
                    <li><a href="//{{ sakura.urls.main }}/settings/avatar" class="underline">Change avatar</a></li>
                    <li><a href="//{{ sakura.urls.main }}/settings/sessions" class="underline">View active sessions</a></li>
                </ul>
                <div class="clear"></div>
            {% else %}
                {% if sakura.lockauth %}
                <div class="head">Whoops!</div>
                You caught the site at the wrong moment! Right now registration <i>and</i> logging in is disabled for unspecified reasons. Sorry for the inconvenience but please try again later!
                {% else %}
                <div class="head">Welcome!</div>
                Welcome to Flashii! This is a site for a bunch of friends to hang out, nothing special. Anyone is pretty much welcome to register so why not have a go?
                <a class="button registerbutton" href="/register">Register!</a>
                <a class="button loginbutton" href="/login">Login</a>
                {% endif %}
            {% endif %}
            <div class="head">Stats</div>
            We have <b>{{ stats.userCount }}</b>, 
            <b><a href="/u/{{ stats.newestUser.id }}" class="default">{{ stats.newestUser.username }}</a></b> is the newest user, 
            it has been <b>{{ stats.lastRegDate }}</b> since the last user registered and 
            there are <b>{{ stats.chatOnline }}</b> in chat right now.
        </div>
        <div class="content-left content-column">
            <div class="head">News <a href="/news.xml" class="fa fa-rss news-rss default"></a></div>
            {% for newsPost in newsPosts %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'flashii';

        /* * * DO NOT EDIT BELOW THIS LINE * * */
        (function () {
            var s = document.createElement('script'); s.async = true;
            s.type = 'text/javascript';
            s.src = '//' + disqus_shortname + '.disqus.com/count.js';
            (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
        }());
    </script>
{% include 'global/footer.tpl' %}
