<?php
/*
Plugin Name: UK Lederwaren Customization
Plugin URI: https://www.rafaeldejongh.com
Description: A plugin that adds various new functionalities to WordPress and WooCommerce created for UKLederwaren
Author: Rafael De Jongh
Version: 1.0
Author URI: https://www.rafaeldejongh.com
*/
//Hide price for non logged-in users
add_action('init','hide_price');
function hide_price(){
	if(!is_user_logged_in()){
	 remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_add_to_cart',10);
	 remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',30);
	 remove_action('woocommerce_single_product_summary','woocommerce_template_single_price',10);
	 remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_price',10);
	 add_action('woocommerce_single_product_summary','print_login_to_see',31);
	 add_action('woocommerce_after_shop_loop_item','print_login_to_see',11);
	}
}
function print_login_to_see(){echo '<a href="' . get_permalink(woocommerce_get_page_id('myaccount')) . '">' . __('Login to see prices','theme_name') . '</a>';}
//Change number or products per row to 3
add_filter('loop_shop_columns','loop_columns',999);
function loop_columns(){return 4;}
//Display 12 products per page.
add_filter('loop_shop_per_page',create_function('$cols','return 12;'),20);
//Change Variable Product Text
add_filter('woocommerce_product_add_to_cart_text','custom_woocommerce_product_add_to_cart_text');
function custom_woocommerce_product_add_to_cart_text(){
	global $product;
	$product_type = $product->product_type;
	switch ($product_type){
		case 'external': return __('Buy product','woocommerce');
		break;
		case 'grouped': return __('View products','woocommerce');
		break;
		case 'simple': return __('Add to cart','woocommerce');
		break;
		case 'variable': return __('Select color','woocommerce');
		break;
		default: return __('Read more','woocommerce');
	}
}
//Hide shipping rates when free shipping is available
add_filter('woocommerce_package_rates','unset_shipping_when_free_is_available',10,2);
function unset_shipping_when_free_is_available($rates,$package){
	$all_free_rates = array();
		foreach ($rates as $rate_id => $rate){
		if('free_shipping' === $rate->method_id){
			$all_free_rates[ $rate_id ] = $rate;
			break;
		}
	}
	if(empty($all_free_rates)){return $rates;}else{return $all_free_rates;}
}
/* ---------------------- Checkout page ----------------------- */
//Checkout Fields
add_filter('woocommerce_checkout_fields','custom_override_checkout_fields');
function custom_override_checkout_fields($fields){
	$fields['billing']['billing_company']['required'] = true;
	$fields['billing']['billing_vat'] = array(
	'label'			=> __('VAT Number','woocommerce'),
	'placeholder'	=> _x('Enter VAT Number','placeholder','woocommerce'),
	'required'		=> true,
	'class'			=> array('form-row-wide'),
	'clear'			=> true
	);
	return $fields;
}
//Display field value on the order edit page
add_action('woocommerce_admin_order_data_after_shipping_address','checkout_order',10,1);
function checkout_order($order){
	echo '<p><strong>'.__('VAT Number').':</strong> ' . get_post_meta($order->id,'_billing_vat',true) . '</p>';
}
//Order the fields
add_filter("woocommerce_checkout_fields","order_fields");
function order_fields($fields){
	$order = array(
		"billing_first_name",
		"billing_last_name",
		"billing_company",
		"billing_vat",
		"billing_country",
		"billing_city",
		"billing_postcode",
		"billing_state",
		"billing_address_1",
		"billing_address_2",
		"billing_email",
		"billing_phone",
	);
foreach($order as $field){$ordered_fields[$field] = $fields["billing"][$field];}
$fields["billing"] = $ordered_fields;
return $fields;
}
/* ---------------------- Registration page ----------------------- */
//Add extra fields in registration form
add_action('woocommerce_register_form_start','my_extra_register_fields');
function my_extra_register_fields(){
?>
	<p class="woocommerce-FormRow form-row form-row-first">
		<label for="reg_billing_first_name"><?php _e('First Name','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if(! empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>"/>
	</p>
	<p class="woocommerce-FormRow form-row form-row-last">
		<label for="reg_billing_last_name"><?php _e('Last Name','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if(! empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>"/>
	</p>
	<div class="clearfix"></div>
	<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
		<label for="reg_billing_company"><?php _e('Company Name','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_company" id="reg_billing_company" value="<?php if(! empty($_POST['billing_company'])) esc_attr_e($_POST['billing_company']); ?>"/>
	</p>
	<div class="clearfix"></div>
	<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
		<label for="reg_billing_vat"><?php _e('VAT Number','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_vat" id="reg_billing_vat" value="<?php if(! empty($_POST['billing_vat'])) esc_attr_e($_POST['billing_vat']); ?>" maxlength="15" placeholder="Enter VAT Number"/>
	</p>
	<div class="clearfix"></div>
<?php
	wp_enqueue_script('wc-country-select');
	woocommerce_form_field('billing_country',array(
		'type'        => 'country',
		'class'       => array('chzn-drop'),
		'label'       => __('Country'),
		'placeholder' => __('Choose your country.'),
		'required'    => true,
		'clear'       => true,
		'default'     => 'BE'
	));
?>
	<p class="woocommerce-FormRow form-row form-row-first">
		<label for="reg_billing_postcode"><?php _e('Postcode / ZIP','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php if(! empty($_POST['billing_postcode'])) esc_attr_e($_POST['billing_postcode']); ?>"/>
	</p>
	<p class="woocommerce-FormRow form-row form-row-last">
		<label for="reg_billing_city"><?php _e('Town / City','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php if(! empty($_POST['billing_city'])) esc_attr_e($_POST['billing_city']); ?>"/>
	</p>
	<div class="clearfix"></div>
	<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
		<label for="reg_billing_address_1"><?php _e('Address','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php if(! empty($_POST['billing_address_1'])) esc_attr_e($_POST['billing_address_1']); ?>" placeholder="Street address"/>
	</p>
	<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
		<input type="text" class="input-text" name="billing_address_2" id="reg_billing_address_2" value="<?php if(! empty($_POST['billing_address_2'])) esc_attr_e($_POST['billing_address_2']); ?>" placeholder="Apartment,suite,unit etc. (optional)"/>
	</p>
	<div class="clearfix"></div>
	<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
		<label for="reg_billing_phone"><?php _e('Phone','woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php if(! empty($_POST['billing_phone'])) esc_attr_e($_POST['billing_phone']); ?>"/>
	</p>
	<div class="clearfix"></div>
<?php
}
//Registration form fields Validation
add_action('woocommerce_register_post','my_validate_extra_register_fields',10,3);
function my_validate_extra_register_fields($username,$email,$validation_errors){
	if(isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])){$validation_errors->add('billing_first_name_error',__('A first name is required!','woocommerce'));}
	if(isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])){$validation_errors->add('billing_last_name_error',__('A last name is required!','woocommerce'));}
	if(isset($_POST['billing_company']) && empty($_POST['billing_company'])){$validation_errors->add('billing_company_error',__('A Company name is required!','woocommerce'));}
	if(isset($_POST['billing_vat']) && empty($_POST['billing_vat'])){$validation_errors->add('billing_vat_error',__('VAT number is required!','woocommerce'));}
	if(isset($_POST['billing_country']) && empty($_POST['billing_country'])){$validation_errors->add('billing_country_error',__('A country is required!','woocommerce'));}
	if(isset($_POST['billing_city']) && empty($_POST['billing_city'])){$validation_errors->add('billing_city_error',__('A city is required!','woocommerce'));}
	if(isset($_POST['billing_postcode']) && empty($_POST['billing_postcode'])){$validation_errors->add('billing_postcode_error',__('A postcode is required!','woocommerce'));}
	if(isset($_POST['billing_state']) && empty($_POST['billing_state'])){$validation_errors->add('billing_state_error',__('A state is required!','woocommerce'));}
	if(isset($_POST['billing_address_1']) && empty($_POST['billing_address_1'])){$validation_errors->add('billing_address_1_error',__('An address is required!','woocommerce'));}
	if(isset($_POST['billing_phone']) && empty($_POST['billing_phone'])){$validation_errors->add('billing_phone_error',__('A phone number is required!','woocommerce'));}
	return $validation_errors;
}
//Below code save extra fields when new user register
add_action('woocommerce_created_customer','my_save_extra_register_fields'); 
function my_save_extra_register_fields($customer_id){
	if(isset($_POST['billing_first_name'])){
		update_user_meta($customer_id,'first_name',sanitize_text_field($_POST['billing_first_name']));
		update_user_meta($customer_id,'billing_first_name',sanitize_text_field($_POST['billing_first_name']));
	}
	if(isset($_POST['billing_last_name'])){
		update_user_meta($customer_id,'last_name',sanitize_text_field($_POST['billing_last_name']));
		update_user_meta($customer_id,'billing_last_name',sanitize_text_field($_POST['billing_last_name']));
	}
	if(isset($_POST['billing_company'])){
		update_user_meta($customer_id,'billing_company',sanitize_text_field($_POST['billing_company']));
	}
	if(isset($_POST['billing_vat'])){
		update_user_meta($customer_id,'billing_vat',sanitize_text_field($_POST['billing_vat']));
	}
	if(isset($_POST['billing_country'])){
		update_user_meta($customer_id,'billing_country',sanitize_text_field($_POST['billing_country']));
	}
	if(isset($_POST['billing_city'])){
		update_user_meta($customer_id,'billing_city',sanitize_text_field($_POST['billing_city']));
	}
	if(isset($_POST['billing_postcode'])){
		update_user_meta($customer_id,'billing_postcode',sanitize_text_field($_POST['billing_postcode']));
	}
	if(isset($_POST['billing_state'])){
		update_user_meta($customer_id,'billing_state',sanitize_text_field($_POST['billing_state']));
	}
	if(isset($_POST['billing_address_1'])){
		update_user_meta($customer_id,'billing_address_1',sanitize_text_field($_POST['billing_address_1']));
	}
	if(isset($_POST['billing_phone'])){
		update_user_meta($customer_id,'billing_phone',sanitize_text_field($_POST['billing_phone']));
	}
	if(isset($_POST['email'])){
		update_user_meta($customer_id,'billing_email',sanitize_text_field($_POST['email']));
	}
}
/* ---------------------- Account page ----------------------- */
//Add field under my account billing
add_filter('woocommerce_billing_fields','woocommerce_billing_fields');
function woocommerce_billing_fields($fields){
	$user_id = get_current_user_id();
	$user	= get_userdata($user_id);
	if(!$user) return;
	$fields['billing_vat'] = array(
		'type'			=> 'text',
		'label'			=> __('VAT','woocommerce'),
		'placeholder'	=> _x('VAT Number','placeholder','woocommerce'),
		'required'		=> true,
		'class'			=> array('form-row'),
		'clear'			=> true,
		'default'		=> get_user_meta($user_id,'billing_vat',true)
	);
	return $fields;
}
//Format custom field to show on my account billing
add_filter('woocommerce_my_account_my_address_formatted_address','custom_my_account_my_address_formatted_address',10,3);
function custom_my_account_my_address_formatted_address($fields,$customer_id,$name){
	$fields['vat'] = get_user_meta($customer_id,$name . '_vat',true);
	return $fields;
}
//Replace the key for custom field to show on my account billing
add_filter('woocommerce_formatted_address_replacements','custom_formatted_address_replacements',10,2);
function custom_formatted_address_replacements($address,$args){
	$address['{vat}'] = '';
	if(! empty($args['vat'])){
		$address['{vat}'] = __('VAT Number','woocommerce') . ': ' . $args['vat'];
	}
	return $address;
}
add_filter('woocommerce_localisation_address_formats','custom_localisation_address_format');
function custom_localisation_address_format($formats){
	foreach($formats as $key => $value) :
		$formats[$key] .= "\n\n{vat}";
	endforeach;
	return $formats;
}
//Registration Auto Login Prevention
function user_autologout(){
	if(is_user_logged_in()){
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$approved_status = get_user_meta($user_id,'wp-approve-user',true);
	if($approved_status == 1){
		return $redirect_url;
	}else{
		wp_logout();
		return get_permalink(woocommerce_get_page_id('myaccount')) . "?approved=false";
		}
	}
}
add_action('woocommerce_registration_redirect','user_autologout',2);
//Display Custom Fields on User Profile
add_filter('woocommerce_customer_meta_fields','add_custom_meta_field');
function add_custom_meta_field($fields){
	$fieldData = array('label' => 'VAT Number');
	$fields['billing']['fields']['billing_vat'] = $fieldData;
	return $fields;
}
//Reduce the strength requirement on the woocommerce password
add_filter('woocommerce_min_password_strength','reduce_woocommerce_min_strength_requirement');
function reduce_woocommerce_min_strength_requirement($strength){return 1;}
//Show Empty Categories
add_filter('woocommerce_product_subcategories_hide_empty','show_empty_categories',10,1);
function show_empty_categories ($show_empty){
	$show_empty = true;
	return $show_empty;
}
//Temprarly disable Cabin Max
function disableEmptyCat(){if(is_shop()) echo'<script>jQuery(".product-category.product.last a").css({cursor:"default"}).removeAttr("href");jQuery(".product-category.product.last h3").prepend("Coming Soon<br>");</script>';}
add_action('wp_footer','disableEmptyCat',100);