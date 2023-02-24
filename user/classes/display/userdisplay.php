<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace core_user\display;

defined('MOODLE_INTERNAL') || die();

use context;
use core_user;
use stdClass;

/**
 * A User Display class can act as a decorator for the User class,
 * providing additional functionality for displaying user information in various formats.
 * The User class may have basic attributes such as name, email, profile url, and picture.
 * However, when it comes to displaying this information to others or for different purposes such as reporting,
 * the User Display class can add methods and attributes to present the data in different ways.
 *
 * For example, the User Display class could have a method to display the user's name in a certain format, such as first name and last name.
 * The User Display class could even have a method to generate a user's profile url/picture based on their name and id.
 *
 * By using the User Display class as a decorator, the original User class remains unchanged and can still be used for its primary purpose of user authentication and management.
 * The User Display class provides an additional layer of functionality to enhance the user experience and make it easier to present user information in various formats.
 *
 * @package    core_user\display
 * @copyright  2023 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userdisplay implements displayable {
    /**
     * @var stdClass $user The user object.
     */
    private stdClass $user;

    /**
     * @var context $context The context object.
     */
    private context $context;

    protected function __construct($user, $context = null) {
        global $PAGE;

        // Make sure we have a user object.
        $user = is_object($user) ? $user : core_user::get_user($user, '*', MUST_EXIST);

        // Make sure we have a context object.
        if ($context) {
            $context = is_object($context) ? $context : context::instance_by_id($context, MUST_EXIST);
        } else {
            $context = $PAGE->context;
        }

        $this->user = $user;
        $this->context = $context;

    }

    public static function create($user): userdisplay {
        return new userdisplay($user);
    }

    public function get_user_and_context(): array {
        return [$this->user, $this->context];
    }

    public function get_name(array $options = []): string {
        global $CFG, $SESSION;

        // User object.
        list($user,) = $this->get_user_and_context();

        // Whether to use alternativefullnameformat or fullnamedisplay.
        $override = !isset($options["usefullnamedisplay"]) || !$options["usefullnamedisplay"];

        if (!isset($user->firstname) and !isset($user->lastname)) {
            return '';
        }

        // Get all of the name fields.
        $allnames = \core_user\fields::get_name_fields();
        if ($CFG->debugdeveloper) {
            foreach ($allnames as $allname) {
                if (!property_exists($user, $allname)) {
                    // If all the user name fields are not set in the user object, then notify the programmer that it needs to be fixed.
                    debugging('You need to update your sql to include additional name fields in the user object.', DEBUG_DEVELOPER);
                    // Message has been sent, no point in sending the message multiple times.
                    break;
                }
            }
        }

        if (!$override) {
            if (!empty($CFG->forcefirstname)) {
                $user->firstname = $CFG->forcefirstname;
            }
            if (!empty($CFG->forcelastname)) {
                $user->lastname = $CFG->forcelastname;
            }
        }

        if (!empty($SESSION->fullnamedisplay)) {
            $CFG->fullnamedisplay = $SESSION->fullnamedisplay;
        }

        $template = null;
        // If the fullnamedisplay setting is available, set the template to that.
        if (isset($CFG->fullnamedisplay)) {
            $template = $CFG->fullnamedisplay;
        }
        // If the template is empty, or set to language, return the language string.
        if ((empty($template) || $template == 'language') && !$override) {
            return get_string('fullnamedisplay', null, $user);
        }

        // Check to see if we are displaying according to the alternative full name format.
        if ($override) {
            if (empty($CFG->alternativefullnameformat) || $CFG->alternativefullnameformat == 'language') {
                // Default to show just the user names according to the fullnamedisplay string.
                return get_string('fullnamedisplay', null, $user);
            } else {
                // If the override is true, then change the template to use the complete name.
                $template = $CFG->alternativefullnameformat;
            }
        }

        $requirednames = array();
        // With each name, see if it is in the display name template, and add it to the required names array if it is.
        foreach ($allnames as $allname) {
            if (strpos($template, $allname) !== false) {
                $requirednames[] = $allname;
            }
        }

        $displayname = $template;
        // Switch in the actual data into the template.
        foreach ($requirednames as $altname) {
            if (isset($user->$altname)) {
                // Using empty() on the below if statement causes breakages.
                if ((string)$user->$altname == '') {
                    $displayname = str_replace($altname, 'EMPTY', $displayname);
                } else {
                    $displayname = str_replace($altname, $user->$altname, $displayname);
                }
            } else {
                $displayname = str_replace($altname, 'EMPTY', $displayname);
            }
        }
        // Tidy up any misc. characters (Not perfect, but gets most characters).
        // Don't remove the "u" at the end of the first expression unless you want garbled characters when combining hiragana or
        // katakana and parenthesis.
        $patterns = array();
        // This regular expression replacement is to fix problems such as 'James () Kirk' Where 'Tiberius' (middlename) has not been
        // filled in by a user.
        // The special characters are Japanese brackets that are common enough to make allowances for them (not covered by :punct:).
        $patterns[] = '/[[:punct:]「」]*EMPTY[[:punct:]「」]*/u';
        // This regular expression is to remove any double spaces in the display name.
        $patterns[] = '/\s{2,}/u';
        foreach ($patterns as $pattern) {
            $displayname = preg_replace($pattern, ' ', $displayname);
        }

        // Trimming $displayname will help the next check to ensure that we don't have a display name with spaces.
        $displayname = trim($displayname);
        if (empty($displayname)) {
            // Going with just the first name if no alternate fields are filled out. May be changed later depending on what
            // people in general feel is a good setting to fall back on.
            $displayname = $user->firstname;
        }
        return $displayname;
    }

    public function get_profile_url(array $options = []): string {
        // User and context object.
        list($user, $context) = $this->get_user_and_context();

        // Params to be passed to the user view page.
        $params = ['id' => $user->id];

        // Check if the context is a course context.
        if (isset($context) && $context->contextlevel == CONTEXT_COURSE) {
            // Course id to the params.
            $params['courseid'] = $context->instanceid;
        }

        // Profile URL.
        $url = new \moodle_url('/user/view.php', $params);

        // Return the URL.
        return $url->out(false);
    }

    public function get_picture(array $options = []): string {
        global $OUTPUT;

        // User and context object.
        list($user, $context) = $this->get_user_and_context();

        // Create a new user picture object.
        $userpicture = new \user_picture($user);

        // Set the options.
        foreach ((array)$options as $key => $value) {
            if (property_exists($userpicture, $key)) {
                $userpicture->$key = $value;
            }
        }

        // Return the user picture.
        return $OUTPUT->render($userpicture);
    }

    public function get_id(array $options = []): string {
        return $this->user->id ?? '';
    }

    public function get_username(array $options = []): string {
        return $this->user->username ?? '';
    }

    public function get_idnumber(array $options = []): string {
        return $this->user->idnumber ?? '';
    }

    public function get_firstname(array $options = []): string {
        return $this->user->firstname ?? '';
    }

    public function get_lastname(array $options = []): string {
        return $this->user->lastname ?? '';
    }

    public function get_email(array $options = []): string {
        return $this->user->email ?? '';
    }

    public function get_phone1(array $options = []): string {
        return $this->user->phone1 ?? '';
    }

    public function get_phone2(array $options = []): string {
        return $this->user->phone2 ?? '';
    }

    public function get_institution(array $options = []): string {
        return $this->user->institution ?? '';
    }

    public function get_department(array $options = []): string {
        return $this->user->department ?? '';
    }

    public function get_address(array $options = []): string {
        return $this->user->address ?? '';
    }

    public function get_city(array $options = []): string {
        return $this->user->city ?? '';
    }

    public function get_country(array $options = []): string {
        return $this->user->country ?? '';
    }

    public function get_timezone(array $options = []): string {
        return $this->user->timezone ?? '';
    }

    public function get_firstaccess(array $options = []): string {
        return $this->user->firstaccess ?? '';
    }

    public function get_lastaccess(array $options = []): string {
        return $this->user->lastaccess ?? '';
    }

    public function get_lastlogin(array $options = []): string {
        return $this->user->lastlogin ?? '';
    }

    public function get_currentlogin(array $options = []): string {
        return $this->user->currentlogin ?? '';
    }

    public function get_lastip(array $options = []): string {
        return $this->user->lastip ?? '';
    }

    public function get_lastnamephonetic(array $options = []): string {
        // Return the user lastnamephonetic.
        return $this->user->lastnamephonetic ?? '';
    }

    public function get_firstnamephonetic(array $options = []): string {
        // Return the user firstnamephonetic.
        return $this->user->firstnamephonetic ?? '';
    }

    public function get_middlename(array $options = []): string {
        // Return the user middlename.
        return $this->user->middlename ?? '';
    }

    public function get_alternatename(array $options = []): string {
        // Return the user alternatename.
        return $this->user->alternatename ?? '';
    }

}
