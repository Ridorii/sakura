<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Sakura Internal Error</title>
        <style type="text/css">
            body {  
                margin: 0;
                padding: 0;
                background: #EEE;
                color: #000;
                font: 12px/20px Verdana, Arial, Helvetica, sans-serif;
            }

            h1 {
                font-weight: 100;
                background: #CAA;
                padding: 8px 5px 10px;
                margin: 0;
            }

            .container {
                border: 1px solid #CAA;
                margin: 10px;
                background: #FFF;
                box-shadow: 2px 2px 1em #888;
            }

            .container .inner {
                padding: 0px 10px;
            }

            .container .inner .error {
                background: #555;
                color: #EEE;
                border-left: 5px solid #C22;
                padding: 4px 6px 5px;
                text-shadow: 0px 1px 1px #888;
                font-family: monospace;
            }

            .container .contact {
                border-top: 1px solid #CAA;
                font-size: x-small;
                padding: 0px 5px 1px;
            }

            a {
                color: #77E;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            a:active {
                color: #E77;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>An Error occurred while executing the script.</h1>
            <div class="inner">
                <p>To prevent potential security risks PHP has stopped execution of the script.</p>
                <p>PHP Reported the following error log:</p>
                <div class="error">
                    {{ error }}
                </div>
                <p>If you have an account on <a href="https://github.com/" target="_blank">GitHub</a> please go to the <a href="https://github.com/circlestorm/Sakura/issues" target="_blank">issues section</a> and report the error listed above (do a check to see if it hasn't been reported yet as well).</p>
            </div>
            <div class="contact">
                Contact the System Operator at <a href="mailto:me@flash.moe">me@flash.moe</a> or check our <a href="http://status.flashii.net/" target="_blank">Status Page</a> and <a href="http://twitter.com/_flashii" target="_blank">Twitter Account</a> to see if anything is going on.
            </div>
        </div>
    </body>
</html>
