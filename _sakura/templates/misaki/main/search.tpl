{% include 'global/header.tpl' %}
<h1 class="sectionHead">search</h1>
<script>
    (function() {
        var cx = '013363332951923767879:krqdp_wt-eg';
        var gcse = document.createElement('script');
        gcse.type = 'text/javascript';
        gcse.async = true;
        gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//cse.google.com/cse.js?cx=' + cx;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(gcse, s);
    })();
</script>
<gcse:searchbox></gcse:searchbox>
<gcse:searchresults></gcse:searchresults>
{% include 'global/footer.tpl' %}
