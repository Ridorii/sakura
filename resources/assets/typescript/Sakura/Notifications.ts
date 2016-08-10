namespace Sakura
{
    export class Notifications
    {
        private static Client: AJAX;
        private static IntervalContainer: number;
        public static DisplayMethod: Function = Notifications.Display;

        public static Init(): void
        {
            Notifications.Client = new AJAX;
            Notifications.Client.SetUrl("/notifications");
            Notifications.Client.AddCallback(200, (client: AJAX) => {
                Notifications.Load(<INotification[]>client.JSON());
            });
            Notifications.Poll();
            Notifications.Start();
        }

        public static Delete(id: number): void
        {
            var deleter: AJAX = new AJAX;
            deleter.SetUrl("/notifications/" + id + "/mark");
            deleter.Start(HTTPMethod.GET);
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
            }, 60000);
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

        public static Display(alert: INotification): void
        {
            console.log(alert);
        }
    }
}
