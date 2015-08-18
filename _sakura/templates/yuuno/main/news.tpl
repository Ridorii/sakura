{% include 'global/header.tpl' %}
    <div class="content">
        <div class="content-column news">
            <div class="head">{% if page.view_post %}{{ newsPosts[0].title }}{% elseif newsPosts|length < 1 %}Post does not exist!{% else %}News <a href="/news.xml" class="fa fa-rss news-rss default"></a>{% endif %}</div>
            {% if newsPosts|length >= 1 %}
                {% if page.view_post %}
                    {% for newsPost in newsPosts %}
                        {% include 'elements/newsPost.tpl' %}
                    {% endfor %}
                {% else %}
                    {% for newsPost in newsPosts[page.currentPage] %}
                        {% include 'elements/newsPost.tpl' %}
                    {% endfor %}
                {% endif %}
                {% if not page.view_post and newsPosts|length > 1 %}
                    <div>
                        <div class="pagination" style="float: right;">
                            {% if page.currentPage > 0 %}
                                <a href="/news/p{{ page.currentPage }}"><span class="fa fa-step-backward"></span></a>
                            {% endif %}
                            {% for id,npage in newsPosts %}
                                <a href="/news/p{{ id + 1 }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                            {% endfor %}
                            {% if page.currentPage + 1 < newsPosts|length %}
                                <a href="/news/p{{ page.currentPage + 2 }}"><span class="fa fa-step-forward"></span></a>
                            {% endif %}
                        </div>
                        <div class="clear"></div>
                    </div>
                {% endif %}
            {% else %}
                <div style="padding: 20px;">
                    <h1>The requested news post does not exist!</h1>
                    There are a few possible reasons for this;
                    <ul style="margin-left: 30px;">
                        <li>The post may have been deleted due to irrelevancy.</li>
                        <li>The post never existed.</li>
                    </ul>
                </div>
            {% endif %}
        </div>
    {% if page.view_post %}
        <div id="disqus_thread">
        </div>
        <script type="text/javascript">

            var disqus_shortname = '{{ sakura.disqus_shortname }}';
            var disqus_identifier = 'news_{{ newsPosts[0].id }}';
            var disqus_title = '{{ newsPosts[0].title }}';
            var disqus_url = 'http://{{ sakura.url_main }}/news/{{ newsPosts[0].id }}';

            (function() {
                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            })();

            var disqus_config = function() {
                this.page.remote_auth_s3 = '{{ page.disqus_sso }}';
                this.page.api_key = '{{ sakura.disqus_api_key }}';
            }

        </script>
        <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
        <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
    {% else %}
        <script type="text/javascript">

            var disqus_shortname = '{{ sakura.disqus_shortname }}';

            (function () {
                var s = document.createElement('script'); s.async = true;
                s.type = 'text/javascript';
                s.src = '//' + disqus_shortname + '.disqus.com/count.js';
                (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
            }());

        </script>
    {% endif %}
    </div>
{% include 'global/footer.tpl' %}
