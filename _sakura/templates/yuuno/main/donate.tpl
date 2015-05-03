{% include 'global/header.tpl' %}
    <div class="content donate">
        {#<h1 style="font-size: 5em; line-height: 1.5em;">Note to Flashwave: Don't forget to update this with the proper shit.</h1>#}
        <div class="head">Support Flashii (Get Tenshi)</div>
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
        {% if user.checklogin %}
        <span style="font-size: 8pt;">We do not (and probably will never) accept cryptocurrencies, sorry. Right now the only option is PayPal but we'll try to add more options in the future.</span>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="paypal-donate-form">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----" />
            <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal – The safer, easier way to pay online." style="border: 0;" />
            <img alt="" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" style="border: 0; width: 1px; height: 1px;" />
        </form>{#<h1 style="text-align: center;">&lt;PayPal donate button here&gt;</h1>#}
        {% else %}
            <h1 style="text-align: center;">You need to be logged in to donate!</h1>
        {% endif %}
    </div>
    <script type="text/javascript">window.onload = function() {donatePage();}</script>
{% include 'global/footer.tpl' %}
