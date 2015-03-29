{% include 'global/header.tpl' %}
    <div class="content homepage">
        <div class="content-right content-column">
            <div class="head">Welcome!</div>
            Welcome to Flashii! This is a site for a bunch of friends to hang out, nothing special. Anyone is pretty much welcome to register so why not have a go?
            <a class="button registerbutton" href="/register">Register!</a>
            <a class="button loginbutton" href="/login">Login</a>
            <div class="head">Stats</div>
            We have <b>{{ frontpage.stats.usercount }}</b>, 
            <b><a href="/u/{{ frontpage.stats.latestid }}" class="default">{{ frontpage.stats.latestname }}</a></b> is the newest user, 
            it has been <b>{{ frontpage.stats.lastsignup }}</b> since the last user registered and 
            there are <b>{{ frontpage.stats.usersinchat }}</b> in chat right now.
        </div>
        <div class="content-left content-column">
            <div class="head">News <a href="/news.xml" class="fa fa-rss news-rss default"></a></div>
            {{ users.1.username }}
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="//{{ sakura.urls.content }}/js/ybabstat.js"></script>
{% include 'global/footer.tpl' %}
