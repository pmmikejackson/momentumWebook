<?php
/**
 * Utility script to retrieve and display Gravity Forms field structure
 * Usage: Run this in WordPress environment to get actual form fields
 */

// Check if Gravity Forms is active
if (!class_exists('GFAPI')) {
    die('Gravity Forms is not active');
}

// Function to get and display form fields
function display_form_fields($form_id) {
    $form = GFAPI::get_form($form_id);
    
    if (!$form) {
        echo "Form ID $form_id not found\n";
        return;
    }
    
    echo "\n=== Form {$form_id}: {$form['title']} ===\n";
    echo "Description: {$form['description']}\n";
    echo "\nFields:\n";
    
    foreach ($form['fields'] as $field) {
        echo "Field ID: {$field->id}\n";
        echo "  Label: {$field->label}\n";
        echo "  Type: {$field->type}\n";
        
        // Handle fields with inputs (like name, address)
        if (!empty($field->inputs)) {
            foreach ($field->inputs as $input) {
                if (!empty($input['label'])) {
                    echo "    Sub-field {$input['id']}: {$input['label']}\n";
                }
            }
        }
        
        // Show additional field properties
        if (!empty($field->adminLabel)) {
            echo "  Admin Label: {$field->adminLabel}\n";
        }
        if (!empty($field->choices)) {
            echo "  Choices: " . count($field->choices) . " options\n";
        }
        
        echo "\n";
    }
}

// Display fields for PI forms
echo "=== PRIVATE INVESTIGATOR FORMS ===\n";

// Form 2 - Old Private Investigator
display_form_fields(2);

// Form 12 - Private Investigator  
display_form_fields(12);

// Also show Security Guard form for comparison
echo "\n=== SECURITY GUARD FORM (for reference) ===\n";
display_form_fields(10);