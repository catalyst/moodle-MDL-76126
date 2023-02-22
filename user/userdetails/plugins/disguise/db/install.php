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
 * Installation script for userdetails_disguise
 *
 * @package    userdetails_disguise
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add the userdetails_disguise plugin to the userdetails table.
 *
 * @return void
 */
function xmldb_userdetails_disguise_install() {
    global $DB;

    $record = new stdClass();
    $record->plugin = 'disguise';
    $record->enabled = 1;
    // Get current max order.
    $maxorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {userdetails}');
    $record->sortorder = $maxorder + 1;

    $DB->insert_record('userdetails', $record);
}