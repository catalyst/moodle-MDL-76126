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
 * @package    core_userdetails
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

defined('MOODLE_INTERNAL') || die();

$action = required_param('action', PARAM_ALPHA);
$plugin = required_param('plugin', PARAM_PLUGIN);

require_sesskey();

// Check if the action is valid.
if (!in_array($action, ['up', 'down', 'disable', 'enable'])) {
    throw new \moodle_exception('invalidaction', 'error');
}

// TODO: Check if the user has the permission to do the action.

// Process the action.
switch ($action) {

    case 'up':
        // Current sort order.
        $currentsortorder = $DB->get_field('userdetails', 'sortorder', ['plugin' => $plugin]);
        // Get the plugin with the sort order just before the current one.
        $previousplugin = $DB->get_record('userdetails', ['sortorder' => $currentsortorder - 1]);
        // Swap the sort order if previous plugin exists.
        if ($previousplugin) {
            $DB->set_field('userdetails', 'sortorder', $currentsortorder, ['plugin' => $previousplugin->plugin]);
            $DB->set_field('userdetails', 'sortorder', $currentsortorder - 1, ['plugin' => $plugin]);
        }
        break;

    case 'down':
        // Current sort order.
        $currentsortorder = $DB->get_field('userdetails', 'sortorder', ['plugin' => $plugin]);
        // Get the plugin with the sort order just after the current one.
        $nextplugin = $DB->get_record('userdetails', ['sortorder' => $currentsortorder + 1]);
        // Swap the sort order if next plugin exists.
        if ($nextplugin) {
            $DB->set_field('userdetails', 'sortorder', $currentsortorder, ['plugin' => $nextplugin->plugin]);
            $DB->set_field('userdetails', 'sortorder', $currentsortorder + 1, ['plugin' => $plugin]);
        }
        break;

    case 'disable':
        $DB->set_field('userdetails', 'active', 0, ['plugin' => $plugin]);
        break;

    case 'enable':
        $DB->set_field('userdetails', 'active', 1, ['plugin' => $plugin]);
        break;

    default:
        throw new \moodle_exception('invalidaction', 'error');

}

// redirect back to the user details page.
redirect(new \moodle_url('/user/userdetails/index.php'));

