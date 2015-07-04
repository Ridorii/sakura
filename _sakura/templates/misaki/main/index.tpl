{% include 'global/header.tpl' %}
<div class="homepage">
    <div class="frontHead">
        <h1 class="sectionHead">a</h1>
    </div>
    <div class="frontStats">
        {# include 'elements/indexRight.tpl' #}
    </div>
    <div class="frontNews">
        {% for newsPost in newsPosts %}
            {% include 'elements/newsPost.tpl' %}
        {% endfor %}
    </div>
    <div class="clear"></div>
    <!--<script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>-->
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'flashii';

        /* * * DO NOT EDIT BELOW THIS LINE * * */
        (function () {
            var s = document.createElement('script');
            s.async = true;
            s.type = 'text/javascript';
            s.src = '//' + disqus_shortname + '.disqus.com/count.js';
            (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
        }());
    </script>
</div>
{% include 'global/footer.tpl' %}
