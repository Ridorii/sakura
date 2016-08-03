namespace Sakura
{
    export class Changelog
    {
        private static Client: AJAX;
        private static Element: DOM;
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

        public static Build(target: DOM)
        {
            this.Client = new AJAX;
            this.Element = DOM.Create('table', 'changelog panelTable');

            this.Element.Element.style.borderSpacing = '0 1px';

            var title: DOM = DOM.Create('div', 'head'),
                link: DOM = DOM.Create('a', 'underline');

            title.Element.style.marginBottom = '1px';

            link.Text('Changelog');
            (<HTMLLinkElement>link.Element).href = Config.ChangelogUrl + '#r' + Config.Revision;
            (<HTMLLinkElement>link.Element).target = '_blank';

            title.Append(link);
            target.Append(title);
            target.Append(this.Element);

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
            var header: DOM = DOM.Create('tr', 'changelog__row changelog__row--header'),
                headerInner: DOM = DOM.Create('th', 'changelog__header');

            headerInner.Text(changelog.date);
            headerInner.Element.style.fontSize = '1.2em';
            (<HTMLTableHeaderCellElement>headerInner.Element).colSpan = 2;

            header.Append(headerInner);
            this.Element.Append(header);

            for (var _i in changelog.changes)
            {
                var change: IChangelogChange = changelog.changes[_i],
                    row: DOM = DOM.Create('tr', 'changelog__row'),
                    action: DOM = DOM.Create('td', 'changelog__column'),
                    message: DOM = DOM.Create('td', 'changelog__column');

                action.Text(change.action.name);
                action.Element.style.backgroundColor = this.Colours[change.action.id];
                action.Element.style.borderBottom = '1px solid ' + this.Colours[change.action.id];

                message.Text(change.message);
                message.Element.style.borderBottom = '1px solid ' + this.Colours[change.action.id];

                row.Append(action);
                row.Append(message);

                this.Element.Append(row);
            }
        }
    }
}
