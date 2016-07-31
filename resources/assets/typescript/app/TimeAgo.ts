namespace Sakura
{
    export class TimeAgo
    {
        private static WatchClass: string = "time-ago";

        public static Init(): void
        {
            var watchElements: NodeListOf<Element> = document.getElementsByClassName(this.WatchClass);

            for (var _i in watchElements) {
                if ((typeof watchElements[_i]).toLowerCase() !== 'object') {
                    continue;
                }

                var elem: HTMLElement = <HTMLElement>watchElements[_i],
                    date: Date = new Date(elem.getAttribute('dateTime') || elem.innerText);

                elem.title = elem.innerText;
                elem.innerText = this.Parse(date);
            }
        }

        public static Parse(date: Date, append: string = ' ago', none: string = 'Just now'): string {
            var time: number = (Date.now() - date.getTime()) / 1000;

            if (time < 1) {
                return none;
            }

            var times: Object = {
                31536000: ['year', 'a'],
                2592000: ['month', 'a'],
                604800: ['week', 'a'],
                86400: ['day', 'a'],
                3600: ['hour', 'an'],
                60: ['minute', 'a'],
                1: ['second', 'a']
            };

            var timeKeys: string[] = Object.keys(times).reverse();

            for (var i in timeKeys) {
                var calc: number = time / parseInt(timeKeys[i]);

                if (calc >= 1) {
                    var display: number = Math.floor(calc);

                    return (display === 1 ? times[timeKeys[i]][1] : display) + " " + times[timeKeys[i]][0] + (display === 1 ? '' : 's') + append;
                }
            }

            return none;
        }
    }
}
