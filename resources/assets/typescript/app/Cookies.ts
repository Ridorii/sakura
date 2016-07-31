namespace Sakura
{
    export class Cookies
    {
        public static Get(name: string): string {
            // Do a regex on document.cookie
            var get: RegExpExecArray = new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)').exec(document.cookie);

            // Return the value
            return get ? get[2] : '';
        }

        public static Set(name: string, value: string): void {
            // Check if the cookie already exists
            if (this.Get(name).length > 1) {
                this.Delete(name);
            }

            // Lifetime
            var life: Date = new Date();

            // Cookies live for a year
            life.setFullYear(life.getFullYear() + 1);

            // Assign the cookie
            document.cookie = name + '=' + value + ';expires=' + life.toUTCString();
        }

        public static Delete(name: string): void {
            document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }
    }
}
