namespace Sakura
{
    export class Main
    {
        public static Startup(): void {
            console.log(this.Supported());
            TimeAgo.Init();
            Friend.Init();
            Notifications.Init();
        }

        public static Supported(): boolean {
            return true;
        }
    }
}
