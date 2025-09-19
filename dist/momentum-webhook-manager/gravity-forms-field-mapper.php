<?php
/**
 * Gravity Forms Field Mapper for Momentum Webhook
 * Version: 1.3.2
 *
 * @package MomentumWebhookManager
 * 
 * This file maps Gravity Forms field IDs to proper field names
 * for all supported forms: Old Alarm Monitoring (1), Old Private Investigator (2), 
 * Old Security Guard (3), Security Guard (10), Alarm Monitoring (11), Private Investigator (12)
 * 
 * Release Notes:
 * 
 * Version 1.3.2 (Current)
 * - Version bump to align with plugin 1.3.2
 *
 * Version 1.3.1
 * - Aligned version with plugin 1.3.1
 * - Fixed duplicate armed payroll key (95/96)
 * - Normalized limit_of_liability label, fixed lawsuit_claims_details typo
 *
 * Version 1.2.2
 * - Fixed Private Investigator form mappings (Forms 2 and 12)
 * - Both PI forms now use comprehensive field mappings with 100+ fields
 * - Fixed switch statement to properly handle Form 10 (Security Guard)
 * - Added error logging for unknown form IDs
 * - Removed incorrect mapping where PI forms were using Security Guard fields
 * 
 * Version 1.2.1
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
	'96' => 'a_car_dealers',
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
	'156' => 'limit_of_liability',
	'157' => 'occurence_form',
	'158' => 'declined_coverage',
	'159' => 'declined_coverage_details',
	'160' => 'req_incidents',
	'161' => 'lawsuit_claims',
	'162' => 'lawsuit_claims_details',
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
	'216' => 'business_start_date',
	
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
 * Pair: Form 11 (current) and Form 1 (legacy) share this mapping.
 * NOTE: Populate this array with the Alarm Monitoring fields. Currently
 * it may share structure with Security Guard but should remain distinct.
 */
function get_alarm_monitoring_form_field_mappings() {
    return array(
        '95' => 'agencyid',
        '94' => 'form_name',
        '77' => 'section_i__general_information',
        '1' => 'applicant_legal_name_including_dbas',
        '2' => 'name_to_appear_on_company_license',
        '3' => 'owner',
        '4' => 'contact_persontitle',
        '5' => 'phone',
        '6' => 'fax',
        '7' => 'email_address',
        '8' => 'mailing_address',
        '8.1' => 'street_address',
        '8.2' => 'address_line_2',
        '8.3' => 'city',
        '8.4' => 'state__province__region',
        '8.5' => 'zip__postal_code',
        '8.6' => 'country',
        '9' => 'physical_address',
        '9.1' => 'street_address',
        '9.2' => 'address_line_2',
        '9.3' => 'city',
        '9.4' => 'state__province__region',
        '9.5' => 'zip__postal_code',
        '9.6' => 'country',
        '10' => 'company_license_numberss',
        '79' => 'check_one',
        '79.1' => 'individual',
        '79.2' => 'partnership',
        '79.3' => 'corporation',
        '79.4' => 'other',
        '11' => 'number_of_years_in_business',
        '12' => 'fein_',
        '80' => 'html_block',
        '13' => 'from',
        '14' => 'to',
        '81' => 'html_block',
        '15' => 'occurrence',
        '17' => 'productscompleted_operations',
        '18' => 'aggregate',
        '82' => 'section_ii__total_estimated_annual_revenue',
        '19' => 'estimated_revenue_for_next_12_months_not_including_monitoring_revenue',
        '20' => 'estimated_monitoring_revenue',
        '21' => '_of_revenue_generated_by_jobs_subcontracted_to_insured_companies',
        '22' => 'number_of_technicians_not_including_owner',
        '23' => 'annual_payroll_for_technicians_not_including_clericaladminowners',
        '24' => 'revenue_for_previous_12_months',
        '25' => 'owners_payroll_only_if_field_work_is_performed_by_owners',
        '83' => 'section_iii__current_insurance_information',
        '26' => 'carrier',
        '27' => 'expiration_date',
        '28' => 'general_aggregate',
        '84' => 'occurrence',
        '85' => 'html_block',
        '29' => 'prior_year',
        '30' => '1st_prior_year',
        '31' => '2nd_prior_year',
        '32' => '3rd_prior_year',
        '33' => '4th_prior_year',
        '34' => 'premium',
        '86' => 'section_iv__operations',
        '35' => 'do_you_operate_in_any_other_states',
        '36' => 'please_list',
        '87' => 'html_block',
        '37' => '_fire_alarm',
        '38' => '_burglar_alarm',
        '39' => '_combination',
        '40' => '_home_theater',
        '41' => '_medical_alert',
        '42' => '_temp_control',
        '43' => '_closed_circuit',
        '44' => 'preconstruction_wiringconduit',
        '45' => '_other',
        '88' => 'html_block',
        '46' => '_commercial',
        '47' => '_apartments',
        '48' => '_industrial',
        '49' => '_single_family',
        '50' => '_institutional',
        '51' => '_condos',
        '52' => '_new_home_builders',
        '89' => 'html_block',
        '53' => 'description',
        '54' => 'tract_homes_condos_townhouses',
        '55' => 'custom_homes',
        '56' => 'total_number_of_customers',
        '57' => 'name_under_contract',
        '90' => 'html_block',
        '58' => 'nursing_homes',
        '59' => 'medical_facilities',
        '60' => 'correctional_facilities',
        '61' => 'detection_facilities',
        '62' => 'if_yes_what_percentage_of_your_total_work_is_designated_to_this',
        '63' => 'does_your_company_do_its_own_monitoring',
        '64' => 'if_no_please_provide_the_name_of_monitoring_company_detection_facilities',
        '91' => 'section_v__alarm_response',
        '65' => 'do_you_provide_securitypatrol_response_to_your_customers_if_and_when_policefireemts_do_not_respond',
        '66' => 'if_yes_are_the_responders_employees_or_hiredcontracted_for_this_service',
        '67' => 'fully_describe_alarm_response_procedures',
        '68' => 'if_responders_are_not_employees_do_you_have_a_written_contract_with_the_security_company_that_provides_the_response',
        '69' => 'if_you_have_a_contract_with_the_security_company_is_either_part_holding_the_other_harmlessproviding_indemnification',
        '70' => 'if_yes_provide_details',
        '71' => 'do_any_employees_or_subcontractors_carry_firearms',
        '92' => 'signage',
        '72' => 'completed_by',
        '73' => 'title',
        '74' => 'date',
        '93' => 'signature',
    );
}

/**
 * Get field mappings for Private Investigator form (Form ID 12)
 */
function get_private_investigator_form_field_mappings() {
    return array(
        // Custom meta fields
        '125' => 'agencyid',
        '124' => 'form_name',

        // Section I - General Information
        '106' => 'section_i__general_information',
        '1' => 'insureds__name_including_dbas',
        '2' => 'mailing_address',
        '2.1' => 'street_address',
        '2.2' => 'address_line_2',
        '2.3' => 'city',
        '2.4' => 'state__province__region',
        '2.5' => 'zip__postal_code',
        '2.6' => 'country',
        '3' => 'physical_address',
        '3.1' => 'street_address',
        '3.2' => 'address_line_2',
        '3.3' => 'city',
        '3.4' => 'state__province__region',
        '3.5' => 'zip__postal_code',
        '3.6' => 'country',
        '4' => 'contact_name',
        '5' => 'title',
        '6' => 'phone',
        '7' => 'cell',
        '8' => 'effective_date_desired',
        '9' => 'check_one',
        '107' => 'html_block',
        '10' => 'occurence',
        '11' => 'aggregate',
        '12' => 'email_address',
        '13' => 'date_company_established',
        '14' => 'what_background_do_the_principals_of_this_organization_have_in_the_investigative_industry',
        '15' => 'federal_id_number_fein',
        '16' => 'license_number',
        '112' => 'html_block',
        '17' => 'does_applicant_subcontract_work_to_others',
        '18' => 'do_subcontractors_maintain_their_own_insurance',
        '19' => 'are_certificates_of_insurance_required_from_subcontractors',
        '20' => 'do_you_require_subcontractors_to_name_you_as_an_additional_insured_on_their_policies',
        '21' => 'annual_subcontractor_cost',
        '22' => 'does_your_firm_provide_any_type_of_security_guard_or_alarm_operations',
        '23' => 'if_yes_please_explain',
        '24' => 'please_provide_a_list_of_your_types_of_clients_along_with_a_description_of_services',

        // Section II - Operations
        '108' => 'section_ii__operations',
        '25' => 'total_number_of_owners',
        '26' => 'number_of_owners_performing_investigations',
        '28' => 'number_of_owners_that_work_250_hours_or_less_per_year',
        '29' => 'number_of_owners_that_work_251_to_450_hours_per_year',
        '30' => 'number_of_owners_that_work_451_hours_or_more_per_year',
        '31' => 'number_of_employees_performing_or_involved_with_investigations',
        '32' => 'number_of_investigation_employees_that_work_250_hours_or_less_per_year',
        '33' => 'number_of_investigation_employees_that_work_251_hours_to_450_hours_per_year',
        '123' => 'number_of_investigation_employees_that_work_451_hours_or_more_per_year',
        '35' => 'annual_corporate_revenue',
        '36' => 'total_employee_payroll',
        '113' => 'employee_training_consists_of',
        '113.1' => 'written_manual',
        '113.2' => 'report_writing',
        '113.3' => 'firearms',
        '113.4' => 'cpr',
        '113.5' => 'powers_of_arrest',
        '113.6' => 'on_the_job',
        '113.7' => 'other',
        '114' => 'preemployment_screening_procedures_for_employees_check_all_that_apply',
        '114.1' => 'driving_record_mvr',
        '114.2' => 'background_check',
        '114.3' => 'drug_screening',
        '114.4' => 'fingerprint_check',
        '114.5' => 'personal_references',
        '114.6' => 'other',

        // Section II - Operations Continued
        '109' => 'section_ii__operations_continued',
        '115' => 'html_block',
        '39' => 'accident_investigationsreconstruction',
        '40' => 'asset_searches',
        '41' => 'background_investigations',
        '42' => 'bank__accounting_fraud',
        '43' => 'child_recoverycustody',
        '44' => 'computer_crime',
        '45' => 'creditpreemployment',
        '46' => 'domestic_matrimonialdivorce',
        '47' => 'environmental',
        '48' => 'executive_protection',
        '49' => 'expert_witness',
        '50' => 'fire__arson',
        '51' => 'insurance_investigations',
        '52' => 'legal_investigations',
        '53' => 'missing_persons__heirs',
        '54' => 'process_serving',
        '55' => 'record_services',
        '56' => 'repossessions',
        '57' => 'shopping_services',
        '58' => 'skip_tracing__collections',
        '59' => 'surveillance__electronic',
        '60' => 'wc__fraud_investigations',
        '61' => 'white_collar_crimes',
        '62' => 'other',
        '116' => 'html_block',
        '63' => 'construction_design',
        '64' => 'criminal',
        '65' => 'data__computer_security',
        '66' => 'kidnap__terrorist',
        '67' => 'seminars__lectures',
        '68' => 'terrorism',
        '69' => 'threat_assessments',
        '70' => 'other',
        '117' => 'html_block',
        '71' => 'paper__pen__pencil',
        '72' => 'polygraph',
        '73' => 'psychological_stress_evaluator',
        '74' => 'other',
        '118' => 'html_block',
        '75' => '_firearms_trainingclassroom_studentsyr',
        '76' => '_securityclassroom_studentsyr',
        '77' => '_firearms_trainingfiring_range_studentsyr',
        '78' => '_other_studentsyr',
        '119' => 'html_block',

        // Section III - Description of Operations
        '110' => 'section_iii__description_of_operations_if_applicable',
        '79' => 'accident_investigationsreconstruction__please_describe_all_operations_below',
        '80' => 'any_fault_assessment',
        '81' => 'executive_protection__please_describe_all_duties_performed_below',
        '82' => 'any_athletes_celebrities_or_entertainers',
        '83' => 'expert_witness__do_you_provide_court_testimony_as_an_expert_for_cases_that_you_are_not_investigating',
        '84' => 'if_yes_please_describe_all_operationsduties_performed',
        '85' => 'provide_resume',
        '86' => 'firearson__please_describe_all_operationsduties_performed_below',
        '87' => 'any_cause_of_origin',
        '88' => 'shopping_services__please_describe_events_locations_and_duties',
        '89' => 'security_consulting__please_describe_clients_scope_of_services_performed',
        '90' => 'provide_a_sample_contract',
        '91' => 'other__please_describe_all_operationsduties_performed',

        // Section IV - Current Insurance Information
        '111' => 'section_iv__current_insurance_information',
        '92' => 'current_carrier',
        '93' => 'inception_date',
        '94' => 'expiration_date',
        '95' => 'premium',
        '96' => 'deductible',
        '97' => 'limit_of_liability',
        '98' => 'occurrence_form',
        '99' => 'have_there_been_any_claims_or_lawsuits_in_the_past_5_years',
        '120' => 'if_yes_please_attach_statement_of_losses',
        '101' => 'do_you_anticipate_any_future_claimslosses',
        '121' => 'signage',
        '102' => 'applicant_name',
        '103' => 'applicant_title',
        '104' => 'date',
        '122' => 'signature',

        // System fields (optional)
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
        'status' => 'entry_status'
    );
}

/**
 * Get field mappings for Old Alarm Monitoring Application (Form 1)
 */
function get_form_1_field_mappings() {
	// Legacy Alarm Monitoring should share Form 11 mappings
	return get_alarm_monitoring_form_field_mappings();
}

/**
 * Get field mappings for Old Private Investigator Application (Form 2)  
 */
function get_form_2_field_mappings() {
	// Form 2 uses the same field mappings as Form 12 (Private Investigator)
	return get_private_investigator_form_field_mappings();
}

/**
 * Get field mappings for Old Security Guard Application (Form 3)
 */
function get_form_3_field_mappings() {
	// Legacy Security Guard should share Form 10 mappings
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
    $mapping_name = 'unknown';
    switch ($form_id) {
    case 1:
        $field_mappings = get_form_1_field_mappings();
        $mapping_name = 'alarm_monitoring_legacy_1';
        break;
    case 2:
        $field_mappings = get_form_2_field_mappings();
        $mapping_name = 'private_investigator_legacy_2';
        break;
    case 3:
        $field_mappings = get_form_3_field_mappings();
        $mapping_name = 'security_guard_legacy_3';
        break;
    case 10:
        $field_mappings = get_security_guard_form_field_mappings();
        $mapping_name = 'security_guard_10';
        break;
    case 11:
        $field_mappings = get_alarm_monitoring_form_field_mappings();
        $mapping_name = 'alarm_monitoring_11';
        break;
    case 12:
        $field_mappings = get_private_investigator_form_field_mappings();
        $mapping_name = 'private_investigator_12';
        break;
    default:
        // For unknown form IDs, return empty array or log warning
        $field_mappings = array();
        error_log('MWM Warning: Unknown form ID ' . $form_id . ' - no field mappings available');
        break;
    }

    if (function_exists('mwm_log')) {
        // Log selected mapping and a small sample of input keys
        $keys = array_keys($data);
        $sample = array_slice($keys, 0, 6);
        mwm_log('Mapper: using ' . $mapping_name . ' for form_id=' . $form_id . ' | sample keys: ' . implode(',', $sample));
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
