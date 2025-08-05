<?php

if( !zota_is_Woocommerce_activated() ) return;

// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
if ( ! function_exists( 'zota_add_custom_product_data_tab' ) ) {
  add_filter( 'woocommerce_product_data_tabs', 'zota_add_custom_product_data_tab', 80 );
  function zota_add_custom_product_data_tab( $product_data_tabs ) {
      $product_data_tabs['zota-options-tab'] = array(
          'label' => esc_html__( 'Zota Options', 'zota' ),
          'target' => 'zota_product_data',
          'class'     => array(),
          'priority' => 100,
      );
      return $product_data_tabs;
  }
}

if ( ! function_exists( 'zota_options_woocom_product_data_fields' ) ) {
// functions you can call to output text boxes, select boxes, etc.
  add_action('woocommerce_product_data_panels', 'zota_options_woocom_product_data_fields');

  function zota_options_woocom_product_data_fields() {
    global $post;

    // Note the 'id' attribute needs to match the 'target' parameter set above
    ?> <div id = 'zota_product_data'
    class = 'panel woocommerce_options_panel' > <?php
        ?> <div class = 'options_group' > <?php
                    // Text Field
        woocommerce_wp_text_input(
          array(
            'id' => '_zota_video_url', 
            'label' => esc_html__('Featured Video URL', 'zota'),
            'placeholder' => esc_html__('Video URL', 'zota'),
            'desc_tip' => true,
            'description' => esc_html__('Enter the video url at https://vimeo.com/ or https://www.youtube.com/', 'zota')
          )
        );

        $post_types = apply_filters( 'tbay_elementor_register_post_types', array());

        if( in_array('customtab', $post_types) ) {
          woocommerce_wp_select(
            array(
              'id'          => '_zota_custom_tab',
              'label'       => esc_html__( 'Select Custom Tab', 'zota' ),
              'options'     => zota_wc_get_custom_tab_options(),
              'class'       => 'cb-admin-multiselect',
              'desc_tip'    => true,
            )
          );
  
          woocommerce_wp_text_input(
            array(
              'id'                => '_zota_custom_tab_priority',
              'label'             => esc_html__( 'Custom Tab priority', 'zota' ),
              'desc_tip'          => true,
              'type'              => 'number',
              'description' => esc_html__('Description – 10, </br>Additional information – 20, </br>Reviews – 30', 'zota'),
              'custom_attributes' => array(
                'step' => 'any',
              ),
            )
          );
  
        }

        ?> 
      </div>

    </div><?php
  }
}

if (!function_exists('zota_options_woocom_save_proddata_custom_fields')) {
  function zota_options_woocom_save_proddata_custom_fields($product) {
      $fields = [
          '_zota_video_url' => '',
          '_zota_custom_tab' => '',
          '_zota_custom_tab_priority' => '',
      ];

      foreach ($fields as $key => $default) {
          $new_value = isset($_POST[$key]) ? wc_clean($_POST[$key]) : $default;
          $old_value = $product->get_meta($key);

          if ($new_value !== $old_value) {
              $product->update_meta_data($key, $new_value);

              if ($key === '_zota_video_url') {
                  $transient_key = 'zota_video_' . $product->get_id();
                  delete_transient($transient_key);

                  if (!empty($old_value)) {
                      $old_video_info = explode(':', zota_video_type_by_url($old_value));
                      if (count($old_video_info) === 2) {
                          $old_transient_key = 'zota_video_thumb_' . $old_video_info[0] . '_' . $old_video_info[1];
                          delete_transient($old_transient_key);
                      }
                  }

                  if (!empty($new_value)) {
                      $video_info = explode(':', zota_video_type_by_url($new_value));
                      if (count($video_info) === 2) {
                          $img_id = zota_save_video_thumbnail(['host' => $video_info[0], 'id' => $video_info[1]]);
                          $product->update_meta_data('_zota_video_image_url', $img_id);
                      }
                  } else {
                      $product->update_meta_data('_zota_video_image_url', '');
                  }
              }
          }
      }
  }
  add_action('woocommerce_admin_process_product_object', 'zota_options_woocom_save_proddata_custom_fields', 20);
}

function zota_save_video_thumbnail($video_info) {
  if (!isset($video_info['host']) || !isset($video_info['id'])) {
      return '';
  }

  $name = isset($video_info['name']) ? $video_info['name'] : $video_info['id'];
  $transient_key = 'zota_video_thumb_' . $video_info['host'] . '_' . $video_info['id'];
  $img_id = get_transient($transient_key);

  if ($img_id !== false) {
      return $img_id;
  }

  $result = 'no';
  $img_url = '';
  $img_id = '';

  switch ($video_info['host']) {
      case 'vimeo':
          if (function_exists('simplexml_load_file')) {
              $img_url = 'http://vimeo.com/api/v2/video/' . $video_info['id'] . '.xml';
              $xml = @simplexml_load_file($img_url);
              if ($xml !== false) {
                  $img_url = isset($xml->video->thumbnail_large) ? (string)$xml->video->thumbnail_large : '';
                  if (!empty($img_url) && @getimagesize($img_url)) {
                      $result = 'ok';
                  }
              }
          }
          break;

      case 'youtube':
          $youtube_image_sizes = ['maxresdefault', 'hqdefault', 'mqdefault', 'sqdefault'];
          $youtube_url = 'https://img.youtube.com/vi/' . $video_info['id'] . '/';
          foreach ($youtube_image_sizes as $image_size) {
              $img_url = $youtube_url . $image_size . '.jpg';
              $response = wp_remote_head($img_url);
              if (!is_wp_error($response) && $response['response']['code'] == 200) {
                  $result = 'ok';
                  break;
              }
          }
          break;
  }

  if ('ok' === $result) {
      $img_id = zota_save_remote_image($img_url, $name);
      set_transient($transient_key, $img_id, WEEK_IN_SECONDS);
  }

  return $img_id;
}

if ( ! function_exists( 'zota_save_remote_image' ) ) {

	function zota_save_remote_image( $url, $newfile_name = '' ) {

		$url = str_replace( 'https', 'http', $url );
		$tmp = download_url( (string) $url );

		$file_array = array();
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', (string) $url, $matches );
		$file_name = basename( $matches[0] );
		if ( '' !== $newfile_name ) {
			$file_name_info = explode( '.', $file_name );
			$file_name      = $newfile_name . '.' . $file_name_info[1];
		}


		if ( ! function_exists( 'remove_accents' ) ) {
			require_once( ABSPATH . 'wp-includes/formatting.php' );
		}
		$file_name = sanitize_file_name( remove_accents( $file_name ) );
		$file_name = str_replace( '-', '_', $file_name );

		$file_array['name']     = $file_name;
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';

		}

		// do the validation and storage stuff
		return media_handle_sideload( $file_array, 0 );
	}

}

if( ! function_exists( 'tbay_size_guide_metabox_output' ) ) {
  function tbay_size_guide_metabox_output($post) {
      $product_image = get_post_meta($post->ID, '_product_size_guide_image', true) ?: '';
      $attachments = array_filter(explode(',', $product_image));
      $attachment_id = !empty($attachments) ? $attachments[0] : ''; // Chỉ lấy hình đầu tiên

      ?>
      <div id="product_size_guide_images_container">
          <ul class="product_size_guide_images">
              <?php if ($attachment_id && wp_attachment_is_image($attachment_id)) : ?>
                  <li class="image" data-attachment_id="<?php echo esc_attr($attachment_id); ?>">
                      <?php echo wp_get_attachment_image($attachment_id, 'thumbnail'); ?>
                      <ul class="actions">
                          <li><a href="#" class="delete tips" data-tip="<?php esc_attr_e('Remove product image', 'zota'); ?>"><?php esc_html_e('Remove product image', 'zota'); ?></a></li>
                      </ul>
                  </li>
              <?php endif; ?>
          </ul>
          <input type="hidden" id="product_size_guide_image" name="product_size_guide_image" value="<?php echo esc_attr($product_image); ?>" />
      </div>
      <p class="add_product_size_guide_images hide-if-no-js">
          <a href="#" data-choose="<?php esc_attr_e('Add Images to Product Size Guide', 'zota'); ?>" data-update="<?php esc_attr_e('Add to image', 'zota'); ?>" data-delete="<?php esc_attr_e('Delete image', 'zota'); ?>" data-text="<?php esc_attr_e('Remove product image', 'zota'); ?>"><?php esc_html_e('Add product Size Guide view images', 'zota'); ?></a>
      </p>
      <?php
  }
}


/**
 * ------------------------------------------------------------------------------------------------
 * Save metaboxes
 * ------------------------------------------------------------------------------------------------
 */
if( ! function_exists( 'zota_proccess_size_guide_view_metabox' ) ) {
  add_action( 'woocommerce_process_product_meta', 'zota_proccess_size_guide_view_metabox', 50, 2 );
  function zota_proccess_size_guide_view_metabox( $post_id, $post ) {
    $attachment_ids = isset( $_POST['product_size_guide_image'] ) ? array_filter( explode( ',', wc_clean( $_POST['product_size_guide_image'] ) ) ) : array();

    update_post_meta( $post_id, '_product_size_guide_image', implode( ',', $attachment_ids ) );
  }
}


/**
 * ------------------------------------------------------------------------------------------------
 * Returns the size guide image attachment ids.
 * ------------------------------------------------------------------------------------------------
 */
if( ! function_exists( 'zota_get_size_guide_attachment_ids' ) ) {
  function zota_get_size_guide_attachment_ids() {
    global $post;

    if( ! $post ) return;

    $product_image = get_post_meta( $post->ID, '_product_size_guide_image', true);

    return apply_filters( 'woocommerce_product_size_guide_attachment_ids', array_filter( array_filter( (array) explode( ',', $product_image ) ), 'wp_attachment_is_image' ) );
  }
}


/**
 * ------------------------------------------------------------------------------------------------
 * Dropdown
 * ------------------------------------------------------------------------------------------------
 */
//Dropdown template
if( ! function_exists( 'tbay_swatch_attribute_template' ) ) {
    function tbay_swatch_attribute_template( $post ){

        global $post;


        $attribute_post_id = get_post_meta( $post->ID, '_zota_attribute_select' );
        $attribute_post_id = isset( $attribute_post_id[0] ) ? $attribute_post_id[0] : '';

        ?>

          <select name="zota_attribute_select" class="zota_attribute_taxonomy">
            <option value="" selected="selected"><?php esc_html_e( 'Global Setting', 'zota' ); ?></option>

              <?php 

                global $wc_product_attributes;

                // Array of defined attribute taxonomies.
                $attribute_taxonomies = wc_get_attribute_taxonomies();

                if ( ! empty( $attribute_taxonomies ) ) {
                  foreach ( $attribute_taxonomies as $tax ) {
                    $attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                    $label                   = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;

                    echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '" '. selected( $attribute_post_id, $attribute_taxonomy_name ) .' >' . esc_html( $label ) . '</option>';
                  }
                }

              ?>

          </select>

        <?php
    }
}


//Dropdown Save
if( ! function_exists( 'zota_attribute_dropdown_save' ) ) {
    add_action( 'woocommerce_process_product_meta', 'zota_attribute_dropdown_save', 30, 2 );

    function zota_attribute_dropdown_save( $post_id ){
        if ( isset( $_POST['zota_attribute_select'] ) ) {    

          update_post_meta( $post_id, '_zota_attribute_select', $_POST['zota_attribute_select'] );     

        }
    }
}

/**
 * ------------------------------------------------------------------------------------------------
 * Dropdown
 * ------------------------------------------------------------------------------------------------
 */
//Dropdown Single layout template
if( ! function_exists( 'tbay_single_select_single_layout_template' ) ) {
    function tbay_single_select_single_layout_template( $post ){

        global $post;


        $layout_post_id = get_post_meta( $post->ID, '_zota_single_layout_select' );
        $layout_post_id = isset( $layout_post_id[0] ) ? $layout_post_id[0] : '';

        ?>

          <select name="zota_layout_select" class="zota_single_layout_taxonomy">
            <option value="" selected="selected"><?php esc_html_e( 'Global Setting', 'zota' ); ?></option>

              <?php 

                global $wc_product_attributes;



                // Array of defined attribute taxonomies.
                $attribute_taxonomies = wc_get_attribute_taxonomies();



                  $layout_selects = apply_filters( 'zota_layout_select_filters', array(
                    'vertical'              => esc_html__('Image Vertical', 'zota'), 
                    'horizontal'            => esc_html__('Image Horizontal', 'zota'),
                    'left-main'             => esc_html__('Left - Main Sidebar', 'zota'),
                    'main-right'            => esc_html__('Main - Right Sidebar', 'zota')
                  ));

                  foreach ( $layout_selects as $key => $select ) {

                    echo '<option value="' . esc_attr( $key ) . '" '. selected( $layout_post_id, $key ) .' >' . esc_html( $select ) . '</option>';
                  }

              ?>

          </select>

        <?php
    }
}


//Dropdown Save
if( ! function_exists( 'zota_single_select_dropdown_save' ) ) {
    add_action( 'woocommerce_process_product_meta', 'zota_single_select_dropdown_save', 30, 2 );

    function zota_single_select_dropdown_save( $post_id ){
        if ( isset( $_POST['zota_layout_select'] ) ) {    

          update_post_meta( $post_id, '_zota_single_layout_select', $_POST['zota_layout_select'] );     

        }
    }
}
