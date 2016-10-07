<?php
/**
 * Holds the forum bbcode cache rebuilder.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Sakura\DB;
use Sakura\Forum\Post;

/**
 * Rebuilds the forum bbcode cache.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class RebuildForumCacheCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Rebuild the forum bbcode cache';
    }

    /**
     * Does the repository installing.
     */
    public function execute()
    {
        $this->getLogger()->writeln("This might take a while...");
        $posts = DB::table('posts')->get(['post_id']);

        foreach ($posts as $post) {
            (new Post($post->post_id))->update(true);
        }

        $this->getLogger()->writeln("Done!");
    }
}
