namespace Sakura
{
    export class Config
    {
        public static UserId: number = 0;
        public static SessionId: string = "";
        public static LoggedIn: boolean = false;
        public static ChangelogUrl: string = "https://sakura.flash.moe/";
        public static ChangelogApi: string = "api.php/";
        public static ForumTitleMin: number = 0;
        public static ForumTitleMax: number = 0;
        public static ForumTextMin: number = 0;
        public static ForumTextMax: number = 0;

        public static Set(config: Object): void
        {
            for (var key in config) {
                this[key] = config[key];
            }
        }
    }
}
