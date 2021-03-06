<?php

include(SIMPLE_WP_MEMBERSHIP_PATH . 'ipn/swpm_handle_subsc_ipn.php');

class SwpmStripeBuyNowIpnHandler {
    
    public function __construct() {
        
        $this->handle_stripe_ipn();
    }
    
    public function handle_stripe_ipn(){
        SwpmLog::log_simple_debug("Stripe Buy Now IPN received. Processing request...", true);
        //SwpmLog::log_simple_debug(print_r($_REQUEST, true), true);//Useful for debugging purpose
        
        //Include the Stripe library.
        include(SIMPLE_WP_MEMBERSHIP_PATH . 'lib/stripe-gateway/init.php');
        
        //Read and sanitize the request parameters.
        $button_id = sanitize_text_field($_REQUEST['item_number']);
        $button_title = sanitize_text_field($_REQUEST['item_name']);
        $payment_amount = sanitize_text_field($_REQUEST['item_price']);
        $price_in_cents = $payment_amount * 100 ;//The amount (in cents). This value is used in Stripe API.
        $currency_code = sanitize_text_field($_REQUEST['currency_code']);
        
        $stripe_token = sanitize_text_field($_POST['stripeToken']);
        $stripe_token_type = sanitize_text_field($_POST['stripeTokenType']);
        $stripe_email = sanitize_email($_POST['stripeEmail']);
        
        //Retrieve the CPT for this button
        $button_cpt = get_post($button_id); 
        if(!$button_cpt){
            //Fatal error. Could not find this payment button post object.
            SwpmLog::log_simple_debug("Fatal Error! Failed to retrieve the payment button post object for the given button ID: ". $button_id, false);
            wp_die("Fatal Error! Payment button (ID: ".$button_id.") does not exist. This request will fail.");
        }
        
        $membership_level_id = get_post_meta($button_id, 'membership_level_id', true);
        
        //Validate and verify some of the main values.
        $true_payment_amount = get_post_meta($button_id, 'payment_amount', true);
        if( $payment_amount != $true_payment_amount ) {
            //Fatal error. Payment amount may have been tampered with.
            $error_msg = 'Fatal Error! Received payment amount ('.$payment_amount.') does not match with the original amount ('.$true_payment_amount.')';
            SwpmLog::log_simple_debug($error_msg, false);
            wp_die($error_msg);
        }
        $true_currency_code = get_post_meta($button_id, 'payment_currency', true);
        if( $currency_code != $true_currency_code ) {
            //Fatal error. Currency code may have been tampered with.
            $error_msg = 'Fatal Error! Received currency code ('.$currency_code.') does not match with the original code ('.$true_currency_code.')';
            SwpmLog::log_simple_debug($error_msg, false);
            wp_die($error_msg);
        }
        
        //Validation passed. Go ahead with the charge.
        
        //Sandbox and other settings
        $settings = SwpmSettings::get_instance();
        $sandbox_enabled = $settings->get_value('enable-sandbox-testing');
        if($sandbox_enabled){
            SwpmLog::log_simple_debug("Sandbox payment mode is enabled. Using test API key details.", true);
            $secret_key = get_post_meta($button_id, 'stripe_test_secret_key', true);;//Use sandbox API key
        } else {
            $secret_key = get_post_meta($button_id, 'stripe_live_secret_key', true);;//Use live API key
        }

        //Set secret API key in the Stripe library
        \Stripe\Stripe::setApiKey($secret_key);
        
        // Get the credit card details submitted by the form
        $token = $stripe_token;
        
        // Create the charge on Stripe's servers - this will charge the user's card
        try {
            $charge = \Stripe\Charge::create(array(
            "amount" => $price_in_cents, //Amount in cents
            "currency" => strtolower($currency_code),
            "source" => $token,
            "description" => $button_title,
        ));
        } catch(\Stripe\Error\Card $e) {
            // The card has been declined
            SwpmLog::log_simple_debug("Stripe Charge Error! The card has been declined. ".$e->getMessage(), false);
            $body = $e->getJsonBody();
            $error  = $body['error'];
            $error_string = print_r($error,true);
            SwpmLog::log_simple_debug("Error details: ".$error_string, false);
            wp_die("Stripe Charge Error! Card charge has been declined. " . $e->getMessage() . $error_string);
        }

        //Everything went ahead smoothly with the charge.
        SwpmLog::log_simple_debug("Stripe Buy Now charge successful.", true);
        
        //Grab the charge ID and set it as the transaction ID.
        $txn_id = $charge->id;//$charge->balance_transaction;
        //The charge ID can be used to retrieve the transaction details using hte following call.
        //\Stripe\Charge::retrieve($charge->id);
        $custom = sanitize_text_field($_REQUEST['custom']);
        $custom_var = SwpmTransactions::parse_custom_var($custom);
        $swpm_id = isset($custom_var['swpm_id'])? $custom_var['swpm_id']: '';
        
        //Create the $ipn_data array.
        $ipn_data = array();
        $ipn_data['mc_gross'] = $payment_amount;
        $ipn_data['first_name'] = '';
        $ipn_data['last_name'] = '';
        $ipn_data['payer_email'] = $stripe_email;
        $ipn_data['membership_level'] = $membership_level_id;
        $ipn_data['txn_id'] = $txn_id;
        $ipn_data['subscr_id'] = $txn_id;
        $ipn_data['swpm_id'] = $swpm_id;
        $ipn_data['ip'] = $custom_var['user_ip'];
        $ipn_data['custom'] = $custom;
        $ipn_data['gateway'] = 'stripe';
        $ipn_data['status'] = 'completed';
        
        $ipn_data['address_street'] = '';
        $ipn_data['address_city'] = '';
        $ipn_data['address_state'] = '';
        $ipn_data['address_zipcode'] = '';
        $ipn_data['country'] = '';

        //Handle the membership signup related tasks.
        swpm_handle_subsc_signup_stand_alone($ipn_data,$membership_level_id,$txn_id,$swpm_id);
        
        //Save the transaction record
        SwpmTransactions::save_txn_record($ipn_data);
        SwpmLog::log_simple_debug('Transaction data saved.', true);
        
        //Trigger the stripe IPN processed action hook (so other plugins can can listen for this event).
        do_action('swpm_stripe_ipn_processed', $ipn_data);
        
        do_action('swpm_payment_ipn_processed', $ipn_data);
        
        //Redirect the user to the return URL (or to the homepage if a return URL is not specified for this payment button).
        $return_url = get_post_meta($button_id, 'return_url', true);
        if (empty($return_url)) {
            $return_url = SIMPLE_WP_MEMBERSHIP_SITE_HOME_URL;
        }
        SwpmLog::log_simple_debug("Redirecting customer to: ".$return_url, true);
        SwpmLog::log_simple_debug("End of Stripe Buy Now IPN processing.", true, true);
        SwpmMiscUtils::redirect_to_url($return_url);
    
    }
}

$swpm_stripe_buy_ipn = new SwpmStripeBuyNowIpnHandler();
