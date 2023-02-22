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
 * @package    userdetails_disguise
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace userdetails_disguise;

use core\userdetails\base;
use core\userdetails\context;
use core\userdetails\stdClass;

defined('MOODLE_INTERNAL') || die();

class operator extends base {

    public function get_full_name(\stdClass $user, $context = null, array $options = []): string {
        global $PAGE;

        if ($context === null) {
            // No context specified - use page context instead.
            $context = $PAGE->context;
        }

        // Try catch to prevent error when user is not logged in.
        try {
            if (($context->has_disguise() || $context->has_own_disguise()) && !$PAGE->is_disguise_configuration_page()) {
                return $context->disguise->displayname($user, $options);
            }
        } catch (\Exception $e) {
            return '';
        }


        return '';
    }

    public function get_profile_url(\stdClass $user, $context = null, array $options = []): string {
        global $PAGE;

        if ($context === null) {
            // No context specified - use page context instead.
            $context = $PAGE->context;
        }

        if ($context->has_disguise() && !$context->disguise->allow_profile_links($user, $options)) {
            // TODO Return a guest user profile or sth else?.
            $user = guest_user();
            $url = new \moodle_url('/user/view.php', ['id' => $user->id]);
            return $url->out(false);
        }
        // Return '' so that the other plugins can process the request.
        return '';

    }

    public function get_user_id(\stdClass $user, $context = null, array $options = []): string {
        // TODO Return a guest id or sth else?.
        if ($context->has_disguise()) {
            $user = guest_user();
            return $user->id;
        }
    }

}