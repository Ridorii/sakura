var illuminati: Array<number> = new Array<number>();
var startTime: number = (new Date()).getTime();

function hideYourMind(conflictions: KeyboardEvent): void {
    var twoThousandTwelveIsTheYearWeAscendToSpaceRobots: number = conflictions.keyCode;

    illuminati.push(twoThousandTwelveIsTheYearWeAscendToSpaceRobots);

    if (illuminati[0] == 68 && illuminati[1] == 73 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 83) {
        var dicksAre: HTMLAudioElement = document.createElement('audio');
        var forMyFriends: HTMLSourceElement = document.createElement('source');
        var whenTheyCome: HTMLSourceElement = document.createElement('source');

        forMyFriends.type = 'audio/mp3';
        whenTheyCome.type = 'audio/ogg';

        forMyFriends.src = 'https://data.flashii.net/sounds/dicks.mp3';
        whenTheyCome.src = 'https://data.flashii.net/sounds/dicks.ogg';

        dicksAre.appendChild(forMyFriends);
        dicksAre.appendChild(whenTheyCome);

        var toMyHouse: HTMLAudioElement = dicksAre;

        toMyHouse.play();

        illuminati = new Array<number>();
    }

    if (illuminati[0] == 77 && illuminati[1] == 69 && illuminati[2] == 87 && illuminati[3] == 79 && illuminati[4] == 87) {
        var noklz: HTMLAudioElement = document.createElement('audio');
        var von: HTMLSourceElement = document.createElement('source');
        var schnitzel: HTMLSourceElement = document.createElement('source');

        von.type = 'audio/mp3';
        schnitzel.type = 'audio/ogg';

        von.src = 'https://data.flashii.net/sounds/mewow.mp3';
        schnitzel.src = 'https://data.flashii.net/sounds/mewow.ogg';

        noklz.appendChild(von);
        noklz.appendChild(schnitzel);

        noklz.play();

        document.body.style.animation = 'spin 5s infinite linear';

        illuminati = new Array<number>();
    }

    if (illuminati[0] == 83 && illuminati[1] == 79 && illuminati[2] == 67 && illuminati[3] == 75 && illuminati[4] == 67 && illuminati[5] == 72 && illuminati[6] == 65 && illuminati[7] == 84) {
        setInterval(twoThousandSixteenIsTheYearWePhysicallyMergeWithCats, 20);

        illuminati = new Array<number>();
    }
}

function twoThousandSixteenIsTheYearWePhysicallyMergeWithCats() {
    var diff: number = (new Date()).getTime() - startTime;
    var vals: Array<number> = [-7 / Math.cos((diff / 500) * (.85 * Math.PI)), -7 * Math.tan((diff / 250) * (.85 * Math.PI))];

    document.body.style.position = 'absolute';
    document.body.style.left = vals[0] + 'px';
    document.body.style.top = vals[1] + 'px';
    document.body.style.fontSize = vals[0] + 'px';
}

document.addEventListener('keydown', hideYourMind, false);
