# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Information

- **GitHub Account**: pmmikejackson
- **Repository**: momentumWebook

## Project Overview

This is a WordPress plugin/code snippet that enhances webhook payloads by converting field IDs to human-readable field names. It specifically focuses on Gravity Forms integration for Security Guard, Alarm Monitoring, and Private Investigator application forms (Form IDs 10, 11, 12).

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

## Important Field Mappings

The plugin maps Gravity Forms field IDs to semantic names:
- System fields: `unique_id` → `AgencyID`, `form_title` → `Form Name`
- Name fields with subfields (e.g., `203.3` → `applicant_first_name`)
- Address fields with subfields (e.g., `2.3` → `city`)
- PDF field: `gpdf_65981c1a21d80` → `generated_pdf_url`

## Webhook Integration

The plugin intercepts Gravity Forms webhooks and transforms the data before sending to Momentum Now Certs. Replace `YOUR_MOMENTUM_WEBHOOK_URL_HERE` in the custom actions with the actual webhook endpoint.