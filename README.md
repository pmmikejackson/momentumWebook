# Momentum Webhook Field Name Enhancer

This WordPress code snippet enhances your webhook payloads to include field names along with field IDs and values, making it easier for Momentum Now Certs to process your data.

## Problem Solved

When WordPress sends webhook data, it often only sends field IDs (like `123`) instead of human-readable field names (like "Product Name" or "Customer Email"). This makes it difficult for external systems like Momentum Now Certs to understand what the data represents.

## Solution

This code snippet automatically intercepts webhook payloads and enhances them by:
- Converting field IDs to readable field names
- Maintaining the original data structure
- Adding field mappings for easy reference
- Supporting WooCommerce, Gravity Forms, Contact Form 7, and generic WordPress data

## Installation

### Option 1: Add to functions.php (Recommended for testing)

1. Go to your WordPress admin panel
2. Navigate to **Appearance > Theme Editor**
3. Click on **functions.php** in the right sidebar
4. Add the entire code from `momentum-webhook-simple.php` at the end of the file
5. Click **Update File**

### Option 2: Create a Custom Plugin

1. Create a new folder in `/wp-content/plugins/momentum-webhook-enhancer/`
2. Copy `momentum-webhook-simple.php` into that folder
3. Rename the file to `momentum-webhook-enhancer.php`
4. Activate the plugin from **Plugins > Installed Plugins**

## How It Works

The enhancer works by:

1. **Intercepting webhook data** before it's sent to Momentum Now Certs
2. **Analyzing the payload** to identify field IDs
3. **Converting IDs to names** using WordPress functions
4. **Restructuring the data** to include both IDs and names
5. **Sending the enhanced payload** with all the information

## Enhanced Payload Structure

Your webhook payloads will now look like this:

### Before (Original):
```json
{
  "product_id": 123,
  "customer_id": 456,
  "order_status": "completed"
}
```

### After (Enhanced):
```json
{
  "original_data": {
    "product_id": 123,
    "customer_id": 456,
    "order_status": "completed"
  },
  "enhanced_data": {
    "product_id": {
      "id": 123,
      "name": "Premium Widget Pro",
      "value": 123,
      "context": "product_id"
    },
    "customer_id": {
      "id": 456,
      "name": "John Doe",
      "value": 456,
      "context": "customer_id"
    },
    "order_status": "completed"
  },
  "field_mappings": {
    "product_id": {
      "id": 123,
      "name": "Premium Widget Pro",
      "type": "numeric_id"
    },
    "customer_id": {
      "id": 456,
      "name": "John Doe",
      "type": "numeric_id"
    },
    "order_status": {
      "value": "completed",
      "type": "string"
    }
  },
  "enhanced_at": "2024-01-15 10:30:00"
}
```

## Supported Field Types

### WooCommerce
- **Products**: Product names from product IDs
- **Orders**: Order numbers from order IDs
- **Customers**: Customer names from user IDs

### Gravity Forms
- **Form Fields**: Field labels from field IDs

### Contact Form 7
- **Form Fields**: Generic field identification

### Generic WordPress
- **Posts/Pages**: Post titles from post IDs
- **Terms**: Term names from term IDs
- **Users**: User display names from user IDs

## Testing

You can test the enhancement using the included test function:

```php
// Test with a sample payload
$test_payload = array(
    'product_id' => 123,
    'customer_id' => 456
);

$result = momentum_test_webhook_enhancement($test_payload);
echo '<pre>' . print_r($result, true) . '</pre>';
```

## Customization

### Adding Custom Field Mappings

You can extend the field name resolution by modifying the `momentum_get_field_name_by_id()` function:

```php
function momentum_get_field_name_by_id($field_id, $context) {
    // Your custom logic here
    if (strpos($context, 'custom_field') !== false) {
        return 'Custom Field: ' . $field_id;
    }
    
    // Call the original function for other cases
    return momentum_get_generic_field_name($field_id, $context);
}
```

### Filtering Specific Webhook Types

To only enhance specific webhook types, modify the filter hooks:

```php
// Only enhance WooCommerce webhooks
add_filter('woocommerce_webhook_payload', 'momentum_enhance_webhook_payload', 10, 1);

// Remove other hooks
// add_filter('wp_webhook_payload', 'momentum_enhance_webhook_payload', 10, 1);
```

## Troubleshooting

### Webhook Not Enhanced
1. Check if the code is properly added to your site
2. Verify that your webhook is using one of the supported hooks
3. Enable WordPress debugging to see enhancement logs

### Field Names Not Resolving
1. Ensure the field IDs are valid WordPress IDs
2. Check if the required plugins (WooCommerce, Gravity Forms) are active
3. Verify the context is being properly detected

### Performance Issues
1. The enhancer only processes webhook data, not regular page loads
2. Consider caching field name lookups for frequently used IDs
3. Monitor webhook processing times in your server logs

## Security Considerations

- The enhancer only processes webhook data, not user input
- All WordPress functions used are safe and properly sanitized
- No external API calls are made
- Field name resolution uses WordPress core functions

## Support

If you encounter issues:

1. Check your WordPress error logs
2. Verify the code is properly installed
3. Test with a simple payload first
4. Ensure all required WordPress functions are available

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Access to modify functions.php or create plugins

## License

This code is provided as-is for educational and implementation purposes. Feel free to modify and adapt it to your specific needs.
