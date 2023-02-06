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
 * @package    core_user
 * @copyright  2023 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(__FILE__)) . '../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

admin_externalpage_setup('userdetails');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('userdetails'));

// Table to display the list of userdetails plugins.
$table = new flexible_table('userdetails_administration_table');
$table->define_columns([
    'name',
    'version',
    'order',
    'uninstall',
    'settings',
]);
$table->define_headers([
    get_string('plugin'),
    get_string('version'),
    get_string('order'),
    get_string('uninstallplugin', 'core_admin'),
    get_string('settings'),
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'userdetailsplugins');
$table->set_attribute('class', 'admintable generaltable');
$table->setup();

// Get the list of userdtails plugins.
$plugins = [];

// Get max sortorder from userdetails table.
$maxsortorder = $DB->get_field_sql('SELECT MAX(sortorder) FROM {userdetails}') ?? -1;

foreach (core_component::get_plugin_list('userdetails') as $plugin => $plugindir) {
    // Get sortorder of the plugin in userdetails table.
    $sortorder = $DB->get_field('userdetails', 'sortorder', ['plugin' => $plugin]);

    // If the plugin is not in userdetails table, add it to the table.
    if ($sortorder === false) {
        $maxsortorder++;
        $DB->insert_record('userdetails', ['plugin' => $plugin, 'sortorder' => $maxsortorder]);
        $sortorder = $maxsortorder;
    }
    $plugins[$plugin] = $sortorder;

}

core_collator::asort($plugins);

foreach ($plugins as $plugin => $sortorder) {
    // Get the name of the plugin.
    $name = get_string('pluginname', 'userdetails_' . $plugin);

    // Get the version of the plugin.
    $pluginconfig = get_config('userdetails_' . $plugin);
    if (!empty($pluginconfig->version)) {
        $version = $pluginconfig->version;
    } else {
        $version = '?';
    }

    // Order of the plugin.
    $updown = '';

    // Spacer.
    $spacer = $OUTPUT->spacer();

    // Down Button.
    $downnurl = new moodle_url('/user/userdetails/action.php', ['sesskey' => sesskey(), 'action' => 'down', 'plugin' => $plugin]);
    $downbutton = $OUTPUT->action_icon($downnurl,
        new pix_icon('t/down', get_string('down'), '', ['class' => 'iconsmall']));

    // Up button.
    $upurl = new moodle_url('/user/userdetails/action.php', ['sesskey' => sesskey(), 'action' => "up", 'plugin' => $plugin]);
    $upbutton = $OUTPUT->action_icon($upurl,
        new pix_icon('t/up', get_string('up'), '', ['class' => 'iconsmall']));

    if ($sortorder == 0) {
        // If the plugin is the first one, only show the down button.
        $updown = $spacer . $downbutton;
    } else if ($sortorder == $maxsortorder) {
        // If the plugin is the last one, only show the up button.
        $updown = $upbutton . $spacer;
    } else {
        // If the plugin is in the middle, show both up and down buttons.
        $updown = $upbutton . $spacer . $downbutton;
    }

    // Get the uninstall link.
    $uninstall = '';
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('userdetails_' . $plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }

    // Get the settings link.
    $settings = '';
    $settingsfile = $CFG->dirroot . "/user/userdetails/plugins/$plugin/settings.php";
    if (file_exists($settingsfile)) {
        $settings = html_writer::link(
            new moodle_url("/user/userdetails/plugins/$plugin/settings.php"),
            get_string('settings')
        );
    }

    // Add the row to the table.
    $table->add_data([
        $name,
        $version,
        $updown,
        $uninstall,
        $settings,
    ]);
}

// Display the table.
$table->finish_output();

echo $OUTPUT->footer();
