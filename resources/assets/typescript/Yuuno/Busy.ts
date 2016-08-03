namespace Yuuno
{
    export class Busy
    {
        private static Container: HTMLElement;
        private static Text: HTMLElement;
        private static Icon: HTMLElement;

        public static Init(): void
        {
            this.Container = Sakura.DOM.ID('busy-window');
            this.Text = Sakura.DOM.ID('busy-status');
            this.Icon = Sakura.DOM.ID('busy-icon');
        }

        public static Hide(): void
        {
            Sakura.DOM.AddClass(this.Container, ['hidden']);
        }

        public static Show(mode: BusyMode = BusyMode.BUSY, text: string = null, hideAfter: number = 0): void
        {
            var icon: string = "fa fa-4x ";

            switch (mode) {
                case BusyMode.OK:
                    icon += 'fa-check';
                    break;
                case BusyMode.FAIL:
                    icon += 'fa-remove';
                    break;
                case BusyMode.BUSY:
                default:
                    icon += 'fa-refresh fa-spin';
            }

            Sakura.DOM.RemoveClass(this.Icon, Sakura.DOM.ClassNames(this.Icon));
            Sakura.DOM.AddClass(this.Icon, icon.split(' '));
            this.Text.innerText = text || '';
            Sakura.DOM.RemoveClass(this.Container, ['hidden']);

            if (hideAfter > 0) {
                setTimeout(Busy.Hide, hideAfter);
            }
        }
    }
}
