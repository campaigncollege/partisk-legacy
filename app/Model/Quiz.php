<?php 
/**
 * Copyright 2013-2014 Partisk.nu Team
 * https://www.partisk.nu/
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @copyright   Copyright 2013-2014 Partisk.nu Team
 * @link        https://www.partisk.nu
 * @package     app.Model
 * @license     http://opensource.org/licenses/MIT MIT
 */

class Quiz extends AppModel {
    public $validate = array(
        'name' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Du måste ange ett namn för quizen'
            )
        ),
        'description' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Du måste en beskrivning för quizen'
            )
        )
    );
    
    const NOT_IMPORTANT_POINTS = 1;
    const IMPORTANT_POINTS = 3;
    const VERY_IMPORTANT_POINTS = 9;
    
    public $hasAndBelongsToMany = array(
        'Question' => array(
            'joinTable' => "question_quizzes"
            )
    );

    public $belongsTo = array(
        'CreatedBy' => array(
            'className' => 'User', 
            'foreignKey' => 'created_by',
            'fields' => array('id', 'username')
        ),
        'UpdatedBy' => array(
            'className' => 'User',
            'foreignKey' => 'updated_by',
            'fields' => array('id', 'username')
        ),
        'ApprovedBy' => array(
            'className' => 'User',
            'foreignKey' => 'approved_by',
            'fields' => array('id', 'username')
        ),
        'QuestionTag'
    );
    
    public $virtualFields = array(
        'questions' => 'select count(QuestionQuiz.id) from question_quizzes as QuestionQuiz
                join questions as Question on Question.question_id = QuestionQuiz.question_id
                and Question.deleted = false and Question.approved = true
                where QuestionQuiz.quiz_id = Quiz.id'
    );
    
    public function calculatePoints($quizSession) {
        
        $answerModel = ClassRegistry::init('Answer');
        $questionModel = ClassRegistry::init('Question');
        $partiesModel = ClassRegistry::init('Party');

        $questionIds = array_map(array($this,"getQuestionIdFromQuiz"), $quizSession);
        $answersConditions = array('deleted' => false, 'approved' => true, 'questionId' => $questionIds);
        $answers = $answerModel->getAnswers($answersConditions); 

        $questionModel->recursive = -1;  
        //$questions = $questionModel->find('all', array('conditions' => array('Question.revision_id' => $questionIds)));
        $questions = $questionModel->getQuestionsByQuizId($quizSession['QuizSession']['quiz_id']);
        $answersMatrix = $answerModel->getAnswersMatrix($questions, $answers);

        $partiesModel->recursive = -1;
        $parties = $partiesModel->getPartiesOrdered();
        

        $results = array();
        $results['parties'] = array();
        $results['questions'] = array();

        $partiesResult = array();
        $questionsResult = array();
        
        foreach ($quizSession as $qResult) {
            if (empty($qResult['Question'])) continue;

            $questionId = $qResult['Question']['question_id'];
            $answer = $qResult['Question']['answer'];
            $importance = $qResult['Question']['importance'];

            $answersMatrix[$questionId]['answer'] = $answer;
            $answersMatrix[$questionId]['importance'] = $importance;
        }	

        foreach ($parties as $party) {
            $partyResult = &$partiesResult[$party['Party']['id']];
            $partyResult = array();
            $partyResult['points'] = 0;
            $partyResult['plus_points'] = 0;
            $partyResult['minus_points'] = 0;
            $partyResult['no_questions'] = 0;
            $partyResult['matched_questions'] = 0;
            $partyResult['missmatched_questions'] = 0;
            $partyResult['unanswered_questions'] = 0;
        }

        foreach ($questions as $question) {
            $questionId = $question['Question']['question_id'];
            $matrixQuestion = $answersMatrix[$questionId];
            
            $userAnswer = $matrixQuestion['answer'];
            $importanceIndex = $matrixQuestion['importance'];
            $importance = 0;
            
            switch ($importanceIndex) {
                case 1: 
                    $importance = self::NOT_IMPORTANT_POINTS;
                    break;
                case 2:
                    $importance = self::IMPORTANT_POINTS;
                    break;
                case 3:
                    $importance = self::VERY_IMPORTANT_POINTS;
                    break;
                    
            }
            
            $questionsResult[$questionId] = array();
            $questionsResult[$questionId]['title'] = $question['Question']['title'];
            $questionsResult[$questionId]['question_id'] = $question['Question']['question_id'];
            $questionsResult[$questionId]['parties'] = array();
                        
            $results[$questionId] = array();

            foreach ($parties as $party) { 
                $partyId = $party['Party']['id']; 
                $sameAnswer = null; 

                $questionsResult[$questionId]['parties'][$partyId] = array();
                $currentQuestionResult = &$questionsResult[$questionId]['parties'][$partyId];

                $questionsResult[$questionId]['answer'] = $userAnswer;
                $questionsResult[$questionId]['importance'] = $importance;

                if (isset($matrixQuestion['answers'][$partyId])) {
                    $partyAnswer = $matrixQuestion['answers'][$partyId]['Answer'];
                    $currentQuestionResult['answer'] = $partyAnswer;
                    if ($userAnswer !== null && $partyAnswer['answer'] !== null) {
                        if ($partyAnswer['answer'] == $userAnswer) {
                            $currentQuestionResult['points'] = $importance;
                            $partiesResult[$partyId]['points'] += $importance;
                            $partiesResult[$partyId]['plus_points'] += $importance;
				            $partiesResult[$partyId]['matched_questions'] += 1;
                                            //debug ($partiesResult);
                        } else {
                            $currentQuestionResult['points'] = -$importance;
                            $partiesResult[$partyId]['points'] -= $importance;
                            $partiesResult[$partyId]['minus_points'] += $importance;
                            $partiesResult[$partyId]['missmatched_questions'] += 1;
                        }
                    } else {
                        $currentQuestionResult['points'] = 0;
                    }
                } else {
                    $partiesResult[$partyId]['unanswered_questions'] += 1;
                    $currentQuestionResult['answer'] = null;
                    $currentQuestionResult['points'] = 0;
                }

                $partiesResult[$partyId]['no_questions'] += 1;
                
            }
        }

        $result['questions'] = $questionsResult;
        $result['parties'] = $partiesResult;
        
        return $result;
    }

    public function getById($id) {
        $this->recursive = -1;
        $this->contain(array("CreatedBy", "UpdatedBy", "ApprovedBy"));
        $quiz = $this->find('all', array(
                'conditions' => array(
                        'Quiz.id' => $id
                    ),
                'fields' => array('Quiz.id, Quiz.name, Quiz.created_date, Quiz.updated_date, Quiz.description, 
                                   Quiz.deleted, Quiz.approved, Quiz.created_by, Quiz.approved_by, Quiz.approved_date')
            ));
        return array_pop($quiz);
    }
    
    public function getVisibleQuizzes() {
        $result = Cache::read('visible', 'quiz');
        if (!$result) {
            $this->recursive = -1;
            $result = $this->find('all', array(
                'conditions' => array('approved' => true, 'deleted' => false)

            ));
            Cache::write('visible', $result, 'quiz');
        }
        
        return $result;
    }
    
    public function getLoggedInQuizzes() {
        $result = Cache::read('loggedin', 'quiz');
        if (!$result) {
            $this->recursive = -1;
            $result = $this->find('all', array(
                'conditions' => array('deleted' => false)

            ));
            Cache::write('loggedin', $result, 'quiz');
        }
        
        return $result;
    }

    public function generateGraphData($partyPoints) {
    	$result = array();
    	$question_agree_rate = array();
    	$points_percentage = array();

    	$maxPoints = null;
    	$totalPoints = 0;
        foreach ($partyPoints as $partyPoint) {
            if ($maxPoints > 0) $maxPoints = $partyPoint['points'];
            if ($partyPoint['points'] > 0) { $totalPoints += $partyPoint['points']; } 
        }

        foreach ($partyPoints as $id => $partyPoint) {
            $questions_to_match = $partyPoint['no_questions'] - $partyPoint['unanswered_questions'];
            $question_agree_rate[$id] = array();
            $points_percentage[$id] = array();

            $range = abs($partyPoint['minus_points']) + $partyPoint['plus_points'];
            if ($range != 0) {
                $question_agree_rate[$id]['result'] = round(($partyPoint['plus_points'] / $range) * 100);
            } else {
                $question_agree_rate[$id]['result'] = 0;
            }

            $question_agree_rate[$id]['range'] = $range;
            $question_agree_rate[$id]['plus_points'] = $partyPoint['plus_points'];
            $question_agree_rate[$id]['minus_points'] = $partyPoint['minus_points'];

            $points_percentage[$id]['result'] = $partyPoint['points'] > 0 ? round(($partyPoint['points'] / $totalPoints) * 100) : 0;
            $points_percentage[$id]['range'] = $totalPoints;
            $points_percentage[$id]['points'] = $partyPoint['points']; 
        }

    	$result['question_agree_rate'] = $question_agree_rate;
    	$result['points_percentage'] = $points_percentage;

    	return $result;
    }

    private function getQuestionIdFromQuiz($quizSession) {
        if (!empty($quizSession['Question'])) {
            return $quizSession['Question']['question_id'];
        }
    }

    public function generateQuizSession($id) {
	$questionModel = ClassRegistry::init('Question');

        $questionModel->recursive = -1;
        
        if ($id === 'all') {
            $quizSession = $questionModel->getAllVisibleQuestionIds();
        } else {
            $quizSession = $questionModel->getAllQuizQuestions($id);
        }
        
        if (sizeof($quizSession) < 1) {
            throw new InvalidArgumentException('A quiz has to have at least 1 question');
        }
        
        shuffle($quizSession);

        $quizSession["QuizSession"] = array(
            'index' => 0,
            'id' => Security::hash($this->randomString() . microtime()),
            'quiz_id' => $id, 
            'has_answers' => false,
            'questions' => sizeof($quizSession)
        );

        return $quizSession;
    }
    
    private function randomString() {
        $length = 20;
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $str = "";    

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $str;
    }
    
    public function getAllQuiz() {
        $result = Cache::read('allquiz', 'quiz');
        if (!$result) {
            $this->Question->recursive = -1;
            $result = array('Quiz' => array(
                'id' => 'all',
                'name' => 'Stora quizen',
                'description' => 'Ett stort quiz med alla sidans frågor',
                'questions' => $this->Question->find('count', array(
                    'conditions' => array('deleted' => false, 'approved' => true)
                ))
            ));            
            Cache::write('allquiz', $result, 'quiz');
        }
        
        return $result;
    }
    
    public function getUserQuizzes($userId) {
        $this->recursive = -1;
        return $this->find('all', array(
           'conditions' => array('created_by' => $userId) 
        ));
    }
    
    public function afterSave($created, $options = array()) {
        parent::afterSave($created, $options);
        Cache::clear(false, 'quiz');
    }
    
    public function afterDelete() {
        parent::afterDelete();
        Cache::clear(false, 'quiz');
    }
}

?>