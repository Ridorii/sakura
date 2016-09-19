namespace Sakura
{
    export class Dialogue
    {
        public Text: string;
        private Type: DialogueType = DialogueType.Info;
        private Callbacks: Dictionary<DialogueButton, Function> = new Dictionary<DialogueButton, Function>();
        public static Container: string = "dialogues";

        public SetType(type: DialogueType): void
        {
            this.Type = type;
        }

        public AddCallback(button: DialogueButton, func: Function): void
        {
            if (this.Callbacks.Keys.indexOf(button) >= 0) {
                this.Callbacks.Remove(button);
            }

            this.Callbacks.Add(button, func);
        }

        public Display(): void
        {
            var modifiers: string[] = [],
                buttons: DialogueButton[] = [];

            switch (this.Type) {
                case DialogueType.Confirm:
                    modifiers.push('confirm');
                    buttons.push(DialogueButton.No);
                    buttons.push(DialogueButton.Yes);
                    break;

                default:
                    modifiers.push('info');
                    buttons.push(DialogueButton.Ok);
            }

            var container: HTMLDivElement = <HTMLDivElement>DOM.Create('div', DOM.BEM('dialogue', null, modifiers)),
                text: HTMLDivElement = <HTMLDivElement>DOM.Create('div', DOM.BEM('dialogue', 'text')),
                buttonCont: HTMLDivElement = <HTMLDivElement>DOM.Create('div', DOM.BEM('dialogue', 'buttons'));

            DOM.Append(text, DOM.Text(this.Text));
            DOM.Append(container, text);

            for (var btnId in buttons) {
                var btnType: DialogueButton = buttons[btnId],
                    btnText: string = DialogueButton[btnType],
                    button: HTMLButtonElement = <HTMLButtonElement>DOM.Create('button', DOM.BEM('dialogue', 'button'));

                DOM.Append(button, DOM.Text(btnText));
                button.setAttribute('data-type', btnType.toString());
                button.addEventListener("click", (ev: any) => {
                    (this.Callbacks.Get(+ev.target.attributes['data-type'].value)).Value.call(this);
                });

                DOM.Append(buttonCont, button);
            }

            DOM.Append(container, buttonCont);

            DOM.Append(DOM.ID('dialogues'), container);
        }
    }
}
