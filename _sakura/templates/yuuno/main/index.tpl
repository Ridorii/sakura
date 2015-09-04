{% include 'global/header.tpl' %}
    <div class="content homepage">
        <div class="content-right content-column">
            {% include 'elements/indexPanel.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">News <a href="{{ urls.format('SITE_NEWS_RSS') }}" class="fa fa-rss news-rss default"></a></div>
            {% for newsPost in newsPosts %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
{% include 'global/footer.tpl' %}
