<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wisdmlabs.com
 * @since      1.0.0
 *
 * @package    Ld_Content_Cloner
 * @subpackage Ld_Content_Cloner/includes
 */

/**
 * The LD course plugin class.
 *
 * @since      1.0.0
 * @package    Ld_Content_Cloner
 * @subpackage Ld_Content_Cloner/includes
 * @author     WisdmLabs <info@wisdmlabs.com>
 */
namespace LdccCourseEnabled;

class LdccCourse
{
    protected static $course_id=0;
    protected static $new_course_id=0;

    /**
     *
     * @since    1.0.0
     */

    public function __construct()
    {
    }

    public static function createDuplicateCourse()
    {
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
        $course_nonce = filter_input(INPUT_POST, 'course');
        $nonce_check = wp_verify_nonce($course_nonce, 'dup_course_' . $course_id);

        if ($nonce_check === false) {
            echo json_encode(array( "error" => __("Security check failed.", "ld-content-cloner") ));
            die();
        }

        if ((!isset($course_id)) || !(get_post_type($course_id) == 'sfwd-courses')) {
            echo json_encode(array( "error" => __("The current post is not a Course and hence could not be cloned.", "ld-content-cloner") ));
            die();
        }

        $course_post = get_post($course_id, ARRAY_A);
        $course_post = \LdccCourse\LdccCourse::stripPostData($course_post);

        $new_course_id = wp_insert_post($course_post, true);

        if (! is_wp_error($new_course_id)) {
            self::setMeta("course", $course_id, $new_course_id);
            $course_steps = get_post_meta($course_id, 'ld_course_steps');
            $course_steps_h = array();
            if (!empty($course_steps)) {
                $course_steps_h = $course_steps[0]["h"];
                $c_data = self::getLDCourseStepsArray($course_steps_h, $new_course_id);
            } else {
                $c_data = self::createLDCourseStepsArray($course_id, $new_course_id);
            }
            $send_result = array(
                "success" => array(
                    "old_course_id" => $course_id,
                    "new_course_id" => $new_course_id,
                    "c_data" => $c_data,
                )
            );
            echo json_encode($send_result);
        } else {
            echo json_encode(array( "error" => __("Some error occurred. The Course could not be cloned.", "ld-content-cloner") ));
        }

        die();
    }

    public static function getLDCourseStepsArray($course_steps, $new_course_id)
    {
        $lessons_list=array();
        $quizzes_list=array();
        $lessons = $course_steps["sfwd-lessons"];
        foreach ($lessons as $lesson_id => $l_content) {
            $new_lesson_id = wp_insert_post(array('post_type' => 'sfwd-lessons'));
            $lessons_list[]= array($lesson_id, get_the_title($lesson_id), $new_lesson_id);
            $topics = $l_content['sfwd-topic'];
            foreach ($topics as $topic_id => $content) {
                $new_topic_id = wp_insert_post(array('post_type' => 'sfwd-topic'));
                $lessons_list[]= array($topic_id, get_the_title($topic_id), $new_topic_id);
                $t_quizzes = $content['sfwd-quiz'];
                foreach ($t_quizzes as $quiz_id => $content) {
                    $new_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
                    $quizzes_list[]=array($quiz_id, get_the_title($quiz_id), $new_quiz_id);
                    $h_t_quiz[$new_topic_id][$new_quiz_id] = array();
                }
                $h_topic[$new_lesson_id][$new_topic_id]['sfwd-quiz'] = $h_t_quiz[$new_topic_id];
            }
            $l_quizzes = $l_content['sfwd-quiz'];
            foreach ($l_quizzes as $quiz_id => $content) {
                $new_l_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
                $quizzes_list[]= array($quiz_id, get_the_title($quiz_id), $new_l_quiz_id);
                $h_quiz[$new_lesson_id][$new_l_quiz_id] = array();
            }
            $h_lesson[$new_lesson_id]['sfwd-topic'] = $h_topic[$new_lesson_id];
            $h_lesson[$new_lesson_id]['sfwd-quiz'] = $h_quiz[$new_lesson_id];
        }
            $quizzes = $course_steps['sfwd-quiz'];
        foreach ($quizzes as $quiz_id => $content) {
            $new_c_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
            $quizzes_list[]= array($quiz_id, get_the_title($quiz_id), $new_c_quiz_id);
            $h_c_quiz[$new_c_quiz_id] = array();
        }
        $h_course['sfwd-lessons'] = $h_lesson;
        $h_course['sfwd-quiz'] = $h_c_quiz;
        $new_course_steps = self::getLDCourseSteps($h_course);
        update_post_meta($new_course_id, 'ld_course_steps', $new_course_steps);
        return array('lesson'=>$lessons_list, 'quiz'=>$quizzes_list);
    }

    // .get entire ld_course_steps array from h subarray
    public static function getLDCourseSteps($h_course)
    {
        $courseStepsClass = new \LDLMS_Course_Steps();
        $course_steps = array();
        if (!empty($h_course)) {
            $course_steps['h'] = $h_course;
            $course_steps['t'] = $courseStepsClass->steps_grouped_by_type($h_course);
            $course_steps['r'] = $courseStepsClass->steps_grouped_reverse_keys($h_course);
            $course_steps['l'] = $courseStepsClass->steps_grouped_linear($h_course);
        }

        return $course_steps;
    }

    public static function createLDCourseStepsArray($course_id, $new_course_id)
    {
        $lessons_list=array();
        $quizzes_list=array();
        $h_course = $h_lesson = $h_topic = $h_quiz = $h_c_quiz = $h_t_quiz = array();
        $lessons = learndash_get_course_lessons_list($course_id);
        foreach ($lessons as $lesson) {
            $lesson_id = $lesson["post"]->ID;
            $new_lesson_id = wp_insert_post(array('post_type' => 'sfwd-lessons'));
            $lessons_list[]= array($lesson_id, $lesson["post"]->post_title, $new_lesson_id);
            $topics = learndash_get_topic_list($lesson_id, $course_id);
            foreach ($topics as $topic) {
                $topic_id = $topic->ID;
                $new_topic_id = wp_insert_post(array('post_type' => 'sfwd-topic'));
                $lessons_list[]= array($topic_id, $topic->post_title, $new_topic_id);
                $t_quizzes = learndash_get_lesson_quiz_list($topic_id, '', $course_id);
                foreach ($t_quizzes as $t_quiz) {
                    $quiz_id = $t_quiz["post"]->ID;
                    $new_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
                    $quizzes_list[]=array($quiz_id, $t_quiz["post"]->post_title, $new_quiz_id);
                    $h_t_quiz[$new_topic_id][$new_quiz_id] = array();
                }
                $h_topic[$new_lesson_id][$new_topic_id]['sfwd-quiz'] = $h_t_quiz[$new_topic_id];
            }
            $l_quizzes = learndash_get_lesson_quiz_list($lesson_id, '', $course_id);
            foreach ($l_quizzes as $l_quiz) {
                $quiz_id = $l_quiz["post"]->ID;
                $new_l_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
                $quizzes_list[]= array($quiz_id, $l_quiz["post"]->post_title, $new_l_quiz_id);
                $h_quiz[$new_lesson_id][$new_l_quiz_id] = array();
            }
            $h_lesson[$new_lesson_id]['sfwd-topic'] = $h_topic[$new_lesson_id];
            $h_lesson[$new_lesson_id]['sfwd-quiz'] = $h_quiz[$new_lesson_id];
        }
        $quizzes = learndash_get_course_quiz_list($course_id);
        foreach ($quizzes as $c_quiz) {
            $quiz_id = $c_quiz["post"]->ID;
            $new_c_quiz_id = wp_insert_post(array('post_type' => 'sfwd-quiz'));
            $quizzes_list[]= array($quiz_id, get_the_title($quiz_id), $new_c_quiz_id);
            $h_c_quiz[$new_c_quiz_id] = array();
        }
        $h_course['sfwd-lessons'] = $h_lesson;
        $h_course['sfwd-quiz'] = $h_c_quiz;
        $new_course_steps = self::getLDCourseSteps($h_course);
        update_post_meta($new_course_id, 'ld_course_steps', $new_course_steps);
        return array('lesson'=>$lessons_list, 'quiz'=>$quizzes_list);
    }

    public static function createDuplicateLesson()
    {
        $lesson_id = filter_input(INPUT_POST, 'lesson_id', FILTER_VALIDATE_INT);
        $new_lesson_id = filter_input(INPUT_POST, 'new_lesson_id', FILTER_VALIDATE_INT);
        if ((!isset($lesson_id)) || (!(get_post_type($lesson_id) == 'sfwd-lessons') && !(get_post_type($lesson_id) == 'sfwd-topic'))) {
            echo json_encode(array( "error" => __("The current post is not a Lesson or topic and hence could not be cloned.", "ld-content-cloner") ));
            die();
        }
        $old_course_id = filter_input(INPUT_POST, 'old_course_id', FILTER_VALIDATE_INT);
        $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
        if ((!isset($course_id)) || !(get_post_type($course_id) == 'sfwd-courses')) {
            echo json_encode(array( "error" => __("The course ID provided with is incorrect for the lesson.", "ld-content-cloner") ));
            die();
        }
        $lesson_post = get_post($lesson_id, ARRAY_A);

        // $lesson_post = self::stripPostData($lesson_post);
        $lesson_post["ID"] = $new_lesson_id;
        $lesson_post['post_title'] = $lesson_post['post_title']." Copy";
        $new_lesson_id = wp_update_post($lesson_post, true);

        if (! is_wp_error($new_lesson_id)) {
            $meta_result = self::setMeta(
                'lesson',
                $lesson_id,
                $new_lesson_id,
                array(
                    "course_id" => $course_id,
                    "old_course_id" => $old_course_id
                )
            );

            $send_result = array( "success" => array( ) );
        } else {
            $send_result = array( "error" => __("Some error occurred. The Lesson was not fully cloned.", "ld-content-cloner") );
        }
        echo json_encode($send_result);
        unset($meta_result);
        die();
    }

    public static function duplicateQuiz($quiz_id = 0, $lesson_id = 0, $course_id = 0)
    {
        // duplicate quiz post
        $send_response = false;
        if ($quiz_id == 0) {
            $quiz_id = filter_input(INPUT_POST, 'quiz_id', FILTER_VALIDATE_INT);
            $new_quiz_id = filter_input(INPUT_POST, 'new_quiz_id', FILTER_VALIDATE_INT);
            $old_course_id = filter_input(INPUT_POST, 'old_course_id', FILTER_VALIDATE_INT);
            $course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
            $send_response = true;
        }
        $quiz_post = get_post($quiz_id, ARRAY_A);
        $quiz_post['ID'] = $new_quiz_id;
        $quiz_post['post_title'] = $quiz_post['post_title']." Copy";

        // $quiz_post = self::stripPostData($quiz_post);

        $new_quiz_id = wp_update_post($quiz_post, true);
        if (! is_wp_error($new_quiz_id)) {
            $meta_result = self::setMeta(
                'quiz',
                $quiz_id,
                $new_quiz_id,
                array(
                    "lesson_id" => $lesson_id,
                    "course_id" => $course_id,
                    "old_course_id" => $old_course_id
                )
            );
            $ld_quiz_data = get_post_meta($new_quiz_id, '_sfwd-quiz', true);
            $pro_quiz_id = $ld_quiz_data['sfwd-quiz_quiz_pro'];
            global $wpdb;
            $_prefix = $wpdb->prefix.'wp_pro_quiz_';

            $_tableQuestion = $_prefix.'question';
            $_tableMaster = $_prefix.'master';
            $_tablePrerequisite = $_prefix.'prerequisite';
            $_tableForm = $_prefix.'form';

            // fetch and create in top quiz master table ( wp_pro_quiz_master )
            $pq_query = "SELECT * FROM $_tableMaster WHERE id = %d;";



            $pro_quiz = $wpdb->get_row($wpdb->prepare($pq_query, $pro_quiz_id), ARRAY_A);

            unset($pro_quiz['id']);
            $pro_quiz['name'] .= " Copy";

            $format = array( '%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d' );

            $ins_result = $wpdb->insert($_tableMaster, $pro_quiz, $format);

            $wp_pro_quiz_id = 0;

            if ($ins_result !== false) {
                $wp_pro_quiz_id = $wpdb->insert_id;
                $ld_quiz_data['sfwd-quiz_quiz_pro'] = $wp_pro_quiz_id;
                update_post_meta($new_quiz_id, '_sfwd-quiz', $ld_quiz_data);
                // fetch and create in pre-requisites table ( wp_pro_quiz_prerequisite )
                $pqr_query = "SELECT * FROM $_tablePrerequisite WHERE prerequisite_quiz_id = %d;";
                $pror_quizzes = $wpdb->get_results($wpdb->prepare($pqr_query, $pro_quiz_id), ARRAY_A);
                if (!empty($pror_quizzes)) {
                    foreach ($pror_quizzes as $pror_quiz) {
                        $pror_quiz['prerequisite_quiz_id'] = $wp_pro_quiz_id;
                        $ins_result = $wpdb->insert($_tablePrerequisite, $pror_quiz, array( '%s', '%s', ));
                    }
                }
                // copy pro quiz questions ( wp_pro_quiz_question )
                $questionArr = \LdccCourse\LdccCourse::getQuestions($pro_quiz_id);
                if (!empty($questionArr)) {
                    \LdccCourse\LdccCourse::copyQuestions($wp_pro_quiz_id, $questionArr);
                }
                //copy custom fields in quiz
                $frm_query = "SELECT * FROM $_tableForm WHERE quiz_id = %d;";
                $frm_quizzes = $wpdb->get_results($wpdb->prepare($frm_query, $pro_quiz_id), ARRAY_A);
                if (!empty($frm_quizzes)) {
                    foreach ($frm_quizzes as $frm_quiz) {
                        unset($frm_quiz['form_id']);
                        $frm_quiz['quiz_id'] = $wp_pro_quiz_id;
                        $frm_ins_result = $wpdb->insert($_tableForm, $frm_quiz, array( '%d', '%s', '%d', '%d', '%d', '%s'));
                    }
                }
            }
            $send_result = array( "success" => array( ) );
        } else {
            $send_result = array( "error" => __("Some error occurred. The Quiz was not fully cloned.", "ld-content-cloner") );
        }

        if ($send_response) {
            echo json_encode($send_result);
            die();
        }
        unset($meta_result);
        unset($_tableQuestion);
        unset($frm_ins_result);
    }

    public static function setMeta($post_type, $old_post_id, $new_post_id, $other_data = array())
    {
        global $wpdb;
        $exclude_post_meta=array('_edit_last', '_edit_lock');
        if (!empty($old_post_id) && !empty($new_post_id)) {
            if ($post_type == 'course') {
                $ld_data = \LdccCourse\LdccCourse::updateCourseMeta($old_post_id, $new_post_id);
                $term_taxonomy_ids = $wpdb->get_results("SELECT term_taxonomy_id FROM ".$wpdb->prefix."term_relationships where object_id=".$old_post_id);
                if (!empty($term_taxonomy_ids)) {
                    foreach ($term_taxonomy_ids as $term_taxonomy_id) {
                        $wpdb->insert(
                            $wpdb->prefix.'term_relationships',
                            array(
                            'object_id' => $new_post_id,
                            'term_taxonomy_id' => $term_taxonomy_id->term_taxonomy_id,
                            'term_order' => 0
                            ),
                            array(
                            '%d',
                            '%d',
                            '%d'
                            )
                        );
                    }
                }
                update_post_meta($new_post_id, '_sfwd-courses', $ld_data);
                array_push($exclude_post_meta, '_sfwd-courses');
            } elseif ($post_type == 'lesson') {
                $sent_c_id = $other_data['course_id'];
                $old_course_id = $other_data['old_course_id'];
                $ld_data = get_post_meta($old_post_id, '_sfwd-lessons', true);
                $lesson_course_id = $sent_c_id;

                $ld_data['sfwd-lessons_course'] = $lesson_course_id;
                $term_taxonomy_ids = $wpdb->get_results("SELECT term_taxonomy_id FROM ".$wpdb->prefix."term_relationships where object_id=".$old_post_id);
                if (!empty($term_taxonomy_ids)) {
                    foreach ($term_taxonomy_ids as $term_taxonomy_id) {
                        $wpdb->insert(
                            $wpdb->prefix.'term_relationships',
                            array(
                            'object_id' => $new_post_id,
                            'term_taxonomy_id' => $term_taxonomy_id->term_taxonomy_id,
                            'term_order' => 0
                            ),
                            array(
                            '%d',
                            '%d',
                            '%d'
                            )
                        );
                    }
                }
                $old_lesson = get_post($old_post_id);
                $menu_order = $old_lesson->menu_order;
                $new_lesson_order = array(
                'ID'           => $new_post_id,
                'menu_order'   => $menu_order,
                );
                wp_update_post($new_lesson_order);
                update_post_meta($new_post_id, '_sfwd-lessons', $ld_data);
                array_push($exclude_post_meta, '_sfwd-lessons', 'course_id', 'course_'.$lesson_course_id.'_lessons_list', 'ld_course_'.$old_course_id);
                update_post_meta($new_post_id, 'ld_course_'.$lesson_course_id, $lesson_course_id);
            } elseif ($post_type == 'quiz') {
                $unit_course_id = $other_data['course_id'];
                $old_course_id = $other_data['old_course_id'];
                $unit_lesson_id = $other_data['lesson_id'];
                $ld_data = get_post_meta($old_post_id, '_sfwd-quiz', true);

                $ld_data['sfwd-quiz_course'] = $unit_course_id;
                $ld_data['sfwd-quiz_lesson'] = $unit_lesson_id;

                $term_taxonomy_ids = $wpdb->get_results("SELECT term_taxonomy_id FROM ".$wpdb->prefix."term_relationships where object_id=".$old_post_id);
                if (!empty($term_taxonomy_ids)) {
                    foreach ($term_taxonomy_ids as $term_taxonomy_id) {
                        $wpdb->insert(
                            $wpdb->prefix.'term_relationships',
                            array(
                            'object_id' => $new_post_id,
                            'term_taxonomy_id' => $term_taxonomy_id->term_taxonomy_id,
                            'term_order' => 0
                            ),
                            array(
                            '%d',
                            '%d',
                            '%d'
                            )
                        );
                    }
                }
                $old_quiz = get_post($old_post_id);
                $menu_order = $old_quiz->menu_order;
                $new_quiz_order = array(
                'ID'           => $new_post_id,
                'menu_order'   => $menu_order,
                );
                wp_update_post($new_quiz_order);
                update_post_meta($new_post_id, '_sfwd-quiz', $ld_data);
                array_push($exclude_post_meta, '_sfwd-quiz', 'course_id', 'lesson_id', 'ld_course_'.$old_course_id);
                update_post_meta($new_post_id, 'ld_course_'.$unit_course_id, $unit_course_id);
            }

            $old_post_meta=get_post_meta($old_post_id);
            if (!empty($old_post_meta)) {
                foreach ($old_post_meta as $key => $value) {
                    if (!in_array($key, $exclude_post_meta)) {
                        update_post_meta($new_post_id, $key, get_post_meta($old_post_id, $key, true));
                    }
                }
            }
            unset($value);
            return true;
        }
        return false;
    }
}
