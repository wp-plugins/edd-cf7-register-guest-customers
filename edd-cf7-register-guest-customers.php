<?php
/*
Plugin Name: EDD CF7 Register Guest Customers
Plugin URI: http://isabelcastillo.com/docs/category/edd-cf7-register-guest-customers
Description: Register EDD guest customers with Contact Form 7 custom registration, disable registration for everyone else.
Version: 0.4.6
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
    public function __construct() {

	    	add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'edd_settings_extensions', array( $this, 'add_settings' ) );
		add_action( 'wpcf7_before_send_mail', array( $this, 'send_cf7_data_to_register' ) );


		/** 
		 * During testing or debugging, you may need to run the following action once
		 *  or else your test-customers will not receive any emails after test 1.
		 *
		 * Use temporarily as a reset ONLY WHILE TESTING. Do not use on live site.
		 * IF YOU USE THIS ON A LIVE SITE registrants will get dozens of email responses upon form submission.
		 */
		
		// add_action( 'init', array( $this, 'allow_duplicate_emails' ) ); // leave commented out on live sites

    }

	public function load_textdomain() {
		load_plugin_textdomain( 'edd-cf7rgc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add settings to the "Downloads > Settings > Extensions" section
	 *
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
				'std' => __( 'Dear', 'edd-cf7rgc' ) . " {registrant_name},\n\n" . __( 'Thank you for trying to register on our site. We are sorry, but your email address is not recognized. Please register with the email that you used to purchase on our site.', 'edd-cf7rgc' ) . "\n\n" . __( 'Best Regards,', 'edd-cf7rgc' ) . "\n\n" . __( 'The team at', 'edd-cf7rgc' ) . " {sitename}"
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
	
		// Merge plugin settings with original EDD settings
		return array_merge( $settings, $eddcf7_settings );
	}

	/**
	 * During a Contact Form 7 form submission, send form data to be registered
	 * only if field names 'edd-register-guest-buyer-email' and 'edd-register-guest-buyer-email' are included in form.
	 * 
	 */

	public function send_cf7_data_to_register( $cf7 ) {
	
		$email = $cf7->posted_data["edd-register-guest-buyer-email"];
		$name = $cf7->posted_data["edd-register-guest-buyer-name"];
		$desired_username = $cf7->posted_data["edd-register-guest-buyer-username"];

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
		
			unset($customer_id, $customer_username);
		
			foreach ( $customers as $customer ) {
		
				if ( $customer->info->email == $email ) {
		
					// it's a customer
					$customer_id = $customer->info->id;
					$customer_username = $customer->info->username;
		
				}
			}
		
			// is this a customer?
		
			if ( ! isset( $customer_id ) || empty( $customer_id ) ) {
		
					// This is not a customer. Send them back an email asking for more details.
		
					$message = $this->do_email_tags( $edd_options['edd_cf7rgc_rejection_message'], $name, $customer_username );
	
					$headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ). ">";
			
					if ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) {
		
						if ( wp_mail( $email, $edd_options['edd_cf7rgc_rejection_subject'], $message, $headers ) ) {
							update_option( 'isa_prevent_duplicate_email_sends', $email . $time );
						}
		
					}
		
			} elseif ( '-1' == $customer_id ) {
		
				// This is guest customer. Register them and send them email notification of login details.
				// if desired username is not taken, give it to this person, otherwise login with email
		
				if ( $desired_username ) {

					$taken_name = get_user_by( 'login', $desired_username );
					$new_user_login = (! $taken_name) ? $desired_username : $email;
				} else {
					$new_user_login = $email;
				}
		
				$password = wp_generate_password();
				$user_id = wp_insert_user(
							array(
								'user_email' 	=> $email,
								'user_login' 	=> $new_user_login,
								'user_pass'	=> $password,
								'first_name'	=> $name,
								)
							);
		
				// If new user is created, send them email and password:
				if ( $user_id && ( get_option( 'isa_prevent_duplicate_email_sends' ) != ( $email . $time ) ) ) {
					wp_new_user_notification( $user_id, $password );
					update_option( 'isa_prevent_duplicate_email_sends', $email . $time );

				}
		
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
	 * Delete the option that prevents sending email to visitors more than once a day. For testing.
	 */
	public function allow_duplicate_emails() {
		delete_option( 'isa_prevent_duplicate_email_sends' );
	}
}
}
$EDD_CF7_Register_Guest_Customers = new EDD_CF7_Register_Guest_Customers();