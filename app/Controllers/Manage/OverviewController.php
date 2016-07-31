<?php
/**
 * Holds the overview controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Manage;

use Sakura\DB;

/**
 * Overview controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class OverviewController extends Controller
{
    public function index()
    {
        return view('manage/overview/index');
    }

    public function data()
    {
        $data = new \stdClass;

        $data->postsCount = DB::table('posts')
            ->count();

        $data->topicsCount = DB::table('topics')
            ->count();

        $data->usersCount = DB::table('users')
            ->count();

        $data->commentsCount = DB::table('comments')
            ->count();

        $data->uploadsCount = DB::table('uploads')
            ->count();

        return $this->json($data);
    }
}
