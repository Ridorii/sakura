/// <reference path="../Sakura.ts" />

namespace Yuuno
{
    export class Main
    {
        public static Startup()
        {
            Sakura.TimeAgo.Init();
            Sakura.Friend.Init();
            Notifications.Init();
            Busy.Init();

            if (window.location.pathname === '/' || window.location.pathname === '/forum' || window.location.pathname === '/forum/') {
                Ybabstat.Initiate();
            }
        }
    }
}
