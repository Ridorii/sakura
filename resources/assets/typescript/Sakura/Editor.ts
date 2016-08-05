namespace Sakura
{
    export class Editor
    {
        private static UpdateTimeout: number = 0;
        private static PreviewClient: AJAX;
        private static PostClient: AJAX;

        public static Prepare(): void
        {
            this.PostClient = new AJAX;
            this.PreviewClient = new AJAX;
            this.PreviewClient.SetUrl("/helper/bbcode/parse");
            this.PreviewClient.Form();
        }

        public static Preview(target: HTMLElement, text: HTMLTextAreaElement): void
        {
            this.PreviewClient.SetSend({"text": text.value});
            this.PreviewClient.AddCallback(200, (client: AJAX) => {
                target.innerHTML = client.Response();
            });
            this.PreviewClient.Start(HTTPMethod.POST);
        }

        public static QuotePost(id: number, username: string, target: HTMLTextAreaElement): void
        {
            this.PostClient.SetUrl("/forum/post/1/raw".replace('1', id.toString()));
            this.PostClient.AddCallback(200, (client: AJAX) => {
                DOM.EnterAtCursor(target, "[quote=" + username + "]" + client.Response() + "[/quote]");
                target.focus();
            });
            this.PostClient.Start(HTTPMethod.GET);
        }

        public static InsertBBCode(target: HTMLTextAreaElement, code: string, param: boolean): void
        {
            var start: string = "[" + code + (param ? "=" : "") + "]",
                end: string = "[/" + code + "]",
                selectionLength = DOM.GetSelectionLength(target);

            DOM.EnterAtCursor(target, start);
            DOM.SetPosition(target, DOM.GetPosition(target) + selectionLength + start.length);
            DOM.EnterAtCursor(target, end);
            DOM.SetPosition(target, DOM.GetPosition(target) - selectionLength);
            DOM.SetPosition(target, DOM.GetPosition(target) + selectionLength, true);
        }

        public static PreviewTimeout(target: HTMLElement, text: HTMLTextAreaElement): void
        {
            if (this.UpdateTimeout !== 0) {
                return;
            }

            this.UpdateTimeout = setTimeout(() => {
                Editor.Preview(target, text);
                clearTimeout(Editor.UpdateTimeout);
                Editor.UpdateTimeout = 0;
            }, 500);
        }
    }
}
