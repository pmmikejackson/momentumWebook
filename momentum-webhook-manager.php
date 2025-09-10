<?php
/**
 * Plugin Name: Momentum Webhook Manager
 * Description: Manages automatic sending and manual resending of Gravity Forms entries to Momentum webhook
 * Version: 1.0.0
 * Author: Momentum Integration
 * 
 * This plugin provides:
 * - Automatic webhook sending for forms 10, 11, 12
 * - Admin interface to view and resend entries
 * - Webhook status tracking
 * - Bulk resend capabilities
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('MWM_VERSION', '1.0.0');
define('MWM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MWM_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Include field mapper if it exists as a separate file
 * Otherwise assume it's already loaded via functions.php
 */
if (file_exists(MWM_PLUGIN_PATH . 'gravity-forms-field-mapper.php')) {
    require_once(MWM_PLUGIN_PATH . 'gravity-forms-field-mapper.php');
}

/**
 * Activation hook - create database table for webhook logs
 */
register_activation_hook(__FILE__, 'mwm_activate');
function mwm_activate() {
    // Set default options
    add_option('mwm_webhook_url', '');
    add_option('mwm_auto_send', 'yes');
    add_option('mwm_retry_failed', 'yes');
    add_option('mwm_log_webhooks', 'yes');
    add_option('mwm_max_retries', 3);
}

/**
 * Add admin menu
 */
add_action('admin_menu', 'mwm_add_admin_menu');
function mwm_add_admin_menu() {
    add_menu_page(
        'Momentum Webhooks',
        'Momentum Webhooks',
        'manage_options',
        'mwm-webhook-manager',
        'mwm_admin_page',
        'dashicons-update',
        30
    );
    
    add_submenu_page(
        'mwm-webhook-manager',
        'Webhook Entries',
        'Entries',
        'manage_options',
        'mwm-webhook-manager',
        'mwm_admin_page'
    );
    
    add_submenu_page(
        'mwm-webhook-manager',
        'Webhook Settings',
        'Settings',
        'manage_options',
        'mwm-settings',
        'mwm_settings_page'
    );
}

/**
 * Main admin page - Entry management
 */
function mwm_admin_page() {
    // Handle single resend
    if (isset($_POST['resend_entry']) && wp_verify_nonce($_POST['mwm_nonce'], 'mwm_resend')) {
        $entry_id = intval($_POST['entry_id']);
        $form_id = intval($_POST['form_id']);
        $result = mwm_send_entry_to_webhook($entry_id, $form_id, true);
        
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    // Handle bulk resend
    if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'resend' && wp_verify_nonce($_POST['mwm_nonce'], 'mwm_bulk')) {
        if (!empty($_POST['entries'])) {
            $success_count = 0;
            $fail_count = 0;
            
            foreach ($_POST['entries'] as $entry_data) {
                list($entry_id, $form_id) = explode(':', $entry_data);
                $result = mwm_send_entry_to_webhook(intval($entry_id), intval($form_id), true);
                
                if ($result['success']) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
            }
            
            $message = sprintf('Bulk resend completed: %d successful, %d failed', $success_count, $fail_count);
            echo '<div class="notice notice-info"><p>' . esc_html($message) . '</p></div>';
        } else {
            echo '<div class="notice notice-warning"><p>No entries selected for bulk action.</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>Momentum Webhook Manager</h1>
        
        <?php
        $webhook_url = get_option('mwm_webhook_url', '');
        if (empty($webhook_url)) {
            echo '<div class="notice notice-warning"><p><strong>Warning:</strong> Webhook URL not configured. <a href="' . admin_url('admin.php?page=mwm-settings') . '">Configure Settings</a></p></div>';
        }
        ?>
        
        <div class="notice notice-info">
            <p><strong>Supported Forms:</strong> Security Guard (ID: 10), Alarm Monitoring (ID: 11), Private Investigator (ID: 12)</p>
        </div>
        
        <?php
        // Filters
        $selected_form = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $selected_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $page_num = isset($_GET['pagenum']) ? intval($_GET['pagenum']) : 1;
        $per_page = 25;
        ?>
        
        <form method="get" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="mwm-webhook-manager" />
            
            <select name="form_id" onchange="this.form.submit()">
                <option value="0">All Forms</option>
                <option value="10" <?php selected($selected_form, 10); ?>>Security Guard (10)</option>
                <option value="11" <?php selected($selected_form, 11); ?>>Alarm Monitoring (11)</option>
                <option value="12" <?php selected($selected_form, 12); ?>>Private Investigator (12)</option>
            </select>
            
            <select name="status" onchange="this.form.submit()">
                <option value="all">All Statuses</option>
                <option value="sent" <?php selected($selected_status, 'sent'); ?>>Sent</option>
                <option value="not_sent" <?php selected($selected_status, 'not_sent'); ?>>Not Sent</option>
                <option value="failed" <?php selected($selected_status, 'failed'); ?>>Failed</option>
            </select>
        </form>
        
        <?php
        // Get entries
        $search_criteria = array('status' => 'active');
        $sorting = array('key' => 'date_created', 'direction' => 'DESC');
        $paging = array('offset' => ($page_num - 1) * $per_page, 'page_size' => $per_page);
        
        // Collect entries from supported forms
        $all_entries = array();
        $total_count = 0;
        $form_ids = $selected_form > 0 ? array($selected_form) : array(10, 11, 12);
        
        foreach ($form_ids as $form_id) {
            if (!GFAPI::form_id_exists($form_id)) {
                continue;
            }
            
            $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting);
            
            // Filter by webhook status if needed
            if ($selected_status !== 'all') {
                $filtered_entries = array();
                foreach ($entries as $entry) {
                    $webhook_status = gform_get_meta($entry['id'], 'mwm_webhook_status');
                    
                    if ($selected_status === 'sent' && $webhook_status === 'sent') {
                        $filtered_entries[] = $entry;
                    } elseif ($selected_status === 'not_sent' && empty($webhook_status)) {
                        $filtered_entries[] = $entry;
                    } elseif ($selected_status === 'failed' && $webhook_status === 'failed') {
                        $filtered_entries[] = $entry;
                    }
                }
                $entries = $filtered_entries;
            }
            
            $all_entries = array_merge($all_entries, $entries);
        }
        
        // Sort all entries by date
        usort($all_entries, function($a, $b) {
            return strtotime($b['date_created']) - strtotime($a['date_created']);
        });
        
        $total_count = count($all_entries);
        
        // Apply pagination
        $entries = array_slice($all_entries, ($page_num - 1) * $per_page, $per_page);
        ?>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=mwm-webhook-manager'); ?>">
            <?php wp_nonce_field('mwm_bulk', 'mwm_nonce'); ?>
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action" id="bulk-action-selector">
                        <option value="">Bulk Actions</option>
                        <option value="resend">Resend Selected</option>
                    </select>
                    <input type="submit" name="apply_bulk" class="button action" value="Apply">
                </div>
                
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf('%d items', $total_count); ?></span>
                    <?php
                    $total_pages = ceil($total_count / $per_page);
                    if ($total_pages > 1) {
                        $base_url = add_query_arg(array(
                            'page' => 'mwm-webhook-manager',
                            'form_id' => $selected_form,
                            'status' => $selected_status
                        ), admin_url('admin.php'));
                        
                        echo '<span class="pagination-links">';
                        if ($page_num > 1) {
                            echo '<a class="prev-page" href="' . add_query_arg('pagenum', $page_num - 1, $base_url) . '">‹</a> ';
                        }
                        
                        echo '<span class="paging-input">';
                        echo $page_num . ' of ' . $total_pages;
                        echo '</span>';
                        
                        if ($page_num < $total_pages) {
                            echo ' <a class="next-page" href="' . add_query_arg('pagenum', $page_num + 1, $base_url) . '">›</a>';
                        }
                        echo '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="check-column"><input type="checkbox" id="select-all" /></th>
                        <th>ID</th>
                        <th>Form</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Webhook Status</th>
                        <th>Last Attempt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="9">No entries found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $entry): ?>
                            <?php
                            $form = GFAPI::get_form($entry['form_id']);
                            $company_name = rgar($entry, '1');
                            $email = rgar($entry, '9');
                            $webhook_status = gform_get_meta($entry['id'], 'mwm_webhook_status');
                            $last_attempt = gform_get_meta($entry['id'], 'mwm_last_attempt');
                            $attempt_count = gform_get_meta($entry['id'], 'mwm_attempt_count') ?: 0;
                            ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="entries[]" value="<?php echo esc_attr($entry['id'] . ':' . $entry['form_id']); ?>" />
                                </th>
                                <td><?php echo esc_html($entry['id']); ?></td>
                                <td><?php echo esc_html($form['title']); ?></td>
                                <td><?php echo esc_html($company_name ?: '-'); ?></td>
                                <td><?php echo esc_html($email ?: '-'); ?></td>
                                <td><?php echo date('m/d/Y', strtotime($entry['date_created'])); ?></td>
                                <td>
                                    <?php
                                    if ($webhook_status === 'sent') {
                                        echo '<span style="color: green;">✓ Sent</span>';
                                    } elseif ($webhook_status === 'failed') {
                                        echo '<span style="color: red;">✗ Failed</span>';
                                        if ($attempt_count > 0) {
                                            echo ' <small>(' . $attempt_count . ' attempts)</small>';
                                        }
                                    } else {
                                        echo '<span style="color: orange;">⊙ Not Sent</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($last_attempt) {
                                        // Convert timestamp to WordPress timezone
                                        $date = new DateTime('@' . $last_attempt);
                                        $date->setTimezone(wp_timezone());
                                        echo $date->format('m/d/Y g:i A');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('mwm_resend', 'mwm_nonce'); ?>
                                        <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry['id']); ?>" />
                                        <input type="hidden" name="form_id" value="<?php echo esc_attr($entry['form_id']); ?>" />
                                        <button type="submit" name="resend_entry" class="button button-small">Resend</button>
                                    </form>
                                    <a href="<?php echo admin_url('admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $entry['id']); ?>" 
                                       class="button button-small" target="_blank">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle select all checkbox
        $('#select-all').on('change', function() {
            $('input[name="entries[]"]').prop('checked', this.checked);
        });
        
        // Ensure form submission works properly
        $('input[name="apply_bulk"]').on('click', function(e) {
            var action = $('#bulk-action-selector').val();
            if (action === '') {
                e.preventDefault();
                alert('Please select a bulk action.');
                return false;
            }
            
            var checked = $('input[name="entries[]"]:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Please select at least one entry.');
                return false;
            }
        });
    });
    </script>
    <?php
}

/**
 * Settings page
 */
function mwm_settings_page() {
    // Save settings
    if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['mwm_settings_nonce'], 'mwm_save_settings')) {
        update_option('mwm_webhook_url', sanitize_url($_POST['webhook_url']));
        update_option('mwm_auto_send', isset($_POST['auto_send']) ? 'yes' : 'no');
        update_option('mwm_retry_failed', isset($_POST['retry_failed']) ? 'yes' : 'no');
        update_option('mwm_log_webhooks', isset($_POST['log_webhooks']) ? 'yes' : 'no');
        update_option('mwm_max_retries', intval($_POST['max_retries']));
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    // Test webhook
    if (isset($_POST['test_webhook']) && wp_verify_nonce($_POST['mwm_test_nonce'], 'mwm_test_webhook')) {
        $result = mwm_test_webhook();
        if ($result['success']) {
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
    
    $webhook_url = get_option('mwm_webhook_url', '');
    $auto_send = get_option('mwm_auto_send', 'yes');
    $retry_failed = get_option('mwm_retry_failed', 'yes');
    $log_webhooks = get_option('mwm_log_webhooks', 'yes');
    $max_retries = get_option('mwm_max_retries', 3);
    ?>
    
    <div class="wrap">
        <h1>Momentum Webhook Settings</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('mwm_save_settings', 'mwm_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="webhook_url">Webhook URL</label>
                    </th>
                    <td>
                        <input type="url" name="webhook_url" id="webhook_url" 
                               value="<?php echo esc_attr($webhook_url); ?>" 
                               class="regular-text" style="width: 100%; max-width: 600px;"
                               placeholder="https://your-webhook-endpoint.com/webhook" />
                        <p class="description">The Momentum webhook endpoint URL where form data will be sent.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Automatic Sending</th>
                    <td>
                        <label>
                            <input type="checkbox" name="auto_send" value="yes" 
                                   <?php checked($auto_send, 'yes'); ?> />
                            Automatically send form submissions to webhook
                        </label>
                        <p class="description">When enabled, forms 10, 11, and 12 will automatically send to the webhook upon submission.</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Retry Failed Webhooks</th>
                    <td>
                        <label>
                            <input type="checkbox" name="retry_failed" value="yes" 
                                   <?php checked($retry_failed, 'yes'); ?> />
                            Automatically retry failed webhook sends
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="max_retries">Maximum Retries</label>
                    </th>
                    <td>
                        <input type="number" name="max_retries" id="max_retries" 
                               value="<?php echo esc_attr($max_retries); ?>" 
                               min="1" max="10" />
                        <p class="description">Number of times to retry a failed webhook (1-10).</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">Debug Logging</th>
                    <td>
                        <label>
                            <input type="checkbox" name="log_webhooks" value="yes" 
                                   <?php checked($log_webhooks, 'yes'); ?> />
                            Log webhook attempts to WordPress debug log
                        </label>
                        <p class="description">Useful for troubleshooting webhook issues.</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_settings" class="button-primary" value="Save Settings" />
            </p>
        </form>
        
        <hr />
        
        <h2>Test Webhook Connection</h2>
        <p>Send a test payload to verify your webhook endpoint is working correctly.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('mwm_test_webhook', 'mwm_test_nonce'); ?>
            <input type="submit" name="test_webhook" class="button" value="Send Test Webhook" />
        </form>
        
        <hr />
        
        <h2>Webhook Statistics</h2>
        <?php
        // Get statistics
        $stats = mwm_get_webhook_statistics();
        ?>
        <table class="widefat" style="max-width: 600px;">
            <thead>
                <tr>
                    <th>Form</th>
                    <th>Total Entries</th>
                    <th>Sent</th>
                    <th>Not Sent</th>
                    <th>Failed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats as $form_id => $stat): ?>
                <tr>
                    <td><?php echo esc_html($stat['form_name']); ?></td>
                    <td><?php echo esc_html($stat['total']); ?></td>
                    <td style="color: green;"><?php echo esc_html($stat['sent']); ?></td>
                    <td style="color: orange;"><?php echo esc_html($stat['not_sent']); ?></td>
                    <td style="color: red;"><?php echo esc_html($stat['failed']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Send entry to webhook
 */
function mwm_send_entry_to_webhook($entry_id, $form_id, $manual = false) {
    // Get webhook URL
    $webhook_url = get_option('mwm_webhook_url', '');
    
    if (empty($webhook_url)) {
        return array(
            'success' => false,
            'message' => 'Webhook URL not configured'
        );
    }
    
    // Get entry and form
    $entry = GFAPI::get_entry($entry_id);
    $form = GFAPI::get_form($form_id);
    
    if (is_wp_error($entry) || is_wp_error($form)) {
        return array(
            'success' => false,
            'message' => 'Could not retrieve entry or form'
        );
    }
    
    // Transform data using field mapper
    if (function_exists('transform_gravity_forms_webhook')) {
        $payload = transform_gravity_forms_webhook($entry, $form_id);
    } else {
        $payload = $entry;
    }
    
    // Add metadata
    $payload['_webhook_sent_at'] = current_time('mysql');
    $payload['_webhook_manual'] = $manual;
    
    // Log attempt
    mwm_log('Sending webhook for entry #' . $entry_id . ' (Form #' . $form_id . ')');
    
    // Send webhook
    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($payload),
        'timeout' => 30,
    ));
    
    // Update attempt metadata with current timestamp
    gform_update_meta($entry_id, 'mwm_last_attempt', current_time('timestamp'));
    $attempt_count = gform_get_meta($entry_id, 'mwm_attempt_count') ?: 0;
    gform_update_meta($entry_id, 'mwm_attempt_count', $attempt_count + 1);
    
    if (is_wp_error($response)) {
        // Failed
        gform_update_meta($entry_id, 'mwm_webhook_status', 'failed');
        gform_update_meta($entry_id, 'mwm_last_error', $response->get_error_message());
        
        mwm_log('Webhook failed for entry #' . $entry_id . ': ' . $response->get_error_message());
        
        return array(
            'success' => false,
            'message' => 'Webhook failed: ' . $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code >= 200 && $response_code < 300) {
        // Success
        gform_update_meta($entry_id, 'mwm_webhook_status', 'sent');
        gform_update_meta($entry_id, 'mwm_webhook_sent', current_time('timestamp'));
        gform_update_meta($entry_id, 'mwm_response_code', $response_code);
        
        mwm_log('Webhook successful for entry #' . $entry_id . ' (Response: ' . $response_code . ')');
        
        return array(
            'success' => true,
            'message' => 'Successfully sent entry #' . $entry_id . ' to webhook (Response: ' . $response_code . ')'
        );
    } else {
        // Failed with error code
        gform_update_meta($entry_id, 'mwm_webhook_status', 'failed');
        gform_update_meta($entry_id, 'mwm_last_error', 'HTTP ' . $response_code);
        gform_update_meta($entry_id, 'mwm_response_body', substr($response_body, 0, 500));
        
        mwm_log('Webhook failed for entry #' . $entry_id . ' with HTTP ' . $response_code);
        
        // Retry if enabled and not manual
        if (!$manual && get_option('mwm_retry_failed', 'yes') === 'yes') {
            $max_retries = get_option('mwm_max_retries', 3);
            if ($attempt_count < $max_retries) {
                // Schedule retry
                wp_schedule_single_event(time() + (60 * pow(2, $attempt_count)), 'mwm_retry_webhook', array($entry_id, $form_id));
            }
        }
        
        return array(
            'success' => false,
            'message' => 'Webhook returned error: HTTP ' . $response_code
        );
    }
}

/**
 * Hook into form submissions to automatically send webhooks
 */
add_action('gform_after_submission', 'mwm_handle_form_submission', 10, 2);
function mwm_handle_form_submission($entry, $form) {
    // Check if auto-send is enabled
    if (get_option('mwm_auto_send', 'yes') !== 'yes') {
        return;
    }
    
    // Only process supported forms
    if (!in_array($form['id'], array(10, 11, 12))) {
        return;
    }
    
    // Send to webhook
    mwm_send_entry_to_webhook($entry['id'], $form['id'], false);
}

/**
 * Handle webhook retries
 */
add_action('mwm_retry_webhook', 'mwm_retry_webhook_handler', 10, 2);
function mwm_retry_webhook_handler($entry_id, $form_id) {
    mwm_log('Retrying webhook for entry #' . $entry_id);
    mwm_send_entry_to_webhook($entry_id, $form_id, false);
}

/**
 * Hook into entry updates to resend
 */
add_action('gform_after_update_entry', 'mwm_handle_entry_update', 10, 2);
function mwm_handle_entry_update($form, $entry_id) {
    // Only process supported forms
    if (!in_array($form['id'], array(10, 11, 12))) {
        return;
    }
    
    // Check if auto-send is enabled
    if (get_option('mwm_auto_send', 'yes') !== 'yes') {
        return;
    }
    
    mwm_log('Entry updated, resending webhook for entry #' . $entry_id);
    mwm_send_entry_to_webhook($entry_id, $form['id'], false);
}

/**
 * Test webhook connection
 */
function mwm_test_webhook() {
    $webhook_url = get_option('mwm_webhook_url', '');
    
    if (empty($webhook_url)) {
        return array(
            'success' => false,
            'message' => 'Please configure the webhook URL first'
        );
    }
    
    $test_payload = array(
        'test' => true,
        'timestamp' => current_time('mysql'),
        'message' => 'Test webhook from Momentum Webhook Manager',
        'plugin_version' => MWM_VERSION,
        'site_url' => site_url(),
    );
    
    $response = wp_remote_post($webhook_url, array(
        'method' => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($test_payload),
        'timeout' => 30,
    ));
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => 'Connection failed: ' . $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    if ($response_code >= 200 && $response_code < 300) {
        return array(
            'success' => true,
            'message' => 'Test successful! Webhook responded with code: ' . $response_code
        );
    } else {
        return array(
            'success' => false,
            'message' => 'Webhook returned error code: ' . $response_code
        );
    }
}

/**
 * Get webhook statistics
 */
function mwm_get_webhook_statistics() {
    $stats = array();
    $form_ids = array(10, 11, 12);
    $form_names = array(
        10 => 'Security Guard',
        11 => 'Alarm Monitoring',
        12 => 'Private Investigator'
    );
    
    foreach ($form_ids as $form_id) {
        if (!GFAPI::form_id_exists($form_id)) {
            continue;
        }
        
        $search_criteria = array('status' => 'active');
        $entries = GFAPI::get_entries($form_id, $search_criteria);
        
        $sent = 0;
        $not_sent = 0;
        $failed = 0;
        
        foreach ($entries as $entry) {
            $status = gform_get_meta($entry['id'], 'mwm_webhook_status');
            
            if ($status === 'sent') {
                $sent++;
            } elseif ($status === 'failed') {
                $failed++;
            } else {
                $not_sent++;
            }
        }
        
        $stats[$form_id] = array(
            'form_name' => $form_names[$form_id] . ' (ID: ' . $form_id . ')',
            'total' => count($entries),
            'sent' => $sent,
            'not_sent' => $not_sent,
            'failed' => $failed
        );
    }
    
    return $stats;
}

/**
 * Logging function
 */
function mwm_log($message) {
    if (get_option('mwm_log_webhooks', 'yes') !== 'yes') {
        return;
    }
    
    if (WP_DEBUG === true) {
        error_log('[Momentum Webhook] ' . $message);
    }
}

/**
 * Add webhook status column to Gravity Forms entry list
 */
add_filter('gform_entry_list_columns', 'mwm_add_status_column', 10, 2);
function mwm_add_status_column($columns, $form_id) {
    if (in_array($form_id, array(10, 11, 12))) {
        $columns['webhook_status'] = 'Webhook';
    }
    return $columns;
}

/**
 * Display webhook status in entry list
 */
add_filter('gform_entries_column_filter', 'mwm_status_column_content', 10, 5);
function mwm_status_column_content($value, $form_id, $field_id, $entry, $query_string) {
    if ($field_id === 'webhook_status' && in_array($form_id, array(10, 11, 12))) {
        $status = gform_get_meta($entry['id'], 'mwm_webhook_status');
        $last_sent = gform_get_meta($entry['id'], 'mwm_webhook_sent');
        
        if ($status === 'sent') {
            $value = '<span style="color: green;">✓</span>';
            if ($last_sent) {
                $date = new DateTime('@' . $last_sent);
                $date->setTimezone(wp_timezone());
                $value .= '<br><small>' . $date->format('m/d') . '</small>';
            }
        } elseif ($status === 'failed') {
            $value = '<span style="color: red;">✗</span>';
        } else {
            $value = '<span style="color: orange;">⊙</span>';
        }
    }
    
    return $value;
}

/**
 * Add quick resend link to entry actions
 */
add_filter('gform_entries_first_column_actions', 'mwm_add_resend_action', 10, 4);
function mwm_add_resend_action($actions, $form_id, $field_id, $entry) {
    if (in_array($form_id, array(10, 11, 12))) {
        $resend_url = wp_nonce_url(
            admin_url('admin.php?page=mwm-webhook-manager&action=quick_resend&entry=' . $entry['id'] . '&form=' . $form_id),
            'mwm_quick_resend'
        );
        $actions['resend_webhook'] = '<a href="' . $resend_url . '">Resend Webhook</a>';
    }
    return $actions;
}

/**
 * Handle quick resend from entry list
 */
add_action('admin_init', 'mwm_handle_quick_resend');
function mwm_handle_quick_resend() {
    if (isset($_GET['action']) && $_GET['action'] === 'quick_resend' && isset($_GET['entry']) && isset($_GET['form'])) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'mwm_quick_resend')) {
            wp_die('Security check failed');
        }
        
        $entry_id = intval($_GET['entry']);
        $form_id = intval($_GET['form']);
        
        $result = mwm_send_entry_to_webhook($entry_id, $form_id, true);
        
        $redirect_url = admin_url('admin.php?page=gf_entries&id=' . $form_id);
        
        if ($result['success']) {
            $redirect_url = add_query_arg('webhook_sent', '1', $redirect_url);
        } else {
            $redirect_url = add_query_arg('webhook_failed', '1', $redirect_url);
        }
        
        wp_redirect($redirect_url);
        exit;
    }
}

/**
 * Display admin notices for quick resend
 */
add_action('admin_notices', 'mwm_display_quick_resend_notices');
function mwm_display_quick_resend_notices() {
    if (isset($_GET['webhook_sent'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Webhook sent successfully!</p></div>';
    }
    
    if (isset($_GET['webhook_failed'])) {
        echo '<div class="notice notice-error is-dismissible"><p>Webhook send failed. Check the Momentum Webhook Manager for details.</p></div>';
    }
}