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
 * AJAX endpoints for vibeyourcourse module.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2025 Alexander Mikasch
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/filelib.php');

// Allow CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get parameters
$action = optional_param('action', '', PARAM_ALPHA);
$cmid = optional_param('cmid', 0, PARAM_INT);

// Debug output
error_log("AJAX Request - Action: $action, CMID: $cmid");
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));

if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action parameter']);
    exit();
}

if (empty($cmid)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing cmid parameter']);
    exit();
}

// Get course module and context
try {
    $cm = get_coursemodule_from_id('vibeyourcourse', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $cm->instance), '*', MUST_EXIST);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode(['error' => 'Course module not found: ' . $e->getMessage()]);
    exit();
}

// Verify login and capabilities
try {
    require_login($course, true, $cm);
    $modulecontext = context_module::instance($cm->id);
    require_capability('mod/vibeyourcourse:view', $modulecontext);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied: ' . $e->getMessage()]);
    exit();
}

// Validate sesskey for write operations
if (in_array($action, ['create_project', 'createproject', 'update_project', 'updateproject', 'delete_project', 'deleteproject', 'process_prompt', 'processprompt'])) {
    $sesskey = optional_param('sesskey', '', PARAM_RAW);
    if (!confirm_sesskey($sesskey)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid session key']);
        exit();
    }
}

// Set JSON response headers
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_projects':
        case 'getprojects':
            echo json_encode(get_user_projects($moduleinstance->id, $USER->id));
            break;
            
        case 'create_project':
        case 'createproject':
            $project_data = [
                'name' => required_param('name', PARAM_TEXT),
                'runtime' => required_param('runtime', PARAM_ALPHA),
                'template' => optional_param('template', '', PARAM_TEXT)
            ];
            echo json_encode(create_project($moduleinstance->id, $USER->id, $project_data));
            break;
            
        case 'get_project':
        case 'getproject':
            $project_id = required_param('project_id', PARAM_INT);
            echo json_encode(get_project_details($project_id, $USER->id));
            break;
            
        case 'update_project':
        case 'updateproject':
            $project_id = required_param('project_id', PARAM_INT);
            $project_data = [
                'name' => optional_param('name', null, PARAM_TEXT),
                'files' => optional_param('files', null, PARAM_RAW),
                'status' => optional_param('status', null, PARAM_ALPHA),
                'completion_percentage' => optional_param('completion_percentage', null, PARAM_INT)
            ];
            echo json_encode(update_project($project_id, $USER->id, $project_data));
            break;
            
        case 'delete_project':
        case 'deleteproject':
            $project_id = required_param('project_id', PARAM_INT);
            echo json_encode(delete_project($project_id, $USER->id));
            break;
            
        case 'process_prompt':
        case 'processprompt':
            $prompt = required_param('prompt', PARAM_TEXT);
            $project_id = optional_param('project_id', 0, PARAM_INT);
            echo json_encode(process_user_prompt($prompt, $project_id, $USER->id, $moduleinstance->id));
            break;
            
        case 'get_course_files':
        case 'getcoursefiles':
            echo json_encode(get_course_files($course->id));
            break;
            
        default:
            throw new moodle_exception('invalidaction', 'mod_vibeyourcourse');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Get all projects for a user in this activity
 */
function get_user_projects($vibeyourcourse_id, $userid) {
    global $DB;
    
    $projects = $DB->get_records('vibeyourcourse_projects', 
        array('vibeyourcourse_id' => $vibeyourcourse_id, 'userid' => $userid), 
        'timemodified DESC'
    );
    
    $result = [];
    foreach ($projects as $project) {
        $result[] = [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ];
    }
    
    return ['success' => true, 'projects' => $result];
}

/**
 * Create a new project
 */
function create_project($vibeyourcourse_id, $userid, $project_data) {
    global $DB;
    
    // Validate runtime against allowed runtimes
    $moduleinstance = $DB->get_record('vibeyourcourse', array('id' => $vibeyourcourse_id), '*', MUST_EXIST);
    $allowed_runtimes = json_decode($moduleinstance->allowed_runtimes, true);
    
    if (!in_array($project_data['runtime'], $allowed_runtimes)) {
        throw new moodle_exception('invalidruntime', 'mod_vibeyourcourse');
    }
    
    // Create initial project files based on runtime
    $initial_files = get_initial_project_files($project_data['runtime'], $project_data['template']);
    
    $project = new stdClass();
    $project->vibeyourcourse_id = $vibeyourcourse_id;
    $project->userid = $userid;
    $project->project_name = $project_data['name'];
    $project->runtime_type = $project_data['runtime'];
    $project->template_id = $project_data['template'];
    $project->project_files = json_encode($initial_files);
    $project->status = 'draft';
    $project->completion_percentage = 0;
    $project->ai_hints_used = 0;
    $project->ai_generations_used = 0;
    $project->grade = 0;
    $project->timecreated = time();
    $project->timemodified = time();
    
    $project_id = $DB->insert_record('vibeyourcourse_projects', $project);
    
    return [
        'success' => true, 
        'project_id' => $project_id,
        'project' => [
            'id' => $project_id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ]
    ];
}

/**
 * Get detailed project information including files
 */
function get_project_details($project_id, $userid) {
    global $DB;
    
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    return [
        'success' => true,
        'project' => [
            'id' => $project->id,
            'project_name' => $project->project_name,
            'runtime_type' => $project->runtime_type,
            'template_id' => $project->template_id,
            'project_files' => json_decode($project->project_files, true),
            'status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'ai_hints_used' => $project->ai_hints_used,
            'ai_generations_used' => $project->ai_generations_used,
            'grade' => $project->grade,
            'feedback' => $project->feedback,
            'timecreated' => $project->timecreated,
            'timemodified' => $project->timemodified
        ]
    ];
}

/**
 * Update an existing project
 */
function update_project($project_id, $userid, $project_data) {
    global $DB;
    
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    $updated = false;
    
    if ($project_data['name'] !== null) {
        $project->project_name = $project_data['name'];
        $updated = true;
    }
    
    if ($project_data['files'] !== null) {
        $project->project_files = $project_data['files'];
        $updated = true;
    }
    
    if ($project_data['status'] !== null) {
        $project->status = $project_data['status'];
        $updated = true;
    }
    
    if ($project_data['completion_percentage'] !== null) {
        $project->completion_percentage = $project_data['completion_percentage'];
        $updated = true;
    }
    
    if ($updated) {
        $project->timemodified = time();
        $DB->update_record('vibeyourcourse_projects', $project);
    }
    
    return ['success' => true, 'updated' => $updated];
}

/**
 * Delete a project
 */
function delete_project($project_id, $userid) {
    global $DB;
    
    // Verify ownership
    $project = $DB->get_record('vibeyourcourse_projects', 
        array('id' => $project_id, 'userid' => $userid), 
        '*', MUST_EXIST
    );
    
    // Delete related executions
    $DB->delete_records('vibeyourcourse_executions', array('project_id' => $project_id));
    
    // Delete related AI interactions
    $DB->delete_records('vibeyourcourse_ai_interactions', array('project_id' => $project_id));
    
    // Delete related analytics
    $DB->delete_records('vibeyourcourse_analytics', array('project_id' => $project_id));
    
    // Delete the project itself
    $DB->delete_records('vibeyourcourse_projects', array('id' => $project_id));
    
    return ['success' => true];
}

/**
 * Get initial project files based on runtime and template
 */
function get_initial_project_files($runtime, $template = '') {
    $files = [];
    
    switch ($runtime) {
        case 'python':
            $files['main.py'] = "# Welcome to your Python project!\nprint('Hello, World!')\n";
            if ($template) {
                // Add template-specific files
                $files = array_merge($files, get_template_files($runtime, $template));
            }
            break;
            
        case 'javascript':
            $files['index.html'] = "<!DOCTYPE html>\n<html>\n<head>\n    <title>My JavaScript Project</title>\n</head>\n<body>\n    <h1>Vote for Moodle Goes Vibe!</h1>\n    <script src=\"script.js\"></script>\n</body>\n</html>";
            $files['script.js'] = "// Welcome to your JavaScript project!\nconsole.log('Hello, World!');\n";
            $files['style.css'] = "/* Add your styles here */\nbody {\n    font-family: Arial, sans-serif;\n    margin: 0;\n    padding: 20px;\n}\n";
            break;
            
        case 'webcontainer':
            $files['package.json'] = json_encode([
                "name" => "my-project",
                "version" => "1.0.0",
                "scripts" => [
                    "start" => "node index.js"
                ]
            ], JSON_PRETTY_PRINT);
            $files['index.js'] = "// Welcome to your Node.js project!\nconsole.log('Hello, World!');\n";
            break;
    }
    
    return $files;
}

/**
 * Process user prompt for AI interaction, generate/update files, and save to the project.
 */
function process_user_prompt($prompt, $project_id, $userid, $vibeyourcourse_id) {
    global $DB;
    
    try {
        $prompt = clean_text($prompt);
        if (empty(trim($prompt))) {
            return ['success' => false, 'error' => 'Prompt darf nicht leer sein.'];
        }
        
        $project_files = [];
        $project = null;
        if ($project_id > 0) {
            $project = $DB->get_record('vibeyourcourse_projects', ['id' => $project_id, 'userid' => $userid]);
            if ($project) {
                $project_files = json_decode($project->project_files, true) ?: [];
            }
        }

        // Claude API Call (bleibt gleich)
        $claude_response = vibeyourcourse_call_claude_api($prompt, $project_files);

        if (!$project) {
            $project_data = [
                'name' => 'AI Project: ' . substr($prompt, 0, 40) . '...',
                'runtime' => 'javascript',
                'template' => ''
            ];
            $new_project_result = create_project($vibeyourcourse_id, $userid, $project_data);
            $project_id = $new_project_result['project_id'];
            $project = $DB->get_record('vibeyourcourse_projects', ['id' => $project_id]);
        }
        
        // Vereinfachte File-Struktur für direktes HTML/CSS/JS
        $files = $claude_response->files;
        
        // Stelle sicher, dass wir die wichtigsten Dateien haben
        if (!isset($files->{'index.html'})) {
            // Erstelle ein Standard HTML wenn keins vorhanden
            $files->{'index.html'} = '<!DOCTYPE html>
<html>
<head>
    <title>Generated App</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div id="app">
        <h1>App wurde generiert!</h1>
        <p>Überprüfe die Dateien im Code-Tab.</p>
    </div>
</body>
</html>';
        }
        
        $updated_files = array_merge($project_files, (array)$files);
        $project->project_files = json_encode($updated_files);
        $project->timemodified = time();
        $DB->update_record('vibeyourcourse_projects', $project);

        $interaction_record = new stdClass();
        $interaction_record->userid = $userid;
        $interaction_record->project_id = $project_id;
        $interaction_record->user_prompt = $prompt;
        $interaction_record->ai_response = $claude_response->response;
        $interaction_record->timecreated = time();
        
        $interaction_id = $DB->insert_record('vibeyourcourse_ai_interactions', $interaction_record);
        
        return [
            'success' => true,
            'response' => $claude_response->response,
            'files' => $updated_files,
            'interaction_id' => $interaction_id,
            'preview_ready' => true,
            'message' => 'Project updated successfully by AI.'
        ];
        
    } catch (Exception $e) {
        error_log("Error in process_user_prompt: " . $e->getMessage());
        
        // Spezifische Fehlermeldungen für häufige Probleme
        $error_message = $e->getMessage();
        $debug_details = "";
        
        if (strpos($error_message, 'Claude API key is not configured') !== false) {
            $error_message = 'Claude API-Schlüssel ist nicht in den Plugin-Einstellungen konfiguriert.';
        } else if (strpos($error_message, 'API request failed') !== false) {
            $error_message = 'Verbindung zur Claude-API fehlgeschlagen. Bitte prüfen Sie Ihre Internetverbindung.';
        } else if (strpos($error_message, 'unreadable response format') !== false) {
            $error_message = 'Claude-API hat eine ungültige Antwort gesendet. Bitte versuchen Sie es erneut.';
        } else if (strpos($error_message, 'invalid data structure') !== false) {
            $error_message = 'Claude-API Antwort hat ein ungültiges Format. Bitte versuchen Sie es erneut.';
        } else if (strpos($error_message, 'missing content field') !== false) {
            $error_message =$e->getMessage(). 'Claude-API Response hat unerwartete Struktur (kein content field).';
            $debug_details = " | Wahrscheinlich API-Fehler oder falscher API-Key";
        }
        
        return [
            'success' => false,
            'error' => $error_message,
            'debug_info' => 'Detailed error: ' . $e->getMessage() . $debug_details . (isset($test) ? $test : '')
        ];
    }
}

/**
 * Get course files (resources) from the current course
 */
function get_course_files($course_id) {
    global $CFG, $DB, $USER;

    require_once($CFG->libdir . '/filelib.php');
    $fs = get_file_storage();
    $files = [];

    // 0) Kurs-Kontext
    $coursectx = context_course::instance($course_id);

    // Hilfs-Lambda: URL & Metadaten bauen
    $build_entry = function($storedfile, $displayname, $source, $cmid = null) use ($CFG) {
        $contextid = $storedfile->get_contextid();
        $component = $storedfile->get_component();      // z.B. 'mod_resource'
        $filearea  = $storedfile->get_filearea();       // z.B. 'content'
        $itemid    = $storedfile->get_itemid();         // meist 0
        $filepath  = $storedfile->get_filepath();       // beginnt/endet mit '/'
        $filename  = $storedfile->get_filename();

        // Sichere URL bauen (encodiert Sonderzeichen korrekt)
        $url = moodle_url::make_pluginfile_url(
            $contextid, $component, $filearea, $itemid, $filepath, $filename
        )->out(false);

        // MIME & Typ
        $mimetype = $storedfile->get_mimetype();
        $type = get_file_type_from_mimetype($mimetype);

        return [
            'name'         => $filename,
            'display_name' => $displayname,
            'type'         => $type,
            'size'         => display_size($storedfile->get_filesize()),
            'mimetype'     => $mimetype,
            'url'          => $url,
            'intro'        => null,
            'source'       => $source,
            'cmid'         => $cmid,
            'path'         => $filepath
        ];
    };

    // 1) Activities im Kurs (Resource + Folder)
    $modinfo = get_fast_modinfo($course_id, $USER->id);
    foreach ($modinfo->cms as $cm) {
        if (!$cm->uservisible) { continue; } // respektiert Sichtbarkeit/Zugriff
        if (!in_array($cm->modname, ['resource', 'folder'])) { continue; }

        $modctx = context_module::instance($cm->id);

        // a) RESOURCE -> content/0/*
        if ($cm->modname === 'resource') {
            $storedfiles = $fs->get_area_files($modctx->id, 'mod_resource', 'content', 0, 'filepath, filename', false);
            foreach ($storedfiles as $sf) {
                $entry = $build_entry($sf, $cm->name ?: $sf->get_filename(), 'resource', $cm->id);
                // optional: Intro laden (für UI)
                if ($cm->has_view()) {
                    $res = $DB->get_record('resource', ['id' => $cm->instance], 'intro', IGNORE_MISSING);
                    $entry['intro'] = $res->intro ?? null;
                }
                $files[] = $entry;
            }
        }

        // b) FOLDER -> content/0/<pfad>/<datei>
        if ($cm->modname === 'folder') {
            $storedfiles = $fs->get_area_files($modctx->id, 'mod_folder', 'content', 0, 'filepath, filename', false);
            foreach ($storedfiles as $sf) {
                $displaypath = trim($sf->get_filepath(), '/');
                $dn = $cm->name;
                if ($displaypath !== '') { $dn .= ' / ' . $displaypath; }
                $dn .= ' / ' . $sf->get_filename();

                $entry = $build_entry($sf, $dn, 'folder', $cm->id);
                // optional: Intro laden
                $fld = $DB->get_record('folder', ['id' => $cm->instance], 'intro', IGNORE_MISSING);
                $entry['intro'] = $fld->intro ?? null;
                $files[] = $entry;
            }
        }
    }

    // 2) Kurs-Dateien: summary & overviewfiles (itemid i.d.R. 0)
    foreach (['summary', 'overviewfiles'] as $area) {
        $storedfiles = $fs->get_area_files($coursectx->id, 'course', $area, 0, 'filepath, filename', false);
        foreach ($storedfiles as $sf) {
            $dn = 'Course (' . $area . '): ' . $sf->get_filename();
            $files[] = $build_entry($sf, $dn, 'course', null);
        }
    }

    // 3) Optional: auf Webservice-URL + Token umbiegen (für externe SPAs)
    //    => nur aktivieren, wenn du hier mit Token arbeiten willst
    /*
    $token = optional_param('ws_token', '', PARAM_ALPHANUMEXT); // z.B. via AJAX mitgeben
    if (!empty($token)) {
        foreach ($files as &$f) {
            $wsurl = str_replace('/pluginfile.php/', '/webservice/pluginfile.php/', $f['url']);
            $wsurl .= (strpos($wsurl, '?') === false ? '?' : '&') . 'token=' . urlencode($token);
            $f['url'] = $wsurl;
        }
        unset($f);
    }
    */

    return [
        'success' => true,
        'files' => $files,
        'debug' => [
            'course_id' => $course_id,
            'total_files' => count($files),
        ]
    ];
}
/**
 * Get file type identifier from MIME type
 */
function get_file_type_from_mimetype($mimetype) {
    $type_map = [
        'application/pdf' => 'pdf',
        'application/vnd.ms-powerpoint' => 'powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'powerpoint',
        'application/msword' => 'word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
        'application/vnd.ms-excel' => 'excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
        'application/zip' => 'archive',
        'application/x-zip-compressed' => 'archive',
        'text/plain' => 'text',
        'image/jpeg' => 'image',
        'image/png' => 'image',
        'image/gif' => 'image',
        'video/mp4' => 'video',
        'video/avi' => 'video',
        'audio/mpeg' => 'audio',
        'audio/wav' => 'audio'
    ];
    
    return isset($type_map[$mimetype]) ? $type_map[$mimetype] : 'file';
}

/**
 * Format file size in human readable format
 */
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

/**
 * Get template-specific files
 */
function get_template_files($runtime, $template) {
    // This would be expanded to load actual templates
    return [];
}
