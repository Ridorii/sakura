/// <reference path="../Sakura.ts" />

namespace Yuuno
{
    export class Editor
    {
        public static InsertBBCode(target: HTMLTextAreaElement, code: string, param: boolean): void
        {
            var start: string = "[" + code + (param ? "=" : "") + "]",
                end: string = "[/" + code + "]",
                selectionLength = Sakura.DOM.GetSelectionLength(target);

            Sakura.DOM.EnterAtCursor(target, start);
            Sakura.DOM.SetPosition(target, Sakura.DOM.GetPosition(target) + selectionLength + start.length);
            Sakura.DOM.EnterAtCursor(target, end);
            Sakura.DOM.SetPosition(target, Sakura.DOM.GetPosition(target) - selectionLength);
            Sakura.DOM.SetPosition(target, Sakura.DOM.GetPosition(target) + selectionLength, true);
        }
    }
}
