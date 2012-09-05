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
 * Unit tests for the OU multiple response question type class.
 *
 * @package    qtype_multichoiceset
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/simpletest/helpers.php');
require_once($CFG->dirroot . '/question/type/multichoiceset/questiontype.php');
require_once($CFG->dirroot . '/question/type/multichoiceset/simpletest/helper.php');


/**
 * Unit tests for (some of) question/type/multichoiceset/questiontype.php.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multichoiceset_test extends UnitTestCase {
    /**
     * @var qtype_multichoiceset
     */
    private $qtype;

    public function setUp() {
        $this->qtype = new qtype_multichoiceset();
    }

    public function tearDown() {
        $this->qtype = null;
    }

    public function assert_same_xml($expectedxml, $xml) {
        $this->assertEqual(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    public function test_name() {
        $this->assertEqual($this->qtype->name(), 'multichoiceset');
    }

    public function test_initialise_question_instance() {
        $qdata = qtype_multichoiceset_test_helper::get_question_data();
        $expectedq = qtype_multichoiceset_test_helper::make_an_multichoiceset_two_of_four();
        $qdata->stamp = $expectedq->stamp;
        $qdata->version = $expectedq->version;
        $qdata->timecreated = $expectedq->timecreated;
        $qdata->timemodified = $expectedq->timemodified;

        $question = $this->qtype->make_question($qdata);

        $this->assertEqual($expectedq, $question);
    }

    public function test_can_analyse_responses() {
        $this->assertTrue($this->qtype->can_analyse_responses());
    }

    public function test_get_possible_responses() {
        $q = new stdClass();
        $q->id = 1;
        $q->options->answers[1] = (object) array('answer' => 'frog', 'answerformat' => FORMAT_HTML, 'fraction' => 1);
        $q->options->answers[2] = (object) array('answer' => 'toad', 'answerformat' => FORMAT_HTML, 'fraction' => 1);
        $q->options->answers[3] = (object) array('answer' => 'newt', 'answerformat' => FORMAT_HTML, 'fraction' => 0);

        $this->assertEqual(array(
            1 => array(1 => new question_possible_response('frog', 1)),
            2 => array(2 => new question_possible_response('toad', 1)),
            3 => array(3 => new question_possible_response('newt', 0))
        ), $this->qtype->get_possible_responses($q));
        $responses = $this->qtype->get_possible_responses($q);
    }

    public function test_get_random_guess_score() {
        $questiondata = new stdClass();
        $questiondata->options->answers = array(
            1 => new question_answer(1, 'A', 1, '', FORMAT_HTML),
            2 => new question_answer(2, 'B', 0, '', FORMAT_HTML),
            3 => new question_answer(3, 'C', 0, '', FORMAT_HTML),
        );
		$this->assertNull($this->qtype->get_random_guess_score($questiondata));
    }

    public function test_xml_import() {
        $xml = '  <question type="multichoiceset">
    <name>
      <text>All or nothing multiple response question</text>
    </name>
    <questiontext format="html">
      <text>Which are the odd numbers?</text>
    </questiontext>
    <generalfeedback>
      <text>General feedback.</text>
    </generalfeedback>
    <defaultgrade>6</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <shuffleanswers>true</shuffleanswers>
    <correctfeedback>
      <text>Well done.</text>
    </correctfeedback>
    <incorrectfeedback>
      <text>Completely wrong!</text>
    </incorrectfeedback>
    <answernumbering>abc</answernumbering>
    <answer fraction="100">
      <text>One</text>
      <feedback>
        <text>Specific feedback to correct answer.</text>
      </feedback>
    </answer>
    <answer fraction="0">
      <text>Two</text>
      <feedback>
        <text>Specific feedback to wrong answer.</text>
      </feedback>
    </answer>
    <answer fraction="100">
      <text>Three</text>
      <feedback>
        <text>Specific feedback to correct answer.</text>
      </feedback>
    </answer>
    <answer fraction="0">
      <text>Four</text>
      <feedback>
        <text>Specific feedback to wrong answer.</text>
      </feedback>
    </answer>
    <hint>
      <text>Try again.</text>
      <shownumcorrect />
    </hint>
    <hint>
      <text>Hint 2.</text>
      <shownumcorrect />
      <clearwrong />
      <options>1</options>
    </hint>
  </question>';
        $xmldata = xmlize($xml);

        $importer = new qformat_xml();
        $q = $importer->try_importing_using_qtypes(
                $xmldata['question'], null, null, 'multichoiceset');

        $expectedq = new stdClass();
        $expectedq->qtype = 'multichoiceset';
        $expectedq->name = 'All or nothing multiple response question';
        $expectedq->questiontext = 'Which are the odd numbers?';
        $expectedq->questiontextformat = FORMAT_HTML;
        $expectedq->generalfeedback = 'General feedback.';
        $expectedq->generalfeedbackformat = FORMAT_HTML;
        $expectedq->defaultmark = 6;
        $expectedq->length = 1;
        $expectedq->penalty = 0.3333333;

        $expectedq->shuffleanswers = 1;
        $expectedq->correctfeedback = array('text' => 'Well done.',
                'format' => FORMAT_HTML, 'files' => array());
        $expectedq->incorrectfeedback = array('text' => 'Completely wrong!',
                'format' => FORMAT_HTML, 'files' => array());
        $expectedq->shownumcorrect = false;
		$expectedq->answernumbering = 'abc';
        $expectedq->answer = array(
            array('text' => 'One', 'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Two', 'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Three', 'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Four', 'format' => FORMAT_HTML, 'files' => array())
        );
        $expectedq->correctanswer = array(1, 0, 1, 0);
        $expectedq->feedback = array(
            array('text' => 'Specific feedback to correct answer.',
                    'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Specific feedback to wrong answer.',
                    'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Specific feedback to correct answer.',
                    'format' => FORMAT_HTML, 'files' => array()),
            array('text' => 'Specific feedback to wrong answer.',
                    'format' => FORMAT_HTML, 'files' => array()),
        );

        $expectedq->hint = array(
                array('text' => 'Try again.', 'format' => FORMAT_HTML, 'files' => array()),
                array('text' => 'Hint 2.', 'format' => FORMAT_HTML, 'files' => array()));
        $expectedq->hintshownumcorrect = array(true, true);
        $expectedq->hintclearwrong = array(false, true);
        $expectedq->hintshowchoicefeedback = array(false, true);

        $this->assert(new CheckSpecifiedFieldsExpectation($expectedq), $q);
        $this->assertEqual($expectedq->answer, $q->answer);
    }

    public function test_xml_export() {
        $qdata = qtype_multichoiceset_test_helper::get_question_data();
        $qdata->defaultmark = 6;

        $exporter = new qformat_xml();
        $xml = $exporter->writequestion($qdata);

        $expectedxml = '<!-- question: 0  -->
  <question type="multichoiceset">
    <name>
      <text>All or nothing multiple response question</text>
    </name>
    <questiontext format="html">
      <text>Which are the odd numbers?</text>
    </questiontext>
    <generalfeedback format="html">
      <text>The odd numbers are One and Three.</text>
    </generalfeedback>
    <defaultgrade>6</defaultgrade>
    <penalty>0.3333333</penalty>
    <hidden>0</hidden>
    <shuffleanswers>true</shuffleanswers>
    <correctfeedback format="html">
      <text>Well done!</text>
    </correctfeedback>
    <incorrectfeedback format="html">
      <text>That is not right at all.</text>
    </incorrectfeedback>
    <shownumcorrect/>
    <answernumbering>123</answernumbering>
    <answer fraction="100" format="plain_text">
      <text>One</text>
      <feedback format="html">
        <text>One is odd.</text>
      </feedback>
    </answer>
    <answer fraction="0" format="plain_text">
      <text>Two</text>
      <feedback format="html">
        <text>Two is even.</text>
      </feedback>
    </answer>
    <answer fraction="100" format="plain_text">
      <text>Three</text>
      <feedback format="html">
        <text>Three is odd.</text>
      </feedback>
    </answer>
    <answer fraction="0" format="plain_text">
      <text>Four</text>
      <feedback format="html">
        <text>Four is even.</text>
      </feedback>
    </answer>
    <hint format="html">
      <text>Hint 1.</text>
      <shownumcorrect/>
    </hint>
    <hint format="html">
      <text>Hint 2.</text>
      <shownumcorrect/>
      <clearwrong/>
      <options>1</options>
    </hint>
  </question>
';

        $this->assert_same_xml($expectedxml, $xml);
    }
}
