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
 * This file contains tests that walks a OU multiple response question through
 * various interaction models.
 *
 * @package    qtype_multichoiceset
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/simpletest/helpers.php');
require_once($CFG->dirroot . '/question/type/multichoiceset/simpletest/helper.php');


/**
 * Unit tests ofr the OU multiple response question type.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_walkthrough_test extends qbehaviour_walkthrough_test_base {

        public function test_deferredfeedback_feedback_multichoiceset() {
        // Create a multichoice, multi question.
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;

        $this->start_attempt_at_question($mc, 'deferredfeedback', 2);
        $this->process_submission($mc->get_correct_response());
        $this->quba->finish_all_questions();

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(2);
        $this->check_current_output(
                $this->get_contains_mc_checkbox_expectation('choice0', false, true),
                $this->get_contains_mc_checkbox_expectation('choice1', false, false),
                $this->get_contains_mc_checkbox_expectation('choice2', false, true),
                $this->get_contains_mc_checkbox_expectation('choice3', false, false),
                $this->get_contains_correct_expectation(),
                new PatternExpectation('/class="r0 correct"/'),
                new PatternExpectation('/class="r1"/'));
    }
}