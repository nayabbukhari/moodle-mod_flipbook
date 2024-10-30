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

// namespace mod_flipbook\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Form for adding and editing Flipbook instances
 *
 * @package    mod_flipbook
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 class mod_flipbook_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // General fieldset.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name field.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', empty($CFG->formatstringstriptags) ? PARAM_CLEANHTML : PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // File picker for uploading PDF.
        $mform->addElement('filepicker', 'pdf', get_string('pdf', 'mod_flipbook'), null, array('accepted_types' => '.pdf'));
        $mform->addRule('pdf', null, 'required', null, 'client');
        $mform->setType('pdf', PARAM_FILE); // Set correct type for file handling.

        // Standard course module elements.
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Pre-process form data before displaying it
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        // Prepare the PDF file field for display.
        $draftitemid = file_get_submitted_draft_itemid('pdf');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_flipbook', 'pdf', 0, array('subdirs' => false));
        $defaultvalues['pdf'] = $draftitemid;
    }

    /**
     * Custom save for file fields
     */
    public function save($instanceid, $data) {
        
        // Ensure to check if we are updating or adding a new instance
    if ($instanceid) {
        // Update existing instance
        $this->update_instance($data);
    } else {
        // Create new instance
        $this->add_instance($data);
    }
    
    // Save PDF file to database.
    $fileoptions = array('subdirs' => false);
    file_save_draft_area_files($data->pdf, $this->context->id, 'mod_flipbook', 'pdf', 0, $fileoptions);
    }
}
