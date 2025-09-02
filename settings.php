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
 * Admin settings for the Vibe Your Course plugin.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // --- General settings header ---
    $settings->add(new admin_setting_heading(
        'mod_vibeyourcourse/general_settings',
        get_string('pluginname', 'mod_vibeyourcourse'),
        get_string('plugindesc', 'mod_vibeyourcourse')
    ));

    // --- Claude API Key Setting ---
    $settings->add(new admin_setting_configpasswordunmask(
        'mod_vibeyourcourse/claude_api_key',
        get_string('claudeapikey', 'mod_vibeyourcourse'),
        get_string('claudeapikey_desc', 'mod_vibeyourcourse'),
        '',
        PARAM_TEXT
    ));
}
