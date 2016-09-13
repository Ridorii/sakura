<?php
/**
 * Hold the Sakurako settings object.
 * @package Sakura
 */

namespace Sakura\Chat;

/**
 * Chat settings. Keep this up-to-date with settings.json for Sakurako.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Settings
{
    /**
     * Protocol the chat will use.
     * @var string
     */
    public $protocol = 'TestRepeater';

    /**
     * Server address the chat will connect to.
     * @var string
     */
    public $server = null;

    /**
     * Title to display on the window/tab.
     * @var string
     */
    public $title = 'Sakurako';

    /**
     * Location to redirect to when the authentication failed.
     * @var string
     */
    public $authRedir = null;

    /**
     * Cookies to send to the server for authentication (in proper order).
     * @var array
     */
    public $authCookies = [];

    /**
     * URL format for avatars, {0} gets replaced with the user's id and set to null to disable.
     * @var string
     */
    public $avatarUrl = null;

    /**
     * URL format for profile links, works the same as avatars.
     * @var string
     */
    public $profileUrl = null;

    /**
     * Enabling compact (classic) by default.
     * @var bool
     */
    public $compactView = false;

    /**
     * Strobe the tab title on new message.
     * @var bool
     */
    public $flashTitle = true;

    /**
     * Enabling browser notifications.
     * @var bool
     */
    public $enableNotifications = true;

    /**
     * Words that trigger a notification separated with spaces.
     * @var string
     */
    public $notificationTriggers = '';

    /**
     * Show the contents of the message in the notification.
     * @var bool
     */
    public $notificationShowMessage = false;

    /**
     * Enabling development mode (e.g. loading eruda).
     * @var bool
     */
    public $development = false;

    /**
     * Default style.
     * @var string
     */
    public $style = 'dark';

    /**
     * Path to language files relative to the chat client's index.
     * @var string
     */
    public $languagePath = './languages/';

    /**
     * Default language file to use.
     * @var string
     */
    public $language = 'en-gb';

    /**
     * Available languages.
     * @var array
     */
    public $languages = [
        'en-gb' => 'English',
    ];

    /**
     * Formatting string to the timestamp, uses the PHP syntax.
     * @var string
     */
    public $dateTimeFormat = 'H:i:s';

    /**
     * Markup parser to use.
     * @var string
     */
    public $parser = 'WaterDown';

    /**
     * Enabling the markup parser.
     * @var bool
     */
    public $enableParser = true;

    /**
     * Enabling emoticon parsing.
     * @var bool
     */
    public $enableEmoticons = true;

    /**
     * Whether urls should be automatically detected in message.
     * @var bool
     */
    public $autoParseUrls = true;

    /**
     * Whether the chat should embed url macros like image embedding.
     * @var bool
     */
    public $autoEmbed = true;

    /**
     * Enabling automatically scrolling down when a new message is received.
     * @var bool
     */
    public $autoScroll = true;

    /**
     * Enabling notification sounds.
     * @var bool
     */
    public $soundEnable = true;

    /**
     * The volume percentage for sounds.
     * @var int
     */
    public $soundVolume = 80;

    /**
     * The default sound pack.
     * @var string
     */
    public $soundPack = 'default';

    /**
     * Available sound packs.
     * @var array
     */
    public $soundPacks = [
        'default' => 'Default',
    ];

    /**
     * Enabling the user join sound.
     * @var bool
     */
    public $soundEnableJoin = true;

    /**
     * Enabling the user leave sound.
     * @var bool
     */
    public $soundEnableLeave = true;

    /**
     * Enabling the error sound.
     * @var bool
     */
    public $soundEnableError = true;

    /**
     * Enabling the server broadcast sound.
     * @var bool
     */
    public $soundEnableServer = true;

    /**
     * Enabling the incoming message sound.
     * @var bool
     */
    public $soundEnableIncoming = true;

    /**
     * Enabling the outgoing message sound.
     * @var bool
     */
    public $soundEnableOutgoing = true;

    /**
     * Enabling the private message sound.
     * @var bool
     */
    public $soundEnablePrivate = true;

    /**
     * Enabling the forceful leave (kick/ban/etc) sound.
     * @var bool
     */
    public $soundEnableForceLeave = true;

    /**
     * Whether to let the user confirm before closing the tab.
     * @var bool
     */
    public $closeTabConfirm = false;

    /**
     * Emoticons to be loaded.
     * @var array
     */
    public $emoticons = [];

    /**
     * Applies settings based on Sakura's configuration.
     */
    public function loadStandard()
    {
        $this->protocol = config('chat.protocol');
        $this->server = config('chat.server');
        $this->title = config('chat.title');
        $this->authRedir = route('auth.login', null, true);
        $cpfx = config('cookie.prefix');
        $this->authCookies = [
            "{$cpfx}id",
            "{$cpfx}session",
        ];
        $this->avatarUrl = route('user.avatar', '{0}', true);
        $this->profileUrl = route('user.profile', '{0}', true);
        $this->development = config('dev.show_errors');
        $this->languagePath = config('chat.language_path');
        $this->language = config('chat.language');
        $this->languages = config('chat.languages');
        $this->dateTimeFormat = config('chat.date_format');
        $this->parser = config('chat.parser');
        $this->soundPack = config('chat.sound_pack');
        $this->soundPacks = config('chat.sound_packs');
    }

    /**
     * Adding an emoticon to the list.
     * @param array $triggers
     * @param string $image
     * @param int $hierarchy
     * @param bool $relativePath
     */
    public function addEmoticon($triggers, $image, $hierarchy = 0, $relativePath = false)
    {
        $this->emoticons[] = [
            'Text' => $triggers,
            'Image' => ($relativePath ? full_domain() : '') . $image,
            'Hierarchy' => $hierarchy,
        ];
    }
}
