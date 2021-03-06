namespace Yuuno
{
    export class Notifications extends Sakura.Notifications
    {
        private static Container: HTMLElement;

        public static Init(): void
        {
            Sakura.Notifications.DisplayMethod = this.Display;
            super.Init();
            Notifications.Container = Sakura.DOM.ID('notifications');
        }

        public static Display(alert: Sakura.INotification): void
        {
            var id = 'yuuno-alert-' + Date.now(),
                container: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert alert--enter', id),
                iconContent: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__icon-inner'),
                icon: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__icon'),
                inner: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__content'),
                title: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__title'),
                text: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__text'),
                close: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__close'),
                closeIcon: HTMLDivElement = <HTMLDivElement>Sakura.DOM.Create('div', 'alert__close-inner');

            if (alert.image === null) {
                Sakura.DOM.AddClass(iconContent, ['alert__icon-inner--font', 'fa', 'fa-info', 'fa-4x']);
            } else if (alert.image.substring(0, 5) == 'FONT:') {
                Sakura.DOM.AddClass(iconContent, ['alert__icon-inner--font', 'fa', alert.image.replace('FONT:', ''), 'fa-4x']);
            } else {
                iconContent.style.background = "url(0) no-repeat center center / cover transparent".replace('0', alert.image);
                iconContent.style.width = "100%";
                iconContent.style.height = "100%";
                iconContent.className += " alert__icon-inner--image";
            }

            Sakura.DOM.Append(icon, iconContent);
            Sakura.DOM.Append(container, icon);

            title.innerText = alert.title;
            text.innerText = alert.text;

            if (alert.link !== null) {
                inner.setAttribute('onclick', alert.link.substr(0, 11) == 'javascript:' ? alert.link.substring(11) : 'window.location.assign("' + alert.link + '");');
            }

            Sakura.DOM.Append(inner, title);
            Sakura.DOM.Append(inner, text);
            Sakura.DOM.Append(container, inner);

            close.setAttribute('onclick', (alert.id ? 'Sakura.Notifications.Delete(' + alert.id + ');' : '') + 'Yuuno.Notifications.CloseAlert(this.parentNode.id)');

            Sakura.DOM.Append(close, closeIcon);
            Sakura.DOM.Append(container, close);

            Sakura.DOM.Append(Notifications.Container, container);

            if (alert.timeout > 0) {
                setTimeout(() => {
                    if (Sakura.DOM.ID(id)) {
                        if (alert.id) {
                            Sakura.Notifications.Delete(alert.id);
                        }
                        Notifications.CloseAlert(id);
                    }
                }, alert.timeout);
            }
        }

        private static CloseAlert(id: string): void
        {
            var element: HTMLElement = Sakura.DOM.ID(id);
            Sakura.DOM.AddClass(element, ['alert--exit']);
            setTimeout(() => {
                Sakura.DOM.Remove(element);
            }, 410);
        }
    }
}
