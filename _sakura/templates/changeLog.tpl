<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Sakura Changelog</title>
        <style type="text/css">
            body {
                background: #000;
                color: #FFF;
                font: 300 12px/20px "Segoe UI", sans-serif;
                margin: 45px 30px;
                padding: 0;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            a:hover {
                text-decoration: underline;
            }

            h1, h3 {
                font-weight: 100;
            }

            h1 {
                font-size: 5em;
            }

            .changecont {
                background: #FFF;
                position: fixed;
                bottom: 45px;
                left: 30px;
                right: 30px;
                top: 130px;
                border: 1px solid #333;
                background: #111;
                overflow: auto;
            }

            .footer {
                position: fixed;
                bottom: 25px;
                left: 30px;
                right: 30px;
                text-align: center;
            }

            .release > div > span.tag {
                display: inline-block;
                min-width: 100px;
                text-align: center;
                background: #222;
                font-weight: 500;
                letter-spacing: 2px;
                border-right: 1px solid #222;
                box-shadow: inset 0 0 .5em rgba(255, 255, 255, .4);
            }
            .release > div > span.addition-tag {
                background: #2A2;
            }
            .release > div > span.removal-tag {
                background: #A22;
            }
            .release > div > span.fixed-tag {
                background: #2AA;
            }
            .release > div > span.update-tag {
                background: #2AA;
            }

            .release > div > span.changedesc {
                padding: 0 5px;
            }

            .release > div {
                border-bottom: 1px #222 solid;
            }
            .release > div:first-child,
            .release:last-child > div:last-child {
                border-bottom: 0;
            }

            .release > .title {
                font-size: 1.5em;
                line-height: 1.5em;
                background: #222;
                box-shadow: inset 0 0 .5em #444;
                padding: 0 5px 2px;
                display: block;
            }
            .release:not(:first-child) {
                margin-top: 5px;
            }
        </style>
    </head>
    <body>
        <h1 style="color: {{ colour }};">Sakura {{ version_label }}</h1>
        <h3>Installed version: {{ version }} ({{ version_type }})</h3>
        <div class="changecont">
            {{ changeloghtml }}
        </div>
        <div class="footer">
            <a href="http://flash.moe">Flashwave</a> /
            <a href="http://sakura.flashii.net">Sakura</a> /
            <a href="http://github.com/flashii/Sakura">GitHub</a>
        </div>
    </body>
</html>
