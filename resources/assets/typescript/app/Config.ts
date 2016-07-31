namespace Sakura
{
    export class Config
    {
        public static Revision: number = 0;
        public static SessionId: string = "";
        public static UserNameMinLength: number = 3;
        public static UserNameMaxLength: number = 16;
        public static PasswordMinEntropy: number = 48;
        public static LoggedIn: boolean = false;
        public static ChangelogUrl: string = "https://sakura.flash.moe/";
        public static ChangelogApi: string = "api.php/";

        public static Set(config: Object): void
        {
            for (var key in config) {
                this[key] = config[key];
            }
        }
    }
}
