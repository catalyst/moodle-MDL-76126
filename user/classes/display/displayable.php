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

/**
 * Interface for user display class.
 *
 * Reasons for separate display class from core user class
 *
 * Separation of concerns: Separating the display functionality into its own class helps to keep the display logic separate from the core logic of the object.
 * This can make the code easier to understand and maintain, as well as making it more modular and reusable.
 *
 * Single Responsibility Principle: The Single Responsibility Principle (SRP) states that a class should have only one reason to change.
 * By separating the display functionality into its own class, we are ensuring that any changes to the display logic will not affect the core logic of the object.
 *
 * Flexibility: By implementing a separate display class, we can easily change the way an object is displayed without having to modify the object itself.
 * For example, we could create different display classes for different contexts (such as web pages, mobile apps, or printed reports),
 * or we could create different display classes for different types of users (such as admins vs. regular users).
 *
 * Testing: Separating the display logic into its own class makes it easier to test the display functionality in isolation from the core logic of the object.
 * This can help to improve the quality of your tests and make them more maintainable.
 *
 * @package    core_user\display
 * @copyright  2023 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface displayable {

    public function get_name(array $options = []): string;

    public function get_profile_url(array $options = []): string;

    public function get_picture(array $options = []): string;

    public function get_id(array $options = []): string;

    public function get_username(array $options = []): string;

    public function get_idnumber(array $options = []): string;

    public function get_firstname(array $options = []): string;

    public function get_lastname(array $options = []): string;

    public function get_email(array $options = []): string;

    public function get_phone1(array $options = []): string;

    public function get_phone2(array $options = []): string;

    public function get_institution(array $options = []): string;

    public function get_department(array $options = []): string;

    public function get_address(array $options = []): string;

    public function get_city(array $options = []): string;

    public function get_country(array $options = []): string;

    public function get_timezone(array $options = []): string;

    public function get_firstaccess(array $options = []): string;

    public function get_lastaccess(array $options = []): string;

    public function get_lastlogin(array $options = []): string;

    public function get_currentlogin(array $options = []): string;

    public function get_lastip(array $options = []): string;

    public function get_lastnamephonetic(array $options = []): string;

    public function get_firstnamephonetic(array $options = []): string;

    public function get_middlename(array $options = []): string;

    public function get_alternatename(array $options = []): string;
}
