<?php

class WRI_Widget extends WP_Widget {


	function __construct() {	
		parent::__construct(false, $name = __('WP Related Items (WRI)','wp-related-items'));
		
		include_once 'wri-utils.php';
		
	}


	function widget($args, $instance) {
		
		global $wri, $wri_general_settings;
			
		if ( (empty($instance['reference_posttype'])
			 )
			&& ( 
				(is_singular() && '1' == $instance['display'])
			   )
			 ) {
		
			
			$content = $wri->wri_display_related( 'on_widget', null, $instance, FALSE);
			
			if ((trim(strip_tags($content))) != null) {
	
				$wri_general_settings = get_option('wri_general_settings');
		
				$width = $instance['thumbnail_width'];
				$height = $instance['thumbnail_height'];
		
				$related_option=get_option( 'wri_related_items___' . $instance['related_posttype'] );
				$yarpp_option = get_option('yarpp');
				
				$yarpp_title = '';
				if ( '1' == $wri_general_settings['use_yarpp_title'] ) {
					$yarpp_title = $yarpp_option['before_related'] . $yarpp_option['after_related'];					
				}
				$title=nvl($instance['title'], nvl( strip_tags($related_option['title'] ), strip_tags( $yarpp_title ) ) ); //if exists use widget custom title, else wri post related tilte, else yarpp title   
				$title = apply_filters( 'widget_title', $title );
				echo $args['before_widget'];
				if ( '1'!=$instance['hide_title'] )
					echo $args['before_title'] . $title . $args['after_title'];
				
				echo $content;
				
				echo $args['after_widget'];
				
			}
		}
	}


	function update($new_instance, $old_instance) {

		$instance = array();

		$instance['related_posttype'] = ( ! empty( $new_instance['related_posttype'] ) ) ? strip_tags( $new_instance['related_posttype'] ) : '';
		$instance['hide_title'] = ( ! empty( $new_instance['hide_title'] ) ) ? strip_tags( $new_instance['hide_title'] ) : 0;
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['display_limit'] = ( ! empty( $new_instance['display_limit'] ) ) ? strip_tags( $new_instance['display_limit'] ) : '';
		$instance['match_threshold'] = ( ! empty( $new_instance['match_threshold'] ) ) ? strip_tags( $new_instance['match_threshold'] ) : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : '';		
		$instance['display'] = ( ! empty( $new_instance['display'] ) ) ? strip_tags( $new_instance['display'] ) : 0;
		$instance['list_thumbnail'] = ( ! empty( $new_instance['list_thumbnail'] ) ) ? strip_tags( $new_instance['list_thumbnail'] ) : '';
		$instance['thumbnail_width'] = ( ! empty( $new_instance['thumbnail_width'] ) ) ? strip_tags( $new_instance['thumbnail_width'] ) : '';
		$instance['thumbnail_height'] = ( ! empty( $new_instance['thumbnail_height'] ) ) ? strip_tags( $new_instance['thumbnail_height'] ) : '';
		$instance['maximum_excerpt_characters'] = ( ! empty( $new_instance['maximum_excerpt_characters'] ) ) ? strip_tags( $new_instance['maximum_excerpt_characters'] ) : '';		
		$instance['hide_if_duplicate'] = ( ! empty( $new_instance['hide_if_duplicate'] ) ) ? strip_tags( $new_instance['hide_if_duplicate'] ) : '';
		$instance['custom_template'] = ( ! empty( $new_instance['custom_template'] ) ) ? strip_tags( $new_instance['custom_template'] ) : '';
		$instance['promote'] = ( ! empty( $new_instance['promote'] ) ) ? strip_tags( $new_instance['promote'] ) : 0;

		return $instance;				
		
	}


	function form($instance) {
	
		$instance = wp_parse_args( $instance, array(
			'related_posttype' => '',
			'hide_title' => 0, 
			'title' => '',
			'display_limit' => null, 
			'match_threshold' => null,
			'order' => null,
			'thumbnail_style' => '',
			'display' => 1,
			'list_thumbnail' => 'list',
			'reference_posttype' => '',
			'thumbnail_width' => '64',
			'thumbnail_height' => '64',
			'maximum_excerpt_characters' => 0,
			'custom_template' => null,
			'promote' => 0
		) );

		global $wri, $wri_is_premium;
	
		$post_types = $wri->wri_used_post_types('objects');
		
		?>

		<p>
			<label for="<?php echo $this->get_field_id('related_posttype'); ?>"><?php echo __('Related post type','wp-related-items').':'; ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('related_posttype'); ?>" name="<?php echo $this->get_field_name('related_posttype'); ?>" >
			<?php 
			foreach ($post_types as $post_type) {
		            printf ('<option value="' . $post_type->name . '"' . selected( $post_type->name, esc_attr( $instance['related_posttype']), false) . '>' . $post_type->labels->name . '</option>');
			}
			?>
            </select>
		</p>
		
		<p><input class="checkbox" id="<?php echo $this->get_field_id('hide_title'); ?>" name="<?php echo $this->get_field_name('hide_title'); ?>" type="checkbox" value="1" <?php echo checked( 1, isset( $instance['hide_title'] ) and $instance['hide_title'], false ); ?>" /><label for="<?php echo $this->get_field_id('hide_title') . '">' . ' ' . __('Hide title','wp-related-items') ?></label></p>
		
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title','wp-related-items').':'; ?><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('display_limit'); ?>"><?php echo __('Limit number of displayed related items','wp-related-items').':'; ?><input class="widefat" id="<?php echo $this->get_field_id('display_limit'); ?>" name="<?php echo $this->get_field_name('display_limit'); ?>" type="number" value="<?php echo esc_attr($instance['display_limit']); ?>" /></label></p>
		
		<p><label for="<?php echo $this->get_field_id('match_threshold'); ?>"><?php echo __('Match threshold','wp-related-items').':'; ?><input class="widefat" id="<?php echo $this->get_field_id('match_threshold'); ?>" name="<?php echo $this->get_field_name('match_threshold'); ?>" type="number" value="<?php echo esc_attr($instance['match_threshold']); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php echo __('Order','wp-related-items').':'; ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('order'); ?>" name="<?php echo $this->get_field_name('order'); ?>" >
	            <option value="" <?php echo selected( '', esc_attr( $instance['order']), false) ?> > </option>
	            <option value="score DESC" <?php echo selected( 'score DESC', esc_attr( $instance['order']), false) . '>' . __('score (high relevance to low)','wp-related-items') ?></option>
	            <option value="score ASC" <?php echo selected( 'score ASC', esc_attr( $instance['order']), false) . '>' . __('score (low relevance to high)','wp-related-items') ?></option>
	            <option value="post_date DESC" <?php echo selected( 'post_date DESC', esc_attr( $instance['order']), false) . '>' . __('date (new to old)','wp-related-items') ?></option>
	            <option value="post_date ASC" <?php echo selected( 'post_date ASC', esc_attr( $instance['order']), false) . '>' . __('date (old to new)','wp-related-items') ?></option>
	            <option value="post_title ASC" <?php echo selected( 'post_title ASC', esc_attr( $instance['order']), false) . '>' . __('title (alphabetical)','wp-related-items') ?></option>
	            <option value="post_title DESC" <?php echo selected( 'post_title DESC', esc_attr( $instance['order']), false) . '>' . __('title (reverse alphabetical)','wp-related-items') ?></option>
            </select>
            
		</p>	

		<p><input id="<?php echo $this->get_field_id('display'); ?>" name="<?php echo $this->get_field_name('display'); ?>" type="checkbox" value="1" <?php echo checked( 1, isset( $instance['display'] ) and $instance['display'], false ); ?>" /><label for="<?php echo $this->get_field_id('display'); ?>"><?php _e(' Display in Singles '); ?></label></p>

		<p><input id="<?php echo $this->get_field_id('display_in_archives'); ?>" name="<?php echo $this->get_field_name('display_in_archives'); ?>" type="checkbox" value="1" <?php echo checked( 1, isset( $instance['display_in_archives'] ) and $instance['display_in_archives'], false ) . ($wri_is_premium ? '' : 'disabled'); ?>" /><label for="<?php echo $this->get_field_id('display_in_archives'); ?>"><?php _e(' Display in Archives (pro)'); ?></label></p>
		
		<p>
			<label for="<?php echo $this->get_field_id('list_thumbnail'); ?>"><?php echo __('List or Thumbnail mode','wp-related-items').':'; ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('list_thumbnail'); ?>" name="<?php echo $this->get_field_name('list_thumbnail'); ?>" >
	            <option value="list" <?php echo selected( 'list', esc_attr( $instance['list_thumbnail']), false) . '>' . __('List','wp-related-items') ?></option>
	            <option value="thumbnail" <?php echo selected( 'thumbnail', esc_attr( $instance['list_thumbnail']), false) . '>' . __('Thumbnail','wp-related-items') ?></option>
	            <option value="yarpp_thumbnail" <?php echo selected( 'yarpp_thumbnail', esc_attr( $instance['list_thumbnail']), false) . '>' . __('According to YARPP display options','wp-related-items') ?></option>
            </select>
            
		</p>	

		
		<p>		
			<label for="<?php echo $this->get_field_id('thumbnail_width'); ?>"><?php echo __('Thumbnail size (width/height)','wp-related-items').':'; ?></label><br>
			
			<input id="<?php echo $this->get_field_id('thumbnail_width'); ?>" name="<?php echo $this->get_field_name('thumbnail_width'); ?>" type="number" value="<?php echo esc_attr($instance['thumbnail_width']); ?>" min="10" max="9999" />			
			<input id="<?php echo $this->get_field_id('thumbnail_height'); ?>" name="<?php echo $this->get_field_name('thumbnail_height'); ?>" type="number" value="<?php echo esc_attr($instance['thumbnail_height']); ?>" min="10" max="9999" />
		
		</p>

		<p><label for="<?php echo $this->get_field_id('maximum_excerpt_characters'); ?>"><?php echo __('Maximum number of characters for excerpt displayed','wp-related-items').':'; ?><input id="<?php echo $this->get_field_id('maximum_excerpt_characters'); ?>" name="<?php echo $this->get_field_name('maximum_excerpt_characters'); ?>" type="number" value="<?php echo esc_attr($instance['maximum_excerpt_characters']); ?>" min="0" max="9999" /></label></p>

		<p><input id="<?php echo $this->get_field_id('hide_if_duplicate'); ?>" name="<?php echo $this->get_field_name('hide_if_duplicate'); ?>" type="checkbox" value="1" <?php echo checked( 1, isset( $instance['hide_if_duplicate'] ) and $instance['hide_if_duplicate'], false ); ?>" /><label for="<?php echo $this->get_field_id('hide_if_duplicate'); ?>"><?php echo ' ' . __('Hide widget if related items for this reference post type displayed directly on the post page, preventing duplicated display','wp-related-items'); ?></label></p>
				
		<p>
			<label for="<?php echo $this->get_field_id('reference_posttype'); ?>"><?php echo __('Display related items only on following post type pages (pro)','wp-related-items').':'; ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('reference_posttype'); ?>" name="<?php echo $this->get_field_name('reference_posttype') . ($wri_is_premium ? '' : 'disabled'); ?>" >
			<?php
			printf ('<option value="' . '' . '"' . selected( '', esc_attr( $instance['reference_posttype']), false) . '>' . '' . '</option>'); 
			foreach ($post_types as $post_type) {
		            printf ('<option value="' . $post_type->name . '"' . selected( $post_type->name, esc_attr( $instance['reference_posttype']), false) . '>' . $post_type->labels->name . '</option>');
			}
			?>
            </select>
		</p>

		<p>
			
			<?php
			global $yarpp;
			$yarpp_templates_data_array = $yarpp->get_templates();
			?>
			
			<label for="<?php echo $this->get_field_id('custom_template'); ?>"><?php _e('Custom template: (according to Yarpp template mechanism)','wp-related-items'); ?></label>
	        <select class="widefat" id="<?php echo $this->get_field_id('custom_template'); ?>" name="<?php echo $this->get_field_name('custom_template'); ?>" >
			<?php
			printf ('<option value="' . '' . '"' . selected( '', esc_attr( $instance['custom_template']), false) . '>' . '' . '</option>');
			if ( is_array( $yarpp_templates_data_array ) ) {
	            foreach ( $yarpp_templates_data_array as $yarpp_templates_data ) {
		            printf ('<option value="' . $yarpp_templates_data['basename'] . '"' . selected( $yarpp_templates_data['basename'], esc_attr( $instance['custom_template']), false) . '>' . $yarpp_templates_data['name'] . '</option>');
				}
			}
			?>
            </select>
		</p>

		<?php				
		
	}
}

function wri_widget_init() {
	register_widget( 'WRI_Widget' );
}
add_action( 'widgets_init', 'wri_widget_init' );