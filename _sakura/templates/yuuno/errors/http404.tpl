<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Cannot find page</title>
		<link rel="stylesheet" type="text/css" href="//{{ sakura.urls.content }}/css/yuuno/error.css" />
	</head>
	<body>
		<audio autoplay="">
			<source src="//{{ sakura.urls.content }}/snd/notfound.ogg" type="audio/ogg" />
			<source src="//{{ sakura.urls.content }}/snd/notfound.mp3" type="audio/mp3" />
		</audio>
		<div id="wrap">
			<h1>
				<img src="//{{ sakura.urls.content }}/img/404-info.gif" />
				The page cannot be found
			</h1>
			<p>
				The page you are looking for might have been removed, had its
				name changed, or is temporarily unavailable.
			</p>
			<hr />
			<h2>
				Please try the following:
			</h2>
			<ul>
				<li>
					If you typed the page address in the Address bar, make
					sure that it is spelled correctly.
				</li>
				<li>
					Open the <a href="/">flashii.net</a>
					home page, and then look for links to the information you want.
				</li>
				<li>
					Click the <img src="//{{ sakura.urls.content }}/img/404-back.gif" /><a href="javascript:;" onclick="history.go(-1);">Back</a>
					button to try another link.
				</li>
				<li>
					Click <img src="//{{ sakura.urls.content }}/img/404-search.gif" /><a href="http://www.bing.com/search?q=flashii">Search</a>
					to look for information on the Internet.
				</li>
			</ul>
			<h3>
				HTTP 404 - File not found
				<br />
				Internet Explorer
			</h3>
		</div>
	</body>
</html>