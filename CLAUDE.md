# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Information

- **GitHub Account**: pmmikejackson
- **Repository**: momentumWebook

## Project Overview

This is a WordPress plugin/code snippet that enhances webhook payloads by converting field IDs to human-readable field names. It specifically focuses on Gravity Forms integration for Security Guard, Alarm Monitoring, and Private Investigator application forms (Form IDs 10, 11, 12).

**Current Version**: 1.0.9  
**Branch**: feature/update-field-mappings

## Architecture

### Main Components

1. **gravity-forms-field-mapper.php** - Core PHP file containing:
   - Field mapping arrays for forms (200+ field ID-to-name mappings)
   - `transform_gravity_forms_webhook()` - Main transformation function
   - WordPress hooks for `gform_webhooks_request_data` filter
   - Custom actions for form-specific webhook handling (`gform_after_submission_10/11/12`)

2. **field-mappings.json** - Configuration file with generic field mapping structures for WooCommerce, WordPress Core, Gravity Forms, and Contact Form 7

### Key Functions

- `get_security_guard_form_field_mappings()` - Returns field mappings for Form ID 10
- `get_alarm_monitoring_form_field_mappings()` - Returns field mappings for Form ID 11  
- `get_private_investigator_form_field_mappings()` - Returns field mappings for Form ID 12
- `transform_gravity_forms_webhook($data, $form_id)` - Transforms field IDs to field names

## Development Commands

This is a WordPress plugin with no build process. To develop:

1. **Installation**: Copy `gravity-forms-field-mapper.php` to WordPress plugins directory or add to theme's `functions.php`

2. **Testing**: No automated tests. Manual testing via WordPress admin or using test webhook payloads

3. **Deployment**: Direct file upload to WordPress installation

## Version Management

**IMPORTANT RULE**: Always increment the version number in `gravity-forms-field-mapper.php` when building a release:
- Update the version comment at the top of the file (e.g., `Version: 1.0.9` → `Version: 1.0.10`)
- Create a corresponding release zip file with the new version number
- Create a GitHub release with detailed release notes
- Tag releases appropriately (e.g., `v1.0.9`)

Current versioning pattern: `1.0.x` for feature updates and field mapping changes.

## Field Mapping Structure

The plugin is organized into 5 main sections:

### Section 1 - General Information
- Basic company information, addresses, contact details
- Personal information and business type classifications

### Section 2 - Operations  
- Supervisor duties, officer counts, equipment usage
- Annual billing information for armed/unarmed services

### Section 3 - Payroll Details
- Comprehensive armed/unarmed payroll classifications
- Wage information and contractor details

### Section 4 - Description of Operations
- Detailed work descriptions for various scenarios (airport, retail, etc.)
- Special event and consulting work details

### Section 5 - Current Insurance Information  
- Coverage details, claims history, liability limits
- Premium and deductible information

### Key Field Examples:
- System fields: `unique_id` → `AgencyID`, `form_title` → `form_name`
- Name fields with subfields (e.g., `203.3` → `applicant_first_name`)
- Address fields with subfields (e.g., `2.3` → `city`)
- PDF field: `gpdf_65981c1a21d80` → `generated_pdf_url`
- Excluded fields: `entry_id`, `form_id`, `is_starred`, `is_read`, `ip_address`, `user_agent`, `currency`, `source_id`

## Webhook Integration

The plugin intercepts Gravity Forms webhooks and transforms the data before sending to Momentum Now Certs. Replace `YOUR_MOMENTUM_WEBHOOK_URL_HERE` in the custom actions with the actual webhook endpoint.