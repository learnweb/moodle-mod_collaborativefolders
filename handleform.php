<?php

/**
 * Created by IntelliJ IDEA.
 * User: nina
 * Date: 06.12.16
 * Time: 16:45
 */
require_once($CFG->dirroot.'/mod/collaborativefolders/enrol_yourself_form.php');

class handleform
{
    function handle_my_form($id){
        $mform = new enrol_yourself_form($id);

        if ($mform->is_cancelled()) {
            // TODO course ->id does not work redirects to view
            redirect(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
            //Handle form cancel operation, if cancel button is present on form
        }
        if ($fromform = $mform->get_data()) {
            //In this case you process validated data. $mform->get_data() returns data posted in form.
        }

        // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.

        // Set default data (if any)
        // displays the form
        return $mform;
    }

}