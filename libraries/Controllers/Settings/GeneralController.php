<?php
/**
 * Holds the general settings section controller.
 *
 * @package Sakura
 */

namespace Sakura\Controllers\Settings;

use Sakura\ActiveUser;
use Sakura\DB;
use Sakura\Perms\Site;
use Sakura\Router;
use Sakura\Template;
use stdClass;

/**
 * General settings.
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class GeneralController extends Controller
{
    public function home()
    {
        return Template::render('settings/general/home');
    }

    public function profile()
    {
        // Check permission
        if (!ActiveUser::$user->permission(Site::ALTER_PROFILE)) {
            $message = "You aren't allowed to edit your profile!";
            $redirect = Router::route('settings.general.home');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        // Get profile fields
        $rawFields = DB::table('profilefields')
            ->get();

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($rawFields as $fieldData) {
            $field = new stdClass;
            $field->id = clean_string($fieldData->field_name, true, true);
            $field->name = $fieldData->field_name;
            $field->type = $fieldData->field_type;
            $field->link = $fieldData->field_link;
            $field->format = $fieldData->field_linkformat;
            $field->description = $fieldData->field_description;
            $field->additional = json_decode($fieldData->field_additional, true);
            $fields[$fieldData->field_id] = $field;
        }

        // Attempt to get the session value
        $session = $_POST['session'] ?? null;

        if ($session) {
            $redirect = Router::route('settings.general.profile');

            // Go over each field
            foreach ($fields as $field) {
                // Add to the store table
                if (isset($_POST["profile_{$field->id}"])) {
                    DB::table('user_profilefields')
                        ->insert([
                            'user_id' => ActiveUser::$user->id,
                            'field_name' => $field->id,
                            'field_value' => $_POST["profile_{$field->id}"],
                        ]);
                }

                // Check if there's additional values we should keep in mind
                if (!empty($field->additional)) {
                    // Go over each additional value
                    foreach ($field->additional as $addKey => $addVal) {
                        // Add to the array
                        $store = (isset($_POST["profile_additional_{$addKey}"]))
                        ? $_POST["profile_additional_{$addKey}"]
                        : false;

                        DB::table('user_profilefields')
                            ->insert([
                                'user_id' => ActiveUser::$user->id,
                                'field_name' => $addKey,
                                'field_value' => $store,
                            ]);
                    }
                }
            }

            // Birthdays
            if (isset($_POST['birthday_day'])
                && isset($_POST['birthday_month'])
                && isset($_POST['birthday_year'])) {
                $day = intval($_POST['birthday_day']);
                $month = intval($_POST['birthday_month']);
                $year = intval($_POST['birthday_year']);

                // Check the values
                if (!checkdate($month, $day, $year ? $year : 1)
                    || $year > date("Y")
                    || ($year != 0 && $year < (date("Y") - 100))) {
                    $message = "Your birthdate was considered invalid, everything else was saved though.";

                    Template::vars(compact('message', 'redirect'));

                    return Template::render('global/information');
                }

                // Combine it into a YYYY-MM-DD format
                $birthdate = implode(
                    '-',
                    [$_POST['birthday_year'], $_POST['birthday_month'], $_POST['birthday_day']]
                );

                DB::table('users')
                    ->where('user_id', ActiveUser::$user->id)
                    ->update([
                        'user_birthday' => $birthdate,
                    ]);
            }

            $message = "Updated your profile!";

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        Template::vars(compact('fields'));

        return Template::render('settings/general/profile');
    }

    public function options()
    {
        // Get profile fields
        $rawFields = DB::table('optionfields')
            ->get();

        // Create output array
        $fields = [];

        // Iterate over the fields and clean them up
        foreach ($rawFields as $fieldData) {
            if (!ActiveUser::$user->permission(constant("Sakura\Perms\Site::{$fieldData->option_permission}"))) {
                continue;
            }

            $field = new stdClass;
            $field->id = $fieldData->option_id;
            $field->name = $fieldData->option_name;
            $field->description = $fieldData->option_description;
            $field->type = $fieldData->option_type;
            $field->permission = $fieldData->option_permission;
            $fields[$fieldData->option_id] = $field;
        }

        // Attempt to get the session value
        $session = $_POST['session'] ?? null;

        if ($session) {
            // Delete all option fields for this user
            DB::table('user_optionfields')
                ->where('user_id', ActiveUser::$user->id)
                ->delete();

            // Go over each field
            foreach ($fields as $field) {
                if (isset($_POST["option_{$field->id}"])) {
                    DB::table('user_optionfields')
                        ->insert([
                            'user_id' => ActiveUser::$user->id,
                            'field_name' => $field->id,
                            'field_value' => $_POST["option_{$field->id}"],
                        ]);
                }
            }

            $message = "Updated your options!";
            $redirect = Router::route('settings.general.options');

            Template::vars(compact('message', 'redirect'));

            return Template::render('global/information');
        }

        Template::vars(compact('fields'));

        return Template::render('settings/general/options');
    }
}
