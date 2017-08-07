<?php

add_shortcode( 'catalog-grid', 'propel_catalog_grid_shortcode' );
add_shortcode( 'catalog-filters', 'propel_catalog_render_filter_sort_search_shortcode' );
add_shortcode( 'catalog-category-filters', 'propel_catalog_render_catalog_filters_shortcode' );

if(function_exists("register_field_group"))
{
  register_field_group(array (
    'id' => 'acf_woo-product-extras',
    'title' => 'Propel Catalog Extras',
    'fields' => array (
      array (
        'key' => 'field_product_course_price_for_members',
        'label' => 'Price for Members',
        'name' => 'price_for_members',
        'type' => 'text',
        'instructions' => 'Use this field to display the price for members',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_catalog_description',
        'label' => 'Catalog Description',
        'name' => 'catalog_description',
        'type' => 'textarea',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_product_course_more_info',
        'label' => 'Course More Info Button Label',
        'name' => 'course_more_info',
        'type' => 'text',
        'instructions' => 'Edit the Course Info button text',
        'default_value' => 'Course Info',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_product_course_authors',
        'label' => 'Course Authors',
        'name' => 'course_authors',
        'type' => 'text',
        'instructions' => 'Use this field for how the author names will display on the my courses page and the course catalog',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_product_credit_type',
        'label' => 'Credit Type',
        'name' => 'credit_type',
        'type' => 'text',
        'instructions' => 'Use this field for how the certificate info will display on the my courses page above the claim button',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_catalog_product_flag',
        'label' => 'Show Catalog Product Flag',
        'name' => 'show_catalog_product_flag',
        'type' => 'true_false',
        'instructions' => 'Set this field to True to show a flag on the catalog page',
        'message' => '',
        'default_value' => 0,
      ),
      array (
        'key' => 'field_catalog_product_flag_label',
        'label' => 'Catalog Product Flag Label',
        'name' => 'catalog_product_flag_label',
        'type' => 'text',
        'instructions' => 'The label for the flag on the Catalog page',
        'conditional_logic' => array (
          'status' => 1,
          'rules' => array (
            array (
              'field' => 'field_catalog_product_flag',
              'operator' => '==',
              'value' => '1',
            ),
          ),
          'allorany' => 'all',
        ),
        'default_value' => 'update',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'none',
        'maxlength' => 35,
      ),
      array (
        'key' => 'field_catalog_product_flag_content',
        'label' => 'Catalog Product Flag Popover Content',
        'name' => 'catalog_product_flag_popover_content',
        'type' => 'wysiwyg',
        'instructions' => 'The content for the popover when the flag is clicked',
        'conditional_logic' => array (
          'status' => 1,
          'rules' => array (
            array (
              'field' => 'field_catalog_product_flag',
              'operator' => '==',
              'value' => '1',
            ),
          ),
          'allorany' => 'all',
        ),
        'default_value' => '',
        'toolbar' => 'basic',
        'media_upload' => 'no',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'product',
          'order_no' => 0,
          'group_no' => 0,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'default',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
}
// multiple instructors
// if( function_exists('acf_add_local_field_group') ):

// acf_add_local_field_group(array (
//   'key' => 'group_57ed340d40226',
//   'title' => 'multiple instructors',
//   'fields' => array (
//     array (
//       'key' => 'field_57ed341bfd4c2',
//       'label' => 'instructors',
//       'name' => 'instructors',
//       'type' => 'repeater',
//       'instructions' => '',
//       'required' => 0,
//       'conditional_logic' => 0,
//       'wrapper' => array (
//         'width' => '',
//         'class' => '',
//         'id' => '',
//       ),
//       'collapsed' => '',
//       'min' => '',
//       'max' => '',
//       'layout' => 'table',
//       'button_label' => 'Add Row',
//       'sub_fields' => array (
//         array (
//           'key' => 'field_57ed343afd4c3',
//           'label' => 'Instructor Name',
//           'name' => 'instructor_name',
//           'type' => 'text',
//           'instructions' => '',
//           'required' => 0,
//           'conditional_logic' => 0,
//           'wrapper' => array (
//             'width' => '',
//             'class' => '',
//             'id' => '',
//           ),
//           'default_value' => '',
//           'placeholder' => '',
//           'prepend' => '',
//           'append' => '',
//           'maxlength' => '',
//         ),
//         array (
//           'key' => 'field_57ed3474fd4c4',
//           'label' => 'Instructor Bio',
//           'name' => 'instructor_bio',
//           'type' => 'wysiwyg',
//           'instructions' => '',
//           'required' => 0,
//           'conditional_logic' => 0,
//           'wrapper' => array (
//             'width' => '',
//             'class' => '',
//             'id' => '',
//           ),
//           'default_value' => '',
//           'tabs' => 'all',
//           'toolbar' => 'full',
//           'media_upload' => 1,
//         ),
//         array (
//           'key' => 'field_57ed349dfd4c5',
//           'label' => 'Instructor Image',
//           'name' => 'instructor_image',
//           'type' => 'image',
//           'instructions' => '',
//           'required' => 0,
//           'conditional_logic' => 0,
//           'wrapper' => array (
//             'width' => '',
//             'class' => '',
//             'id' => '',
//           ),
//           'return_format' => 'url',
//           'preview_size' => 'thumbnail',
//           'library' => 'all',
//           'min_width' => '',
//           'min_height' => '',
//           'min_size' => '',
//           'max_width' => '',
//           'max_height' => '',
//           'max_size' => '',
//           'mime_types' => '',
//         ),
//       ),
//     ),
//   ),
//   'location' => array (
//     array (
//       array (
//         'param' => 'post_type',
//         'operator' => '==',
//         'value' => 'product',
//       ),
//     ),
//   ),
//   'menu_order' => 0,
//   'position' => 'normal',
//   'style' => 'default',
//   'label_placement' => 'top',
//   'instruction_placement' => 'label',
//   'hide_on_screen' => '',
//   'active' => 1,
//   'description' => '',
// ));

// endif;

function propel_catalog_render_filter_sort_search_shortcode(){
  ?>
  <div class="course-catalog-filters">
    <div class="list-filters">
      <input class="search search-catalog" placeholder="Search Courses" />
    </div>
    <hr />
    <div class="list-filters">
      <span class="dropdown filter-widget">
        Filter:
        <select class="sort-filter filter-list">
          <option value="all">All</option>
          <option value="bundles">Bundles &amp; Curriculum</option>
          <option value="freeformembers">Free for Members</option>
        </select>
      </span>
      <span class="dropdown sort-widget">
        Sort:
        <select class="sort-filter sort-list">
          <option value="publish_date|desc">Release Date (recent)</option>
          <option value="publish_date|asc">Release Date (oldest)</option>
          <option value="price_non_members|desc">Price (high to low)</option>
          <option value="price_non_members|asc">Price (low to high)</option>
          <option value="course_title|asc">Title (abc)</option>
          <option value="course_title|desc">Title (zyx)</option>
        </select>
      </span>
    </div>
  </div>
  <?php
}
function propel_catalog_render_catalog_filters_shortcode() {
  ?>
  <h5> Course Categories</h5>
  <div class="catalog-category-filters"></div>

  <?php
}

function propel_catalog_grid_shortcode( $attr ) {

  wp_enqueue_style('course_catalog_style');
  wp_enqueue_script('course_catalog_script');

  global $post;
  $user_id = get_current_user_id();
  global $wpdb;

  $params = array(
    'posts_per_page' => 50,
    'post_type' => array('product', 'product_variation'),
    'meta_query' => array(
        array(
            'key' => '_stock_status',
            'value' => 'instock'
        ),
        array(
          'key'       => '_visibility',
          'value'     => 'hidden',
          'compare'   => '!=',
        )
    )
  );
  $wc_query = new WP_Query($params);

  echo '<div class="course-catalog-container" id="course-catalog-container">';
  echo '<div class="list">';
  echo '<script>function addWooCategory(a,u){return void 0===window.woo_cat&&(window.woo_cat={}),void 0===window.woo_cat[a]?window.woo_cat[a]=1:window.woo_cat[a]+=1,window.woo_cat}</script>';
  if ($wc_query->have_posts()) {
    while ($wc_query->have_posts()) {
      $wc_query->the_post();
      $product_id = $post->ID;
      propel_render_catalog_item($post);
    }
    wp_reset_postdata();
  } else {
    echo "No Products";
  }
  echo '</div>'; // ending div.list
  echo '</div>'; // ending div.course-catalog-container
}

function propel_render_catalog_item($post) {
  $options = get_option('sfwd_cpt_options');
  $_pf = new WC_Product_Factory();
  $product = $_pf->get_product($post->ID);
  $post_image_id = get_post_thumbnail_id($post->ID);
  if ($post_image_id) {
    $thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
    if ($thumbnail) (string)$thumbnail = $thumbnail[0];
  }
  $user_id = get_current_user_id();
  ?>

<section class="courselist-course" data-post-id="<?php echo $post->ID; ?>">
    <div class="extra-filter-data">
      <p class="course_count"><?php echo propel_product_has_many_courses($product) ?></p>
      <p class="publish_date"><?php echo get_the_date("U", $post); ?></p>
    </div>
    <a href="#" class="course-image-link">
      <img src="<?php echo $thumbnail; ?>" />
    </a>
    <div class="courselist-course-body">
      <div class="row activation_key">
        <div class="courselist-course-info">
          <h5 class="ellipsis course_title"
              data-title="<?php echo get_the_title($post); ?>">
            <?php echo propel_catalog_badge_icon($post); ?>
            <?php echo get_the_title($post); ?>
          </h5>
          <p class="authors ellipsis"><?php echo get_field("course_authors", $post->ID); ?></p>
          <p class="categories"><?php propel_get_categories($post); ?></p>
          <p class="excerpt ellipsis">
            <?php echo get_field("catalog_description", $post->ID); ?>
          </p>
          <?php echo propel_catalog_more_info_button( $post ) ?>
        </div>
        <div class="courselist-cert-claim">
          <p>
            <span class='price-label'> Price: </span>
            <span class="price-number price_non_members"><?php echo propel_product_price_for_non_members($product); ?></span>
            <br />
            <span class='price-label'> MEMBERS: </span>
            <span class="price-number price_members"><?php echo propel_product_price_for_members($post); ?></span>
            <?php //echo propel_catalog_price($product); ?>
          </p>
          <?php
              echo propel_catalog_add_to_cart($product);
          ?>
        </div>
      </div>
    </div>
</section>

<?php
 // end the propel_render_my_courses_list function
}

function propel_product_has_many_courses($product) {
  $courses_id = get_post_meta( $product->id, '_related_course', true );
  if ( $courses_id && is_array( $courses_id ) ) {
    return count($courses_id);
  }
  return "0";
}

function propel_get_categories($post) {
  $terms = get_the_terms( $post->ID, 'product_cat' );
  foreach ($terms as $term) {
    $cat = $term->name;
    $iconurl = propel_product_category_icons_from_category_name($cat);
    echo "<script>addWooCategory('$cat','$iconurl');</script> $cat";
    $product_cat_id = $term->term_id;
  }
}

function propel_product_price_for_non_members($product) {
  $price = $product->get_price();
  return free_or_dollars($price);
}
function propel_product_price_for_members($post) {
  $price = number_format(floatval(get_field("price_for_members", $post->ID)), 2);
  return free_or_dollars($price);
}
function free_or_dollars($price){
  if ($price <=0 || $price === '') {
    return "FREE";
  }
  return '<em>$</em>' . str_replace(".00", '', floatval($price));
}

function propel_catalog_add_to_cart($product) {
  $sku = $product->get_sku();
  $label = $product->add_to_cart_text();
  $carturi = $product->add_to_cart_url();
  $productid = $product->id;
  return "<a href='$carturi' data-product_sku='$sku' data-product_id='$productid' class='cert-button act-btn push-bottom'>Add to Cart</a>";
}

function propel_catalog_price($product) {
  $html = $product->get_price_html();
  return $html;
}

function propel_catalog_more_info_button( $post ) {
  $uri = esc_url( get_permalink( $post ) );
  $label = get_field("course_more_info", $post->ID) ?: "Course Info";
  return "<a class='course-access act-btn push-bottom nonellipsis' role='button' href='$uri'>$label</a>";
}

function propel_catalog_badge_icon($post) {
  if (get_field("show_catalog_product_flag", $post->ID) == false) {
    return '';
  }
  $postid = $post->ID;
  $label = get_field("catalog_product_flag_label", $post->ID);
  $content = get_field("catalog_product_flag_popover_content", $post->ID);
  return "<span class='badge' id='badge-$postid' data-position='bottom right'>$label</span>";
  /* return "<a class='badge' id='badge-$postid' data-position='bottom right'>$label</a><div id='badge-popover-$postid' class='ui special popup' style='display:none'>$content</div>"; */
}
