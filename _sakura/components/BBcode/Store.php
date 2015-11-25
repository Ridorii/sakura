<?php
/*
 * BBcode storage format generator/converter
 */

namespace Sakura\BBcode;

class Store
{
    // Text
    private $text;
    private $seed;

    // Special escapes
    protected $escapes = [
        '[' => '&#91;',
        ']' => '&#93;',
        '.' => '&#46;',
        ':' => '&#58;',
    ];

    // Spaces
    protected $spaces = [
        "(^|\s)",
        "((?:\.|\))?(?:$|\s|\n|\r))",
    ];

    // Simple bbcodes
    protected $simple = [
        'b',
        'i',
        'u',
        's',
        'h',
        'img',
        'spoiler',
    ];

    // Constructor
    public function __construct($text = "", $seed = "")
    {
        $this->setText($text);
        $this->seed = $seed;
    }

    // Set text
    public function setText($text)
    {
        $this->text = $text;
    }

    // Colour tag
    public function parseColour($text)
    {
        return preg_replace(
            ",\[(color=(?:#[[:xdigit:]]{6}|[[:alpha:]]+))\](.+?)\[(/color)\],",
            "[\\1:{$this->seed}]\\2[\\3:{$this->seed}]",
            $text
        );
    }

    // Align tag
    public function parseAlign($text)
    {
        return preg_replace(
            ",\[(align=(?:[[:alpha:]]+))\](.+?)\[(/align)\],",
            "[\\1:{$this->seed}]\\2[\\3:{$this->seed}]",
            $text
        );
    }

    // Size tag
    public function parseSize($text)
    {
        return preg_replace(
            ",\[(size=(?:[[:digit:]]+))\](.+?)\[(/size)\],",
            "[\\1:{$this->seed}]\\2[\\3:{$this->seed}]",
            $text
        );
    }

    // Simple tags
    public function parseSimple($text)
    {
        // Parse all simple tags
        foreach ($this->simple as $code) {
            $text = preg_replace(
                "#\[{$code}](.*?)\[/{$code}\]#s",
                "[{$code}:{$this->seed}]\\1[/{$code}:{$this->seed}]",
                $text
            );
        }
        return $text;
    }

    // Code tag
    public function parseCode($text)
    {
        $text = preg_replace_callback(
            "#\[code\](((?R)|.)*?)\[/code\]#s",
            function ($t) {
                $escaped = $this->escape($t[1]);

                return "[code:{$this->seed}]{$escaped}[/code:{$this->seed}]";
            },
            $text
        );

        return $text;
    }

    // Quote tag
    public function parseQuote($text)
    {
        $patterns = ["/\[(quote(?:=&quot;.+?&quot;)?)\]/", '[/quote]'];
        $counts = [preg_match_all($patterns[0], $text), substr_count($text, $patterns[1])];
        $limit = min($counts);

        $text = preg_replace($patterns[0], "[\\1:{$this->seed}]", $text, $limit);
        $text = preg_replace('/' . preg_quote($patterns[1], '/') . '/', "[/quote:{$this->seed}]", $text, $limit);

        return $text;
    }

    public function parseList($text)
    {
        $patterns = ["/\[(list(?:=.+?)?)\]/", '[/list]'];
        $counts = [preg_match_all($patterns[0], $text), substr_count($text, $patterns[1])];
        $limit = min($counts);

        $text = str_replace('[*]', "[*:{$this->seed}]", $text);
        $text = str_replace('[/*]', '', $text);

        $text = preg_replace($patterns[0], "[\\1:{$this->seed}]", $text, $limit);
        $text = preg_replace('/' . preg_quote($patterns[1], '/') . '/', "[/list:o:{$this->seed}]", $text, $limit);

        return $text;
    }

    public function parseEmotes($text)
    {
        $match = [];
        $replace = [];

        foreach ($this->emoticons as $emote) {
            $match[] = '(?<=^|[\n .])' . preg_quote($emote['code'], '#') . '(?![^<>]*>)';
            $replace[] = '<!-- s' . $emote['code'] . ' --><img src="' . $emote['url'] . '" alt="' . $emote['code'] . '" /><!-- s' . $emote['code'] . ' -->';
        }

        if (count($match)) {
            $text = trim(
                preg_replace(
                    explode(
                        chr(0),
                        '#' . implode('#' . chr(0) . '#', $match) . '#'
                    ),
                    $replace,
                    $text
                )
            );
        }

        return $text;
    }

    public function parseUrl($text)
    {
        $urlPattern = '(?:https?|ftp)://.+?';

        $text = preg_replace_callback(
            "#\[url\]({$urlPattern})\[/url\]#",
            function ($m) {
                $url = $this->escape($m[1]);
                return "[url:{$this->seed}]{$url}[/url:{$this->seed}]";
            },
            $text
        );

        $text = preg_replace_callback(
            "#\[url=({$urlPattern})\](.+?)\[/url\]#",
            function ($m) {
                $url = $this->escape($m[1]);
                return "[url={$url}:{$this->seed}]{$m[2]}[/url:{$this->seed}]";
            },
            $text
        );

        return $text;
    }

    public function parseLinks($text)
    {
        // Spaces
        $spaces = ["(^|\s)", "((?:\.|\))?(?:$|\s|\n|\r))"];

        // HTTP(s), FTP, IRC and osu
        $text = preg_replace(
            "#{$spaces[0]}((?:https?|ftp|irc|osu)://[^\s]+?){$spaces[1]}#",
            "\\1<!-- m --><a href='\\2' rel='nofollow'>\\2</a><!-- m -->\\3",
            $text
        );

        // Prefixed with www.
        $text = preg_replace(
            "/{$spaces[0]}(www\.[^\s]+){$spaces[1]}/",
            "\\1<!-- w --><a href='http://\\2' rel='nofollow'>\\2</a><!-- w -->\\3",
            $text
        );

        // E-mail addresses
        $text = preg_replace(
            "/{$spaces[0]}([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z-]+){$spaces[1]}/",
            "\\1<!-- e --><a href='mailto:\\2' rel='nofollow'>\\2</a><!-- m -->\\3",
            $text
        );

        return $text;
    }

    // Escapes
    public function escape($text)
    {
        return str_replace(
            array_keys($this->escapes),
            $this->escapes,
            $text
        );
    }

    // Generator
    public function generate()
    {
        // Get the text
        $text = htmlentities($this->text);

        $text = $this->parseCode($text);
        $text = $this->parseQuote($text);
        $text = $this->parseList($text);

        $text = $this->parseSimple($text);
        $text = $this->parseAlign($text);
        $text = $this->parseUrl($text);
        $text = $this->parseSize($text);
        $text = $this->parseColour($text);

        $text = $this->parseLinks($text);

        return $text;
    }
}
