<?php
 /**
  * Helper functions for WooCommerce
  */

 /** propel_product_category_icons_list_html()
  * Generates a <ul> with icons and / or Names with / without links 
  * of categories linked to woo products
  *
  * Requires a $product 
  */
 function propel_product_category_icons_list_html( $product, $hyperlink = '' ) {
	$wcatTerms = wp_get_post_terms( $product->id, 'product_cat', array('hide_empty' => 0, 'orderby' => 'ASC',  'parent' => 0));
	?><ul class="product-category-icons"><?php
		foreach($wcatTerms as $wcatTerm) { 
			?><li><?php
			if( '' !== $hyperlink ) {
				?><a class="category-icon" href="<?php echo $hyperlink; ?>"><?php
			}
			$thumbnail_id = get_woocommerce_term_meta( $wcatTerm->term_id, 'thumbnail_id', true );
			$image = wp_get_attachment_url( intval($thumbnail_id) ); 
			?><img class="category-icon" src="<?php echo $image; ?>" /><?php
			if( '' !== $hyperlink ) {
				?></a><?php
			}
			?></li><?php
		}
	?></ul><?php
 }

 function propel_product_category_icons_from_category_name( $cat_name ) {
	$wcatTerms = get_terms( 'product_cat', array('name_like' => $cat_name, 'orderby' => 'ASC',  'parent' => 0));
	foreach($wcatTerms as $wcatTerm) { 
		$thumbnail_id = get_woocommerce_term_meta( $wcatTerm->term_id, 'thumbnail_id', true );
		return wp_get_attachment_url( intval($thumbnail_id) ); 
	}
 }


 function propel_product_category_names_list_html( $product, $hyperlink = '' ) {
	$wcatTerms = wp_get_post_terms( $product->id, 'product_cat', array('hide_empty' => 0, 'orderby' => 'ASC',  'parent' => 0));
	$shown_first_item = false;
	?><ul><?php
		foreach($wcatTerms as $wcatTerm) { 
			if( '' !== $hyperlink ) {
				?><a class="category-icon" href="<?php echo $hyperlink; ?>"><?php
			}
			if( $shown_first_item ) {
				echo ', ';
			}
			echo $wcatTerm->name ;
			$shown_first_item = true;
			if( '' !== $hyperlink ) {
				?></a><?php
			}
		}
	?></ul><?php
 } 