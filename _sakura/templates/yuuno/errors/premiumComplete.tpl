{% include 'global/header.tpl' %}
    <div class="content standalone" style="text-align: center;">
        <h1 class="stylised" style="margin: 1em auto;">Thank you for your contribution!</h1>
        <h1 class="fa fa-heart stylised" style="font-size: 20em;"></h1>
        <h3>Your Tenshi will expire on {{ page.expiration|date("l \\t\\h\\e jS F o") }}.</h3>
    </div>
{% include 'global/footer.tpl' %}
