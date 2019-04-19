<?php
/**
 * moosh - Moodle Shell
 *
 * @copyright  2016 onwards Tomasz Muras
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moosh\Command\Moodle23\Course;
use Moosh\MooshCommand;
use course_enrolment_manager;
use context_course;


class CourseUnenrolbyname extends MooshCommand
{
    public function __construct()
    {
        parent::__construct('unenrolbyname', 'course');

		$this->addArgument('cshortname');
        $this->addArgument('username');
        $this->minArguments = 0;
        $this->maxArguments = 255;

        //$this->addOption('t|test', 'option with no value');
        //$this->addOption('o|option:', 'option with value and default', 'default');

    }

    public function execute()
    {
        global $CFG, $DB, $PAGE;
		
        require_once($CFG->dirroot . '/enrol/locallib.php');
        require_once($CFG->dirroot . '/group/lib.php');

        $options = $this->expandedOptions;
        $arguments = $this->arguments;

		//find course
		$course = $DB->get_record('course', array('shortname' => $arguments[0]), '*', MUST_EXIST);
		//find user
        $user = $DB->get_record('user', array('username' => $arguments[1]), '*', MUST_EXIST);

        $manager = new course_enrolment_manager($PAGE, $course);
        $enrolments = $manager->get_user_enrolments($user->id);
		foreach ($enrolments as $enrolment) {
			list ($instance, $plugin) = $manager->get_user_enrolment_components($enrolment);
			if ($instance && $plugin && $plugin->allow_unenrol_user($instance, $enrolment)) {
				$plugin->unenrol_user($instance, $user->id);
			}
		}
    }
}
