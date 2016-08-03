namespace Sakura
{
    export class DOM
    {
        public static BEM(block: string, element: string = null, modifiers: string[] = [], firstModifierOnly: boolean = false): string
        {
            var className: string = "";

            if (firstModifierOnly && modifiers.length === 0) {
                return null;
            }

            className += block;

            if (element !== null) {
                className += "__" + element;
            }

            var baseName: string = className;

            for (var _i in modifiers) {
                if (firstModifierOnly) {
                    return baseName + "--" + modifiers[_i];
                }

                className += " " + baseName + "--" + modifiers[_i];
            }

            return className;
        }

        public static Create(name: string, className: string = null, id: string = null): HTMLElement {
            var element = document.createElement(name);

            if (className !== null) {
                element.className = className;
            }

            if (id !== null) {
                element.id = id;
            }

            return element;
        }

        public static Text(text: string): Text {
            return document.createTextNode(text);
        }

        public static ID(id: string): HTMLElement {
            return document.getElementById(id);
        }

        public static Remove(element: HTMLElement): void {
            element.parentNode.removeChild(element);
        }

        public static Class(className: string): NodeListOf<HTMLElement> {
            return <NodeListOf<HTMLElement>>document.getElementsByClassName(className);
        }

        public static Prepend(target: HTMLElement, element: HTMLElement | Text, before: HTMLElement | Node = null): void {
            if (before === null) {
                before = target.firstChild;
            }

            if (target.children.length) {
                target.insertBefore(element, before);
            } else {
                this.Append(target, element);
            }
        }

        public static Append(target: HTMLElement, element: HTMLElement | Text): void {
            target.appendChild(element);
        }

        public static ClassNames(target: HTMLElement): string[] {
            var className: string = target.className,
                classes: string[] = [];

            if (className.length > 1) {
                classes = className.split(' ');
            }

            return classes;
        }

        public static AddClass(target: HTMLElement, classes: string[]): void {
            for (var _i in classes) {
                var current: string[] = this.ClassNames(target),
                    index: number = current.indexOf(classes[_i]);

                if (index >= 0) {
                    continue;
                }

                current.push(classes[_i]);

                target.className = current.join(' ');
            }
        }

        public static RemoveClass(target: HTMLElement, classes: string[]): void {
            for (var _i in classes) {
                var current: string[] = this.ClassNames(target),
                    index: number = current.indexOf(classes[_i]);

                if (index < 0) {
                    continue;
                }

                current.splice(index, 1);

                target.className = current.join(' ');
            }
        }

        public static Clone(subject: HTMLElement): HTMLElement {
            return (<HTMLElement>subject.cloneNode(true));
        }

        public static Query(query: string): NodeListOf<Element> {
            return document.querySelectorAll(query);
        }

        public static SetPosition(element: HTMLTextAreaElement, pos: number, end: boolean = false): void {
            if (end) {
                element.selectionEnd = pos;
                return;
            }

            element.selectionStart = pos;
        }

        public static GetPosition(element: HTMLTextAreaElement, end: boolean = false): number {
            if (end) {
                return element.selectionEnd;
            }

            return element.selectionStart;
        }

        public static GoToStart(element: HTMLTextAreaElement): void {
            this.SetPosition(element, 0);
        }

        public static GoToEnd(element: HTMLTextAreaElement): void {
            this.SetPosition(element, element.value.length);
        }

        public static GetSelectionLength(element: HTMLTextAreaElement): number {
            var length: number = this.GetPosition(element, true) - this.GetPosition(element);

            if (length < 0) {
                length = this.GetPosition(element) - this.GetPosition(element, true);
            }

            return length;
        }

        public static EnterAtCursor(element: HTMLTextAreaElement, text: string, overwrite: boolean = false): void {
            var value: string = this.GetText(element),
                final: string = "",
                current: number = this.GetPosition(element);

            final += value.slice(0, current);
            final += text;
            final += value.slice(current + (overwrite ? text.length : 0));

            this.SetText(element, final);
            this.SetPosition(element, current);
        }

        public static GetSelectedText(element: HTMLTextAreaElement): string {
            return this.GetText(element).slice(this.GetPosition(element), this.GetPosition(element, true));
        }

        public static GetText(element: HTMLTextAreaElement): string {
            return element.value;
        }

        public static SetText(element: HTMLTextAreaElement, text: string): void {
            element.value = text;
        }
    }
}
