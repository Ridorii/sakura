{% include 'global/header.tpl' %}
    <div class="loginPage">
        {% if auth.redirect == sakura.urls.chat %}<h1 class="stylised" style="line-height: 1.8em; text-align: center;">You need to be logged in to use the chat.</h1>{% endif %}
        <div class="loginCont">
            <div class="loginForm">
                <div class="head">
                    Login to Flashii
                </div>
                <form method="post" action="http://{{ sakura.urls.main }}/authenticate">
                    <input type="hidden" name="redirect" value="{{ auth.redirect }}" />
                    <input type="hidden" name="session" value="{{ php.sessionid }}" />
                    <input type="hidden" name="time" value="{{ php.time }}" />
                    <input type="hidden" name="mode" value="login" />
                    <div class="leftAlign">
                        <label for="loginUserName">Username:</label>
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="text" id="loginUserName" name="username" autofocus="true" />
                    </div>
                    <div class="leftAlign">
                        <label for="loginPassword">Password:</label>
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="password" id="loginPassword" name="password" />
                    </div>
                    <div class="subLinks centreAlign">
                        <input class="inputStyling" name="remember" type="checkbox" class="ignore-css" id="loginRemember" /><label for="loginRemember">Remember Me</a>
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="submit" id="loginButton" name="submit" value="Login" />
                    </div>
                </form>
            </div>
            <div class="passwordForm">
                <div class="head">
                    Lost Password
                </div>
                <form method="post" action="http://{{ sakura.urls.main }}/authenticate">
                    <input type="hidden" name="mode" value="forgotpassword" />
                    <input type="hidden" name="session" value="{{ php.sessionid }}" />
                    <input type="hidden" name="time" value="{{ php.time }}" />
                    <div class="leftAlign">
                        <label for="forgotUserName">Username:</label>
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="text" id="forgotUserName" name="username" />
                    </div>
                    <div class="leftAlign">
                        <label for="forgotEmail">E-mail:</label>
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="text" id="forgotEmail" name="email" />
                    </div>
                    <div class="centreAlign">
                        <input class="inputStyling" type="submit" name="submit" value="Request Password" />
                    </div>
                    <div class="subLinks centreAlign">
                        If you lost access to your e-mail address please <a href="/contact" class="default" target="_blank">contact us</a>.
                    </div>
                </form>
            </div>
        </div>
        <div class="registerForm">
            <div class="head">
                Register on Flashii
            </div>
            <form id="registerForm" method="post" action="http://{{ sakura.urls.main }}/authenticate" style="display:{% if auth.blockRegister.do %}none{% else %}block{% endif %};">
                <input type="hidden" name="mode" value="register" />
                <input type="hidden" name="session" value="{{ php.sessionid }}" />
                <input type="hidden" name="time" value="{{ php.time }}" />
                <div class="leftAlign">
                    <label for="registerUserName">Username:</label>
                </div>
                <div class="centreAlign">
                    <input class="inputStyling" type="text" id="registerUserName" name="username" placeholder="Any character" />
                </div>
                <div class="leftAlign">
                    <label for="registerEmail">E-mail:</label>
                </div>
                <div class="centreAlign">
                    <input class="inputStyling" type="text" id="registerEmail" name="email" placeholder="Used for e.g. password retrieval" />
                </div>
                <div class="leftAlign">
                    <label for="registerPassword">Password:</label>
                </div>
                <div class="centreAlign">
                    <input class="inputStyling" type="password" id="registerPassword" name="password" placeholder="Must be at least 5 characters." />
                </div>
                <div class="leftAlign">
                    <label for="registerConfirmPassword">Confirm Password:</label>
                </div>
                <div class="centreAlign">
                    <input class="inputStyling" type="password" id="registerConfirmPassword" name="confirmpassword" placeholder="Just to make sure" />
                </div>
                <div class="leftAlign">
                    <label for="recaptcha_response_field">Verification:</label>
                </div>
                <div class="centreAlign">
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <div class="g-recaptcha" data-sitekey="{{ sakura.recaptcha_public }}" style="margin: auto; display: inline-block;"></div>
                    <noscript>
                        <div style="width: 302px; height: 352px; margin: auto; display: inline-block;">
                            <div style="width: 302px; height: 352px; position: relative;">
                                <div style="width: 302px; height: 352px; position: absolute;">
                                    <iframe src="https://www.google.com/recaptcha/api/fallback?k={{ sakura.recaptcha_public }}" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;"></iframe>
                                </div>
                                <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
                                <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value=""></textarea>
                            </div>
                        </div>
                      </div>
                    </noscript>
                </div>
                <div class="subLinks centreAlign">
                    <input class="inputStyling" name="tos" type="checkbox" class="ignore-css" id="registerToS" /><label for="registerToS">I agree to the <a class="default" href="/r/terms" target="_blank">Terms of Service</a>.
                </div>
                <div class="centreAlign">
                    <input class="inputStyling" type="submit" name="submit" value="Register" />
                </div>
            </form>
            {% if auth.blockRegister.do %}
            <div class="registerForm" id="registerWarn" style="display: block;">
                <div class="centreAlign">
                    <div class="fa fa-warning fa-5x" style="display: block; margin: 10px 0 0;"></div>
                    <h1>Are you {{ auth.blockRegister.username }}?</h1>
                    <p>Making more than one account is not permitted.</p>
                    <p>If you lost your password please use the form on the bottom left but if you don't already have an account you can go ahead and click the link below to show the registration form this check is based on your IP so in some cases someone may have registered/used the site on this IP already.</p>
                    <p>If we find out that you already have an account we may question you about it, if you can give a good reason we'll let it slide otherwise we may issue a temporary ban.</p>
                </div>
                <div class="subLinks centreAlign">
                    <a href="javascript:;" class="default" onclick="document.getElementById('registerWarn').style.display='none';document.getElementById('registerForm').style.display='block';">Register anyway</a>.
                </div>
            </div>
            {% endif %}
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
