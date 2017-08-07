<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LearnDash Import CPT
 *
 * This file contains functions to handle import of the LearnDash CPT Topic
 *
 * @package LearnDash
 * @subpackage LearnDash
 * @since 1.0.0
 */

if ( !class_exists( 'LearnDash_Import_Quiz_Question' ) ) {
	class LearnDash_Import_Quiz_Question extends LearnDash_Import_Post {
		private $version			= '1.0';
		
	    function __construct() {
		}
		
		function startQuizQuestionSet() {
			$pro_quiz_question_import = new WpProQuiz_Model_Question();
			
			return $pro_quiz_question_import->get_object_as_array();
		}
		
		function saveQuizQuestionSet( $quiz_question_data = array() ) {
			if ( !empty( $quiz_question_data ) ) {
				
//				if ( isset( $quiz_question_data['_answerData'] ) ) {
//					$quiz_question_data['_answerData'] = $this->saveQuizQuestionAnswerTypesSet( $quiz_question_data['_answerData'] );
//				}
				
				// Called to ensure we have a working Question Set ( WpProQuiz_Model_Question )
				$pro_quiz_question_import = new WpProQuiz_Model_Question();
				$pro_quiz_question_import->set_array_to_object( $quiz_question_data );
				
				$quizQuestionMapper = new WpProQuiz_Model_QuestionMapper();
				$quizQuestionMapper->save( $pro_quiz_question_import );
			}
		}

		function startQuizQuestionAnswerTypesSet() {
			$pro_quiz_question_answer_types_import = new WpProQuiz_Model_AnswerTypes();
			
			return $pro_quiz_question_answer_types_import->get_object_as_array();
		}

		function saveQuizQuestionAnswerTypesSet( $answer_type_data = array() ) {
			if ( !empty( $answer_type_data ) ) {
				
				$answer_import_array = array();
				
				foreach( $answer_type_data as $answer_item ) {
					if ( is_array( $answer_item ) ) {
						$answer_import = new WpProQuiz_Model_AnswerTypes();

						foreach( $answer_item as $key => $value ) {
							switch( $key ) {
					
								case '_answer':
									$answer_import->setAnswer( $value );
									break;

								case '_html':
									$answer_import->setHtml( $value );
									break;

								case '_points':
									$answer_import->setPoints( $value );
									break;
					
								case '_correct':
									$answer_import->setCorrect( $value );
									break;

								case '_sortString':
									$answer_import->setSortString( $value );
									break;

								case '_sortStringHtml':
									$answer_import->setSortStringHtml( $value );
									break;

								case '_graded':
									$answer_import->setGraded( $value );
									break;

								case '_gradingProgression':
									$answer_import->setGradingProgression( $value );
									break;

								case '_gradedType':
									$answer_import->setGradedType( $value );
									break;

								default:
									break;
							}
						}
						$answer_import_array[] = $answer_import;
					} else if ( is_a($answer_item, 'WpProQuiz_Model_AnswerTypes' ) ) {
						$answer_import_array[] = $answer_item;
					} else {
						// If not an array and not an instance of WpProQuiz_Model_AnswerTypes we ignore.
					}
				}
			
				return $answer_import_array;
			}
		}

		// End of functions
	}
}