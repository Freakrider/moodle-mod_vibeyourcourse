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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/\>.

/**
 * Library of interface functions and constants.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function vibeyourcourse_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the vibeyourcourse into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param stdClass $moduleinstance An object from the form.
 * @param mod_vibeyourcourse_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function vibeyourcourse_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance->timemodified = time();

    // Process AI configuration if provided.
    if (isset($moduleinstance->ai_config)) {
        $moduleinstance->ai_config = json_encode($moduleinstance->ai_config);
    }

    // Process allowed runtimes.
    $runtimes = [];
    if (!empty($moduleinstance->allow_python)) {
        $runtimes[] = 'python';
    }
    if (!empty($moduleinstance->allow_javascript)) {
        $runtimes[] = 'javascript';
    }
    if (!empty($moduleinstance->allow_webcontainer)) {
        $runtimes[] = 'webcontainer';
    }
    $moduleinstance->allowed_runtimes = json_encode($runtimes);

    $id = $DB->insert_record('vibeyourcourse', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the vibeyourcourse in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param stdClass $moduleinstance An object from the form in mod_form.php.
 * @param mod_vibeyourcourse_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function vibeyourcourse_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Process AI configuration if provided.
    if (isset($moduleinstance->ai_config)) {
        $moduleinstance->ai_config = json_encode($moduleinstance->ai_config);
    }

    // Process allowed runtimes.
    $runtimes = [];
    if (!empty($moduleinstance->allow_python)) {
        $runtimes[] = 'python';
    }
    if (!empty($moduleinstance->allow_javascript)) {
        $runtimes[] = 'javascript';
    }
    if (!empty($moduleinstance->allow_webcontainer)) {
        $runtimes[] = 'webcontainer';
    }
    $moduleinstance->allowed_runtimes = json_encode($runtimes);

    return $DB->update_record('vibeyourcourse', $moduleinstance);
}

/**
 * Removes an instance of the vibeyourcourse from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function vibeyourcourse_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('vibeyourcourse', array('id' => $id));
    if (!$exists) {
        return false;
    }

    // Delete all related projects.
    $DB->delete_records('vibeyourcourse_projects', array('vibeyourcourse_id' => $id));
    
    // Delete all related executions.
    $projects = $DB->get_records('vibeyourcourse_projects', array('vibeyourcourse_id' => $id), '', 'id');
    foreach ($projects as $project) {
        $DB->delete_records('vibeyourcourse_executions', array('project_id' => $project->id));
    }

    // Delete the main instance.
    $DB->delete_records('vibeyourcourse', array('id' => $id));

    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in vibeyourcourse activities and print it out.
 *
 * @param stdClass $course The course record.
 * @param bool $viewfullnames Should we display full names.
 * @param int $timestart Print activity since this timestamp.
 * @return bool True if anything was printed, otherwise false.
 */
function vibeyourcourse_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property.
 * @param int $index the index in the $activities to use for the next record.
 * @param int $timestart append activity since this time.
 * @param int $courseid the id of the course we produce the report for.
 * @param int $cmid course module id.
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users).
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups).
 */
function vibeyourcourse_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@link vibeyourcourse_get_recent_mod_activity()}.
 *
 * @param stdClass $activity activity record with added 'cmid' property.
 * @param int $courseid the id of the course we produce the report for.
 * @param bool $detail print detailed report.
 * @param array $modnames as returned by {@link get_module_types_names()}.
 * @param bool $viewfullnames display users' full names.
 */
function vibeyourcourse_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron.
 *
 * @return bool True if successful.
 */
function vibeyourcourse_cron() {
    return true;
}

/**
 * Returns all other caps used in the module.
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array Array of capability strings.
 */
function vibeyourcourse_get_extra_capabilities() {
    return array();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of vibeyourcourse?
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given vibeyourcourse instance.
 */
function vibeyourcourse_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('vibeyourcourse', array('id' => $moduleinstanceid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of vibeyourcourse.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any vibeyourcourse instance.
 */
function vibeyourcourse_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('vibeyourcourse', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the given vibeyourcourse instance.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 * @return void.
 */
function vibeyourcourse_grade_item_update($moduleinstance, $reset = false) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    $item = array();
    $item['itemname'] = clean_param($moduleinstance->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;

    if ($moduleinstance->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $moduleinstance->grade;
        $item['grademin']  = 0;
    } else if ($moduleinstance->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$moduleinstance->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item['reset'] = true;
    }

    grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse',
            $moduleinstance->id, 0, null, $item);
}

/**
 * Delete grade item for given vibeyourcourse instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return grade_item.
 */
function vibeyourcourse_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse',
            $moduleinstance->id, 0, null, array('deleted' => 1));
}

/**
 * Update vibeyourcourse grades in the gradebook.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of specific user only, 0 means all participants.
 */
function vibeyourcourse_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = array();

    grade_update('mod/vibeyourcourse', $moduleinstance->course, 'mod', 'vibeyourcourse', $moduleinstance->id, 0, $grades);
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context.
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @return array of [(string)filearea] => (string)description
 */
function vibeyourcourse_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for vibeyourcourse file areas.
 *
 * @package mod_vibeyourcourse
 * @category files
 *
 * @param file_browser $browser file browser object
 * @param array $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function vibeyourcourse_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the vibeyourcourse file areas.
 *
 * @package mod_vibeyourcourse
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the vibeyourcourse's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function vibeyourcourse_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding vibeyourcourse nodes if there is a relevant content.
 *
 * @param navigation_node $navref An object representing the navigation node.
 * @param stdClass $course Course object.
 * @param stdClass $module Module object.
 * @param cm_info $cm Course module info object.
 */
function vibeyourcourse_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the vibeyourcourse settings.
 *
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param navigation_node $modulenode The node to add module settings to.
 */
function vibeyourcourse_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $modulenode = null) {
}

/**
 * Sends a prompt to the Claude API and returns the structured response using Moodle's best practices.
 *
 * @param string $prompt The user's prompt.
 * @param array $project_files (Optional) Existing project files for context.
 * @return stdClass The structured response from Claude.
 * @throws moodle_exception If the API call fails or returns an invalid format.
 */
function vibeyourcourse_call_claude_api($prompt, $project_files = []) {
    global $CFG;

    error_log("Claude API: Starting API call with prompt: " . substr($prompt, 0, 100));

    $api_key = get_config('mod_vibeyourcourse', 'claude_api_key');
    if (empty($api_key)) {
        error_log("Claude API: API key is empty or not configured");
        throw new moodle_exception('Claude API key is not configured in the plugin settings.', 'mod_vibeyourcourse');
    }
    
    error_log("Claude API: API key found, length: " . strlen($api_key));

    $api_url = 'https://api.anthropic.com/v1/messages';
    $anthropic_version = '2023-06-01';
    $model = 'claude-sonnet-4-20250514';

    $system_prompt = "You are 'Vibe Coder', an expert AI assistant within a Moodle course. Your goal is to function as a 'pair programmer' by generating and modifying complete, runnable web applications based on user prompts.
    
    KEY INSTRUCTIONS:
    - Your entire output MUST be a single, valid JSON object. Do not include any text before or after the JSON.
    - The JSON object must contain two keys: 'response' (a user-friendly, markdown-formatted explanation of your changes) and 'files' (an object of filenames and their complete string content).
    - When modifying a project, always return the COMPLETE, updated content for every changed file. Do not use diffs.
    - Generate simple, clean, single-page 'micro-apps'. Prioritize clarity and functionality for a learning environment.
    - KEEP FILES CONCISE: Use minimal CSS/HTML, avoid unnecessary comments or whitespace to stay within token limits.
    - OPTIMIZE FOR SIZE: Inline CSS when possible, use short variable names, avoid repetition.
    - If the user asks a question not related to coding, gently guide them back to the task in the 'response' field and provide an empty 'files' object.";
    
    $user_message_content = "User Prompt: \"{$prompt}\"";
    if (!empty($project_files)) {
        $user_message_content .= "\n\n--- Current Project Files ---\n" . json_encode($project_files, JSON_PRETTY_PRINT);
    }

    $post_data = json_encode([
        'model' => $model,
        'max_tokens' => 6400, // Erhöht für vollständige HTML/CSS/JS Dateien
        'system' => $system_prompt,
        'messages' => [
            ['role' => 'user', 'content' => $user_message_content]
        ]
    ]);

    $options = [
        'method'  => 'POST',
        'headers' => [
            'Content-Type'        => 'application/json',
            'x-api-key'           => $api_key,
            'anthropic-version'   => $anthropic_version,
            'User-Agent'          => 'Moodle/' . $CFG->version . ' (mod_vibeyourcourse)'
        ],
        'post_data' => $post_data,
        'timeout'   => 120
    ];

    error_log("Claude API: Making request to " . $api_url);
    error_log("Claude API: Request payload size: " . strlen($post_data) . " bytes");
    
    // Versuche erst download_file_content, falls das fehlschlägt verwende direktes cURL
    $result = download_file_content($api_url, $options);
    
    if ($result === false) {
        error_log("Claude API: download_file_content failed, trying direct cURL...");
        
        // Fallback auf direktes cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $api_key,
                'anthropic-version: ' . $anthropic_version,
                'User-Agent: Moodle/' . $CFG->version . ' (mod_vibeyourcourse)'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        
        $result = curl_exec($ch);
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        
        error_log("Claude API: Direct cURL HTTP Code: " . $http_code);
        if ($curl_errno) {
            error_log("Claude API: cURL Error (" . $curl_errno . "): " . $curl_error);
        }
        
        if ($result === false || $http_code >= 400) {
            error_log("Claude API: Direct cURL also failed");
        }
    }
    
    error_log("Claude API: Response received, size: " . (is_string($result) ? strlen($result) : 'false') . " bytes");

    if ($result === false) {
        // Mehr Details über den cURL-Fehler loggen
        $curl_error = "cURL request failed";
        if (function_exists('curl_error') && function_exists('curl_errno')) {
            // Diese Informationen sind nur verfügbar wenn wir direkten cURL-Zugang haben
            error_log("Claude API: cURL error details not available through download_file_content");
        }
        error_log("Claude API: Request failed, API URL: " . $api_url);
        error_log("Claude API: Headers sent: " . print_r($options['headers'], true));
        throw new moodle_exception('API request failed. Check Moodle logs for cURL errors.', 'mod_vibeyourcourse');
    }

    error_log("Claude API: Raw response: " . substr($result, 0, 1000) . (strlen($result) > 1000 ? "... (truncated)" : ""));
    error_log("Claude API: FULL Raw response (for debugging): " . $result);
    
    $response_data = json_decode($result);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Claude API: JSON decode error: " . json_last_error_msg());
        error_log("Claude API: Full raw response: " . $result);
        throw new moodle_exception('AI returned an unreadable response format. JSON Error: ' . json_last_error_msg(), 'mod_vibeyourcourse');
    }
    
    if (empty($response_data)) {
        error_log("Claude API: Response data is empty after JSON decode");
        throw new moodle_exception('AI returned empty response data.', 'mod_vibeyourcourse');
    }
    
    error_log("Claude API: Decoded response structure: " . print_r($response_data, true));
    
    // Debug: Vergleiche Original-Response-Größe mit extrahierter Text-Größe
    $original_size = strlen($result);
    error_log("Claude API: Original response size: " . $original_size . " bytes");
    
    // Prüfe die Response-Struktur schrittweise
    if (!isset($response_data->content)) {
        error_log("Claude API: Missing 'content' field in response");
        throw new moodle_exception('AI response missing content field.', 'mod_vibeyourcourse');
    }
    
    if (!is_array($response_data->content) && !is_object($response_data->content)) {
        error_log("Claude API: 'content' field is not array/object, type: " . gettype($response_data->content));
        throw new moodle_exception('AI response content field has wrong type.', 'mod_vibeyourcourse');
    }
    
    if (empty($response_data->content[0])) {
        error_log("Claude API: Missing content[0] in response");
        error_log("Claude API: Content structure: " . print_r($response_data->content, true));
        throw new moodle_exception('AI response missing content[0].', 'mod_vibeyourcourse');
    }
    
    if (empty($response_data->content[0]->text)) {
        error_log("Claude API: Missing content[0]->text in response structure");
        error_log("Claude API: content[0] structure: " . print_r($response_data->content[0], true));
        throw new moodle_exception('AI response missing expected text field.', 'mod_vibeyourcourse');
    }
    
    // Sichere Extraktion des Text-Inhalts mit expliziter String-Konvertierung
    // Verwende json_encode/decode Zyklus zur Sicherstellung korrekter String-Behandlung
    $content_object = $response_data->content[0];
    error_log("Claude API: content[0] object type: " . gettype($content_object));
    error_log("Claude API: content[0] text type: " . gettype($content_object->text));
    
    // Direkte Zuweisung mit expliziter String-Konvertierung
    $json_string = (string) $content_object->text;
    
    // Alternative: Falls das nicht funktioniert, versuche über json_encode/decode
    if (strlen($json_string) === 0 || empty($json_string)) {
        error_log("Claude API: Direct assignment failed, trying json_encode/decode method");
        $content_array = json_decode(json_encode($response_data->content[0]), true);
        $json_string = $content_array['text'] ?? '';
    }
    
    // Debug-Information über die extrahierte String-Länge
    error_log("Claude API: Content text length: " . strlen($json_string));
    error_log("Claude API: Content text preview (first 500 chars): " . substr($json_string, 0, 500));
    error_log("Claude API: Content text ending (last 100 chars): " . substr($json_string, -100));
    
    // Zusätzliche Sicherheitsprüfung für leeren String
    if (empty($json_string) || strlen($json_string) === 0) {
        error_log("Claude API: Extracted json_string is empty!");
        throw new moodle_exception('AI response text content is empty after extraction.', 'mod_vibeyourcourse');
    }
    
    // WICHTIG: Claude API liefert JSON oft in Markdown-Codeblöcken!
    // Entferne ```json am Anfang und ``` am Ende
    $json_string = trim($json_string);
    
    if (str_starts_with($json_string, '```json')) {
        error_log("Claude API: Removing markdown code block wrapper");
        // Entferne ```json am Anfang (und optional \n)
        $json_string = preg_replace('/^```json\s*\n?/', '', $json_string);
        // Entferne ``` am Ende
        $json_string = preg_replace('/\n?```\s*$/', '', $json_string);
        $json_string = trim($json_string);
        error_log("Claude API: After markdown removal - Length: " . strlen($json_string));
        error_log("Claude API: After markdown removal - First 100 chars: " . substr($json_string, 0, 100));
        error_log("Claude API: After markdown removal - Last 100 chars: " . substr($json_string, -100));
    }
    
    // Prüfe auf abgeschnittene JSON-Response
    if (!str_ends_with($json_string, '}')) {
        error_log("Claude API: Response scheint abgeschnitten zu sein (endet nicht mit '}')");
        error_log("Claude API: Actual string length: " . strlen($json_string));
        error_log("Claude API: Last 200 chars: " . substr($json_string, -200));
        
        // Versuche trotzdem zu parsen, falls es ein anderes Format ist
        error_log("Claude API: Attempting to parse truncated response anyway...");
    }
    
    $claude_response = json_decode($json_string);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Claude API: Error parsing Claude's JSON response: " . json_last_error_msg());
        error_log("Claude API: Full Claude response text: " . $json_string);
        
        // Zusätzliche Diagnose für häufige Probleme
        if (json_last_error() === JSON_ERROR_SYNTAX) {
            $lines = explode("\n", $json_string);
            error_log("Claude API: Response hat " . count($lines) . " Zeilen, Länge: " . strlen($json_string) . " Zeichen");
            
            // Prüfe auf unvollständige JSON-Struktur
            if (!str_contains($json_string, '"files"') || !str_contains($json_string, '"response"')) {
                throw new moodle_exception('AI response ist unvollständig. Max-Token-Limit möglicherweise erreicht.', 'mod_vibeyourcourse');
            }
        }
        
        throw new moodle_exception('AI returned invalid JSON format: ' . json_last_error_msg(), 'mod_vibeyourcourse');
    }
    
    if (!isset($claude_response->response)) {
        error_log("Claude API: Missing 'response' field in Claude's JSON");
        error_log("Claude API: Claude response structure: " . print_r($claude_response, true));
        throw new moodle_exception('AI response missing required "response" field.', 'mod_vibeyourcourse');
    }
    
    if (!isset($claude_response->files)) {
        error_log("Claude API: Missing 'files' field in Claude's JSON");
        throw new moodle_exception('AI response missing required "files" field.', 'mod_vibeyourcourse');
    }
    
    if (!is_object($claude_response->files)) {
        error_log("Claude API: 'files' field is not an object, type: " . gettype($claude_response->files));
        throw new moodle_exception('AI response "files" field must be an object.', 'mod_vibeyourcourse');
    }

    return $claude_response;
}
