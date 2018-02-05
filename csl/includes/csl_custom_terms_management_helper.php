<?php
	
/**
 * CSL. Helper included file. Custom Taxonomies Management
 *
 * The csl_custom_terms_management_helper file uses a class to insert two new options to top-left 
 * bulk actions dropdown selector inside taxonomies pages management: 
 * MERGE = you can take one or more terms and include the associated posts in other existent term.
 * CHANGE TAXONOMY = you can take one or more terms and change from one taxonomy to any other.
 * This class is based in "Term Management Tools" v1.1.3, a scribu (http://scribu.net/) plugin.
 *
 * PHP version 5.x+
 *
 * LICENSE: CreativeCommons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0),
 * available at https://creativecommons.org/licenses/by-sa/4.0/
 *
 * @category   Main theme files
 * @package    CSL ExpoFinder
 * @author     Antonio Cruces Rodríguez <antonio.cruces@uma.es>
 * @author     iArtHis_LAB Research Group http://iarthis.hdplus.es
 * @copyright  Copyleft 2015 Antonio Cruces Rodríguez & iArtHis_LAB Research Group
 * @license    https://creativecommons.org/licenses/by-sa/4.0/ CC BY-SA 4.0
 * @version    1.4.0 RC2
 * @link       http://admin.expofinder.es
 * @see        changelog.txt
 * @since      File available since v1.3.0 RC2
 */

/**
 * CLS_Taxonomies_Management class.
 */
 
class CLS_Taxonomies_Management {

	/**
	 * init function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function init() {
		add_action( 'load-edit-tags.php', array( __CLASS__, 'handler' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );

		load_plugin_textdomain( 'term-management-tools', '', basename( dirname( __FILE__ ) ) . '/lang' );
	}

	/**
	 * get_actions function.
	 * 
	 * @access private
	 * @static
	 * @param mixed $taxonomy
	 * @return void
	 */
	private static function get_actions( $taxonomy ) {
		$actions = array(
			'merge'        => __( 'Merge', CSL_TEXT_DOMAIN_PREFIX ),
			'change_tax'   => __( 'Change taxonomy', CSL_TEXT_DOMAIN_PREFIX ),
		);

		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			$actions = array_merge( array(
				'set_parent' => __( 'Set parent', CSL_TEXT_DOMAIN_PREFIX ),
			), $actions );
		}

		return $actions;
	}

	/**
	 * handler function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function handler() {
		$defaults = array(
			'taxonomy' => 'post_tag',
			'post_type' => 'post',
			'delete_tags' => false,
			'action' => false,
			'action2' => false
		);

		$data = shortcode_atts( $defaults, $_REQUEST );

		$tax = get_taxonomy( $data['taxonomy'] );
		if ( !$tax )
			return;

		if ( !current_user_can( $tax->cap->manage_terms ) )
			return;

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'script' ) );
		add_action( 'admin_footer', array( __CLASS__, 'inputs' ) );

		$action = false;
		foreach ( array( 'action', 'action2' ) as $key ) {
			if ( $data[ $key ] && '-1' != $data[ $key ] ) {
				$action = $data[ $key ];
			}
		}

		if ( !$action )
			return;

		self::delegate_handling( $action, $data['taxonomy'], $data['delete_tags'] );
	}

	/**
	 * delegate_handling function.
	 * 
	 * @access protected
	 * @static
	 * @param mixed $action
	 * @param mixed $taxonomy
	 * @param mixed $term_ids
	 * @return void
	 */
	protected static function delegate_handling( $action, $taxonomy, $term_ids ) {
		if ( empty( $term_ids ) )
			return;

		foreach ( array_keys( self::get_actions( $taxonomy ) ) as $key ) {
			if ( 'bulk_' . $key == $action ) {
				check_admin_referer( 'bulk-tags' );
				$r = call_user_func( array( __CLASS__, 'handle_' . $key ), $term_ids, $taxonomy );
				break;
			}
		}

		if ( !isset( $r ) )
			return;

		$post_type = $_REQUEST['post_type'];
		if ( $referer = wp_get_referer() && false !== strpos( $referer, 'edit-tags.php' ) ) {
			$location = $referer;
		} else {
			$location = add_query_arg( array( 'taxonomy' => $taxonomy, 'post_type' => $post_type), 'edit-tags.php' );
		}

		wp_redirect( add_query_arg( 'message', $r ? 'tmt-updated' : 'tmt-error', $location ) );
		die;
	}

	/**
	 * notice function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function notice() {
		if ( !isset( $_GET['message'] ) )
			return;

		switch ( $_GET['message'] ) {
		case  'tmt-updated':
			echo '<div id="message" class="updated"><p>' . __( 'Terms updated.', CSL_TEXT_DOMAIN_PREFIX ) . '</p></div>';
			break;

		case 'tmt-error':
			echo '<div id="message" class="error"><p>' . __( 'Terms not updated.', CSL_TEXT_DOMAIN_PREFIX ) . '</p></div>';
			break;
		}
	}

	/**
	 * handle_merge function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $term_ids
	 * @param mixed $taxonomy
	 * @param mixed $post_type
	 * @return void
	 */
	static function handle_merge( $term_ids, $taxonomy, $post_type ) {
		$term_name = $_REQUEST['bulk_to_tag'];

		if ( !$term = term_exists( $term_name, $taxonomy ) )
			$term = wp_insert_term( $term_name, $taxonomy );

		if ( is_wp_error( $term ) )
			return false;

		$to_term = $term['term_id'];

		$to_term_obj = get_term( $to_term, $taxonomy );

		foreach ( $term_ids as $term_id ) {
			if ( $term_id == $to_term )
				continue;

			$old_term = get_term( $term_id, $taxonomy );

			$ret = wp_delete_term( $term_id, $taxonomy, array( 'default' => $to_term, 'force_default' => true ) );
			if ( is_wp_error( $ret ) ) {
				continue;
			}

			do_action( 'CLS_Taxonomies_Management_term_merged', $to_term_obj, $old_term );
		}

		return true;
	}

	/**
	 * handle_set_parent function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $term_ids
	 * @param mixed $taxonomy
	 * @return void
	 */
	static function handle_set_parent( $term_ids, $taxonomy ) {
		$parent_id = $_REQUEST['parent'];

		foreach ( $term_ids as $term_id ) {
			if ( $term_id == $parent_id )
				continue;

			$ret = wp_update_term( $term_id, $taxonomy, array( 'parent' => $parent_id ) );

			if ( is_wp_error( $ret ) )
				return false;
		}

		return true;
	}

	/**
	 * handle_change_tax function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $term_ids
	 * @param mixed $taxonomy
	 * @return void
	 */
	static function handle_change_tax( $term_ids, $taxonomy ) {
		global $wpdb;

		$new_tax = $_POST['new_tax'];

		if ( !taxonomy_exists( $new_tax ) )
			return false;

		if ( $new_tax == $taxonomy )
			return false;

		$tt_ids = array();
		foreach ( $term_ids as $term_id ) {
			$term = get_term( $term_id, $taxonomy );

			if ( $term->parent && !in_array( $term->parent,$term_ids ) ) {
				$wpdb->update( $wpdb->term_taxonomy,
					array( 'parent' => 0 ),
					array( 'term_taxonomy_id' => $term->term_taxonomy_id )
				);
			}

			$tt_ids[] = $term->term_taxonomy_id;

			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				$child_terms = get_terms( $taxonomy, array(
					'child_of' => $term_id,
					'hide_empty' => false
				) );
				$tt_ids = array_merge( $tt_ids, wp_list_pluck( $child_terms, 'term_taxonomy_id' ) );
			}
		}
		$tt_ids = implode( ',', array_map( 'absint', $tt_ids ) );

		$wpdb->query( $wpdb->prepare( "
			UPDATE $wpdb->term_taxonomy SET taxonomy = %s WHERE term_taxonomy_id IN ($tt_ids)
		", $new_tax ) );

		if ( is_taxonomy_hierarchical( $taxonomy ) && !is_taxonomy_hierarchical( $new_tax ) ) {
			$wpdb->query( "UPDATE $wpdb->term_taxonomy SET parent = 0 WHERE term_taxonomy_id IN ($tt_ids)" );
		}

		delete_option( "{$taxonomy}_children" );
		delete_option( "{$new_tax}_children" );

		return true;
	}

	/**
	 * script function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function script() {
		global $taxonomy;
		wp_enqueue_script( 'term-management-tools', get_template_directory_uri() . '/assets/js/csl-be-tm.js', array( 'jquery' ), '1.1' );
		wp_localize_script( 'term-management-tools', 'tmtL10n', self::get_actions( $taxonomy ) );
	}

	/**
	 * inputs function.
	 * 
	 * 
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function inputs() {
		global $taxonomy;

		foreach ( array_keys( self::get_actions( $taxonomy ) ) as $key ) {
			echo "<div id='tmt-input-$key' style='display:none'>\n";
			call_user_func( array( __CLASS__, 'input_' . $key ), $taxonomy );
			echo "</div>\n";
		}
	}

	/**
	 * input_merge function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $taxonomy
	 * @return void
	 */
	static function input_merge( $taxonomy ) {
		printf( __( 'into: %s', CSL_TEXT_DOMAIN_PREFIX ), '<input name="bulk_to_tag" type="text" size="20"></input>' );
	}

	/**
	 * input_change_tax function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $taxonomy
	 * @return void
	 */
	static function input_change_tax( $taxonomy ) {
		$tax_list = get_taxonomies( array( 'show_ui' => true ), 'objects' );
?>
		<select class="postform" name="new_tax">
<?php
		foreach ( $tax_list as $new_tax => $tax_obj ) {
			if ( $new_tax == $taxonomy )
				continue;

			echo "<option value='$new_tax'>$tax_obj->label</option>\n";
		}
?>
		</select>
<?php
	}

	/**
	 * input_set_parent function.
	 *
	 * Allows to set a parent taxonomy for any selected terms bulk basis
	 * 
	 * @access public
	 * @static
	 * @param mixed $taxonomy
	 * @return void
	 */
	static function input_set_parent( $taxonomy ) {
		wp_dropdown_categories( array(
			'hide_empty' => 0,
			'hide_if_empty' => false,
			'name' => 'parent',
			'orderby' => 'name',
			'taxonomy' => $taxonomy,
			'hierarchical' => true,
			'show_option_none' => __( 'None', CSL_TEXT_DOMAIN_PREFIX )
		) );
	}
}

/**
 * Initialize class
 */

CLS_Taxonomies_Management::init();

?>
