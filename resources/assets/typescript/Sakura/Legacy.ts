declare function escape(a);

namespace Sakura
{
    export class Legacy
    {
        // Alternative for Math.log2() since it's still experimental
        public static log2(num: number): number {
            return Math.log(num) / Math.log(2);
        }

        // Get the number of unique characters in a string
        public static unique(string: string): number {
            // Store the already found character
            var used: string[] = [];

            // The amount of characters we've already found
            var count: number = 0;

            // Count the amount of unique characters
            for (var i = 0; i < string.length; i++) {
                // Check if we already counted this character
                if (used.indexOf(string[i]) == -1) {
                    // Push the character into the used array
                    used.push(string[i]);

                    // Up the count
                    count++;
                }
            }

            // Return the count
            return count;
        }

        // Calculate password entropy
        public static entropy(string: string): number {
            // Decode utf-8 encoded characters
            string = this.utf8_decode(string);

            // Count the unique characters in the string
            var unique: number = this.unique(string);

            // Do the entropy calculation
            return unique * this.log2(256);
        }

        // Validate string lengths
        public static stringLength(string: string, minimum: number, maximum: number): boolean {
            // Get length of string
            var length = string.length;

            // Check if it meets the minimum/maximum
            if (length < minimum || length > maximum) {
                return false;
            }

            // If it passes both return true
            return true;
        }

        // Validate email address formats
        public static validateEmail(email: string): boolean {
            // RFC compliant e-mail address regex
            var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,48})+$/;

            // Test it on the email var which'll return a boolean
            return re.test(email);
        }

        // Decode a utf-8 string
        public static utf8_decode(string): string {
            return decodeURIComponent(escape(string));
        }
    }
}
