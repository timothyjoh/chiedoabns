<?php


/**
 * Shortcode to list expired courses
 * 
 * @since 2.1.0
 * 
 * @param  array  $attr   shortcode attributes
 * @return string       shortcode output
 */

// deprecated versions, using underscores:
add_shortcode( 'expired_courses', 'propel_expired_courses_shortcode' );
add_shortcode( 'my_courses', 'propel_my_courses_shortcode' );

// new, improved versions, with dashes:
add_shortcode( 'propel-certificate', 'propel_certificate' );
add_shortcode( 'expired-courses', 'propel_expired_courses_shortcode' );
add_shortcode( 'my-courses', 'propel_my_courses_shortcode' );
add_shortcode( 'my-courses-filters', 'propel_my_courses_render_filter_sort_search_shortcode' );

function propel_expired_courses_shortcode( $attr ) {
  
  global $post;
  $user_id = get_current_user_id();
  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;
  // Will return 0 or 1 depending if record exists
  wp_enqueue_script('jQuery');
  wp_enqueue_script( 'certificate_launch',  plugins_url() . '/propel-wordpress/js/shortcodes/certificate_launch.js', array(), null, true );

  $post_ids = $wpdb->get_col( "
                SELECT post_id 
                FROM $propel_table 
                WHERE user_id = $user_id
                  AND expiration_date < NOW()
              " );

  if ( ! empty( $post_ids) ){
    foreach( $post_ids as $post_id ) {
      propel_render_my_courses_list($post_id, false);
    }
  } else {
    echo "You don't have any expired courses. When you do, they will show up here";
  }
}

/**
 * Shortcode to list courses
 * 
 * @since 2.1.0
 * 
 * @param  array 	$attr 	shortcode attributes
 * @return string   		shortcode output
 */
function propel_my_courses_shortcode( $attr ) {
  wp_enqueue_style('my_courses_style');
  wp_enqueue_script('my_courses_script');

  global $post;
  $user_id = get_current_user_id();
  global $wpdb;
  $propel_table = $wpdb->prefix . Propel_DB::enrollments_table;
  // Will return 0 or 1 depending if record exists
  wp_enqueue_script('jQuery');
  wp_enqueue_script( 'easy_launch_xapi',  plugins_url() . '/propel-wordpress/js/shortcodes/easy_launch_xapi.js', array(), null, true );
  wp_enqueue_script( 'certificate_launch',  plugins_url() . '/propel-wordpress/js/shortcodes/certificate_launch.js', array(), null, true );

  $keys = $wpdb->get_results( "
                SELECT p.*, e.post_id, e.activation_date, e.expiration_date, e.activation_key, e.passed, e.completion_date
                FROM $propel_table e
                LEFT JOIN wp_posts p ON p.id = e.post_id
                WHERE e.user_id = $user_id
                ORDER BY e.activation_date DESC, p.menu_order ASC
              " );
  ?>
  <div class="courselist-container propel-override" id="courselist-container">
  <div class="list">
  <?php
  foreach( $keys as $key ) {
    propel_render_my_courses_list($key);
  }
  echo '</div></div>';
  if (empty($keys)){
    ?>
    <div class="OoopsNotice">
    <p>You aren't enrolled in any courses! <a href="/course-catalog/">Click here to purchase a course!</a></p>
    <p>Already have a key? <a href="/activate-key/">Click here to activate a key!</a></p>
    </div>
    <?php
  }
}

function propel_render_my_courses_list($key) {
  // error_log(" - - > propel_render_my_courses_list >>> ");
  // error_log(json_encode($key));
  $active = $key->expiration_date > date("Y-m-d H:i:s");
  $post_id = $key->post_id;

  $post = get_post( $post_id );

  $options = get_option('sfwd_cpt_options');
  $post_image_id = get_post_thumbnail_id($post->ID);
  if ($post_image_id) {
    $thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
    if ($thumbnail) (string)$thumbnail = $thumbnail[0];
  }
  $claimable = $active ? 1 : 0;
  $course_permalink = get_permalink($post->ID);
  $user_id = get_current_user_id();
  $user_courses = ld_get_mycourses($user_id);
  $usermeta = get_user_meta( $user_id, '_sfwd-quizzes', true );
  $progress = learndash_course_progress(array("user_id" => $user_id, "course_id" => $post_id, "array" => true));
  $lessons_completed = $progress["completed"] . " out of " . $progress["total"] . " lessons completed";
  $percentage_completed = $progress['percentage'];
  $complete = $progress["percentage"] == 100 ? true : false;
  $completed_string = "Get Started";
  if ($complete) {
    $completed_string = "Complete";
  } elseif ($percentage_completed > 0) {
    $completed_string = $lessons_completed;
  }
  ?>

<section class="courselist-course">
    <a href="#" class="course-image-link">
      <img src="<?php echo $thumbnail ?>" />
    </a>
    <div class="courselist-course-body course_active" data-active="<?php echo $active; ?>">
      <div class="row activation_key" data-key="<?php echo $key->activation_key; ?>">
        <div class="courselist-course-info">
          <h5 class="ellipsis course_title"
              data-title="<?php echo $post->post_title; ?>">
            <?php propel_my_courses_badge_icon($post_id); ?>
            <?php echo $post->post_title; ?>
          </h5>
          <p class="authors"><?php echo get_field("course_authors", $post_id); ?></p>
          <div class="push-bottom course-progress">
            <div class="clearfix fs-mini course_started" data-started="<?php echo ''; ?>">
              <span><?php echo $completed_string; ?></span>
            </div>
            <div class="progress" data-progress="<?php echo $percentage_completed; ?>">
              <div class="progress-bar" style="width: <?php echo $percentage_completed; ?>%;"></div>
            </div>
          </div>
          <?php echo propel_render_course_access_button($post_id, $percentage_completed, $active, $key->expiration_date); ?>
        </div>
        <div class="courselist-cert-claim">
          <p class="light"><?php echo get_field("credit_type", $post_id); ?></p>
          <?php
            if ($complete) {
              echo link_to_certificate($post_id, $user_id, $claimable, get_field("certificate_button_label", $post_id));
            }
          ?>
        </div>
      </div>
    </div>
</section>

<?php
 // end the propel_render_my_courses_list function
}

function scitent_render_xapi_button( $post_id, $percentage ) {
	if ($percentage == 0) {
		$button_label = "Access Course";
	} else if ($percentage == 100) {
		$button_label = "View Completed Course";
	} else {
		$button_label = "Resume Course";
	}

  global $wpdb;
  // var_dump($post_id);
  $lesson_ids = $wpdb->get_col( "
                            SELECT post_id
                            FROM wp_postmeta 
                            WHERE meta_key = 'course_id'
                              AND meta_value = $post_id
                          ;" );
  if (sizeof($lesson_ids) == 1) {
  	$lesson_id = $lesson_ids[0];
	  $xapi_id = $wpdb->get_var( "
                            SELECT meta_value
                            FROM wp_postmeta 
                            WHERE post_id = $lesson_id
                              AND meta_key = 'show_xapi_content'
                          ;" );
	  if ($xapi_id != 0) {
			error_log("scitent_render_xapi_button " . $xapi_id);
		  return do_shortcode("[grassblade text='$button_label' id=".$xapi_id." target=_blank]");
		} else {
			error_log("scitent_render_xapi_button NO EMBED");
		}

  } 

  // If the above didnt work, embed the old button to go to the course page
	return "<a class='btn btn-primary courseBtn' role='button' href='". get_the_permalink($post_id) ."' rel='bookmark'> $button_label </a>";

}


///////////Certificate functions//////////////////////////

/**
 * Get course certificate link for user
 *
 * @since 2.1.0
 * 
 * @param  int     $course_id
 * @param  int     $user_id
 * @return string
 */
function new_learndash_get_course_certificate_link( $course_id, $user_id = null ) {
  $user_id = get_current_user_id();
  if ( empty( $course_id ) || empty( $user_id ) || ! sfwd_lms_has_access( $course_id, $user_id ) ) {
    return '';
  }

  $certificate_id = learndash_get_setting( $course_id, 'certificate' );

  if ( empty( $certificate_id ) ) {
    return '';
  }

  $course_status = learndash_course_status( $course_id, $user_id );

  if ( $course_status != __( 'Completed', 'learndash' ) ) {
    return '';
  }

  $url = get_permalink( $certificate_id );
  $url = ( strpos( '?', $url ) === false ) ? $url.'?' : $url.'&';
  $url = $url.'course_id='.$course_id.'&user_id='.$user_id;

  return $url;
}
//////////////////////////////////


/**
 * Get certificate details
 *
 * Return a link to certificate and certificate threshold
 *
 * @since 2.1.0
 * 
 * @param  int    $post_id
 * @param  int    $user_id
 * @return array    certificate details
 */
function new_learndash_certificate_details( $post_id, $user_id = null ) {
  $user_id = ! empty( $user_id ) ? $user_id : get_current_user_id();

  $certificateLink = '';
  $post = get_post( $post_id );
  $meta = get_post_meta( $post_id, '_sfwd-quiz' );
  $cert_post = '';
  $certificate_threshold = '0.8';

  if ( is_array( $meta ) && ! empty( $meta ) ) {
    $meta = $meta[0];

    if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_certificate'] ) ) ) {
      $certificate_post = $meta['sfwd-quiz_certificate'];
    }

    if ( is_array( $meta ) && ( ! empty( $meta['sfwd-quiz_threshold'] ) ) ) {
      $certificate_threshold = $meta['sfwd-quiz_threshold'];
    }
  }

  if ( ! empty( $certificate_post ) ) {
    $certificateLink = get_permalink( $certificate_post );
  }

  if ( ! empty( $certificateLink ) ) {
    $certificateLink .= ( strpos( 'a'.$certificateLink,'?' ) ) ? '&' : '?';
    $certificateLink .= "quiz={$post->ID}&print=" . wp_create_nonce( $post->ID . $user_id );
  }

  return array( 'certificateLink' => $certificateLink, 'certificate_threshold' => $certificate_threshold );
}

function propel_my_courses_badge_icon($post_id) {
  if (get_field("show_my_courses_flag", $post_id) == false) {
    return '';
  }
  $label = get_field("my_courses_flag_label", $post_id);
  $content = get_field("my_courses_flag_popover_content", $post_id);
  echo "<a class='badge' id='badge-$post_id' data-position='bottom right'>$label</a><div id='badge-popover-$post_id' class='ui special popup'>$content</div>";
}

function propel_render_course_access_button( $post_id, $percentage, $active, $expires ) {
  if ($active == false) {
    $expired_on = DateTime::createFromFormat('Y-m-d H:i:s', $expires)->format('Y-m-d');
    return "<a class='course-access progress-$percentage push-bottom expired nonellipsis' role='button' href='#'>Expired on $expired_on</a>";
  }

  $button_label = get_button_label($percentage);

  if (get_field('skip_to_xapi', $post_id)) {
    $xapi_id = get_single_xapi( $post_id );
    $url = do_shortcode("[grassblade text='$button_label' id=".$xapi_id." target='url']");
    $extraclasses = $extraclasses . " grassblade_launch_link";
    $linktarget = "_blank";
  } else {
    $url = get_the_permalink($post_id);
    $extraclasses = "";
    $linktarget = "_self";
  }

  // If the above didnt work, embed the old button to go to the course page
  return "<a class='course-access progress-$percentage act-btn nonellipsis push-bottom$extraclasses' role='button' href='". $url ."' target='". $linktarget ."'>$button_label</a>";
}

function get_button_label( $p ) {
  if ($p == 0) {
    $b = "Access Course";
  } else if ($p == 100) {
    $b = "Review Course";
  } else {
    $b = "Resume Course";
  }
  return $b;
}

/**
 * Renders the [propel-certificate] shortcode for any given course on the course home page
 *   Generally includes an iframe contacting the Scitent OKM server
 *   Shortcode attributes include:
 *     - $embed_code   = short code from the OKM for that specific certificate package
 *     - $button_text
 *     - $height
 *
 * @author  timothy johnson
 *
 * @created 2015-09-28
 *
 * @param   Array    $atts  The attributes sent through the shortcode
 *
 * @return  string   $out   The html output
 */
function propel_certificate( $atts_in, $button_label ) {

  global $current_user;
  get_currentuserinfo();

  if ( isset( $atts_in ) && ! empty( $atts_in ) ) {
    $atts = shortcode_atts( array(
          'embed_code' => '',
          'course' => 'COURSE',
          'button_label' => 'Access Certificate',
          'claimable' => '1'
    ), $atts_in );
  }
  // var_dump($atts);
  // wp_die();

  $embed_code = $atts['embed_code'];
  if( '' === $embed_code ) {
    return '';
  }
  $course_id = $atts['course'];
  error_log(json_encode($atts));

  $propel_settings = get_option( 'propel_settings' );
  $tenant_secret_key = $propel_settings['okm_tenant_secret_key'];
  $okm_server = Propel_LMS::okm_server();
  $course_name = get_the_title( $course );
  $key_code = get_active_enrollment_key($current_user->ID, $course_id);

  $button_text = $atts['button_label'];
  $claimable = $atts['claimable'];
  global $propel_shortcode_embed_certificate_script_included_already;
  if (empty($propel_shortcode_embed_certificate_script_included_already)){

        $out = "
        <script>
          jQuery(document).ready(function(){
              Claimer = window.Claimer || {};
              Claimer.pageVars = {};
              Claimer.pageData = {};
                var cpv = Claimer.pageVars;
                cpv.uri = '".$okm_server."/';
                cpv.location = window.location;
                cpv.tenant_secret_key = '". $tenant_secret_key ."';
                cpv.product = '". addslashes($course_name) ."';
                cpv.user_email = '". addslashes($current_user->user_email) ."';
                cpv.first_name = '". addslashes($current_user->user_firstname) ."';
                cpv.last_name = '". addslashes($current_user->user_lastname) ."';
                cpv.ext_user_id = '". $current_user->ID ."';
                cpv.button_name = '". $button_text ."';
                cpv.showModalCallback = function(){
                  jQuery('#header-secondary-outer').addClass( 'hideme' );
                  jQuery('#header-outer').addClass( 'hideme' );
                  jQuery('.page-header-no-bg').addClass( 'hideme' );
                };
                cpv.hideModalCallback = function(){
                  jQuery('#header-secondary-outer').removeClass( 'hideme' );
                  jQuery('#header-outer').removeClass( 'hideme' );
                  jQuery('.page-header-no-bg').removeClass( 'hideme' );
                };
                var script = document.createElement('script');
                script.src = cpv.uri + 'claim/claim.js';
                script.async = true;
                var entry = document.getElementsByTagName('script')[0];
                entry.parentNode.insertBefore(script, entry);

          });
        </script>
        ";

    $propel_shortcode_embed_certificate_script_included_already = true;
  }
    //$out .= '<div data-claimer-embed-id="1" button_text="Get Your Certificate"></div>';
    $out .= '<a data-claimer-embed-id="' . $embed_code . '" data-claimer-key-code="' . $key_code . '" data-claimer-claimable="' . $claimable . '" class="cert-button act-btn push-bottom">'.$button_text.'</a>';


  return $out;
}

function propel_my_courses_render_filter_sort_search_shortcode() {
  if ( !has_active_enrollments_evaluator('') ) {
    return '';
  }
?>
  <div class="course-catalog-filters propel-override">
  <div class="list-filters">
    <input class="search" placeholder="Search" class='search-catalog' />
  </div>
  <hr />
  <div class="list-filters">
  <span>Filter: </span>
  <select class="sort-filter filter-list">
    <option value="active">All Active</option>
    <option value="incomplete">Incomplete</option>
    <option value="completed">Completed</option>
    <option value="expired">Expired</option>
  </select>
  <!--&nbsp;&nbsp;&nbsp;&nbsp; -->
  <span>Sort: </span>
  <select class="sort-filter sort-list">
    <option value="course_started|desc">Date Started (recent first)</option>
    <option value="course_started|asc">Date Started (oldest first)</option>
    <option value="course_title|asc">Title (abc)</option>
    <option value="course_title|desc">Title (zyx)</option>
  </select>
  </div>
</div>
<?php
}

function link_to_certificate($post_id, $user_id, $claimable, $button_label) {
  if( get_field('embed_code', $post_id) ){
    $link = link_to_propelokm_certificate($post_id, $claimable, $button_label);
  } else {
    $link = link_to_learndash_certificate($post_id, $user_id, $button_label);
  }
  if ($link == null) {
    return "No certificate available";
  } else {
    return $link;
  }
}

function link_to_learndash_certificate($post_id, $user_id, $button_label) {
  $cert_details = new_learndash_certificate_details($post_id);
  $course_meta = get_post_meta($post_id);
  $course_array = get_post_meta($post_id,'_sfwd-courses');
  $cert_id = $course_array[0]['sfwd-courses_certificate'];
  $cert_path = get_post_permalink($cert_id);
  $course_certficate_link = new_learndash_get_course_certificate_link( $course_id, $user_id );
  $new_course_certficate_link = $cert_path ."?". "course_id=" . $post_id . "&user_id=" . $user_id;

  $quiz_id = $course_array[0]['sfwd-courses_wdm_final_quiz'];
  $quiz_meta = get_post_meta($quiz_id);
  $quiz_array = $quiz_meta['_sfwd-quiz'];
  $unser_quiz_array = maybe_unserialize($quiz_array[0]);
  $quiz_cert_id = $unser_quiz_array['sfwd-quiz_certificate'];
  $quiz_cert_path = get_post_permalink($quiz_cert_id);
  $new_quiz_certficate_link = $quiz_cert_path ."?". "quiz=" . $quiz_id . "&print=" . wp_create_nonce( $quiz_id . $user_id );
  if ($cert_id != 0){
    $url = $new_course_certficate_link;
  } elseif ($quiz_cert_id != 0) {
    $url = $new_quiz_certficate_link;
  }
  if (isset($url)) {
    return "<a href='$url' class='cert-button push-bottom' target='_blank'>".$button_label."</a>";
  } else {
    return null;
  }
}
function link_to_propelokm_certificate($post_id, $claimable, $button_label) {
  $embedCode = get_field('embed_code', $post_id);
  $embedThis = " embed_code='".$embedCode . "' course='" . $post_id . "' button_label='" . $button_label . "' claimable='" . $claimable . "'";
  return do_shortcode('[propel-certificate ' . $embedThis . ']');
}

function get_single_xapi( $post_id ) {
  global $wpdb;
  $lesson_ids = $wpdb->get_col( "
                          SELECT post_id
                          FROM wp_postmeta
                          WHERE meta_key = 'course_id'
                            AND meta_value = $post_id
                        ;" );
  $xapi_id = $wpdb->get_var( "
                        SELECT meta_value
                        FROM wp_postmeta
                        WHERE post_id = $lesson_ids[0]
                          AND meta_key = 'show_xapi_content'
                      ;" );
  return $xapi_id;
}
