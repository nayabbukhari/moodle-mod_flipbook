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
 * View Flipbook instance
 *
 * @package    mod_flipbook
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id or Activity instance id.
$id = optional_param('id', 0, PARAM_INT);
$f = optional_param('f', 0, PARAM_INT);


$cm = get_coursemodule_from_id('flipbook', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$moduleinstance = $DB->get_record('flipbook', ['id' => $cm->instance], '*', MUST_EXIST);

$context = context_module::instance($cm->id);
require_login($course, true, $cm);

// Trigger event.
\mod_flipbook\event\course_module_viewed::create_from_record($moduleinstance, $cm, $course)->trigger();

$PAGE->set_url('/mod/flipbook/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));

// Fetch the flipbook file data.
$sql = "SELECT f.id, f.pdf, fl.filename
        FROM {flipbook} f
        JOIN {files} fl ON fl.itemid = f.pdf
        WHERE f.id = :instanceid AND fl.filename IS NOT NULL AND fl.filename != '.'";
        
$params = ['instanceid' => $cm->instance];
$flipbook_data = $DB->get_record_sql($sql, $params);

// Check if records exist.
if (!$flipbook_data) {
    throw new moodle_exception('noflipbook', 'mod_flipbook');
}

// Display header.
echo $OUTPUT->header();

// Construct URL for the PDF file.
$pdfurl = moodle_url::make_pluginfile_url($context->id, 'mod_flipbook', 'content', $flipbook_data->id, '/', $flipbook_data->filename)->out();
echo '<p><a href="' . $pdfurl . '" download>Download Flipbook PDF</a></p>';
// Output URL for debugging if needed.
// print_object($pdfurl->out());

// Display the flipbook container.
echo '<div id="flipbook-container" style="width: 100%; height: 600px; background: #eee;"></div>';

// Initialize the flipbook JavaScript with the PDF URL.
$PAGE->requires->js_call_amd('mod_flipbook/flipbook', 'init', [$pdfurl]);

// Output footer.
echo $OUTPUT->footer();
