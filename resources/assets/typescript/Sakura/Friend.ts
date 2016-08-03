declare var ajaxBusyView: any;

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
                    error: string = response.error || null;

                if (error !== null) {
                    ajaxBusyView(true, error, 'fail');

                    setTimeout(() => {
                        ajaxBusyView(false);
                    }, 1500);
                } else {
                    ajaxBusyView(true, response.message, 'ok');
                    location.reload();
                }
            });
            this.Client.Start(HTTPMethod.POST);
        }
    }
}
