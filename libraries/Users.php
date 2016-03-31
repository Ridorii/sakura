<?php
/**
 * Holds various functions to interface with users.
 *
 * @package Sakura
 */

namespace Sakura;

use Sakura\Perms\Site;
use Sakura\Router;

/**
 * User management
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Users
{
    /**
     * Send password forgot e-mail
     *
     * @param string $userId The user id.
     * @param string $email The e-mail.
     */
    public static function sendPasswordForgot($userId, $email)
    {
        $user = User::construct($userId);

        if (!$user->id || $user->permission(Site::DEACTIVATED)) {
            return;
        }

        // Generate the verification key
        $verk = ActionCode::generate('LOST_PASS', $user->id);

        $siteName = Config::get('sitename');
        $baseUrl = "http://" . Config::get('url_main');
        $reactivateLink = Router::route('auth.resetpassword') . "?u={$user->id}&k={$verk}";
        $signature = Config::get('mail_signature');

        // Build the e-mail
        $message = "Hello {$user->username},\r\n\r\n"
            . "You are receiving this notification because you have (or someone pretending to be you has)"
            . " requested a password reset link to be sent for your account on \"{$siteName}\"."
            . " If you did not request this notification then please ignore it,"
            . " if you keep receiving it please contact the site administrator.\r\n\r\n"
            . "To use this password reset key you need to go to a special page."
            . " To do this click the link provided below.\r\n\r\n"
            . "{$baseUrl}{$reactivateLink}\r\n\r\n"
            . "If successful you should be able to change your password here.\r\n\r\n"
            . "You can of course change this password yourself via the settings page."
            . " If you have any difficulties please contact the site administrator.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        Utils::sendMail([$user->email => $user->username], "{$siteName} password restoration", $message);
    }

    /**
     * Send activation e-mail.
     *
     * @param mixed $userId User ID.
     * @param mixed $customKey Key.
     */
    public static function sendActivationMail($userId, $customKey = null)
    {

        // Get the user data
        $user = User::construct($userId);

        // User is already activated or doesn't even exist
        if (!$user->id || !$user->permission(Site::DEACTIVATED)) {
            return;
        }

        // Generate activation key
        $activate = ActionCode::generate('ACTIVATE', $user->id);

        $siteName = Config::get('sitename');
        $baseUrl = "http://" . Config::get('url_main');
        $activateLink = Router::route('auth.activate') . "?u={$user->id}&k={$activate}";
        $profileLink = Router::route('user.profile', $user->id);
        $signature = Config::get('mail_signature');

        // Build the e-mail
        $message = "Welcome to {$siteName}!\r\n\r\n"
            . "Please keep this e-mail for your records. Your account intormation is as follows:\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Username: {$user->username}\r\n\r\n"
            . "Your profile: {$baseUrl}{$profileLink}\r\n\r\n"
            . "----------------------------\r\n\r\n"
            . "Please visit the following link in order to activate your account:\r\n\r\n"
            . "{$baseUrl}{$activateLink}\r\n\r\n"
            . "Your password has been securely stored in our database and cannot be retrieved. "
            . "In the event that it is forgotten,"
            . " you will be able to reset it using the email address associated with your account.\r\n\r\n"
            . "Thank you for registering.\r\n\r\n"
            . "--\r\n\r\nThanks\r\n\r\n{$signature}";

        // Send the message
        Utils::sendMail([$user->email => $user->username], "{$siteName} activation mail", $message);
    }

    /**
     * Get all available profile fields.
     *
     * @return array|null The fields.
     */
    public static function getProfileFields()
    {
        // Get profile fields
        $profileFields = DB::table('profilefields')
            ->get();

        // If there's nothing just return null
        if (!count($profileFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($profileFields as $field) {
            $field = get_object_vars($field);
            $fields[$field['field_id']] = $field;
            $fields[$field['field_id']]['field_identity'] = Utils::cleanString($field['field_name'], true, true);
            $fields[$field['field_id']]['field_additional'] = json_decode($field['field_additional'], true);
        }

        // Return the yeahs
        return $fields;
    }

    /**
     * Get all available option fields.
     *
     * @return array|null The fields.
     */
    public static function getOptionFields()
    {
        // Get option fields
        $optionFields = DB::table('optionfields')
            ->get();

        // If there's nothing just return null
        if (!count($optionFields)) {
            return null;
        }

        // Create output array
        $fields = [];

        $user = User::construct(self::checkLogin()[0]);

        // Iterate over the fields and clean them up
        foreach ($optionFields as $field) {
            $field = get_object_vars($field);

            if (!$user->permission(constant('Sakura\Perms\Site::' . $field['option_permission']))) {
                continue;
            }

            $fields[$field['option_id']] = $field;
        }

        // Return the yeahs
        return $fields;
    }
}
