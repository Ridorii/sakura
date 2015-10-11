<html>
    <head>
        <style type="text/css">
            body {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                background: #FBEEFF;
                color: #330066;
            }
            a {
                color: #22E;
                text-decoration: none;
            }
            a:hover {
                color: #22E;
                text-decoration: underline;
            }
            a:active {
                color: #E22;
                text-decoration: underline;
            }
            .head {
                background: #9472B2;
                padding-left: 5px;
                font-size: 1.5em;
                line-height: 1.4em;
                text-align: left;
            }
            .foot {
                background: #9472B2;
                line-height: 1em;
                font-size: .8em;
                padding: 4px 5px;
                text-align: right;
            }
            .cont {
                font-size: 12px;
                line-height: 20px;
            }
        </style>
    </head>
    <body>
        <table style="width: 100%;">
            <tr>
                <td class="head">
                    <strong>{{ sitename }}</strong>
                </td>
            </tr>
            <tr>
                <td class="cont">
                    {{ contents }}
                </td>
            </tr>
            <tr>
                <td class="foot">
                    <strong>This message has been sent to the email address associated with your <a href="{{ siteurl }}">{{ sitename }}</a> account.</strong>
                </td>
            </tr>
        </table>
    </body>
</html>
