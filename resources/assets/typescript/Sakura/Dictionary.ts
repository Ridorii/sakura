namespace Sakura
{
    export class Dictionary<TKey, TValue>
    {
        public Keys: TKey[] = [];
        public Values: TValue[] = [];

        public Add(key: TKey, value: TValue): void
        {
            if (this.Keys.indexOf(key) !== -1) {
                return;
            }

            this.Keys.push(key);
            this.Values.push(value);
        }

        public Remove(key: TKey): void
        {
            var index: number = this.Keys.indexOf(key);

            if (index >= 0) {
                this.Keys.splice(index, 1);
                this.Values.splice(index, 1);
            }
        }

        public Reset(): void
        {
            this.Keys = [];
            this.Values = [];
        }

        public Get(key: TKey): KeyValuePair<TKey, TValue>
        {
            var index: number = this.Keys.indexOf(key);

            if (index >= 0) {
                var pair: KeyValuePair<TKey, TValue> = new KeyValuePair<TKey, TValue>();
                pair.Key = this.Keys[index];
                pair.Value = this.Values[index];
                return pair;
            }

            return null;
        }

        public Update(key: TKey, value: TValue): void
        {
            var index: number = this.Keys.indexOf(key);

            if (index >= 0) {
                this.Values[index] = value;
            }
        }
    }
}
