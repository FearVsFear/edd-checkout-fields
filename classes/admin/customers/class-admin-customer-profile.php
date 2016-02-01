<?php
/**
 * CFM Profile
 *
 * This file deals with the rendering and saving of CFM forms,
 * particularly from shortcodes.
 *
 * @package CFM
 * @subpackage Frontend
 * @since 2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { 
	exit;
}

/**
 * CFM Forms.
 *
 * Register the form shortcodes and create render/save 
 * ajax functions for them.
 *
 * @since 2.0.0
 * @access public
 */
class CFM_Admin_Customer_Profile {
	/**
	 * CFM Form Actions and Shortcodes.
	 *
	 * Registers ajax endpoints to save CFM forms with
	 * on the frontend as well as registers shortcodes for
	 * the default CFM forms.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @return void
	 */	
	function __construct() {
		add_filter( 'edd_customer_tabs', array( $this, 'tab' ), 10, 1 );
		add_filter( 'edd_customer_views', array( $this, 'view' ), 10, 1 );
	}
	
	public function tab( $tabs ){
		$tabs['usermeta'] = array( 'dashicon' => 'dashicons-analytics', 'title' => __( 'Custom User Meta', 'edd_cfm' ) );
		return $tabs;
	}
	
	public function view( $views ){
		$views['usermeta']  = 'cfm_customers_view';
		return $views;
	}
	
	/**
	 * Render Profile Form.
	 *
	 * Renders profile form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int  $post_id User id to edit.
	 * @param bool $readonly Whether the form is readonly.
	 * @param array $args Additional arguments to send 
	 *                    to form rendering functions.
	 * @return string HTML of profile form.
	 */
	public function page( $customer ){
		?>
		<?php do_action( 'edd_customer_tools_top', $customer ); ?>
		<div class="info-wrapper customer-section">

			<div class="customer-notes-header">
				<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
			</div>
			<h3><?php _e( 'Checkout Fields', 'edd_cfm' ); ?></h3>
			<div class="edd-item-info customer-info">
				<form method="post" id="edit-admin-customer-profile-form" class="cfm-form">
					<span>
						<?php 
						$form_id = get_option( 'cfm-checkout-form', false );

						// load the scripts so others don't have to
						EDD_CFM()->setup->enqueue_form_assets();

						// Make the CFM Form
						$form = new CFM_Checkout_Form( $form_id, 'id', -2, $customer->user_id );

						echo $form->render_form_admin( get_current_user_id(), true );
						?>
						<input type="hidden" name="edd_action" value="admin_customer_profile" />
						<input type="hidden" name="user_id" value="<?php echo $customer->user_id ;?>" />
						<br />
						<input type="submit" id="edit-admin-customer-profile-submit" value="<?php _e( 'Submit', 'edd_cfm' ); ?>" class="button-secondary"/>
						<span class="spinner"></span>
					</span>
				</form>
			</div>
		</div>
		<?php
	}


	/**
	 * Submit Admin Customer Profile Form.
	 *
	 * Submit admin customer profile CFM form.
	 *
	 * @since 2.0.0
	 * @access public
	 * 
	 * @param int  $id User id to edit.
	 * @param array $values Values to save.
	 * @param array $args Additional arguments to send 
	 *                    to form rendering functions.
	 * @return void
	 */
	function save( $values = array() ) {
		$form_id   = !empty( $values ) && isset( $values['form_id'] )   ? absint( $values['form_id'] )   : ( isset( $_REQUEST['form_id'] )   ? absint( $_REQUEST['form_id'] )   : get_option( 'cfm-checkout-form', false ) );
		$values    = !empty( $values ) ? $values : $_POST;
		$user_id = -2;
		if ( !empty( $values['user_id'] ) ){
			$user_id = $values['user_id'];
		}
		// Make the CFM Form
		$form      = new CFM_Checkout_Form( $form_id, 'id', -2, $user_id );
		// Save the CFM Form
		$form->save_form_admin( $values, get_current_user_id(), true );
	}
}
