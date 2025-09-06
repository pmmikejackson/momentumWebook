<?php
/**
 * Gravity Forms Field Mapper for Momentum Webhook
 * Version: 1.0.7
 * 
 * This file maps Gravity Forms field IDs to proper field names
 * for the Security Guard Application forms (Form IDs 10, 11, 12)
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
        '11' => 'type_of_company',
        '12' => 'fein_number',
        '14' => 'license_number',
        '15' => 'years_under_this_name',
        '16' => 'any_other_name',
        '17' => 'height',
        '18' => 'percent_security_service',
        '19' => 'percent_investigations',
        '20' => 'percent_consulting',
        '21' => 'gender',
        '22' => 'marital_status',
        '23' => 'citizenship_status',
        '24' => 'military_service',
        '25' => 'military_branch',
        '26' => 'military_discharge_type',
        '27' => 'military_discharge_date',
        
        // Emergency Contact
        '29' => 'emergency_contact_name',
        '30' => 'emergency_contact_relationship',
        '31' => 'emergency_contact_phone',
        '32' => 'emergency_contact_address',
        
        // Employment History
        '33' => 'current_employer',
        '34' => 'current_position',
        '37' => 'employer_phone',
        '38' => 'employer_address',
        '39' => 'employment_start_date',
        '40' => 'employment_end_date',
        '41' => 'reason_for_leaving',
        '42' => 'supervisor_name',
        '43' => 'supervisor_phone',
        '44' => 'may_contact_employer',
        
        // Previous Employment
        '45' => 'previous_employer',
        '46' => 'previous_position',
        '47' => 'previous_employer_phone',
        '48' => 'previous_employer_address',
        '49' => 'previous_employment_start_date',
        '50' => 'previous_employment_end_date',
        '51' => 'previous_reason_for_leaving',
        '52' => 'previous_supervisor_name',
        '53' => 'previous_supervisor_phone',
        
        // Education
        '54' => 'high_school_name',
        '55' => 'high_school_graduation_year',
        '56' => 'college_name',
        '57' => 'college_degree',
        '58' => 'college_graduation_year',
        '59' => 'other_education',
        
        // Security Guard Experience
        '60' => 'security_guard_license_number',
        '61' => 'security_guard_license_state',
        '62' => 'security_guard_license_expiration',
        '63' => 'years_security_experience',
        '64' => 'armed_guard_certification',
        '65' => 'firearm_permit_number',
        '66' => 'firearm_permit_expiration',
        '67' => 'security_training_completed',
        '68' => 'cpr_certification',
        '69' => 'first_aid_certification',
        '70' => 'other_certifications',
        
        // References
        '71' => 'reference_1_name',
        '72' => 'reference_1_phone',
        '73' => 'reference_1_email',
        '74' => 'reference_1_relationship',
        '75' => 'reference_1_years_known',
        
        '76' => 'reference_2_name',
        '77' => 'reference_2_phone',
        '78' => 'reference_2_email',
        '79' => 'reference_2_relationship',
        '80' => 'reference_2_years_known',
        
        '81' => 'reference_3_name',
        '82' => 'reference_3_phone',
        '83' => 'reference_3_email',
        '84' => 'reference_3_relationship',
        '85' => 'reference_3_years_known',
        
        // Criminal History
        '86' => 'criminal_convictions',
        '87' => 'conviction_details',
        '88' => 'pending_charges',
        '89' => 'pending_charges_details',
        
        // Availability
        '91' => 'available_monday',
        '92' => 'available_tuesday',
        '93' => 'available_wednesday',
        '94' => 'available_thursday',
        '95' => 'available_friday',
        '96' => 'available_saturday',
        '97' => 'available_sunday',
        '98' => 'available_days',
        '99' => 'available_nights',
        '100' => 'available_weekends',
        '101' => 'available_holidays',
        '102' => 'available_overtime',
        '103' => 'preferred_shift',
        '104' => 'date_available_to_start',
        
        // Additional Information
        '105' => 'how_did_you_hear_about_us',
        '106' => 'referred_by',
        '107' => 'previously_applied',
        '108' => 'previous_application_date',
        '109' => 'previously_employed_here',
        '110' => 'previous_employment_dates',
        '111' => 'reason_for_interest',
        '112' => 'salary_requirements',
        '113' => 'willing_to_relocate',
        '114' => 'willing_to_travel',
        '115' => 'travel_percentage',
        
        // Skills and Qualifications
        '116' => 'computer_skills',
        '117' => 'language_skills',
        '118' => 'special_skills',
        '119' => 'physical_limitations',
        '120' => 'accommodation_needs',
        
        // Legal Authorization
        '121' => 'authorized_to_work_us',
        '122' => 'require_sponsorship',
        '123' => 'age_18_or_older',
        '124' => 'agree_background_check',
        '125' => 'agree_drug_test',
        '126' => 'agree_to_terms',
        
        // Additional Questions
        '127' => 'additional_question_1',
        '128' => 'additional_question_2',
        '129' => 'additional_question_3',
        '130' => 'additional_comments',
        
        // Insurance Related
        '131' => 'insurance_amount',
        '132' => 'insurance_type',
        '133' => 'insurance_carrier',
        '134' => 'policy_number',
        '135' => 'coverage_start_date',
        '136' => 'coverage_end_date',
        '137' => 'premium_amount',
        '138' => 'deductible_amount',
        '139' => 'beneficiary_name',
        '140' => 'beneficiary_relationship',
        '141' => 'beneficiary_contact',
        
        // Additional Fields
        '151' => 'field_151',
        '152' => 'field_152',
        '153' => 'field_153',
        '154' => 'field_154',
        '155' => 'field_155',
        '156' => 'field_156',
        '157' => 'field_157',
        '158' => 'field_158',
        '159' => 'field_159',
        '160' => 'field_160',
        '161' => 'field_161',
        '162' => 'field_162',
        '164' => 'field_164',
        '165' => 'field_165',
        '167' => 'field_167',
        '168' => 'application_date',
        '169' => 'field_169',
        '170' => 'field_170',
        '171' => 'field_171',
        '172' => 'field_172',
        '173' => 'field_173',
        
        // Additional compound fields
        '174' => 'compound_field_174',
        '174.1' => 'compound_174_part_1',
        '174.2' => 'compound_174_part_2',
        '174.3' => 'compound_174_part_3',
        '174.4' => 'compound_174_part_4',
        '174.5' => 'compound_174_part_5',
        '174.6' => 'compound_174_part_6',
        '174.7' => 'compound_174_part_7',
        
        '175' => 'compound_field_175',
        '175.1' => 'compound_175_part_1',
        '175.2' => 'compound_175_part_2',
        '175.3' => 'compound_175_part_3',
        '175.4' => 'compound_175_part_4',
        '175.5' => 'compound_175_part_5',
        '175.6' => 'compound_175_part_6',
        
        '176' => 'field_176',
        '177' => 'field_177',
        '178' => 'field_178',
        '179' => 'field_179',
        '181' => 'field_181',
        '182' => 'field_182',
        '183' => 'field_183',
        '184' => 'field_184',
        '185' => 'field_185',
        '186' => 'field_186',
        '187' => 'field_187',
        '188' => 'field_188',
        '189' => 'field_189',
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
        '201' => 'Form Name',
        '202' => 'field_202',
        
        // Name fields
        '203' => 'applicant_name',
        '203.2' => 'applicant_prefix',
        '203.3' => 'applicant_first_name',
        '203.4' => 'applicant_middle_name',
        '203.6' => 'applicant_last_name',
        '203.8' => 'applicant_suffix',
        
        '208' => 'alternate_name',
        '208.2' => 'alternate_prefix',
        '208.3' => 'alternate_first_name',
        '208.4' => 'alternate_middle_name',
        '208.6' => 'alternate_last_name',
        '208.8' => 'alternate_suffix',
        
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
        'user_agent' => 'user_agent',
        'currency' => 'currency',
        'payment_status' => 'payment_status',
        'payment_date' => 'payment_date',
        'payment_amount' => 'payment_amount',
        'payment_method' => 'payment_method',
        'transaction_id' => 'transaction_id',
        'is_fulfilled' => 'is_fulfilled',
        'created_by' => 'created_by_user_id',
        'transaction_type' => 'transaction_type',
        'status' => 'entry_status',
        'source_id' => 'source_id',
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
 * Transform Gravity Forms webhook data to use field names instead of IDs
 */
function transform_gravity_forms_webhook($data, $form_id = null) {
    // Determine form ID from data if not provided
    if ($form_id === null && isset($data['form_id'])) {
        $form_id = intval($data['form_id']);
    }
    
    // Select appropriate mappings based on form ID
    switch ($form_id) {
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
    
    $transformed = array();
    
    foreach ($data as $field_id => $value) {
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
 * Hook into Gravity Forms webhook to transform the data
 */
add_filter('gform_webhooks_request_data', function($request_data, $feed, $entry, $form) {
    // Get form ID from the form array
    $form_id = isset($form['id']) ? intval($form['id']) : null;
    
    // Transform the entry data with form ID
    $transformed_data = transform_gravity_forms_webhook($entry, $form_id);
    
    // Return the transformed data
    return $transformed_data;
}, 10, 4);

/**
 * Alternative: Create custom actions for each form that sends properly formatted data
 */

// Form 10: Security Guard Application
add_action('gform_after_submission_10', function($entry, $form) {
    // Transform the entry data with form ID
    $transformed_data = transform_gravity_forms_webhook($entry, 10);
    
    // Send to your webhook endpoint
    $webhook_url = 'YOUR_MOMENTUM_WEBHOOK_URL_HERE';
    
    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($transformed_data),
        'timeout' => 30
    ));
    
    // Log the response if needed
    if (is_wp_error($response)) {
        error_log('Webhook Error (Form 10): ' . $response->get_error_message());
    }
}, 10, 2);

// Form 11: Alarm Monitoring Application
add_action('gform_after_submission_11', function($entry, $form) {
    // Transform the entry data with form ID
    $transformed_data = transform_gravity_forms_webhook($entry, 11);
    
    // Send to your webhook endpoint
    $webhook_url = 'YOUR_MOMENTUM_WEBHOOK_URL_HERE';
    
    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($transformed_data),
        'timeout' => 30
    ));
    
    // Log the response if needed
    if (is_wp_error($response)) {
        error_log('Webhook Error (Form 11): ' . $response->get_error_message());
    }
}, 10, 2);

// Form 12: Private Investigator Application
add_action('gform_after_submission_12', function($entry, $form) {
    // Transform the entry data with form ID
    $transformed_data = transform_gravity_forms_webhook($entry, 12);
    
    // Send to your webhook endpoint
    $webhook_url = 'YOUR_MOMENTUM_WEBHOOK_URL_HERE';
    
    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($transformed_data),
        'timeout' => 30
    ));
    
    // Log the response if needed
    if (is_wp_error($response)) {
        error_log('Webhook Error (Form 12): ' . $response->get_error_message());
    }
}, 10, 2);