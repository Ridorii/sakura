{% extends 'global/master.tpl' %}

{% block content %}
    {% if page.fail %}
        <div class="headerNotify">
            <h1>The payment failed or was cancelled!</h1>
            <p>Something went wrong while processing the transaction, your PayPal account wasn't charged.</p>
        </div>
    {% endif %}
    <div class="content support">
        <div class="head">Support {{ sakura.siteName }}</div>
        <div style="font-size: .9em; margin-bottom: 10px;">
            <p>In order to keep the site, its services and improvements on it going I need money but I'm not that big of a fan of asking for money without giving anything special in return thus Tenshi exists. Tenshi is the name for our supporter rank which gives you access to an extra set of features (which are listed further down on this page). With your help we can keep adding new stuff, get new hardware and keep the site awesome!</p>
            <h3><a href="{{ urls.format('SITE_DONATE_TRACK') }}" class="default">Ever wonder what happens on the financial side of things? View the donation tracker!</a></h3>
        </div>
        {% if page.current[0] %}
        <div class="sectionHeader">
            Your current Tenshi tag
        </div>
        <div style="margin-bottom: 10px;">
            <h3>{% if page.current[0] == 2 %}Your rank has persistent Tenshi.{% else %}Your Tenshi tag is valid till {{ page.current[2]|date(sakura.dateFormat) }}.{% endif %}</h3>
            <progress value="{{ page.current[0] == 2 ? 100 : (100 - (((php.time - page.current[1]) / (page.current[2] - page.current[1])) * 100)) }}" max="100" style="width: 100%"></progress>
        </div>
        {% endif %}
        <div class="sectionHeader">
            Why should I get Tenshi?
        </div>
        <div class="featureParent">
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-money"></div>
                <div class="featureBoxDesc">Helping us pay for the bills to survive</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-certificate"></div>
                <div class="featureBoxDesc">A <span style="font-weight: bold; color: #EE9400">special</span> name colour to stand out in the crowd</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-magic"></div>
                <div class="featureBoxDesc">The ability to change your username once a month</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-pencil"></div>
                <div class="featureBoxDesc">You can set a custom user title</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-archive"></div>
                <div class="featureBoxDesc">You'll be able to read the chat logs</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-eye-slash"></div>
                <div class="featureBoxDesc">You can create temporary channels in the chat</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-users"></div>
                <div class="featureBoxDesc">You get to create a user group</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox">
                <div class="featureBoxIcon fa fa-picture-o"></div>
                <div class="featureBoxDesc">You get the ability to set a profile background</div>
                <div class="clear"></div>
            </div>
            <div class="featureBox final">
                <div class="featureBoxIcon fa fa-heart"></div>
                <div class="featureBoxIcon right fa fa-heart"></div>
                <div class="featureBoxDesc">The good feeling of helping the staff of your favourite site keep it up and awesome</div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="sectionHeader">
            Payment Options
            <div style="float: right; font-size: 10px; text-align: right;">
                Our transactions are handled through PayPal.
                <div class="paymentOptions fa">
                    <div class="fa-cc-paypal"></div>
                    <div class="fa-cc-visa"></div>
                    <div class="fa-cc-mastercard"></div>
                    <div class="fa-cc-discover"></div>
                    <div class="fa-cc-amex"></div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        {% if session.checkLogin and user.checkPermission('SITE', 'OBTAIN_PREMIUM') %}
            <div class="slider">
                <input class="inputStyling" type="range" min="1" max="{{ page.amount_max }}" value="1" onchange="document.getElementById('monthsNo').value = this.value; document.getElementById('monthNoBtn').innerHTML = this.value; document.getElementById('monthsTrailingS').innerHTML = (this.value == 1 ? '' : 's'); document.getElementById('totalAmount').innerHTML = (this.value * {{ page.price }}).formatMoney(2);" />
            </div>
            <div class="checkout" style="line-height: 28px;">
                <div style="float: left;">
                    <h1 class="stylised">Total: &#8364;<span id="totalAmount"></span></h1>
                </div>
                <div style="float: right;">
                    <button class="inputStyling" onclick="document.getElementById('purchaseForm').submit();">Get <span id="monthNoBtn">1</span> month<span id="monthsTrailingS"></span> of Tenshi</button>
                </div>
                <div class="clear"></div>
            </div>
        {% elseif session.checkLogin %}
            <h1 style="text-align: center; margin: 1em auto;" class="stylised">You can't get Tenshi at the current moment!</h1>
        {% else %}
            <h1 style="text-align: center; margin: 1em auto;" class="stylised">You need to be logged in to get Tenshi!</h1>
        {% endif %}
    </div>
    {% if session.checkLogin and user.checkPermission('SITE', 'OBTAIN_PREMIUM') %}
        <form action="{{ urls.format('SITE_PREMIUM') }}" method="post" id="purchaseForm" class="hidden">
            <input type="hidden" name="mode" value="purchase" />
            <input type="hidden" name="time" value="{{ php.time }}" />
            <input type="hidden" name="session" value="{{ php.sessionid }}" />
            <input type="hidden" name="months" id="monthsNo" value="1" />
        </form>
        <script type="text/javascript">
            window.onload = function() { document.getElementById('totalAmount').innerHTML = ({{ page.price }}).formatMoney(2); };
        </script>
    {% endif %}
{% endblock %}
