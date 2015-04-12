var illuminati = Array();
var startTime = (new Date()).getTime();

function hideYourMind(conflictions) {
	var twoThousandTwelveIsTheYearWeAscendToSpaceRobots = conflictions.keyCode;
	illuminati.push(twoThousandTwelveIsTheYearWeAscendToSpaceRobots);
	if(illuminati[0] == 68 && illuminati[1] == 73 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 83) {
		$('body').append(
			'<audio autoplay="true">'
			+ '<source type="audio/mp3" src="//cdn.flashii.net/snd/dicks.mp3" />'
			+ '<source type="audio/ogg" src="//cdn.flashii.net/snd/dicks.ogg" />'
			+ '</audio>'
		);
		$('.logo').attr("style", "background: url('//cdn.flashii.net/img/dicksareformyfriends-mio.png') no-repeat scroll left top / cover transparent !important");
		illuminati = Array();
	}
	if(illuminati[0] == 77 && illuminati[1] == 69 && illuminati[2] == 87 && illuminati[3] == 79 && illuminati[4] == 87) {
		$('head').append('<link rel="stylesheet" type="text/css" href="//cdn.flashii.net/css/spinny.css" />');
		$('body').addClass('spinny');
		$('body').append(
			'<audio autoplay="true">'
			+ '<source type="audio/mp3" src="//cdn.flashii.net/snd/mewow.mp3" />'
			+ '<source type="audio/ogg" src="//cdn.flashii.net/snd/mewow.ogg" />'
			+ '</audio>'
		);
		illuminati = Array();
	}
	if(illuminati[0] == 83 && illuminati[1] == 79 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 67 && illuminati[5] == 72 && illuminati[6] == 65 && illuminati[7] == 84) {
		setInterval("meow();", 17);
		
		illuminati = Array();
	}
}

function meow() {
	var diff = (new Date()).getTime() - startTime;
	var vals = [-7*1/Math.cos((diff/500)*(.85*Math.PI)), -7*Math.tan((diff/250)*(.85*Math.PI))];
	
	document.body.style.position = 'absolute';
	document.body.style.left = vals[0] +"px";
	document.body.style.top = vals[1] +"px";
	document.body.style.fontSize = vals[0] +"px";
}

document.addEventListener("onkeydown", hideYourMind, false);
document.addEventListener("keydown", hideYourMind, false);
