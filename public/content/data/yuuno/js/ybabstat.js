var illuminati  = [];
var startTime   = (new Date()).getTime();

function hideYourMind(conflictions) {

    var twoThousandTwelveIsTheYearWeAscendToSpaceRobots = conflictions.keyCode;

    illuminati.push(twoThousandTwelveIsTheYearWeAscendToSpaceRobots);

    if(illuminati[0] == 68 && illuminati[1] == 73 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 83) {

        var dicksAre        = document.createElement('audio');
        var forMyFriends    = document.createElement('source');
        var whenTheyCome    = document.createElement('source');

        forMyFriends.setAttribute('type', 'audio/mp3');
        whenTheyCome.setAttribute('type', 'audio/ogg');

        forMyFriends.setAttribute('src', sakuraVars.content + '/sounds/dicks.mp3');
        whenTheyCome.setAttribute('src', sakuraVars.content + '/sounds/dicks.ogg');

        dicksAre.appendChild(forMyFriends);
        dicksAre.appendChild(whenTheyCome);

        var toMyHouse = dicksAre;

        toMyHouse.play();

        illuminati = [];

    }

    if(illuminati[0] == 77 && illuminati[1] == 69 && illuminati[2] == 87 && illuminati[3] == 79 && illuminati[4] == 87) {

        var noklz       = document.createElement('audio');
        var von         = document.createElement('source');
        var schnitzel   = document.createElement('source');

        von.setAttribute('type', 'audio/mp3');
        schnitzel.setAttribute('type', 'audio/ogg');

        von.setAttribute('src', sakuraVars.content + '/sounds/mewow.mp3');
        schnitzel.setAttribute('src', sakuraVars.content + '/sounds/mewow.ogg');

        noklz.appendChild(von);
        noklz.appendChild(schnitzel);

        noklz.play();

        document.body.style.animation = 'spin 5s infinite linear';

        illuminati = [];

    }

    if(illuminati[0] == 83 && illuminati[1] == 79 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 67 && illuminati[5] == 72 && illuminati[6] == 65 && illuminati[7] == 84) {

        setInterval("twoThousandSixteenIsTheYearWePhysicallyMergeWithCats();", 17);

        illuminati = [];

    }

}

function twoThousandSixteenIsTheYearWePhysicallyMergeWithCats() {

    var diff = (new Date()).getTime() - startTime;
    var vals = [-7 * 1 / Math.cos((diff / 500) * (.85 * Math.PI)), -7 * Math.tan((diff / 250) * (.85 * Math.PI))];

    document.body.style.position    = 'absolute';
    document.body.style.left        = vals[0] + 'px';
    document.body.style.top         = vals[1] + 'px';
    document.body.style.fontSize    = vals[0] + 'px';

}

document.addEventListener("onkeydown",  hideYourMind, false);
document.addEventListener("keydown",    hideYourMind, false);
