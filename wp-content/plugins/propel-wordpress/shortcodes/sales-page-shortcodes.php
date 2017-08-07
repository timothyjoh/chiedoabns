<?php
/*
*  Display Multiple Instructors
*/            
function enqueue_sales_page_styles(){
    wp_enqueue_style('sales_page_style', plugins_url( '../css/shortcodes/sales-page.css', __FILE__ ));
}
enqueue_sales_page_styles();
/* display Name, Bio, Image for one or more instructors*/
function propelInstructors_fn() {
if( function_exists('have_rows') ) {
    ?>
    <?php
    ob_start();
    if( have_rows('propel_instructors') ): ?>

        <?php while( have_rows('propel_instructors') ): the_row(); ?>
          <div class="eachInstructor">
            <img class="instructor_image" src=" <?php the_sub_field('propel_instructor_image'); ?> " alt=""/>
            <p class="instructor_name"><?php the_sub_field('propel_instructor_name'); ?></p>
            <p class="instructor_bio"><?php the_sub_field('propel_instructor_bio'); ?> </p>
          </div> 
        <?php endwhile; ?>      
    <?php endif;    
    $content = ob_get_contents();
    ob_end_clean();
    return $content; 
    }  
}
add_shortcode( 'propelInstructors', 'propelInstructors_fn' );

/* display intro text*/
function propelIntroductoryText_fn(){ 
    if(get_field(introductory_text)):
        ob_start(); ?>
            <p class="course-intro-text"><?php the_field(introductory_text);?></p>
        <?php $contentIntroText = ob_get_contents();
        ob_end_clean();
        return $contentIntroText; 
    endif;
}
add_shortcode( 'propelIntroText', 'propelIntroductoryText_fn' );


/*Display Main Course Description*/
function propelMainCourseDescription_fn(){ 
    if(get_field(main_course_description)):
        ob_start();?> 
            <p class="course-main-description"><?php the_field(main_course_description);?></p>
        <?php $contentMainDesc = ob_get_contents();
        ob_end_clean();
        return $contentMainDesc; 
    endif;

}
add_shortcode( 'propelMainDescription', 'propelMainCourseDescription_fn' );
