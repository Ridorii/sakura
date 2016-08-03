namespace Sakura
{
    export class Notifications
    {
        private static Client: AJAX;
        private static IntervalContainer: number;
        public static DisplayMethod: Function = (alert: INotification) => {
            console.log(alert);
        };

        public static Init(): void
        {
            this.Client = new AJAX;
            this.Client.SetUrl("/notifications");
            this.Client.AddCallback(200, (client: AJAX) => {
                Notifications.Load(<INotification[]>client.JSON());
            });
            this.Poll();
            this.Start();
        }

        public static Poll(): void
        {
            this.Client.Start(HTTPMethod.GET);
        }

        public static Start(): void
        {
            this.IntervalContainer = setInterval(() => {
                if (document.hidden) {
                    return;
                }

                Notifications.Poll();
            }, 5000);
        }

        public static Stop(): void
        {
            this.Client.Stop();
            clearInterval(this.IntervalContainer);
        }

        private static Load(alerts: INotification[]): void
        {
            for (var i in alerts) {
                this.DisplayMethod(alerts[i]);
            }
        }
    }
}
