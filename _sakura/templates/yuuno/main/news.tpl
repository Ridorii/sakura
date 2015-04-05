{% include 'global/header.tpl' %}
    <div class="content">
        <div class="content-column news">
            <div class="head">News <a href="/news.xml" class="fa fa-rss news-rss default"></a></div>
            {% for newsPost in newsPosts %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
    </div>
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
    <div id="disqus_thread">
    </div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'flashii';
		var disqus_identifier = 'news_".$getNews[0]['id']."';
		var disqus_title = '".$getNews[0]['title']."';
		var disqus_url = 'http://".$_SERVER['HTTP_HOST']."news".$getNews[0]['id']."';
		
        /* * * DO NOT EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
    
{% include 'global/footer.tpl' %}
