<?php
/**
 * Hold the controller for informational pages.
 * @package Sakura
 */

namespace Sakura\Controllers;

/**
 * Informational controller.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class InfoController extends Controller
{
    /**
     * Renders the terms of service.
     * @return string
     */
    public function terms()
    {
        return view('info/terms');
    }

    /**
     * Renders the privacy policy.
     * @return string
     */
    public function privacy()
    {
        return view('info/privacy');
    }

    /**
     * Renders the contact page.
     * @return string
     */
    public function contact()
    {
        $contact = config('contact');

        return view('info/contact', compact('contact'));
    }

    /**
     * Renders the rules page.
     * @return string
     */
    public function rules()
    {
        return view('info/rules');
    }

    /**
     * Renders the welcome page.
     * @return string
     */
    public function welcome()
    {
        return view('info/welcome');
    }
}
