<?php
/*
Plugin Name: EDD CF7 Register Guest Customers
Plugin URI: http://isabelcastillo.com/docs/category/edd-cf7-register-guest-customers
Description: Register EDD guest customers with Contact Form 7 custom registration, disable registration for everyone else.
Version: 1.0
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
Text Domain: edd-cf7rgc
Domain Path: languages

Copyright 2014 Isabel Castillo

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if(!class_exists('EDD_CF7_Register_Guest_Customers')) {

class EDD_CF7_Register_Guest_Customers{

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
    	add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );
		add_action( 'wpcf7_before_send_mail', array( $this, 'send_cf7_data_to_register' ) );
    }

	public function load_textdomain() {
		load_plugin_textdomain( 'edd-cf7rgc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add settings to the "Downloads > Settings > Extensions" section
	 * @since 1.0
	 */
	public function add_settings( $settings ) {
		$eddcf7_settings = array(
			array(
				'id' => 'edd_cf7rgc_settings_header',
				'name' => '<h3 class="title">'. __( 'CF7 Register Guest Customers', 'edd-cf7rgc' ) . '</h3>',
				'type' => 'header'
			),
			array(
				'id' => 'edd_cf7rgc_api_key',
				'name' => __( 'Your EDD public API key', 'edd-cf7rgc' ),
				'desc' => __( 'Go to Users -> Your Profile and check the box at the bottom that says Generate API Keys.', 'edd-cf7rgc' ),
				'type' => 'text'
			),
			array(
				'id' => 'edd_cf7rgc_api_token',
				'name' => __( 'Your EDD API token', 'edd-cf7rgc' ),
				'desc' => __( 'You get this token when you generate the keys for the option above.', 'edd-cf7rgc' ),
				'type' => 'text',
			),
			array(
				'id' => 'edd_cf7rgc_rejection_subject',
				'name' => __( 'Rejection Email Subject', 'edd-cf7rgc' ), 
				'desc' => __( 'Enter the subject line for the rejection email', 'edd-cf7rgc' ),
				'type' => 'text',
				'std' => 'Your registration is not complete'
			),

			array(
				'id' => 'edd_cf7rgc_rejection_message',
				'name' => __( 'Rejection Email Message', 'edd-cf7rgc' ), 
				'desc' => __( 'Enter the message that is sent to someone when their email address is not recognized. HTML links (a tags) are accepted. These other special tags are allowed:', 'edd-cf7rgc' ) . '<br />{registrant_name}<br />{sitename}<br />{siteurl}',
				'type' => 'textarea',
				'std' => __( 'Dear', 'edd-cf7rgc' ) . " {registrant_name},\n\n" . __( 'Thank you for trying to register on our site. We are sorry, but your email address is not recognized. Please register with the email that you used to complete a purchase on our site.', 'edd-cf7rgc' ) . "\n\n" . __( 'Best Regards,', 'edd-cf7rgc' ) . "\n\n" . __( 'The team at', 'edd-cf7rgc' ) . " {sitename}"
			),

			array(
				'id' => 'edd_cf7rgc_already_exists_subject',
				'name' => __( 'Email Subject If Account Already Exists', 'edd-cf7rgc' ),
				'desc' => __( 'Enter the subject line for the email sent to registrant if their account already exists', 'edd-cf7rgc' ),
				'type' => 'text',
				'std' => 'Your registration details'

			),

			array(
				'id' => 'edd_cf7rgc_already_exists_message',
				'name' => __( 'Email Message If Account Already Exists', 'edd-cf7rgc' ), 
				'desc' => __( 'Enter the message that is sent to registrant if their account already exists. HTML links (a tags) are accepted. These other special tags are allowed:', 'edd-cf7rgc' ) . '<br />{registrant_name}<br />{registrant_username}<br />{sitename}<br />{siteurl}',
				'type' => 'textarea',
				'std' => __( 'Hello', 'edd-cf7rgc' ) . " {registrant_name},\n\n" . __( 'It seems you already have an account on our site.', 'edd-cf7rgc' ) . "\n\n" . __( 'Your username is:', 'edd-cf7rgc' ) . "  {registrant_username}\n\n" .	__( 'Warm Regards,', 'edd-cf7rgc' ) . "\n\n" . __( 'The team at', 'edd-cf7rgc' ) . " {sitename}\n{siteurl}"
			),
		);
		// Merge plugin settings with EDD settings
		return array_merge( $settings, $eddcf7_settings );
	}

	/**
	 * During a Contact Form 7 form submission, send form data to be registered
	 * only if field names 'edd-register-guest-buyer-email' and 'edd-register-guest-buyer-name' are included in form.
	 */
	public function send_cf7_data_to_register( $cf7 ) {

		 // get out now if this is not our form
		if ( ! isset( $_POST['edd-register-guest-buyer-email'] ) )
			return false;

		// get posted data from WPCF7_Submission object
		$submission = WPCF7_Submission::get_instance();
		if ( $submission ) {
			$posted_data = $submission->get_posted_data();
		}
			
		$email = $posted_data["edd-register-guest-buyer-email"];
		$name = $posted_data["edd-register-guest-buyer-name"];
		$desired_username = $posted_data["edd-register-guest-buyer-username"];

		// sanitize
		$email = sanitize_text_field( $email );
		$email = sanitize_email( $email );

		$name = sanitize_text_field( $name );

		$desired_username = sanitize_text_field( $desired_username );
		$desired_username = sanitize_user( $desired_username );

		
		// make sure we only deal with this custom form
		if ( $email && $name ) {

			/** 
			 * See if posted email is from an existing guest customer. 
			 * If yes, register them as a user, and send them email notification of their login details.
			 * If email is already a registered user's email, send them their existing username.
			 * If email is not matched with any customer, reply to email asking them to register with the email they used on checkout page.
			 *
			*/
		
			// prevent duplicate emails per minute
			$time = date("Y-m-d g:i a");
		
			$api_url = site_url( '/edd-api/customers/' );
		
			global $edd_options;
	
			$api_params = array( 
				'key'	=>	$edd_options['edd_cf7rgc_api_key'],
				'token' 	=>	$edd_options['edd_cf7rgc_api_token'],
				'number'	=>	'-1'
			);
				
			$response = wp_remote_get( add_query_arg( $api_params, $api_url ) );
		
			if ( is_wp_error( $response ) )
					return false;
		
			$customers_data = json_decode( wp_remote_retrieve_body( $response ) );
				
			$customers = $customers_data->customers;
		
			unset($customer_id, $customer_username, $total_purchases);
		
			foreach ( $customers as $customer ) {

				// only if there's a matching email addy

				if ( $customer->info->email == $email ) {

					// it's a customer
					$customer_id = $customer->info->id;
					$customer_username = $customer->info->username;

					// get total purchases
					$total_purchases = $customer->stats->total_purchases;

				}
			}
		
			// is this a customer?
		
			if ( ( ! isset( $customer_id ) || empty( $customer_id ) ) || 
				( ( '-1' == $customer_id ) && ( empty( $total_purchases ) ) ) ) { // pending guest
		
					// This is not a customer. Send them back an email asking for more details.
		
					$message = $this->do_email_tags( $edd_options['edd_cf7rgc_rejection_message'], $name, '' );
					$headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ). ">";
			
					if ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) {
		
						if ( wp_mail( $email, $edd_options['edd_cf7rgc_rejection_subject'], $message, $headers ) ) {
							update_option( 'isa_prevent_duplicate_email_sends', $email . $time );
						}
		
					}
		
			} elseif ( ( '-1' == $customer_id ) && ( ! empty( $total_purchases ) ) ) {
		
				// This is guest customer who actually made a purchase (i.e. not pending). 
				// Register and send email notification of login details.
		
				unset( $new_user_login, $new_user_email );

				// If desired username is not taken, give it to this person, otherwise login with email

				if ( $desired_username ) {

					$taken_name = get_user_by( 'login', $desired_username );
					$new_user_login = empty( $taken_name ) ? $desired_username : $email;
				} else {

					// if email is not already taken as a username, assign it
					$taken_name = get_user_by( 'login', $email );
					$new_user_login = empty( $taken_name ) ? $email : '';
				}
		
				// make sure email is not taken
				$taken_email = get_user_by( 'email', $email );
				$new_user_email = empty( $taken_email ) ? $email : '';

				// only add user if we have a unique userlogin and email
				if ( ! empty( $new_user_login ) && ! empty( $new_user_email ) ) {
					$password = wp_generate_password();
					$user_id = wp_insert_user(
								array(
									'user_email' 	=> $email,
									'user_login' 	=> $new_user_login,
									'user_pass'	=> $password,
									'first_name'	=> $name,
									)
								);
			
					if ( $user_id && ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) ) {
						wp_new_user_notification( $user_id, $password );
						update_option( 'isa_prevent_duplicate_email_sends', $email . $time );
	
					} 
		
				} else {
					// rare case, if at all possible
					$message = sprintf( __( 'Dear %s', 'edd-cf7rgc' ), $name ) . "\n\n" . __( 'We could not complete your registration because the username you are trying to use is taken. Please register again with a different desired username.', 'edd-cf7rgc' ) . "\n\n" . __( 'Best regards,', 'edd-cf7rgc' ) . "\n\n" . sprintf( __( 'the team at %s', 'edd-cf7rgc' ), get_bloginfo('name') ) . "\r\n" . get_bloginfo('url');
					$headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ). ">";
					if ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) {
						if ( wp_mail( $email, 'Your registration is not complete', $message, $headers ) ) {
							update_option( 'isa_prevent_duplicate_email_sends', $email . $time );
						}
					}
				} // end if ( ! empty( $new_user_login ) && ! empty( $new_user_email ) )

			} elseif ( ! empty( $customer_id ) && ! empty( $customer_username ) ) {
		
				// id is not empty, nor -1, so user already exists. Send them their username.
		
				$message = $this->do_email_tags( $edd_options['edd_cf7rgc_already_exists_message'], $name, $customer_username );
					
				$headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ). ">";
		
				if ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) {
					if ( wp_mail( $email, $edd_options['edd_cf7rgc_already_exists_subject'], $message, $headers ) ) {
						update_option( 'isa_prevent_duplicate_email_sends', $email . $time );

					}
				}
		
			} // end check if this is a customer

			$cf7->skip_mail = 1;
		}

		return $cf7;
	
	} // end send_cf7_data_to_register

	/**
	* Replace email tags with proper content
	*
	* @param string $content Content to search for email tags
	* @param string $name Name from form
	* @param string $username Registrant's existing username
	*
	* @since 1.0
	*
	* @return string Content with email tags filtered out.
	*/
	public function do_email_tags( $content, $name, $username ) {
	
		$search = array( '{registrant_name}', '{registrant_username}', '{sitename}', '{siteurl}' );
		$replace = array( $name, $username, get_bloginfo('name'), get_bloginfo('url') );
	
		$new_content = str_replace ( $search, $replace, $content );
	
		return $new_content;
	}

	/**
	 * Upon deactivation, delete the option that prevents sending email to visitors more than once a minute
	 */
	public static function deactivate() {
		delete_option( 'isa_prevent_duplicate_email_sends' );
	}
}
}
register_deactivation_hook( __FILE__, array( 'EDD_CF7_Register_Guest_Customers', 'deactivate' ) );
$EDD_CF7_Register_Guest_Customers = EDD_CF7_Register_Guest_Customers::get_instance();