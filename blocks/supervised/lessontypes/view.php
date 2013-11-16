<?php
require_once('../../../config.php');

global $DB, $OUTPUT, $PAGE;

$courseid   = required_param('courseid', PARAM_INT);
$blockid    = required_param('blockid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_supervised', $courseid);
}

$site = get_site();
require_login($course);
$PAGE->set_url('/blocks/supervised/lessontypes/view.php', array('courseid' => $courseid, 'blockid' => $blockid));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('lessontypespagetitle', 'block_supervised'));
include("breadcrumbs.php");

// Display header.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("lessontypesview", 'block_supervised'), 3);

// Prepare table data
$lessontypes = $DB->get_records('block_supervised_lessontype', array('courseid'=>$courseid));
$tabledata = array();
foreach ($lessontypes as $id=>$lessontype) {
    $editurl = new moodle_url('/blocks/supervised/lessontypes/mod.php', array('id' => $id, 'blockid' => $blockid, 'courseid' => $courseid));
    $deleteurl = new moodle_url('/blocks/supervised/lessontypes/delete.php', array('blockid' => $blockid, 'courseid' => $courseid, 'id' => $id));
    $iconedit = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));
    $icondelete = $OUTPUT->action_icon($deleteurl, new pix_icon('t/delete', get_string('delete')));
    $tabledata[] = array($lessontype->name . $iconedit . $icondelete);
}
$headname = get_string('lessontype', 'block_supervised');
$addurl = new moodle_url('/blocks/supervised/lessontypes/mod.php', array('blockid' => $blockid, 'courseid' => $courseid));
$iconadd = $OUTPUT->action_icon($addurl, new pix_icon('t/add', get_string('add')));
// Build table.
$table = new html_table();
$table->head = array($headname . $iconadd);
$table->data = $tabledata;
echo html_writer::table($table);

// Display footer.
echo $OUTPUT->footer();

?>