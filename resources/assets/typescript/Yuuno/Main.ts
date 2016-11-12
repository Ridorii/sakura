/// <reference path="../Sakura.d.ts" />

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
        }
    }
}
