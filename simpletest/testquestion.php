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
 * Unit tests for the All or nothing multiple response question class.
 *
 * @package   qtype_multichoiceset
 * @copyright 2008 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/simpletest/helpers.php');
require_once($CFG->dirroot . '/question/type/multichoiceset/question.php');
require_once($CFG->dirroot . '/question/type/multichoiceset/simpletest/helper.php');


/**
 * Unit tests for (some of) question/type/multichoiceset/questiontype.php.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_multichoiceset_question_test extends UnitTestCase {
    private $tolerance = 0.000001;

    public function test_grade_responses_right_right() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array('choice0' => '1', 'choice2' => '1'));
        $this->assertWithinMargin(1, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedright);
    }

    public function test_grade_responses_right() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array('choice0' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }

    public function test_grade_responses_wrong_wrong() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array('choice1' => '1', 'choice3' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }

    public function test_grade_responses_right_wrong_wrong() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(
                array('choice0' => '1', 'choice1' => '1', 'choice3' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }

    public function test_grade_responses_right_wrong() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array('choice0' => '1', 'choice1' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }

    public function test_grade_responses_right_right_wrong() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array(
                'choice0' => '1', 'choice2' => '1', 'choice3' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }

    public function test_grade_responses_right_right_wrong_wrong() {
        $mc = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $mc->shuffleanswers = false;
        $mc->start_attempt(new question_attempt_step(), 1);

        list($fraction, $state) = $mc->grade_response(array(
                'choice0' => '1', 'choice1' => '1', 'choice2' => '1', 'choice3' => '1'));
        $this->assertWithinMargin(0, $fraction, $this->tolerance);
        $this->assertEqual($state, question_state::$gradedwrong);
    }
}
