<?php
/*
 * BBcode parser
 */

namespace Sakura\BBcode;

class Parse
{
    // Text
    private $text;
    private $seed;

    // Simple bbcodes
    private $simple = [
        'b' => 'strong',
        'i' => 'em',
        'u' => 'u',
        's' => 'del',
        'h' => 'h2',
    ];

    // Advanced bbcodes
    private $advanced = [
        'spoiler' => '<span class="spoiler">|</span>',
    ];

    public function __construct($text = "", $seed = '9001')
    {
        $this->setText($text);
        $this->seed = $seed;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function parseImage($text)
    {
        return preg_replace_callback(
            "#\[img:{$this->seed}\](?<url>[^[]+)\[/img:{$this->seed}\]#",
            function ($i) {
                return "<img src=\"{$i['url']}\" alt=\"{$i['url']}\" />";
            },
            $text
        );
    }

    public function parseList($text)
    {
        $text = preg_replace("#\[list=\d+:{$this->seed}\]#", '<ol>', $text);
        $text = preg_replace("#\[list(=.?)?:{$this->seed}\]#", "<ol class='unordered'>", $text);
        $text = preg_replace("#\[/\*(:m)?:{$this->seed}\]\n?#", '</li>', $text);
        $text = str_replace("[*:{$this->seed}]", '<li>', $text);
        $text = str_replace("[/list:o:{$this->seed}]", '</ol>', $text);
        $text = str_replace("[/list:u:{$this->seed}]", '</ol>', $text);

        return $text;
    }

    public function parseCode($text)
    {
        return preg_replace_callback(
            "#[\r|\n]*\[code:{$this->seed}\][\r|\n]*(.*?)[\r|\n]*\[/code:{$this->seed}\][\r|\n]*#s",
            function ($c) {
                return '<pre class="prettyprint linenums">' . str_replace('<br />', '', $c[1]) . '</pre>';
            },
            $text
        );
    }

    public function parseQuote($text)
    {
        $text = preg_replace("#\[quote=&quot;([^:]+)&quot;:{$this->seed}\]#", '<h4>\\1 wrote:</h4><blockquote>', $text);
        $text = str_replace("[quote:{$this->seed}]", '<blockquote>', $text);
        $text = str_replace("[/quote:{$this->seed}]", '</blockquote>', $text);

        return $text;
    }

    public function parseSimple($text)
    {
        // Parse all simple tags
        foreach ($this->simple as $code => $tag) {
            $text = str_replace("[{$code}:{$this->seed}]", "<{$tag}>", $text);
            $text = str_replace("[/{$code}:{$this->seed}]", "</{$tag}>", $text);
        }
        return $text;
    }

    public function parseAdvanced($text)
    {
        // Parse all advanced tags
        foreach ($this->advanced as $code => $tags) {
            $tags = explode('|', $tags);

            $text = str_replace("[{$code}:{$this->seed}]", $tags[0], $text);
            $text = str_replace("[/{$code}:{$this->seed}]", $tags[1], $text);
        }
        return $text;
    }

    public function parseColour($text)
    {
        $text = preg_replace("#\[color=([^:]+):{$this->seed}\]#", "<span style='color:\\1'>", $text);
        $text = str_replace("[/color:{$this->seed}]", '</span>', $text);

        return $text;
    }

    public function parseUrl($text)
    {
        $text = preg_replace("#\[url:{$this->seed}\](.+?)\[/url:{$this->seed}\]#", "<a rel='nofollow' href='\\1'>\\1</a>", $text);
        $text = preg_replace("#\[url=(.+?):{$this->seed}\]#", "<a rel='nofollow' href='\\1'>", $text);
        $text = str_replace("[/url:{$this->seed}]", '</a>', $text);

        return $text;
    }

    public function parse()
    {
        // Get text
        $text = $this->text;

        $text = str_replace("\n", '<br />', $text);

        $text = $this->parseCode($text);
        $text = $this->parseList($text);
        $text = $this->parseQuote($text);

        $text = $this->parseAdvanced($text);
        $text = $this->parseColour($text);
        $text = $this->parseImage($text);
        $text = $this->parseSimple($text);
        $text = $this->parseUrl($text);

        return $text;
    }
}
