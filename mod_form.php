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
 * The main mod_vibeyourcourse configuration form.
 *
 * @package     mod_vibeyourcourse
 * @copyright   2024 Alexander Bias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_vibeyourcourse
 * @copyright  2024 Alexander Bias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_vibeyourcourse_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('vibeyourcoursename', 'mod_vibeyourcourse'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 1333), 'maxlength', 1333, 'client');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Runtime Configuration Section.
        $mform->addElement('header', 'runtimeconfig', get_string('runtimeconfig', 'mod_vibeyourcourse'));
        $mform->setExpanded('runtimeconfig', true);

        // Allowed Runtimes.
        $mform->addElement('advcheckbox', 'allow_python', get_string('allowpython', 'mod_vibeyourcourse'));
        $mform->addHelpButton('allow_python', 'allowpython', 'mod_vibeyourcourse');
        $mform->setDefault('allow_python', 1);

        $mform->addElement('advcheckbox', 'allow_javascript', get_string('allowjavascript', 'mod_vibeyourcourse'));
        $mform->addHelpButton('allow_javascript', 'allowjavascript', 'mod_vibeyourcourse');
        $mform->setDefault('allow_javascript', 1);

        $mform->addElement('advcheckbox', 'allow_webcontainer', get_string('allowwebcontainer', 'mod_vibeyourcourse'));
        $mform->addHelpButton('allow_webcontainer', 'allowwebcontainer', 'mod_vibeyourcourse');
        $mform->setDefault('allow_webcontainer', 0);

        // AI Configuration Section.
        $mform->addElement('header', 'aiconfig', get_string('aiconfig', 'mod_vibeyourcourse'));

        // AI Provider Selection.
        $aiproviders = [
            'openai' => 'OpenAI (GPT-3.5/GPT-4)',
            'anthropic' => 'Anthropic (Claude)',
            'disabled' => get_string('disabled', 'mod_vibeyourcourse')
        ];
        $mform->addElement('select', 'ai_provider', get_string('aiprovider', 'mod_vibeyourcourse'), $aiproviders);
        $mform->addHelpButton('ai_provider', 'aiprovider', 'mod_vibeyourcourse');
        $mform->setDefault('ai_provider', 'openai');

        // AI Assistance Level.
        $ailevels = [
            'full' => get_string('aiassistance_full', 'mod_vibeyourcourse'),
            'hints' => get_string('aiassistance_hints', 'mod_vibeyourcourse'),
            'minimal' => get_string('aiassistance_minimal', 'mod_vibeyourcourse'),
            'none' => get_string('aiassistance_none', 'mod_vibeyourcourse')
        ];
        $mform->addElement('select', 'ai_assistance_level', get_string('aiassistancelevel', 'mod_vibeyourcourse'), $ailevels);
        $mform->addHelpButton('ai_assistance_level', 'aiassistancelevel', 'mod_vibeyourcourse');
        $mform->setDefault('ai_assistance_level', 'hints');

        // Learning Configuration Section.
        $mform->addElement('header', 'learningconfig', get_string('learningconfig', 'mod_vibeyourcourse'));

        // Difficulty Level.
        $difficultylevels = [
            'beginner' => get_string('difficulty_beginner', 'mod_vibeyourcourse'),
            'intermediate' => get_string('difficulty_intermediate', 'mod_vibeyourcourse'),
            'advanced' => get_string('difficulty_advanced', 'mod_vibeyourcourse'),
            'adaptive' => get_string('difficulty_adaptive', 'mod_vibeyourcourse')
        ];
        $mform->addElement('select', 'difficulty_level', get_string('difficultylevel', 'mod_vibeyourcourse'), $difficultylevels);
        $mform->addHelpButton('difficulty_level', 'difficultylevel', 'mod_vibeyourcourse');
        $mform->setDefault('difficulty_level', 'adaptive');

        // Project Templates.
        $mform->addElement('textarea', 'project_templates', get_string('projecttemplates', 'mod_vibeyourcourse'), 
                          array('rows' => 10, 'cols' => 80));
        $mform->addHelpButton('project_templates', 'projecttemplates', 'mod_vibeyourcourse');
        $mform->setType('project_templates', PARAM_TEXT);
        
        $defaulttemplates = json_encode([
            'python_basics' => [
                'name' => 'Python Basics',
                'description' => 'Learn Python fundamentals',
                'runtime' => 'python',
                'template' => "print('Hello, World!')\n# Your code here"
            ],
            'web_page' => [
                'name' => 'Simple Web Page',
                'description' => 'Create your first web page',
                'runtime' => 'javascript',
                'template' => "<!DOCTYPE html>\n<html>\n<head>\n    <title>My Page</title>\n</head>\n<body>\n    <h1>Hello, World!</h1>\n</body>\n</html>"
            ]
        ], JSON_PRETTY_PRINT);
        $mform->setDefault('project_templates', $defaulttemplates);

        // Assessment Configuration.
        $mform->addElement('header', 'assessmentconfig', get_string('assessmentconfig', 'mod_vibeyourcourse'));

        // Auto Assessment.
        $mform->addElement('advcheckbox', 'auto_assessment', get_string('autoassessment', 'mod_vibeyourcourse'));
        $mform->addHelpButton('auto_assessment', 'autoassessment', 'mod_vibeyourcourse');
        $mform->setDefault('auto_assessment', 1);

        // Peer Review.
        $mform->addElement('advcheckbox', 'peer_review', get_string('peerreview', 'mod_vibeyourcourse'));
        $mform->addHelpButton('peer_review', 'peerreview', 'mod_vibeyourcourse');
        $mform->setDefault('peer_review', 0);

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    /**
     * Prepares the form before data are set
     *
     * @param array $data to be set
     * @return void
     */
    public function data_preprocessing(&$data) {
        // Decode JSON fields for form display.
        if (!empty($data['project_templates'])) {
            $templates = json_decode($data['project_templates'], true);
            if ($templates) {
                $data['project_templates'] = json_encode($templates, JSON_PRETTY_PRINT);
            }
        }
    }

    /**
     * Validates the form data
     *
     * @param array $data form data
     * @param array $files form files
     * @return array array of errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate that at least one runtime is enabled.
        if (empty($data['allow_python']) && empty($data['allow_javascript']) && empty($data['allow_webcontainer'])) {
            $errors['allow_python'] = get_string('error_noruntimesenabled', 'mod_vibeyourcourse');
        }

        // Validate project templates JSON.
        if (!empty($data['project_templates'])) {
            $templates = json_decode($data['project_templates'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['project_templates'] = get_string('error_invalidjson', 'mod_vibeyourcourse');
            }
        }

        return $errors;
    }
}
