<?php


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Patron_Metabox {

	function __construct() {

		add_action( 'add_meta_boxes', array($this, 'patreon_plugin_meta_boxes') );
		add_action( 'save_post', array($this, 'patreon_plugin_save_post_class_meta'), 10, 2 );
		add_action( 'add_meta_boxes', array($this, 'patreon_banner_metabox') );
		add_action( 'save_post', array($this, 'patreon_post_banner_save') );

	}

	function patreon_plugin_meta_boxes($post_type) {

		$post_types = get_post_types(array('public'=>true),'names');

	    $exclude = array(
	    	'attachment'
	    	);

	    if (in_array($post_type,$exclude) == false && in_array($post_type, $post_types)) {

			add_meta_box(
				'patreon-level',      // Unique ID
				esc_html__( 'Patreon Level', 'Patreon Contribution Requirement' ),
				array($this, 'patreon_plugin_meta_box'),
				$post_type,
				'side',
				'default'
			);
		}

	}

	function patreon_plugin_meta_box( $object, $box ) { ?>

		<?php wp_nonce_field( basename( __FILE__ ), 'patreon_metabox_nonce' ); ?>
		<p>
			<label for="patreon-level"><?php _e( "Add a minimum Patreon contribution required to access this content.", '1' ); ?></label>
			<br><br>
			<strong>&#36; </strong><input type="text" id="patreon-level" name="patreon-level" value="<?php echo get_post_meta( $object->ID, 'patreon-level', true ); ?>">
		</p>

		<?php
	}

	function patreon_plugin_save_post_class_meta( $post_id, $post ) {

		if ( !isset( $_POST['patreon_metabox_nonce'] ) || !wp_verify_nonce( $_POST['patreon_metabox_nonce'], basename( __FILE__ ) ) )
			return $post_id;

		$post_type = get_post_type_object( $post->post_type );

		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		if(isset( $_POST['patreon-level']) && is_numeric($_POST['patreon-level'])) {
			$new_patreon_level = $_POST['patreon-level'];
		} else if (isset( $_POST['patreon-level']) && ($_POST['patreon-level'] == 0 || $_POST['patreon-level'] == '0') ) {
			$new_patreon_level = 0;
		} else {

			$new_patreon_level = 0;
			
		}

		$patreon_level = get_post_meta( $post_id, 'patreon-level', true );

		if ( $new_patreon_level && '' == $patreon_level ) {

			add_post_meta( $post_id, 'patreon-level', $new_patreon_level, true );

		} else if ( ($new_patreon_level || $new_patreon_level == 0 || $new_patreon_level == '0') && $new_patreon_level != $patreon_level ) {

			update_post_meta( $post_id, 'patreon-level', $new_patreon_level );

		} else if ( '' == $new_patreon_level && $patreon_level ) {

			delete_post_meta( $post_id, 'patreon-level', $patreon_level );

		}

		$patreon_level = get_post_meta( $post_id, 'patreon-level', true );

	}


	function patreon_banner_metabox($post_type) {

	    $post_types = get_post_types(array('public'=>true),'names');

	    $exclude = array(
	    	'attachment'
	    	);

	    if (in_array($post_type,$exclude) == false && in_array($post_type, $post_types)) {

	        add_meta_box(
	            'patreon-post-banner-meta-box',
	            'Custom Patreon Banner',
	            array($this, 'patreon_post_banner_meta_box'),
	            $post_type,
	            'normal',
	            'high'
	        );

	    }

	}

	function patreon_post_banner_meta_box($post) {

	    $patreon_post_banner = get_post_meta($post->ID, 'patreon_post_banner', true);

	    wp_editor( $patreon_post_banner, 'patreon_post_banner' );

	    wp_nonce_field(
	        plugin_basename(__FILE__),
	        'patreon_post_banner_none'
	    );

	}

	function patreon_post_banner_save($post_id) {

	    if ( ( (isset($_POST['post_type']) && $_POST['post_type'] === 'page') && current_user_can('edit_page', $post_id) ) || current_user_can('edit_post', $post_id) ) {

	        if ((( ! defined('DOING_AUTOSAVE')) || ( ! DOING_AUTOSAVE)) && (( ! defined('DOING_AJAX')) || ( ! DOING_AJAX))) {

	            if (isset($_POST['patreon_post_banner_none']) && wp_verify_nonce($_POST['patreon_post_banner_none'], plugin_basename(__FILE__))) {

	                $patreon_post_banner = wp_kses_post($_POST['patreon_post_banner']);

	                if ($patreon_post_banner !== '') {
	                    add_post_meta($post_id, 'patreon_post_banner', $patreon_post_banner, true) OR update_post_meta($post_id, 'patreon_post_banner', $patreon_post_banner);
	                } else {
	                    delete_post_meta($post_id, 'patreon_post_banner');

	                }

	            }

	        }

	    }

	}


}


?>
