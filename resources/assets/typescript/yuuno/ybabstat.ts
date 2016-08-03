/// <reference path="../Sakura.ts" />

namespace Yuuno
{
    export class Ybabstat
    {
        private static Illuminati: number[] = [];
        private static FreeMason: number = Date.now();

        public static Initiate(): void
        {
            document.addEventListener('keydown', Ybabstat.HideYourMind);
        }

        private static TwoThousandSixteenIsTheYearWePhysicallyMergeWithCats(): void
        {
            var diff: number = Date.now() - Ybabstat.FreeMason,
                vals: number[] = [
                    -7 / Math.cos((diff / 500) * (.85 * Math.PI)),
                    -7 * Math.tan((diff / 250) * (.85 * Math.PI))
                ];

            document.body.style.position = 'absolute';
            document.body.style.left = vals[0] + 'px';
            document.body.style.top = vals[1] + 'px';
            document.body.style.fontSize = vals[0] + 'px';
        }

        private static HideYourMind(conflictions: KeyboardEvent): void
        {
            var twoThousandTwelveIsTheYearWeAscendToSpaceRobots: number = conflictions.keyCode;

            Ybabstat.Illuminati.push(twoThousandTwelveIsTheYearWeAscendToSpaceRobots);

            if (Ybabstat.Illuminati[0] === 68 && Ybabstat.Illuminati[1] === 73 && Ybabstat.Illuminati[2] === 67 && Ybabstat.Illuminati[3] === 75 && Ybabstat.Illuminati[4] === 83) {
                var dicksAreForMy: HTMLAudioElement = <HTMLAudioElement>Sakura.DOM.Create('audio'),
                    friendsWhenThey: HTMLSourceElement = <HTMLSourceElement>Sakura.DOM.Create('source'),
                    comeToMyHouse: HTMLSourceElement = <HTMLSourceElement>Sakura.DOM.Create('source');

                friendsWhenThey.type = 'audio/mp3';
                comeToMyHouse.type = 'audio/ogg';

                friendsWhenThey.src = 'https://data.flashii.net/assets/sounds/dicks.mp3';
                comeToMyHouse.src = 'https://data.flashii.net/assets/sounds/dicks.ogg';

                Sakura.DOM.Append(dicksAreForMy, friendsWhenThey);
                Sakura.DOM.Append(dicksAreForMy, comeToMyHouse);

                dicksAreForMy.play();

                Ybabstat.Illuminati = [];
            }

            if (Ybabstat.Illuminati[0] === 77 && Ybabstat.Illuminati[1] === 69 && Ybabstat.Illuminati[2] === 87 && Ybabstat.Illuminati[3] === 79 && Ybabstat.Illuminati[4] === 87) {
                var noklz: HTMLAudioElement = <HTMLAudioElement>Sakura.DOM.Create('audio'),
                    von: HTMLSourceElement = <HTMLSourceElement>Sakura.DOM.Create('source'),
                    schnitzel: HTMLSourceElement = <HTMLSourceElement>Sakura.DOM.Create('source');

                von.type = 'audio/mp3';
                schnitzel.type = 'audio/ogg';

                von.src = 'https://data.flashii.net/assets/sounds/mewow.mp3';
                schnitzel.src = 'https://data.flashii.net/assets/sounds/mewow.ogg';

                Sakura.DOM.Append(noklz, von);
                Sakura.DOM.Append(noklz, schnitzel);

                noklz.play();

                document.body.style.animation = 'spin 5s infinite linear';

                Ybabstat.Illuminati = [];
            }

            if (Ybabstat.Illuminati[0] == 83 && Ybabstat.Illuminati[1] == 79 && Ybabstat.Illuminati[2] == 67 && Ybabstat.Illuminati[3] == 75 && Ybabstat.Illuminati[4] == 67 && Ybabstat.Illuminati[5] == 72 && Ybabstat.Illuminati[6] == 65 && Ybabstat.Illuminati[7] == 84) {
                setInterval(Ybabstat.TwoThousandSixteenIsTheYearWePhysicallyMergeWithCats, 20);

                Ybabstat.Illuminati = [];
            }
        }
    }
}
