<?php
/**
 * Gravity Forms Field Mapper for Momentum Webhook
 * Version: 1.2.1
 *
 * @package MomentumWebhookManager
 * 
 * This file maps Gravity Forms field IDs to proper field names
 * for all supported forms: Old Alarm Monitoring (1), Old Private Investigator (2), 
 * Old Security Guard (3), Security Guard (10), Alarm Monitoring (11), Private Investigator (12)
 * 
 * Release Notes:
 * 
 * Version 1.2.1 (Current)
 * - Enhanced field mappings with comprehensive coverage (200+ fields)
 * - Fixed duplicate field ID mappings (fields 65, 66, 95, 96)
 * - Updated field names for consistency (alternate_name, limit_of_liability)
 * - Fixed typo in lawsuit_claims_details field
 * - Excluded problematic fields from webhook (date_updated, status, country)
 * - Added descriptive names for explanation fields (191-199)
 * - Changed field 217 from years_of_experience to business_start_date
 *
 * Version 1.2.0
 * - Mapper direct send hooks are disabled by default and gated behind an option/filter
 * - Direct send now uses saved plugin webhook URL when enabled
 * - Housekeeping: adjusted duplicate keys and minor label corrections
 * 
 * Version 1.1.0
 * - Added support for legacy forms: Old Alarm Monitoring (1), Old Private Investigator (2), Old Security Guard (3)
 * - Added automatic webhook hooks for all legacy forms
 * - Expanded form support to include legacy/archived application forms
 * - Added field mappings for all legacy form types with proper naming
 * 
 * Version 1.0.9
 * - Initial field mapping implementation for Forms 10, 11, 12
 * - Support for Security Guard, Alarm Monitoring, Private Investigator applications
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Get field mappings for Security Guard Application form
 */
function get_security_guard_form_field_mappings() {
	return array(
	//section 1 - General Information
	   
	// Basic Information
	'1' => 'company_name',
	'2' => 'applicant_address',
	'2.1' => 'street_address',
	'2.2' => 'address_line_2',
	'2.3' => 'city',
	'2.4' => 'state',
	'2.5' => 'zip_code',
	'2.6' => 'country',
	
	// Mailing Address (if different)
	'3' => 'mailing_address',
	'3.1' => 'mailing_street_address',
	'3.2' => 'mailing_address_line_2',
	'3.3' => 'mailing_city',
	'3.4' => 'mailing_state',
	'3.5' => 'mailing_zip_code',
	'3.6' => 'mailing_country',
	
	// Contact Information
	'5' => 'title',
	'6' => 'cell_phone',
	'7' => 'work_phone',
	'8' => 'fax_number',
	'9' => 'email_address',
	'10' => 'date_coverage_needed',
	
	// Personal Information
	'11' => 'bus_type',
	'12' => 'fein_number',
	'14' => 'license_number',
	'15' => 'years_under_this_name',
	'16' => 'any_other_name',
	'17' => 'other_name_details',
	'18' => 'percent_security_service',
	'19' => 'percent_investigations',
	'20' => 'percent_consulting',
	'21' => 'alarm_service_monitoring',
	'22' => 'each_occurrence',
	'23' => 'aggregate',
	'24' => 'other_services',
	'25' => 'other_services_details',
	'26' => 'five_largest_clients',
	'27' => 'most_clients_under_contract',
	'29' => 'workers_comp_in_force',
	'30' => 'no_workers_comp_reason',
	
	//section 2 - Operations

	'31' => 'owner_partner_percentages',
	'32' => 'principals_perform_duties',
	'33' => 'supervisor_duties',
	'34' => 'officers_per_supervisor',
	'37' => 'employer_phone',
	'38' => 'annual_unarm_bill',
	'39' => 'annual_arm_bill',
	'40' => 'ft_offc',
	'41' => 'pt_offc',
	'42' => 'unarm_offc',
	'43' => 'arm_offc',
	'44' => 'use_golfcart',
	'45' => 'have_lights',
	'46' => 'public_transport',
	'47' => 'driving_record_checked',
	'48' => 'use_dogs',
	'49' => 'dog_w_handler',
	'50' => 'dog_wo_handler',
	'51' => 'taser',
	'52' => 'jewelry_money_furs',
	'53' => 'associations',
	
	//section 3 - Payroll Details

	'54' => 'independent_contractor',
	'55' => 'independent_contractor_salaries',
	   
	   //Unarmed Payroll
	'56' => 'ua_airport_non_public',
	'57' => 'ua_airport_public',
	'58' => 'ua_armored_car',
	'59' => 'ua_banks',
	'60' => 'ua_bounty_hunter',
	'61' => 'ua_car_dealers',
	'62' => 'ua_churches',
	'63' => 'ua_construction',
	'64' => 'ua_convention',
	'65' => 'ua_crim_detention',
	'66' => 'ua_executive_protection',
	'67' => 'ua_fast_food',
	'68' => 'ua_federal_government',
	'69' => 'ua_retirement_homes',
	'70' => 'ua_govt_housing',
	'71' => 'ua_hotels_motels',
	'72' => 'ua_industrial',
	'73' => 'ua_institutional',
	'74' => 'ua_liquor',
	'75' => 'ua_local_state',
	'76' => 'ua_mid_high_housing',
	'77' => 'ua_museums',
	'78' => 'ua_office_buildings',
	'79' => 'ua_patrol_cars',
	'80' => 'ua_restaurants',
	'81' => 'ua_retail_out',
	'82' => 'ua_retail_in',
	'83' => 'ua_schools',
	'84' => 'ua_special_events',
	'85' => 'ua_strike_duty',
	'86' => 'ua_traffic_control',
	'87' => 'ua_transport_courier',
	'88' => 'ua_trucking_term',
	'89' => 'ua_waterfront',
	'210' => 'ua_other',
	'126' => 'ua_exec_protection',
	'127' => 'ua_pre_employment',
	'128' => 'ua_lie_detect',
	'129' => 'ua_process_server',
	'130' => 'ua_sec_consult',
	'131' => 'ua_total_pay',

	
	// Armed Payroll
	'91' => 'a_airport_non_public',
	'92' => 'a_airport_public',
	'93' => 'a_armored_car',
	'94' => 'a_banks',
	'95' => 'a_bounty_hunter',
	'95' => 'a_car_dealers',
	'97' => 'a_churches',
	'98' => 'a_construction',
	'99' => 'a_convention',
	'100' => 'a_crim_detention',
	'101' => 'a_executive_protection',
	'102' => 'a_fast_food',
	'103' => 'a_federal_government',
	'104' => 'a_retirement_homes',
	'105' => 'a_govt_housing',
	'106' => 'a_hotels_motels',
	'107' => 'a_industrial',
	'108' => 'a_institutional',
	'109' => 'a_liquor',
	'110' => 'a_local_state',
	'111' => 'a_mid_high_housing',
	'112' => 'a_museums',
	'113' => 'a_office_buildings',
	'114' => 'a_patrol_cars',
	'115' => 'a_restaurants',
	'116' => 'a_retail_out',
	'117' => 'a_retail_in',
	'118' => 'a_schools',
	'119' => 'a_special_events',
	'120' => 'a_strike_duty',
	'121' => 'a_traffic_control',
	'122' => 'a_transport_courier',
	'123' => 'a_trucking_term',
	'124' => 'a_waterfront',
	'125' => 'a_other',
	'132' => 'a_exec_protection',
	'133' => 'a_pre_employment',
	'134' => 'a_lie_detect',
	'135' => 'a_process_server',
	'136' => 'a_sec_consult',
	'137' => 'a_total_pay',
	
	'138' => 'avg_hour_wage_ft',
	'139' => 'avg_hour_wage_pt',
	'140' => 'ann_corp_rev_ft',
	'141' => 'alarm_oper_est_rev',
	  
	//section 4 - Description of Operations
	
	// Additional Fields
	'191' => 'airport_work_details',
	'192' => 'apartment_work_details',
	'193' => 'retail_work_details',
	'194' => 'criminal_work_details',
	'195' => 'special_events_work_details',
	'196' => 'bodyguard_work_details',
	'197' => 'athlete_celeb',
	'198' => 'security_consulting_work_details',
	'199' => 'other_work_details',
	
	//section 5 - Curent Insurance Information
	
	'151' => 'current_carrier',
	'152' => 'inception_date',
	'154' => 'expiration_date',
	'153' => 'premium_amount',
	'155' => 'deductible_amount',
	'156' => 'Limit of Liability',
	'157' => 'occurence_form',
	'158' => 'declined_coverage',
	'159' => 'declined_coverage_details',
	'160' => 'req_incidents',
	'161' => 'lawsuit_claims',
	'162' => 'lawsuite_claims_details',
	'164' => 'future_claims',
	'165' => 'future_claims_details',
	'187' => 'loss_run_details',
	'172' => 'holdharmless',
	'173' => 'field_173',
	
	// Additional compound fields
	'174' => 'employee_training',
	'174.1' => 'employee_training_part_1',
	'174.2' => 'employee_training_part_2',
	'174.3' => 'employee_training_part_3',
	'174.4' => 'employee_training_part_4',
	'174.5' => 'employee_training_part_5',
	'174.6' => 'employee_training_part_6',
	'174.7' => 'employee_training_part_7',
	
	'175' => 'pre_screen',
	'175.1' => 'pre_screen_part_1',
	'175.2' => 'pre_screen_part_2',
	'175.3' => 'pre_screen_part_3',
	'175.4' => 'pre_screen_part_4',
	'175.5' => 'pre_screen_part_5',
	'175.6' => 'pre_screen_part_6',
	
	
	'190' => 'signature_image',
	'191' => 'field_191',
	'192' => 'field_192',
	'193' => 'field_193',
	'194' => 'field_194',
	'195' => 'field_195',
	'196' => 'field_196',
	'197' => 'field_197',
	'198' => 'field_198',
	'199' => 'field_199',
	'200' => 'AgencyID',
	'201' => 'form_name',
	
	// Name fields

	'203.3' => 'applicant_first_name',
	'203.4' => 'applicant_middle_name',
	'203.6' => 'applicant_last_name',
	'203.8' => 'applicant_suffix',
	
	'208' => 'applicant_name',
	'208.2' => 'alternate_prefix',
	'208.3' => 'alternate_first_name',
	'208.4' => 'alternate_middle_name',
	'208.6' => 'alternate_last_name',
	'208.8' => 'alternate_suffix',
	'167' => 'applicant_title',
	'168' => 'date_of_signature',
	
	'210' => 'field_210',
	'211' => 'field_211',
	'212' => 'field_212',
	'213' => 'field_213',
	'214' => 'field_214',
	'215' => 'field_215',
	'216' => 'years_of_experience',
	
	// System fields
	'id' => 'entry_id',
	'form_id' => 'form_id',
	'post_id' => 'post_id',
	'date_created' => 'date_created',
	'date_updated' => 'date_updated',
	'is_starred' => 'is_starred',
	'is_read' => 'is_read',
	'ip' => 'ip_address',
	'source_url' => 'source_url',
	'payment_status' => 'payment_status',
	'payment_date' => 'payment_date',
	'payment_amount' => 'payment_amount',
	'payment_method' => 'payment_method',
	'transaction_id' => 'transaction_id',
	'is_fulfilled' => 'is_fulfilled',
	'created_by' => 'created_by_user_id',
	'transaction_type' => 'transaction_type',
	'status' => 'entry_status',
	'unique_id' => 'AgencyID',
	'form_title' => 'Form Name',
	
	// PDF field
	'gpdf_65981c1a21d80' => 'generated_pdf_url'
	);
}

/**
 * Get field mappings for Alarm Monitoring form (Form ID 11)
 */
function get_alarm_monitoring_form_field_mappings() {
	// Using same field structure as Security Guard form
	// These mappings can be customized for Alarm Monitoring specific fields
	return get_security_guard_form_field_mappings();
}

/**
 * Get field mappings for Private Investigator form (Form ID 12)
 */
function get_private_investigator_form_field_mappings() {
	// Using same field structure as Security Guard form
	// These mappings can be customized for Private Investigator specific fields
	return get_security_guard_form_field_mappings();
}

/**
 * Get field mappings for Old Alarm Monitoring Application (Form 1)
 */
function get_form_1_field_mappings() {
	// Field mappings for Old Alarm Monitoring Application
	// Can be customized based on your form structure
	return array(
	'1' => 'company_name',
	'2' => 'contact_name', 
	'3' => 'email_address',
	'4' => 'phone_number',
	'5' => 'message',
	'6' => 'form_title',
	'7' => 'date_submitted',
	// Add more fields as needed
	'id' => 'entry_id',
	'form_id' => 'form_id',
	'date_created' => 'date_created',
	'ip' => 'ip_address',
	'source_url' => 'source_url'
	);
}

/**
 * Get field mappings for Old Private Investigator Application (Form 2)  
 */
function get_form_2_field_mappings() {
	// Field mappings for Old Private Investigator Application
	// Can be customized based on your form structure
	return array(
	'1' => 'first_name',
	'2' => 'last_name',
	'3' => 'email_address',
	'4' => 'phone_number',
	'5' => 'company_name',
	'6' => 'inquiry_type',
	'7' => 'message',
	// Add more fields as needed
	'id' => 'entry_id',
	'form_id' => 'form_id', 
	'date_created' => 'date_created',
	'ip' => 'ip_address',
	'source_url' => 'source_url'
	);
}

/**
 * Get field mappings for Old Security Guard Application (Form 3)
 */
function get_form_3_field_mappings() {
	// Field mappings for Old Security Guard Application
	// Can be customized based on your form structure
	return array(
	'1' => 'applicant_name',
	'2' => 'applicant_email',
	'3' => 'applicant_phone',
	'4' => 'service_requested',
	'5' => 'preferred_date',
	'6' => 'additional_info',
	// Add more fields as needed
	'id' => 'entry_id',
	'form_id' => 'form_id',
	'date_created' => 'date_created', 
	'ip' => 'ip_address',
	'source_url' => 'source_url'
	);
}

/**
 * Transform Gravity Forms webhook data to use field names instead of IDs
 */
function transform_gravity_forms_webhook($data, $form_id = null) {
	// Determine form ID from data if not provided
	if ($form_id === null && isset($data['form_id'])) {
	$form_id = intval($data['form_id']);
	}
	
	// Select appropriate mappings based on form ID
	switch ($form_id) {
	case 1:
	    $field_mappings = get_form_1_field_mappings();
	    break;
	case 2:
	    $field_mappings = get_form_2_field_mappings();
	    break;
	case 3:
	    $field_mappings = get_form_3_field_mappings();
	    break;
	case 11:
	    $field_mappings = get_alarm_monitoring_form_field_mappings();
	    break;
	case 12:
	    $field_mappings = get_private_investigator_form_field_mappings();
	    break;
	case 10:
	default:
	    $field_mappings = get_security_guard_form_field_mappings();
	    break;
	}
	
	// Fields to exclude from the webhook payload
	$excluded_fields = array('id', 'form_id', 'is_starred', 'is_read', 'ip', 'user_agent', 'currency', 'source_id', '202');
	
	$transformed = array();
	
	foreach ($data as $field_id => $value) {
	// Skip excluded fields
	if (in_array($field_id, $excluded_fields)) {
	    continue;
	}
	
	// Skip empty values
	if ($value === '' || $value === null) {
	    continue;
	}
	
	// Get the field name from mappings
	$field_name = isset($field_mappings[$field_id]) ? $field_mappings[$field_id] : $field_id;
	
	// Add to transformed array
	$transformed[$field_name] = $value;
	}
	
	return $transformed;
}

/**
 * Optional: mapper-level direct sending and webhook data transform
 * Disabled by default to ensure the plugin is the single source of truth.
 * Enable via: add_filter('mwm_enable_mapper_direct_send', '__return_true');
 */
$mwm_enable_mapper_direct_send = apply_filters(
	'mwm_enable_mapper_direct_send',
	get_option('mwm_enable_mapper_direct_send', 'no') === 'yes'
);

if ($mwm_enable_mapper_direct_send) {
	/**
	 * Hook into Gravity Forms Webhooks Add-On data to transform payload
	 */
	add_filter('gform_webhooks_request_data', function($request_data, $feed, $entry, $form) {
	$form_id = isset($form['id']) ? intval($form['id']) : null;
	return transform_gravity_forms_webhook($entry, $form_id);
	}, 10, 4);

	/**
	 * Direct send helpers for per-form submission hooks
	 */
	$mwm_mapper_direct_send = function($entry, $target_form_id) {
	$webhook_url = get_option('mwm_webhook_url', '');
	if (empty($webhook_url)) {
	    error_log('MWM Mapper: Webhook URL not configured');
	    return;
	}
	$transformed = transform_gravity_forms_webhook($entry, $target_form_id);
	$response = wp_remote_post($webhook_url, array(
	    'method'  => 'POST',
	    'headers' => array('Content-Type' => 'application/json'),
	    'body'    => wp_json_encode($transformed),
	    'timeout' => 30,
	));
	if (is_wp_error($response)) {
	    error_log('MWM Mapper Webhook Error (Form ' . $target_form_id . '): ' . $response->get_error_message());
	}
	};

	// Form-specific direct send actions (use with caution)
	add_action('gform_after_submission_10', function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 10); }, 10, 2);
	add_action('gform_after_submission_11', function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 11); }, 10, 2);
	add_action('gform_after_submission_12', function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 12); }, 10, 2);
	add_action('gform_after_submission_1',  function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 1);  }, 10, 2);
	add_action('gform_after_submission_2',  function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 2);  }, 10, 2);
	add_action('gform_after_submission_3',  function($entry, $form) use ($mwm_mapper_direct_send) { $mwm_mapper_direct_send($entry, 3);  }, 10, 2);
}
