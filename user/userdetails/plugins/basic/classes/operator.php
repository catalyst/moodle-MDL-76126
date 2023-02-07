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

/**
 * Description
 *
 * @package    userdetails_basic
 * @copyright  2023 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace userdetails_basic;

use core\userdetails\base;
use core\userdetails\context;
use core\userdetails\stdClass;

defined('MOODLE_INTERNAL') || die();

class operator extends base {

    public function get_full_name(\stdClass $user, \context $context = null, array $options = []): string {
        global $CFG, $SESSION;

        $override = isset($options["usefullnamedisplay"]) ?? false;

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

    public function get_profile_url(\stdClass $user, \context $context = null, array $options = []): string {
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
        return $url->out();
    }

    public function get_profile_picture(\stdClass $user, \context $context = null, array $options = []): string {
        global $OUTPUT;

        // Create a new user picture object.
        $userpicture = new \user_picture($user);

        // Set the options.
        foreach ((array)$options as $key=>$value) {
            if (property_exists($userpicture, $key)) {
                $userpicture->$key = $value;
            }
        }

        // Return the user picture.
        return $OUTPUT->render($userpicture);
    }

    public function get_user_id(\stdClass $user, \context $context = null, array $options = []): string {
        return $user->id;
    }

    public function get_user_email(\stdClass $user, \context $context = null, array $options = []): string {
        return $user->email;
    }

    public function get_ip_address(\stdClass $user, \context $context = null, array $options = []): string {
        return $user->lastip;
    }
}