namespace Sakura
{
    export class Friend
    {
        private static Client: AJAX;

        public static Init(): void
        {
            this.Client = new AJAX;
            this.Client.Form();
        }

        public static Add(id: number): void
        {
            this.Mode("/friends/1/add".replace('1', id.toString()));
        }

        public static Remove(id: number): void
        {
            this.Mode("/friends/1/remove".replace('1', id.toString()));
        }

        private static Mode(url: string): void
        {
            this.Client.SetUrl(url);
            this.Client.SetSend({ "session": Config.SessionId });
            this.Client.AddCallback(200, (client: AJAX) => {
                var response: IFriendResponse = <IFriendResponse>client.JSON(),
                    alert: INotification = {
                        id: -(Date.now()),
                        user: Config.UserId,
                        time: Math.round(Date.now() / 1000),
                        read: false,
                        title: response.error || response.message,
                        text: "",
                        link: null,
                        image: "FONT:fa-user-plus",
                        timeout: 60000
                    };

                Notifications.DisplayMethod.call(this, alert);

                // replace this with a thing that just updates the dom
                window.location.reload();
            });
            this.Client.Start(HTTPMethod.POST);
        }
    }
}
