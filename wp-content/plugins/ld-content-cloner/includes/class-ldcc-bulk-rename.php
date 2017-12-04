<?php
namespace LdBulkRename;

class LdBulkRename
{

    public function ldbrRenameSubmenuPage()
    {
        add_submenu_page(
            "learndash-lms",
            __("Course Bulk Rename", "ld-content-cloner"),
            __("Course Bulk Rename", "ld-content-cloner"),
            "edit_courses",
            "learndash-course-bulk-rename",
            array( $this, "ldbrPageCallback" )
        );
    }

    public function ldbrAddSubmenu($submenu)
    {
        $submenu[] = array(
            "name"  => __("Course Bulk Rename", "ld-content-cloner"),
            "cap" => "edit_courses",
            "link" => "admin.php?page=learndash-course-bulk-rename"
        );
        return $submenu;
    }

    public function ldbrPageCallback()
    {
        $args = array(
                    'post_type' => 'sfwd-courses',
                    'post_status' => array( 'publish', 'draft' ),
                    'posts_per_page'=> -1
                );
        $courses = get_posts($args);
        $selected_course = filter_input(INPUT_GET, 'ldbr-select-course', FILTER_VALIDATE_INT);
        $selected = "";

        $this->addSlider();
        ?>
        <div>
            <h2>Course Bulk Rename</h2>
            <?php
                $this->addCourseList($courses, $selected_course);
        ?>

            <div>
                    <?php
                    if (!empty($selected_course)) {
                        $lesson_ids = array();
                        $topic_ids = array();
                        $quiz_ids = array();
                        echo '<form id="" name="">';

                        $c_quizzes = array();
                        $l_quizzes = array();
                        $t_quizzes = array();

                        $lessons = learndash_get_course_lessons_list($selected_course);

                        $c_quizzes = learndash_get_course_quiz_list($selected_course);
                        if (!empty($lessons)) {
                            foreach ($lessons as $lesson) {
                                $lesson_ids[$lesson["post"]->ID] = $lesson["post"]->post_title;

                                $topics =  learndash_topic_dots($lesson['post']->ID, false, 'array', null, $selected_course);
                                foreach ($topics as $topic) {
                                    $topic_ids[$topic->ID] = $topic->post_title;
                                    $t_quizzes = array_merge($t_quizzes, learndash_get_lesson_quiz_list($topic->ID, '', $selected_course));
                                }
                                unset($topics);

                                $l_quizzes = array_merge($l_quizzes, learndash_get_lesson_quiz_list($lesson["post"]->ID, '', $selected_course));
                            }
                        }

                        $quizzes = array_merge($c_quizzes, $l_quizzes, $t_quizzes);
                        if (!empty($quizzes)) {
                            foreach ($quizzes as $quiz) {
                                $quiz_ids[$quiz["post"]->ID] = $quiz["post"]->post_title;
                            }
                        }

                        $lesson_ids = array_unique($lesson_ids);
                        $topic_ids = array_unique($topic_ids);
                        $quiz_ids = array_unique($quiz_ids);

                        echo "<table class='ldbr-table'>";
                        echo "<tr class='ldbr-head-row'><th>Post Type</th><th>Post Title</th><th>New Title</th></tr>";
                        $this->ldbrDisplayRenaming($selected_course, get_the_title($selected_course));

                        foreach ($lesson_ids as $id => $title) {
                            $this->ldbrDisplayRenaming($id, $title);
                        }

                        foreach ($topic_ids as $id => $title) {
                            $this->ldbrDisplayRenaming($id, $title);
                        }

                        foreach ($quiz_ids as $id => $title) {
                            $this->ldbrDisplayRenaming($id, $title);
                        }

                        echo '<tr class="ldbr-foot-row">
									<td colspan="3">
										<input type="hidden" name="ldbr_security" id="ldbr_security" value="'.wp_create_nonce('bulk_renaming').'" />
										<input type="button" class="button button-primary" name="save_post_titles" id="save_post_titles" data-lock="0" value="Save New Titles" />
									</td>
								</tr>';
                        echo "</table></form>";
                    }
        ?>
            </div>

        </div>
        <?php
        unset($selected);
    }


    public function addCourseList($courses, $selected_course)
    {
        ?>
            <form action="" method="get" id="ldbr-select-form" name="ldbr-select-form">
                <select id="ldbr-select-course" name="ldbr-select-course">
                    <option value="0"> ( ID ) Select Course </option>
                    <?php
                    foreach ($courses as $sin_course) {
                        if (!empty($selected_course)) {
                            $selected = selected($selected_course, $sin_course->ID, 0);
                        }

                        ?>
                        <option value="<?php echo $sin_course->ID;
                        ?>" <?php echo $selected;
                        ?>><?php echo "( " . $sin_course->ID . " ) ". $sin_course->post_title;
                        ?></option>
                    <?php
                    }
        ?>
                </select>
                <input type="hidden" name="page" value="learndash-course-bulk-rename" />
                <input type="submit" class="button button-primary" id="ldbr-select-button" name="ldbr-select-button" value="Select Course" />
            </form>
        <?php
    }
    public function addSlider()
    {
        ?>
        <div class="wrap">
        <?php require_once('ldcc-slider.php'); ?>
        </div>
        <?php
    }

    // .added hyperlink to edit contents
    // .Edited hyperlink title
    public function ldbrDisplayRenaming($post_id, $title)
    {
        $shared_steps = '';
        if (class_exists('\LearnDash_Settings_Section')) {
            $shared_steps = \LearnDash_Settings_Section::get_section_setting('LearnDash_Settings_Courses_Builder', 'shared_steps');
        }
        $obj = get_post_type_object(get_post_type($post_id));
        $lesson_id = "";
        $lesson_name = "";
        if ($shared_steps != 'yes') {
            if (get_post_type($post_id) == "sfwd-topic") {
                $lesson_id = get_post_meta($post_id, "lesson_id", true);
                if ($lesson_id != "") {
                    $lesson_name = "<a href='".get_edit_post_link($lesson_id)."' title = 'Edit This Lesson'>".get_the_title($lesson_id) ."</a> -> ";
                }
            }
        }
        echo "<tr class='ldbr-row'>
				<td class='ldbr-post-type'> ". $obj->labels->singular_name ."</td>
				<td class='ldbr-post-title'>".$lesson_name."<a title = 'Edit This ".$obj->labels->singular_name."' href='".get_edit_post_link($post_id)."'>".$title ." </a></td>
				<td> <input class='ldbr-post-new-title' type='text' data-post-id='" . $post_id . "' value='". $title."'> </td>
			</tr>";
    }

    public function ldbrBulkRenameCallback()
    {
        $security = filter_input(INPUT_POST, 'security', FILTER_SANITIZE_STRING);

        if (wp_verify_nonce($security, 'bulk_renaming')) {
            $rename_data = filter_input(INPUT_POST, 'course_data');
            $rename_data = (array) json_decode($rename_data);
            foreach ($rename_data as $post_id => $new_title) {
                if (get_the_title($post_id) != trim($new_title)) {
                    $this->updateThePost($post_id, $new_title);
                }
            }
            echo json_encode(array( "success" => "All Post Titles Updated." ));
        } else {
            echo json_encode(array( "error" => "Security check failed." ));
        }
        die();
    }

    public function updateThePost($post_id, $new_title)
    {
        $post_arr = array(
                        'ID'        => $post_id,
                        'post_title'=> $new_title,
                    );
        if (get_post_status($post_id) === 'publish') {
            $new_slug = sanitize_title($post->post_title);
            $post_arr['post_name'] = $new_slug;
        }

        wp_update_post($post_arr);
        unset($post_id);
        unset($post);
    }
}
