namespace Sakura
{
    export class DOM
    {
        public Element: HTMLElement;

        constructor(object: any, mode: DOMSelector) {
            switch (mode) {
                case DOMSelector.ID:
                    this.Element = document.getElementById(object);
                    break;

                case DOMSelector.CLASS:
                    this.Element = <HTMLElement>document.getElementsByClassName(object)[0];
                    break;

                case DOMSelector.ELEMENT:
                    this.Element = object;
                    break;

                case DOMSelector.QUERY:
                    this.Element = <HTMLElement>document.querySelector(object);
                    break;
            }
        }

        public static Create(element: string, className: string = null, id: string = null): DOM {
            var elem: HTMLElement = document.createElement(element),
                cont: DOM = new DOM(elem, DOMSelector.ELEMENT);

            if (className !== null) {
                cont.SetClass(className);
            }

            if (id !== null) {
                cont.SetId(id);
            }

            return cont;
        }

        public Text(text: string): void {
            this.Element.appendChild(document.createTextNode(text));
        }

        public Append(element: DOM): void {
            this.Element.appendChild(element.Element);
        }

        public Prepend(element: DOM, before: HTMLElement | Node = null): void {
            if (before === null) {
                before = this.Element.firstChild;
            }

            if (this.Element.children.length) {
                this.Element.insertBefore(element.Element, before);
            } else {
                this.Append(element);
            }
        }

        public SetId(id: string): void {
            this.Element.id = id;
        }

        public SetClass(name: string): void {
            this.Element.className = name;
        }

        public Remove(): void {
            this.Element.parentNode.removeChild(this.Element);
        }
    }
}
