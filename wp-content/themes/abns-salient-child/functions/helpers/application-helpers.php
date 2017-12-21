<?php
/**
 * Laboratory Theme Helper Functions.
 *
 * @package WordPress
 * @subpackage Laboratory Theme
 */

/**
 * Try and get the current post from the GET or POST variable
 * note - this uses super-global form vars but doesn't actually deal with form submission, so usage of these vars is safe.
 *
 * @return {Object | false}
 */
function get_current_post() {
  // Get the Post ID.
  if ( array_key_exists( 'post', $_GET ) ) {
    $post_id = wp_unslash( $_GET['post'] );
  } elseif ( array_key_exists( 'post_ID', $_POST ) ) {
    $post_id = wp_unslash( $_POST['post_ID'] );
  } else {
    // if 'post' is not in the $_GET or $_POST array, die.
    return false;
  }
  if ( ! isset( $post_id ) ) {
    return false;
  }
  // get post from post id.
  $post = get_post( $post_id );
  if ( ! isset( $post ) ) {
    return false;
  }
  return $post;
}

/**
 * Get a key->value array of pages by page title
 * example list:
 * array(
 *   09 => 'Home',
 *   10 => 'About',
 *   12 => 'News',
 * )
 *
 * @return {array}
 */
function get_list_of_pages() {
  $args  = array(
    'post_type'   => 'page',
    'post_status' => 'publish',
  );
  $pages = get_pages( $args );
  if ( ! $pages ) {
    return array();
  }
  // we only want to show pages that are set. plus we reduce the change of mutating data.
  $pages_list = array();
  foreach ( $pages as $page ) {
    if ( isset( $page ) ) {
      $pages_list[ $page->ID ] = $page->post_title;
    }
  }
  return $pages_list;
}

/**
 * Those times when you need to get a template part, but don't want to echo it right away.
 * See - https://developer.wordpress.org/reference/functions/get_template_part/
 *
 * @param {string} $template_name - the template name.
 * @param {string} $part_name - the part name.
 * @return {string} template content
 */
function load_template_part( $template_name, $part_name = null ) {
  ob_start();
  get_template_part( $template_name, $part_name );
  $var = ob_get_contents();
  ob_end_clean();
  return $var;
}

/**
 * Takes an array of image attachment ids and returns mapped image src
 * Each returned array element is an array (url, width, height, is_intermediate), or false, if no image is available.
 *
 * @param {int[]} $ids - attachment ids.
 * @return {array}
 */
function get_image_src_array_from_attachment_ids( $ids ) {
  if ( empty( $ids ) ) {
    return false;
  }
  // if single image id, return image src array.
  if ( gettype( $ids ) === 'string' ) {
    return wp_get_attachment_image_src( $ids, 'full' );
  }
  // get image sources in an array for each image id.
  $images = array_map( 'vb_get_attachment_image_src', $ids );
  // if $images is an array with only 1 element, return the first element of the array.
  if ( ! empty( $images ) && count( $images ) === 1 ) {
    return reset( $images );
  }
  return $images;
}

/**
 * Outputs HTML an Input with div wraps, error locations and more. Intended for use with text, password, textareas, email inputs but not tested for
 * inputs such as radio buttons, etc. Similar functions could be written for select boxes, etc. Also much more functionality could be added
 * to this. This is only a starting point
 *
 * @param Array $args see below.
 * - wrapper_class (String): The wrapper class
 * - label (String): The content for the label
 * - input_class (String): The class for the input element
 * - after_input (String): html to add directly after the input
 * - name (String): The name for the input
 * - type (String): The input type
 * - default_value (String): The default value for the input
 * - error (String): The error
 * - required (String): Boolean for if an input is required or not. Defaults to an empty string.
 */
function application_text_input( $args = array() ) {
  // Set defaults.
  if ( empty( $args['type'] ) ) {
    $args['type'] = 'text';
  }
  if ( ! empty( $args['default_value'] ) ) {
    $default_value = $args['default_value'];
  } else {
    $default_value = '';
  }
  if ( ! empty( $args['required'] ) ) {
    $required = 'required';
  } else {
    $required = '';
  }
?>
<div class="input-wrap <?php echo wp_kses_post( $args['wrapper_class'] ); ?>">
  <label class="label" for="<?php echo wp_kses_post( $args['name'] ); ?>"><?php echo wp_kses_post( $args['label'] ); ?></label>
  <?php if ( 'textarea' === $args['type'] ) : ?>
    <textarea class="<?php echo wp_kses_post( $args['input_class'] ); ?>" name="<?php echo wp_kses_post( $args['name'] ); ?>" value="" <?php echo wp_kses_post( $required ); ?>/>
    <?php echo wp_kses_post( $default_value ); ?></textarea>
  <?php else : ?>
    <input class="<?php echo wp_kses_post( $args['input_class'] ); ?>" name="<?php echo wp_kses_post( $args['name'] ); ?>" type="<?php echo wp_kses_post( $args['type'] ); ?>"
      value="<?php echo wp_kses_post( $default_value ); ?>"  <?php echo wp_kses_post( $required ); ?>/>
  <?php endif ?>
  <?php echo wp_kses_post( $args['after_input'] ); ?>
  <div class="error"><?php echo wp_kses_post( $args['error'] ); ?></div>
</div><!--wrapper-class-->
<?php
}

/**
 * Useful for stripping special characters from a string
 *
 * @param String $string - the string to clean.
 */
function labs_clean( $string ) {
  $string = str_replace( ' ', '-', $string ); // Replaces all spaces with hyphens.
  return preg_replace( '/[^A-Za-z0-9\-]/', '', $string ); // Removes special chars.
}
