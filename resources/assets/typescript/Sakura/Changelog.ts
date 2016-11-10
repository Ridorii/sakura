namespace Sakura
{
    export class Changelog
    {
        private static Client: AJAX;
        private static Element: HTMLDivElement;
        private static Fetch: number = 0;
        private static Colours: string[] = [
            'inherit', // Unknown
            '#2A2', // Add
            '#2AA', // Update
            '#2AA', // Fix
            '#A22', // Remove
            '#62C', // Export
            '#C44', // Revert
        ];

        public static Build(target: HTMLElement)
        {
            this.Client = new AJAX;
            this.Element = <HTMLDivElement>DOM.Create('table', 'changelog sidepanel-table');

            this.Element.style.borderSpacing = '0 1px';

            var title: HTMLDivElement = <HTMLDivElement>DOM.Create('div', 'content__header content__header--alt'),
                link: HTMLLinkElement = <HTMLLinkElement>DOM.Create('a', 'underline');

            title.style.marginBottom = '1px';

            link.innerText = 'Changelog';
            link.href = Config.ChangelogUrl;
            link.target = '_blank';
            link.style.color = 'inherit';

            DOM.Append(title, link);
            DOM.Append(target, title);
            DOM.Append(target, this.Element);

            this.Client.SetUrl(Config.ChangelogUrl + Config.ChangelogApi);

            this.Client.AddCallback(200, (client: AJAX) => {
                Changelog.Add(<IChangelogDate>client.JSON());

                if (Changelog.Fetch < 2) {
                    Changelog.Fetch++;
                    Changelog.Client.SetUrl(Config.ChangelogUrl + Config.ChangelogApi + Changelog.Fetch);
                    Changelog.Client.Start(HTTPMethod.GET);
                }
            });

            this.Client.SetUrl(Config.ChangelogUrl + Config.ChangelogApi + Changelog.Fetch);
            this.Client.Start(HTTPMethod.GET);
        }

        private static Add(changelog: IChangelogDate)
        {
            var header: HTMLTableRowElement = <HTMLTableRowElement>DOM.Create('tr', 'changelog__row changelog__row--header'),
                headerInner: HTMLTableHeaderCellElement = <HTMLTableHeaderCellElement>DOM.Create('th', 'changelog__header sidepanel-table__head');

            headerInner.innerText = changelog.date;
            headerInner.style.fontSize = '1.2em';
            headerInner.colSpan = 2;

            DOM.Append(header, headerInner);
            DOM.Append(this.Element, header);

            for (var _i in changelog.changes)
            {
                var change: IChangelogChange = changelog.changes[_i],
                    row: HTMLTableRowElement = <HTMLTableRowElement>DOM.Create('tr', 'changelog__row'),
                    action: HTMLTableCellElement = <HTMLTableCellElement>DOM.Create('td', 'changelog__column sidepanel-table__column'),
                    message: HTMLTableCellElement = <HTMLTableCellElement>DOM.Create('td', 'changelog__column sidepanel-table__column');

                action.innerText = change.action.name;
                action.style.backgroundColor = this.Colours[change.action.id];
                action.style.borderBottom = '1px solid ' + this.Colours[change.action.id];

                message.innerText = change.message;
                message.style.borderBottom = '1px solid ' + this.Colours[change.action.id];
                message.style.textAlign = 'left';

                DOM.Append(row, action);
                DOM.Append(row, message);
                DOM.Append(this.Element, row);
            }
        }
    }
}
