/// <reference path="../Sakura/INotification.ts" />

namespace Yuuno
{
    export class Notifications
    {
        private static Container;

        public static RegisterDisplay(): void
        {
            this.Container = new Sakura.DOM('notifications', Sakura.DOMSelector.ID);
            Sakura.Notifications.DisplayMethod = this.Display;
        }

        public static Display(alert: Sakura.INotification): void
        {
            var id = 'yuuno-alert-' + Date.now(),
                container = Sakura.DOM.Create('div', 'notification-enter', id),
                icon = Sakura.DOM.Create('div', 'notification-icon'),
                inner = Sakura.DOM.Create('div', 'notification-content'),
                title = Sakura.DOM.Create('div', 'notification-title'),
                text = Sakura.DOM.Create('div', 'notification-text'),
                close = Sakura.DOM.Create('div', 'notification-close'),
                closeIcon = Sakura.DOM.Create('div'),
                clear = Sakura.DOM.Create('div');
        }
    }
}
