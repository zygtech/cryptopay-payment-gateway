<?php
/*
Plugin Name: WooCommerce Cryptocoin Payment Gateway
Plugin URI: http://www.zygtech.pl
Description: Cryptocoin Payment gateway for woocommerce
Version: 0.1
Author: Chris Hrybacz
Author URI: http://www.zygtech.pl
*/

add_filter( 'woocommerce_currencies', 'add_my_currency' );

function add_my_currency( $currencies ) {
     $currencies['CTC'] = 'Cryptocoins';
     return $currencies;
}

add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol( $currency_symbol, $currency ) {
     switch( $currency ) {
          case 'CTC': $currency_symbol = 'mCTC'; break;
     }
     return $currency_symbol;
}

add_action('plugins_loaded', 'woocommerce_cryptocoin_init', 0);
add_action('woocommerce_after_register_post_type', 'check_ctc_response',0);

    function check_ctc_response(){
        $query_args = array(
			'post_type'      => wc_get_order_types(),
			'post_status'    => [ 'wc-pending' ],
			'posts_per_page' => 999999999999,
		);

		$all_orders = get_posts($query_args);

		foreach ( $all_orders as $single_order ) {
			$order = wc_get_order($single_order->ID);
			
			$check = file_get_contents('http://domain.com/check.php?o=' . $order->ID . '&s=' . get_site_url());
				
				if (substr($check,0,5)=='Payed') {
					$order -> payment_complete();                           
				}
			
				if (substr($check,0,6)=='Failed') {				
					$order -> update_status('failed');
				}
			
		}
		
 
    }
    
function woocommerce_cryptocoin_init(){
  if(!class_exists('WC_Payment_Gateway')) return;
 
  class WC_Cryptocoin extends WC_Payment_Gateway{
    public function __construct(){
      $this -> id = 'ctc';
      $this -> medthod_title = 'Cryptocoin';
      $this -> has_fields = false;
 
      $this -> init_form_fields();
      $this -> init_settings();
 
      $this -> title = $this -> settings['title'];
      $this -> description = $this -> settings['description'];
      $this -> my_address = $this -> settings['my_address'];
 
      $this -> msg['message'] = "";
      $this -> msg['class'] = "";
      
      if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
             } else {
                add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
            }
            
   }
   
    function init_form_fields(){
 
       $this -> form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable Cryptocoin Payment Module.',
                    'default' => 'no'),
                'title' => array(
                    'title' => 'Title:',
                    'type'=> 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default' => 'Cryptocoin'),
                'description' => array(
                    'title' => __('Description:', 'mrova'),
                    'type' => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default' => 'Pay securly with Cryptocoins'),
                'my_address' => array(
                    'title' => 'CTC Address',
                    'type' => 'text',
                    'description' => 'Address where earned cryptocoins gonna be paid.',
					'default' => 'LdkpKBztH4sj9guqsef6hiCu9uNeaFhLGr')
            );
    }
 
       public function admin_options(){
        echo '<h3>Cryptocoins Payment Gateway</h3>';
        echo '<p>Cryptocoins CTC Cryptocurrency Payment Gateway</p>';
        echo '<table class="form-table">';
        // Generate the HTML For the settings form.
        $this -> generate_settings_html();
        echo '</table>';
 
    }
 
    /**
     *  There are no payment fields for payu, but we want to show the description if set.
     **/
    function payment_fields(){
        if($this -> description) echo wpautop(wptexturize($this -> description));
    }
    
    /**
     * Process the payment and return the result
     **/
     function process_payment($order_id){
	global $woocommerce;
        $order = new WC_Order($order_id);
	if ($woocommerce -> cart!=null) $woocommerce -> cart -> empty_cart();
        return array('result' => 'success', 'redirect' => 'http://domain.com/pay.php?id=' . $order_id . '&site=' . get_site_url() . '&amount=' . $order -> order_total . '&my_address=' . $this -> my_address
        );
    }
 
    /**
     * Check for valid payu server callback
     **/

 
    function showMessage($content){
            return '<div class="box '.$this -> msg['class'].'-box">'.$this -> msg['message'].'</div>'.$content;
        }
     // get all pages
    function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';
            // show indented child pages?
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }
            // add to page list array array
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }
}
   /**
     * Add the Gateway to WooCommerce
     **/
    function woocommerce_add_cryptocoin_gateway($methods) {
        $methods[] = 'WC_Cryptocoin';
        return $methods;
    }
 
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_cryptocoin_gateway' );
}
