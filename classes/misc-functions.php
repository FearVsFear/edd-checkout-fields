<?php
/**
 * Misc Functions
 *
 * This file contains lots of little misc functions
 * used all over CFM.
 *
 * @package CFM
 * @subpackage Misc
 * @since 2.0.0
 *
 * @todo Split out classes into their own files.
 * @todo General function cleanup.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}


/**
 * Associate attachment to a post.
 *
 * CFM uses this to attach uploaded media items
 * to the download post type.
 *
 * @since 2.0
 * @access public
 *
 * @global $wpdb WordPress database object.
 *
 * @param int $attachment_id Upload media item's id.
 * @param int $post_id Download's post id.
 */
function cfm_associate_attachment( $attachment_id, $post_id ) {
	global $wpdb;

	$wpdb->update(
		$wpdb->posts,
		array(
			'post_parent' => $post_id,
		),
		array(
			'ID' => $attachment_id,
		),
		array(
			'%d'
		),
		array(
			'%d'
		)
	);
}

/**
 * Get attachment ID from a URL.
 *
 * CFM stores the attachment ids for file fields. This
 * function gets the attachment ID from a URL.
 *
 * @since 2.1.8
 * @access public
 *
 * @link http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/ Original Implementation
 * @todo  This could be improved alot.
 * @global type $wpdb
 * 
 * @param string $attachment_url URL of attachment.
 * @param int $author_id User ID of uploader.
 * @return int ID of the attachment.
 */
function cfm_get_attachment_id_from_url( $attachment_url = '', $author_id = 0 ) {
	global $wpdb;

	$attachment_id = false;

	// If there is no url, return.
	if ( '' == $attachment_url )
		return;

	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();

	// Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

		// Don't remove this for now. See https://github.com/chriscct7/edd-cfm/issues/662
		// todo: remove in 2.4 unless 662 is reopened
		// If this is the URL of an auto-generated thumbnail, get the URL of the original image
		// $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

		// Remove the upload path base directory from the attachment URL
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		// Remove the -avatar suffix
		$attachment_url = str_replace( '-avatar.', '.', $attachment_url );

		// If an author ID is specified
		$author = '';
		if ( ! empty( $author_id ) ) {
			$author = "wposts.post_author = $author_id AND";
		}

		// Finally, run a custom database query to get the attachment ID from the modified attachment URL
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE $author wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
	}

	return $attachment_id;
}

/**
 * Retrieve a list of the allowed HTML tags
 *
 * This array is fed into wp_kses to allow
 * specific HTML tags on textarea and other
 * fields that allow HTML.
 *
 * @since   2.2.2
 * @access  public
 *
 * @return array Allowed HTML tags.
*/
function cfm_allowed_html_tags() {
	$tags = array(
		'p' => array(
			'class' => array(),
			'style' => array()
		),
		'h1' => array(
			'class' => array(),
			'style' => array()
		),
		'h2' => array(
			'class' => array(),
			'style' => array()
		),
		'h3' => array(
			'class' => array(),
			'style' => array()
		),
		'h4' => array(
			'class' => array(),
			'style' => array()
		),
		'h5' => array(
			'class' => array(),
			'style' => array()
		),
		'h6' => array(
			'class' => array(),
			'style' => array()
		),
		'span' => array(
			'class' => array(),
			'style' => array()
		),
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array(),
			'title' => array(),
			'style' => array()
		),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'br' => array(),
		'img' => array(
			'src' => array(),
			'title' => array(),
			'alt' => array(),
			'class' => array(),
			'size' => array(),
			'width' => array(),
			'height' => array(),
			'style' => array()
		),
		'div' => array(
			'class' => array(),
			'style' => array()
		),
		'ul' => array(
			'class' => array(),
			'style' => array()
		),
		'ol' => array(
			'class' => array(),
			'style' => array()
		),
		'li' => array(
			'class' => array(),
			'style' => array()
		),
		'font' => array()
	);
	/**
	 * Allowed HTML Tags
	 *
	 * Filter the allowed HTML tags in CFM fields.
	 *
	 * @since 2.2.2
	 * 
	 * @param array $tags Array of allowed HTML elements.
	 */
	return apply_filters( 'cfm_allowed_html_tags', $tags );
}

function cfm_allowed_extensions() {
    $extensions = array(
        'images' => array('ext' => 'jpg,jpeg,gif,png,bmp', 'label' => __( 'Images', 'edd_cfm' )),
        'audio' => array('ext' => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi', 'label' => __( 'Audio', 'edd_cfm' )),
        'video' => array('ext' => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe', 'label' => __( 'Videos', 'edd_cfm' )),
        'pdf' => array('ext' => 'pdf', 'label' => __( 'PDF', 'edd_cfm' )),
        'office' => array('ext' => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt', 'label' => __( 'Office Documents', 'edd_cfm' )),
        'zip' => array('ext' => 'zip,gz,gzip,rar,7z', 'label' => __( 'Zip Archives' )),
        'exe' => array('ext' => 'exe', 'label' => __( 'Executable Files', 'edd_cfm' )),
        'csv' => array('ext' => 'csv', 'label' => __( 'CSV', 'edd_cfm' ))
    );

    return apply_filters( 'cfm_allowed_extensions', $extensions );
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook cfm_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function. Based on the one in EDD core.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @since 2.3.0
 * @access public
 *
 * @uses do_action() Calls 'cfm_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'cfm_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *   
 * @param string  $function    The function that was called
 * @param string  $version     The version of WordPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 * @return void
 */
function _cfm_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {

	/**
	 * Deprecated Function Action.
	 *
	 * Allow plugin run an action on the use of a 
	 * deprecated function. This could be used to
	 * feed into an error logging program or file.
	 *
	 * @since 2.3.0
	 * 
	 * @param string  $function    The function that was called.
	 * @param string  $version     The version of WordPress that deprecated the function.
	 * @param string  $replacement Optional. The function that should have been called.
	 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function.
	 */	
	do_action( 'cfm_deprecated_function_run', $function, $version, $replacement, $backtrace );

	$show_errors = current_user_can( 'manage_options' );

	/**
	 * Output Error Trigger.
	 *
	 * Allow plugin to filter the output error trigger.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $show_errors Whether to show errors.
	 */
	$show_errors = apply_filters( 'cfm_deprecated_function_trigger_error', $show_errors );
	if ( WP_DEBUG && $show_errors ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads Checkout Fields Manager version %2$s! Use %3$s instead.', 'edd_cfm' ), $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Easy Digital Downloads Checkout Fields Manager version %2$s.', 'edd_cfm' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Marks something as deprecated.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * @since 2.3.0
 * @access public
 *
 * @uses apply_filters() Calls 'cfm_deprecated_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $message     Deprecation message shown.
 * @return void
 */
function _cfm_deprecated( $message ) {

	/**
	 * Deprecated Message Filter.
	 *
	 * Allow plugin to filter the deprecated message.
	 *
	 * @since 2.3.0
	 * 
	 * @param string $message Error message.
	 */	
	do_action( 'cfm_deprecated_run', $message );

	$show_errors = current_user_can( 'manage_options' );

	/**
	 * Deprecated Error Trigger.
	 *
	 * Allow plugin to filter the output error trigger.
	 *
	 * @since 2.3.0
	 * 
	 * @param bool $show_errors Whether to show errors.
	 */
	$show_errors = apply_filters( 'cfm_deprecated_trigger_error', $show_errors );
	if ( WP_DEBUG && $show_errors ) {
		trigger_error( sprintf( __( '%s', 'edd_cfm' ), $message) );
	}
}

/**
 * Key Exists In Array.
 * 
 * This PHP function checks an associative array for a key of a particular name. 
 * This may seem trivial but CFM does this alot.
 *
 * Example:
 * $a = array( "one" => 1, "two" => 2 );
 * if ( cfm_is_key( "one", $a ) ) { … } // == true
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $needle    The key we're looking for.
 * @param array   $haystack  The array we're searching.
 *
 * @return bool True if key in array else false.
 */
function cfm_is_key( $needle = '', $haystack = array() ) {
	if ( strlen( $needle ) > 0 && count( $haystack ) > 0 ) {
		if ( in_array( $needle, array_keys( $haystack ) ) ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Key Value Pair Exists In Array.
 * 
 * This PHP function checks an associative array for a specific key with a particular value
 * This may seem trivial but CFM does this alot.
 *
 * Example:
 * $a = array( "one" => 1, "two" => 2 );
 * if ( cfm_is_key_value( "one", 1, $a ) ) { … } // == true
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $needle    The key we're looking in.
 * @param string  $needle    The value we're looking for.
 * @param array   $haystack  The array we're searching.
 *
 * @return bool True if key in array else false.
 */
function cfm_has_key_value( $needle = '', $value = '', $haystack = array() ) {
	foreach ( $haystack as $item ){
		if ( isset( $item[ $needle ] ) && $item[ $needle ] == $value ){
			return true;
		}
	}
	return false;
}

/**
 * Convert Dashes to Underscore.
 * 
 * Converts all dashes in a string to underscores.
 *
 * @since 2.3.0
 * @access public
 *
 * @param string  $string    String to convert.
 * @return string Converted string.
 */
function cfm_dash_to_lower( $string ){
	return str_replace( '-', '_', $string );
}

/**
 * Is Frontend.
 * 
 * Determines if user is on frontend. Defined
 * by not being in the admin, and not being in an
 * api request.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are on frontend.
 */
function cfm_is_frontend(){
	if ( !cfm_is_api_request() && !cfm_is_admin() ){
		return true;
	} else {
		return false;
	}
}

/**
 * Is Admin.
 * 
 * Determines if user is in admin.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in admin.
 */
function cfm_is_admin(){
	$output = false;
	if ( is_admin() && !cfm_is_api_request() && !cfm_is_frontend_ajax_request() ){
		$output = true;
	}
	return $output;
}

/**
 * Is API Request.
 * 
 * For now unused. Reserved for future
 * use.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in api request.
 */
function cfm_is_api_request(){
	return false;
}

/**
 * Is Ajax Request.
 * 
 * Determines if the user is in an
 * ajax request.
 *
 * @since 2.3.0
 * @access public
 *
 * @return bool Whether we are in ajax request.
 */
function cfm_is_ajax_request(){
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ){
		return true;
	} else {
		return false;
	}
}

/**
 * Is Frontend Ajax Request.
 * 
 * Determines if the user is in an
 * frontend ajax request.
 *
 * @since 2.3.0
 * @access public
 *
 * @todo  There has to be a better way.
 * @todo  Make a custom ajax endpoint.
 * 
 * @return bool Whether we are in frontend ajax request.
 */
function cfm_is_frontend_ajax_request(){
	$output = false;
	if ( cfm_is_ajax_request() ){
		// This is a replication of (and replaces a call to) wp_get_referer() function, see https://core.trac.wordpress.org/ticket/25294
		// First we see if there's the server referrer and use that if possible, to see if its in the admin
		// If its not there we then try to use the referrer field
		// This is literally insanity but there is no better way for now. We'll use a custom AJAX endpoint to get rid of this nonsense in 2.4
		// unless WordPress and/or EDD can finish their proposed inprovements on this issue, and if so we'll use theirs.
		$ref = '';
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ){
			$ref = wp_unslash( $_SERVER['HTTP_REFERER'] );
			if ( strpos( $ref, admin_url() ) === false ){
				$output = true; // not found
			}
		} else if ( ! empty( $_REQUEST['_wp_http_referer'] ) ){
			$ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
			if ( strpos( $ref, 'wp-admin' ) === false){
				$output = true; // not found
			}
		}
	}
	return $output;
}


// better CFM upload files protection. Circa 2.3

/**
 * Change Downloads Upload Directory.
 *
 * Hooks the edd_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for EDD to an edd directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/edd/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since 2.3
 * @access public 
 *
 * @param array $file Unused but contains file being currently uploaded.
 * @return array File that was uploaded.
 */
function cfm_change_downloads_upload_dir( $file ) {
	$override_default_dir = apply_filters('override_default_cfm_dir', false );
	if ( EDD()->session->get( 'CFM_FILE_UPLOAD' ) ) {
		if ( function_exists( 'edd_set_upload_dir' ) && !$override_default_dir ) {
			add_filter( 'upload_dir', 'edd_set_upload_dir' );
		} else if ( $override_default_dir ) {
			add_filter( 'upload_dir', 'cfm_set_custom_upload_dir' );
		} else {
			// wierd. Should never get here
		}
	}
	return $file;
}
add_action( 'wp_handle_upload_prefilter', 'cfm_change_downloads_upload_dir' );

/**
 * Turn on File Filter.
 * 
 * When this is active, intercepts all files and
 * puts them in the CFM file directory.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function cfm_turn_on_file_filter(){
	if ( !EDD()->session->get( 'CFM_FILE_UPLOAD' ) ) {
		EDD()->session->set( 'CFM_FILE_UPLOAD', true );
	}
}
add_action( 'wp_ajax_cfm_turn_on_file_filter', 'cfm_turn_on_file_filter' );
add_action( 'wp_ajax_nopriv_cfm_turn_on_file_filter', 'cfm_turn_on_file_filter' );

/**
 * Turn off File Filter.
 * 
 * Used after an CFM file finishes uploading.
 *
 * @since 2.3.0
 * @access public
 * 
 * @return void
 */
function cfm_turn_off_file_filter(){
	if ( EDD()->session->get( 'CFM_FILE_UPLOAD' )  ) {
		EDD()->session->set( 'CFM_FILE_UPLOAD', false );
	}
}
add_action( 'wp_ajax_cfm_turn_off_file_filter', 'cfm_turn_off_file_filter' );
add_action( 'wp_ajax_nopriv_cfm_turn_off_file_filter', 'cfm_turn_off_file_filter' );

function cfm_customers_view( $customer ){
	EDD_CFM()->admin_profile->page( $customer );
}

function cfm_customers_view_save( $args ){
	EDD_CFM()->admin_profile->save( $args );
}
add_action( 'edd_admin_customer_profile', 'cfm_customers_view_save', 10, 1  );