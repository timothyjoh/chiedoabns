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

if ( !class_exists( 'LearnDash_Import_Quiz' ) ) {
	class LearnDash_Import_Quiz extends LearnDash_Import_Post {
		private $version			= '1.0';
		
		protected $dest_post_type 	= 'sfwd-quiz';
		protected $source_post_type = 'sfwd-quiz';

	    function __construct() {
		}
		
		function duplicate_post( $source_post_id = 0, $force_copy = false ) {
			$new_post = parent::duplicate_post( $source_post_id, $force_copy );
			
			return $new_post;
		}

		function duplicate_post_tax_term( $source_term, $create_parents = false ) {
			$new_term = parent::duplicate_post( $source_term, $create_parents );
			
			return $new_term;
		}

		function startQuizSet() {
			$pro_quiz_import = new WpProQuiz_Model_Quiz();
			
			return $pro_quiz_import->get_object_as_array();
		}

		function saveQuizSet( $quiz_data = array() ) {
			if ( !empty( $quiz_data ) ) {
				
				$quiz_import = new WpProQuiz_Model_Quiz();
				$quiz_import->set_array_to_object( $quiz_data );
				
				$quizMapper = new WpProQuiz_Model_QuizMapper();
				$quizMapper->save( $quiz_import );
				
				$quiz_id = $quiz_import->getId();
				
				return $quiz_id;
			}
		}

		// End of functions
	}
}