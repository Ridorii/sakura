<?php
/**
 * Hold the controller for informational pages.
 *
 * @package Sakura
 */

namespace Sakura\Controllers;

/**
 * Informational controller.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class InfoController extends Controller
{
    public function terms()
    {
        return view('info/terms');
    }

    public function privacy()
    {
        return view('info/privacy');
    }

    public function contact()
    {
        $contact = config('contact');

        return view('info/contact', compact('contact'));
    }

    public function rules()
    {
        return view('info/rules');
    }

    public function welcome()
    {
        return view('info/welcome');
    }
}
