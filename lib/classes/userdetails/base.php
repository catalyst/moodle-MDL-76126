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
 * Base class for user details plugin.
 *
 * @package    core
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Nathan Nguyen <nathannguyen@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\userdetails;

defined('MOODLE_INTERNAL') || die();

abstract class base {

    /**
     * Get user's full name according to context.
     *
     * @param stdClass $user
     * @param context $context
     * @param array $options
     * @return string
     */
    public function get_full_name(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

    /**
     * Get user's id according to context.
     *
     * @param \stdClass $user
     * @param \context $context $context
     * @param array $options
     * @return mixed
     */
    public function get_user_id(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

    /**
     * Get user's profile picture according to context.
     *
     * @param stdClass $user
     * @param context $context
     * @param array $options
     * @return string
     */
    public function get_profile_picture(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

    /**
     * Get user's profile url according to context.
     *
     * @param \stdClass $user
     * @param \context $context $context
     * @param array $options
     * @return string
     */
    public function get_profile_url(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

    /**
     * Get user's email according to context.
     *
     * @param \stdClass $user
     * @param \context $context $context
     * @param array $options
     * @return string
     */
    public function get_user_email(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

    /**
     * Get user's ip address according to context.
     *
     * @param \stdClass $user
     * @param \context $context $context
     * @param array $options
     * @return string
     */
    public function get_ip_address(\stdClass $user, \context $context = null, array $options = []): string {
        return '';
    }

}
