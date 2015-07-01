{% include 'global/header.tpl' %}
    {% if page.fail %}
    <div class="headerNotify">
        <h1>The payment failed or was cancelled!</h1>
        <p>Something went wrong while processing the transaction, your PayPal account wasn't charged.</p>
    </div>
    {% endif %}
    <div class="content donate">
        <div class="head">Support Flashii</div>
        <div style="font-size: .9em; margin-bottom: 10px;">
            <p>In order to keep the site, its services and improvements on it going I need money but I'm not that big of a fan of asking for money without really giving anything special in return.</p>
            <p>Thus Tenshi exists. Tenshi is the &quot;premium&quot; user group on Flashii which gives you access to an extra set of features (which are listed further down on this page).</p>
            <p>A set of new features is planned as well! Be sure to look out for them.</p>
        </div>
        <div class="sectionHeader">
            Why should I get Tenshi?
            <div style="float: right; font-size: 10px; text-align: right;"> 
                Click a box to expand its contents.
            </div>
        </div>
        <div class="featureParent">
            {% for id,box in page.whytenshi %}
            <div class="featureBox" id="fbw{{ id }}" onclick="donatePage(this.id);">
                <div class="featureBoxHeader">
                    {{ box[0]|raw }}
                </div>
                <div class="featureBoxDesc">
                    {{ box[1]|raw }}
                </div>
            </div>
            {% endfor %}
        </div>
        <div class="sectionHeader">
            What do I get in return?
            <div style="float: right; font-size: 10px; text-align: right;">
                Click a box to expand its contents.
            </div>
        </div>
        <div class="featureParent">
            {% for id,box in page.tenshifeatures %}
            <div class="featureBox" id="fbr{{ id }}" onclick="donatePage(this.id);">
                <div class="featureBoxHeader">
                    {{ box[0]|raw }}
                </div>
                <div class="featureBoxDesc">
                    {{ box[1]|raw }}
                </div>
            </div>
            {% endfor %}
        </div>
        <div class="sectionHeader">Payment Options</div>
        {% if user.checklogin and perms.canGetPremium %}
        <span style="font-size: 8pt;">Our transactions are handled through PayPal.</span>
        <form action="/support" method="post" style="text-align: center;">
            <input type="hidden" name="mode" value="purchase" />
            <input type="hidden" name="time" value="{{ php.time }}" />
            <input type="hidden" name="session" value="{{ php.sessionid }}" />
            <input type="number" name="months" /> <input type="submit" value="throw money at flashwave" />
        </form>
        {% elseif user.checklogin %}
            <h1 style="text-align: center; margin: 1em auto;" class="stylised">You can't get Tenshi at the current moment!</h1>
        {% else %}
            <h1 style="text-align: center; margin: 1em auto;" class="stylised">You need to be logged in to get Tenshi!</h1>
        {% endif %}
    </div>
    <script type="text/javascript">window.onload = function() {donatePage();}</script>
{% include 'global/footer.tpl' %}
