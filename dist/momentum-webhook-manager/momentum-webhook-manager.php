<?php
/**
 * Plugin Name: Momentum Webhook Manager
 * Description: Manages automatic sending and manual resending of Gravity Forms entries to Momentum webhook
 * Version: 1.2.1
 * Author: Momentum Integration
 * 
 * This plugin provides:
 * - Automatic webhook sending for Old Alarm Monitoring (1), Old Private Investigator (2), Old Security Guard (3), Security Guard (10), Alarm Monitoring (11), Private Investigator (12)
 * - Admin interface to view and resend entries
 * - WordPress admin dashboard widget with statistics
 * - Admin bar quick access menu with notifications
 * - Webhook status tracking and timezone support
 * - Bulk resend capabilities
 * - Field mapping support for all supported forms
 * 
 * Release Notes:
 * 
 * Version 1.2.1 (Current)
 * - Prevent fatal errors when Gravity Forms is inactive by guarding GFAPI calls
 * - Admin Bar, Dashboard Widget, and Entries screen now check for GF availability
 * 
 * Version 1.2.0
 * - Added Settings toggle to enable/disable legacy mapper direct send (disabled by default)
 * - Made plugin the source of truth for sending; mapper direct hooks are gated
 * - Fixed undefined filter variables in bulk resend actions
 * - Improved sanitization for webhook URL and bulk action inputs
 * - Logging streamlined to respect plugin option
 * 
 * Version 1.1.0
 * - Added support for legacy forms: Old Alarm Monitoring (1), Old Private Investigator (2), Old Security Guard (3)
 * - Added WordPress admin dashboard widget with real-time statistics
 * - Added admin bar menu with notification badges for pending/failed entries
 * - Added field mappings for all legacy forms
 * - Enhanced UI with form breakdown and quick access links
 * - Improved statistics tracking across all 6 supported forms
 * 
 * Version 1.0.0
 * - Initial release with support for Forms 10, 11, 12
 * - Basic webhook management and resend functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('MWM_VERSION', '1.2.1');
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
    add_option('mwm_enable_mapper_direct_send', 'no');
}

/**
 * Add Quick Actions to Admin Bar
 */
add_action('admin_bar_menu', 'mwm_add_admin_bar_menu', 100);
function mwm_add_admin_bar_menu($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $notification_count = 0;
    if (class_exists('GFAPI')) {
        $stats = mwm_get_webhook_statistics();
        $total_pending = 0;
        $total_failed = 0;
        foreach ($stats as $stat) {
            $total_pending += $stat['not_sent'];
            $total_failed += $stat['failed'];
        }
        $notification_count = $total_pending + $total_failed;
    }
    $title = 'Webhook Manager';
    
    if ($notification_count > 0) {
        $title .= ' <span class="awaiting-mod count-' . $notification_count . '"><span class="pending-count">' . $notification_count . '</span></span>';
    }
    
    // Main menu
    $wp_admin_bar->add_menu(array(
        'id' => 'mwm-webhook-manager',
        'title' => $title,
        'href' => admin_url('admin.php?page=mwm-webhook-manager')
    ));
    
    // Submenu items
    $wp_admin_bar->add_menu(array(
        'parent' => 'mwm-webhook-manager',
        'id' => 'mwm-manage-entries',
        'title' => 'Manage Entries',
        'href' => admin_url('admin.php?page=mwm-webhook-manager')
    ));
    
    if ($total_pending > 0) {
        $wp_admin_bar->add_menu(array(
            'parent' => 'mwm-webhook-manager',
            'id' => 'mwm-pending-entries',
            'title' => 'Pending Entries (' . $total_pending . ')',
            'href' => admin_url('admin.php?page=mwm-webhook-manager&status=not_sent')
        ));
    }
    
    if ($total_failed > 0) {
        $wp_admin_bar->add_menu(array(
            'parent' => 'mwm-webhook-manager',
            'id' => 'mwm-failed-entries',
            'title' => 'Failed Entries (' . $total_failed . ')',
            'href' => admin_url('admin.php?page=mwm-webhook-manager&status=failed')
        ));
    }
    
    $wp_admin_bar->add_menu(array(
        'parent' => 'mwm-webhook-manager',
        'id' => 'mwm-settings',
        'title' => 'Settings',
        'href' => admin_url('admin.php?page=mwm-settings')
    ));
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
    // Require Gravity Forms for this screen
    if (!class_exists('GFAPI')) {
        echo '<div class="wrap"><h1>Momentum Webhook Manager</h1><div class="notice notice-error"><p>Gravity Forms is required for this page. Please activate Gravity Forms.</p></div></div>';
        return;
    }
    // Read current filters early so they are available to POST handlers
    $selected_form = isset($_REQUEST['form_id']) ? intval($_REQUEST['form_id']) : 0;
    $selected_status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
    $selected_read = isset($_REQUEST['read_state']) ? sanitize_text_field($_REQUEST['read_state']) : 'all';

    // Handle toggle read/unread
    if (isset($_POST['toggle_read']) && wp_verify_nonce($_POST['mwm_nonce'], 'mwm_toggle_read')) {
        $entry_id = intval($_POST['entry_id']);
        $new_read = isset($_POST['new_read']) && $_POST['new_read'] === '1' ? true : false;
        $updated = GFAPI::update_entry_property($entry_id, 'is_read', $new_read);
        if (!is_wp_error($updated)) {
            echo '<div class="notice notice-success"><p>Entry #' . esc_html($entry_id) . ' marked as ' . ($new_read ? 'read' : 'unread') . '.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Failed to update read state for entry #' . esc_html($entry_id) . '.</p></div>';
        }
    }

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
    if (isset($_POST['bulk_action']) && wp_verify_nonce($_POST['mwm_nonce'], 'mwm_bulk')) {
        $bulk_action = sanitize_text_field($_POST['bulk_action']);
        
        if ($bulk_action === 'resend') {
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
        } elseif ($bulk_action === 'mark_read' || $bulk_action === 'mark_unread') {
            if (!empty($_POST['entries'])) {
                $set_read = ($bulk_action === 'mark_read');
                $success_count = 0;
                $fail_count = 0;
                foreach ($_POST['entries'] as $entry_data) {
                    list($entry_id) = explode(':', $entry_data);
                    $updated = GFAPI::update_entry_property(intval($entry_id), 'is_read', $set_read);
                    if (!is_wp_error($updated)) {
                        $success_count++;
                    } else {
                        $fail_count++;
                    }
                }
                $msg = $set_read ? 'read' : 'unread';
                $message = sprintf('Marked %d entries as %s. %d failed.', $success_count, $msg, $fail_count);
                echo '<div class="notice notice-info"><p>' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>No entries selected.</p></div>';
            }
        } elseif (in_array($bulk_action, array('resend_all', 'resend_all_not_sent', 'resend_all_failed'))) {
            // Handle resend all variations
            $entries_to_process = mwm_get_all_entries_for_bulk_action($selected_form, $selected_status, $bulk_action);
            
            if (!empty($entries_to_process)) {
                $success_count = 0;
                $fail_count = 0;
                
                foreach ($entries_to_process as $entry) {
                    $result = mwm_send_entry_to_webhook($entry['id'], $entry['form_id'], true);
                    
                    if ($result['success']) {
                        $success_count++;
                    } else {
                        $fail_count++;
                    }
                }
                
                $message = sprintf('Bulk resend ALL completed: %d successful, %d failed out of %d total entries', $success_count, $fail_count, count($entries_to_process));
                echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-warning"><p>No entries found to process.</p></div>';
            }
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
            <p><strong>Supported Forms:</strong> Old Alarm Monitoring (ID: 1), Old Private Investigator (ID: 2), Old Security Guard (ID: 3), Security Guard (ID: 10), Alarm Monitoring (ID: 11), Private Investigator (ID: 12)</p>
        </div>
        
        <?php
        // Filters (already initialized above; keep in sync from GET for UI)
        $selected_form = isset($_GET['form_id']) ? intval($_GET['form_id']) : $selected_form;
        $selected_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : $selected_status;
        $selected_read = isset($_GET['read_state']) ? sanitize_text_field($_GET['read_state']) : $selected_read;
        $page_num = isset($_GET['pagenum']) ? intval($_GET['pagenum']) : 1;
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 25;
        
        // Validate per_page options
        if (!in_array($per_page, array(25, 50, 100, 200, 500))) {
            $per_page = 25;
        }
        ?>
        
        <form method="get" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="mwm-webhook-manager" />
            
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div>
                    <label for="form_id" style="font-weight: bold;">Form:</label>
                    <select name="form_id" id="form_id" onchange="this.form.submit()">
                        <option value="0">All Forms</option>
                        <option value="1" <?php selected($selected_form, 1); ?>>Old Alarm Monitoring (1)</option>
                        <option value="2" <?php selected($selected_form, 2); ?>>Old Private Investigator (2)</option>
                        <option value="3" <?php selected($selected_form, 3); ?>>Old Security Guard (3)</option>
                        <option value="10" <?php selected($selected_form, 10); ?>>Security Guard (10)</option>
                        <option value="11" <?php selected($selected_form, 11); ?>>Alarm Monitoring (11)</option>
                        <option value="12" <?php selected($selected_form, 12); ?>>Private Investigator (12)</option>
                    </select>
                </div>
                
                <div>
                    <label for="status" style="font-weight: bold;">Status:</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="all">All Statuses</option>
                        <option value="sent" <?php selected($selected_status, 'sent'); ?>>Sent</option>
                        <option value="not_sent" <?php selected($selected_status, 'not_sent'); ?>>Not Sent</option>
                        <option value="failed" <?php selected($selected_status, 'failed'); ?>>Failed</option>
                    </select>
                </div>
                
                <div>
                    <label for="read_state" style="font-weight: bold;">Read:</label>
                    <select name="read_state" id="read_state" onchange="this.form.submit()">
                        <option value="all" <?php selected($selected_read, 'all'); ?>>All</option>
                        <option value="read" <?php selected($selected_read, 'read'); ?>>Read</option>
                        <option value="unread" <?php selected($selected_read, 'unread'); ?>>Unread</option>
                    </select>
                </div>
                <div>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=mwm-webhook-manager') ); ?>" class="button">Clear Filters</a>
                </div>
                
                <div>
                    <label for="per_page" style="font-weight: bold;">Show:</label>
                    <select name="per_page" id="per_page" onchange="this.form.submit()">
                        <option value="25" <?php selected($per_page, 25); ?>>25 per page</option>
                        <option value="50" <?php selected($per_page, 50); ?>>50 per page</option>
                        <option value="100" <?php selected($per_page, 100); ?>>100 per page</option>
                        <option value="200" <?php selected($per_page, 200); ?>>200 per page</option>
                        <option value="500" <?php selected($per_page, 500); ?>>500 per page</option>
                    </select>
                </div>
            </div>
        </form>
        
        <?php
        // Get entries
        $search_criteria = array('status' => 'active');
        $sorting = array('key' => 'date_created', 'direction' => 'DESC');
        $paging = array('offset' => ($page_num - 1) * $per_page, 'page_size' => $per_page);
        
        // Collect entries from supported forms
        $all_entries = array();
        $total_count = 0;
        $form_ids = $selected_form > 0 ? array($selected_form) : array(1, 2, 3, 10, 11, 12);
        
        foreach ($form_ids as $form_id) {
            if (!GFAPI::form_id_exists($form_id)) {
                continue;
            }
            
            // Force GFAPI to return ALL entries by setting unlimited page size
        $unlimited_paging = array('offset' => 0, 'page_size' => 999999);
        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $unlimited_paging);
        
        // Debug: Log entry counts per form (respects logging option)
        mwm_log('Form ' . $form_id . ' returned ' . count($entries) . ' entries for bulk processing');
            
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
            
            // Filter by read/unread if needed
            if ($selected_read !== 'all') {
                $filtered_entries = array();
                foreach ($entries as $entry) {
                    $is_read = !empty($entry['is_read']);
                    if ($selected_read === 'read' && $is_read) {
                        $filtered_entries[] = $entry;
                    } elseif ($selected_read === 'unread' && !$is_read) {
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
        
        // Store filter params for pagination links
        $filter_params = array(
            'form_id' => $selected_form,
            'status' => $selected_status,
            'read_state' => $selected_read,
            'per_page' => $per_page
        );
        ?>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=mwm-webhook-manager'); ?>">
            <?php wp_nonce_field('mwm_bulk', 'mwm_nonce'); ?>
            
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action" id="bulk-action-selector">
                        <option value="">Bulk Actions</option>
                        <option value="resend">Resend Selected</option>
                        <option value="mark_read">Mark Selected as Read</option>
                        <option value="mark_unread">Mark Selected as Unread</option>
                        <option value="resend_all">Resend All <?php echo $total_count; ?> Entries</option>
                        <?php if ($selected_status === 'not_sent'): ?>
                            <option value="resend_all_not_sent">Resend All Not Sent (<?php echo $total_count; ?>)</option>
                        <?php endif; ?>
                        <?php if ($selected_status === 'failed'): ?>
                            <option value="resend_all_failed">Resend All Failed (<?php echo $total_count; ?>)</option>
                        <?php endif; ?>
                    </select>
                    <input type="submit" name="apply_bulk" class="button action" value="Apply">
                </div>
                
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf('%d items', $total_count); ?></span>
                    <?php
                    $total_pages = ceil($total_count / $per_page);
                    if ($total_pages > 1) {
                        $base_params = array_merge(array('page' => 'mwm-webhook-manager'), $filter_params);
                        $base_url = add_query_arg($base_params, admin_url('admin.php'));
                        
                        echo '<span class="pagination-links">';
                        if ($page_num > 1) {
                            echo '<a class="prev-page button" href="' . add_query_arg('pagenum', $page_num - 1, $base_url) . '">‹ Previous</a> ';
                        }
                        
                        // Show page numbers for small datasets, or just current/total for large ones
                        if ($total_pages <= 10) {
                            for ($i = 1; $i <= $total_pages; $i++) {
                                if ($i == $page_num) {
                                    echo '<span class="current" style="padding: 3px 5px; background: #0073aa; color: white; border-radius: 3px; margin: 0 2px;">' . $i . '</span> ';
                                } else {
                                    echo '<a href="' . add_query_arg('pagenum', $i, $base_url) . '" style="padding: 3px 5px; text-decoration: none; border: 1px solid #ccc; margin: 0 2px;">' . $i . '</a> ';
                                }
                            }
                        } else {
                            echo '<span class="paging-input">';
                            echo 'Page ' . $page_num . ' of ' . $total_pages;
                            echo '</span>';
                        }
                        
                        if ($page_num < $total_pages) {
                            echo ' <a class="next-page button" href="' . add_query_arg('pagenum', $page_num + 1, $base_url) . '">Next ›</a>';
                        }
                        echo '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="mwm-legend" role="note" aria-label="Legend">
                <span class="legend-item"><span style="color: green;" aria-hidden="true">✓</span> Sent</span>
                <span class="legend-item"><span style="color: red;" aria-hidden="true">✗</span> Failed</span>
                <span class="legend-item"><span style="color: orange;" aria-hidden="true">⊙</span> Not Sent</span>
                <span class="legend-item"><span class="mwm-read-dot" aria-hidden="true">●</span> Read</span>
                <span class="legend-item"><span class="mwm-unread-dot" aria-hidden="true">●</span> Unread</span>
                <span class="legend-item"><strong>Bold row</strong> = Not Sent</span>
            </div>

            <table class="wp-list-table widefat fixed striped" aria-describedby="mwm-table-caption">
                <caption id="mwm-table-caption" class="screen-reader-text">Momentum Webhook entries list</caption>
                <thead>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" id="select-all" />
                            <br><small style="font-weight: normal;">Page (<?php echo count($entries); ?>)</small>
                            <br><a href="#" id="mwm-mark-page-read" class="mwm-quick-link">Mark page read</a>
                            <br><a href="#" id="mwm-mark-page-unread" class="mwm-quick-link">Mark page unread</a>
                        </th>
                        <th>ID</th>
                        <th>Form</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th>Webhook Status</th>
                        <th>Read</th>
                        <th>Last Attempt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="10">No entries found.</td>
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
                            $is_read = isset($entry['is_read']) ? (int) $entry['is_read'] : 0;
                            $is_not_sent = empty($webhook_status);
                            ?>
                            <tr class="<?php echo $is_not_sent ? 'mwm-not-sent' : ''; ?>">
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
                                    <?php if ($is_read): ?>
                                        <span class="mwm-read-dot" title="Read" aria-label="Read" role="img">●</span>
                                    <?php else: ?>
                                        <span class="mwm-unread-dot" title="Unread" aria-label="Unread" role="img">●</span>
                                    <?php endif; ?>
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
                                    <form method="post" style="display: inline; margin-left: 4px;">
                                        <?php wp_nonce_field('mwm_toggle_read', 'mwm_nonce'); ?>
                                        <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry['id']); ?>" />
                                        <input type="hidden" name="new_read" value="<?php echo $is_read ? '0' : '1'; ?>" />
                                        <button type="submit" name="toggle_read" class="button button-small">
                                            <?php echo $is_read ? 'Mark Unread' : 'Mark Read'; ?>
                                        </button>
                                    </form>
                                    <a href="<?php echo admin_url('admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $entry['id']); ?>" 
                                       class="button button-small" target="_blank">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <select name="bulk_action" id="bulk-action-selector-bottom">
                        <option value="">Bulk Actions</option>
                        <option value="resend">Resend Selected</option>
                        <option value="mark_read">Mark Selected as Read</option>
                        <option value="mark_unread">Mark Selected as Unread</option>
                        <option value="resend_all">Resend All <?php echo $total_count; ?> Entries</option>
                        <?php if ($selected_status === 'not_sent'): ?>
                            <option value="resend_all_not_sent">Resend All Not Sent (<?php echo $total_count; ?>)</option>
                        <?php endif; ?>
                        <?php if ($selected_status === 'failed'): ?>
                            <option value="resend_all_failed">Resend All Failed (<?php echo $total_count; ?>)</option>
                        <?php endif; ?>
                    </select>
                    <input type="submit" name="apply_bulk" class="button action" value="Apply">
                </div>
            </div>
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
            var actionTop = $('#bulk-action-selector').val();
            var actionBottom = $('#bulk-action-selector-bottom').val();
            var action = actionBottom || actionTop;
            if (action === '') {
                e.preventDefault();
                alert('Please select a bulk action.');
                return false;
            }
            
            // Handle "resend all" actions differently
            if (action.startsWith('resend_all')) {
                var confirmMessage = 'This will attempt to resend ALL entries matching your current filters. This may take several minutes for large datasets. Continue?';
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
            } else if (action === 'resend' || action === 'mark_read' || action === 'mark_unread') {
                // For actions that require selected entries
                var checked = $('input[name="entries[]"]:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Please select at least one entry.');
                    return false;
                }
                if (action === 'resend') {
                    if (!confirm('Resend ' + checked + ' selected entr' + (checked === 1 ? 'y' : 'ies') + ' to webhook?')) {
                        e.preventDefault();
                        return false;
                    }
                }
            }
        });

        // Quick links: mark entire page read/unread
        function mwmSubmitPageMark(setTo) {
            // Select all on page
            $('input[name="entries[]"]').prop('checked', true);
            // Set bulk action
            $('#bulk-action-selector, #bulk-action-selector-bottom').val(setTo);
            // Submit the form via the Apply button to reuse handlers
            $('input[name="apply_bulk"]').trigger('click');
        }
        $('#mwm-mark-page-read').on('click', function(e) {
            e.preventDefault();
            mwmSubmitPageMark('mark_read');
        });
        $('#mwm-mark-page-unread').on('click', function(e) {
            e.preventDefault();
            mwmSubmitPageMark('mark_unread');
        });
    });
    </script>
    <style>
    /* Bold rows that have not been sent to the webhook */
    tr.mwm-not-sent td { font-weight: 600; }
    .mwm-quick-link { font-size: 11px; text-decoration: none; }
    .mwm-quick-link:hover { text-decoration: underline; }
    .mwm-read-dot { color: #00a32a; font-size: 16px; line-height: 1; }
    .mwm-unread-dot { color: #d63638; font-size: 16px; line-height: 1; }
    .mwm-legend { margin: 8px 0 6px; color: #555; font-size: 12px; }
    .mwm-legend .legend-item { display: inline-flex; align-items: center; gap: 4px; margin-right: 12px; }
    </style>
    <?php
}

/**
 * Settings page
 */
function mwm_settings_page() {
    // Save settings
    if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['mwm_settings_nonce'], 'mwm_save_settings')) {
        update_option('mwm_webhook_url', esc_url_raw($_POST['webhook_url']));
        update_option('mwm_auto_send', isset($_POST['auto_send']) ? 'yes' : 'no');
        update_option('mwm_retry_failed', isset($_POST['retry_failed']) ? 'yes' : 'no');
        update_option('mwm_log_webhooks', isset($_POST['log_webhooks']) ? 'yes' : 'no');
        update_option('mwm_max_retries', intval($_POST['max_retries']));
        update_option('mwm_enable_mapper_direct_send', isset($_POST['mapper_direct_send']) ? 'yes' : 'no');
        
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
    $mapper_direct_send = get_option('mwm_enable_mapper_direct_send', 'no');
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
                    <th scope="row">Mapper Direct Send (Legacy)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="mapper_direct_send" value="yes" 
                                   <?php checked($mapper_direct_send, 'yes'); ?> />
                            Enable direct send from field-mapper hooks (may duplicate sends). Use only for testing.
                        </label>
                        <p class="description">If enabled, the mapper file will post directly to the configured webhook on form submission in addition to the plugin logic.</p>
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
    if (!in_array($form['id'], array(1, 2, 3, 10, 11, 12))) {
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
    if (!in_array($form['id'], array(1, 2, 3, 10, 11, 12))) {
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
 * Get all entries for bulk actions (handles large datasets)
 */
function mwm_get_all_entries_for_bulk_action($selected_form, $selected_status, $bulk_action) {
    if (!class_exists('GFAPI')) {
        return array();
    }
    $search_criteria = array('status' => 'active');
    $sorting = array('key' => 'date_created', 'direction' => 'DESC');
    
    $all_entries = array();
    $form_ids = $selected_form > 0 ? array($selected_form) : array(1, 2, 3, 10, 11, 12);
    
    foreach ($form_ids as $form_id) {
        if (!GFAPI::form_id_exists($form_id)) {
            continue;
        }
        
        // Force GFAPI to return ALL entries by setting unlimited page size
        $unlimited_paging = array('offset' => 0, 'page_size' => 999999);
        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $unlimited_paging);
        
        // Debug: Log entry counts per form
        error_log('MWM Debug: Form ' . $form_id . ' returned ' . count($entries) . ' entries');
        
        // Filter entries based on bulk action type
        $filtered_entries = array();
        foreach ($entries as $entry) {
            $webhook_status = gform_get_meta($entry['id'], 'mwm_webhook_status');
            
            $include_entry = false;
            
            switch ($bulk_action) {
                case 'resend_all':
                    $include_entry = true;
                    break;
                case 'resend_all_not_sent':
                    $include_entry = empty($webhook_status);
                    break;
                case 'resend_all_failed':
                    $include_entry = ($webhook_status === 'failed');
                    break;
            }
            
            // Also filter by selected status if needed
            if ($include_entry && $selected_status !== 'all') {
                switch ($selected_status) {
                    case 'sent':
                        $include_entry = ($webhook_status === 'sent');
                        break;
                    case 'not_sent':
                        $include_entry = empty($webhook_status);
                        break;
                    case 'failed':
                        $include_entry = ($webhook_status === 'failed');
                        break;
                }
            }
            
            if ($include_entry) {
                $filtered_entries[] = $entry;
            }
        }
        
        $all_entries = array_merge($all_entries, $filtered_entries);
    }
    
    return $all_entries;
}

/**
 * Get webhook statistics
 */
function mwm_get_webhook_statistics() {
    if (!class_exists('GFAPI')) {
        return array();
    }
    $stats = array();
    $form_ids = array(1, 2, 3, 10, 11, 12);
    $form_names = array(
        1 => 'Old Alarm Monitoring',
        2 => 'Old Private Investigator', 
        3 => 'Old Security Guard',
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
    if (in_array($form_id, array(1, 2, 3, 10, 11, 12))) {
        $columns['webhook_status'] = 'Webhook';
    }
    return $columns;
}

/**
 * Display webhook status in entry list
 */
add_filter('gform_entries_column_filter', 'mwm_status_column_content', 10, 5);
function mwm_status_column_content($value, $form_id, $field_id, $entry, $query_string) {
    if ($field_id === 'webhook_status' && in_array($form_id, array(1, 2, 3, 10, 11, 12))) {
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
    if (in_array($form_id, array(1, 2, 3, 10, 11, 12))) {
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

/**
 * Add WordPress Admin Dashboard Widget
 */
add_action('wp_dashboard_setup', 'mwm_add_dashboard_widget');
function mwm_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'mwm_webhook_dashboard',
        'Momentum Webhook Manager',
        'mwm_dashboard_widget_content',
        'mwm_dashboard_widget_control'
    );
}

/**
 * Dashboard widget content
 */
function mwm_dashboard_widget_content() {
    $webhook_url = get_option('mwm_webhook_url', '');
    $stats = class_exists('GFAPI') ? mwm_get_webhook_statistics() : array();
    if (!class_exists('GFAPI')) {
        echo '<div class="notice notice-error inline" style="margin: 0 0 15px 0; padding: 10px;"><p><strong>Gravity Forms is not active</strong><br>This widget requires Gravity Forms to display entry stats.</p></div>';
    }
    
    // Calculate totals
    $total_entries = 0;
    $total_sent = 0;
    $total_failed = 0;
    $total_not_sent = 0;
    
    foreach ($stats as $stat) {
        $total_entries += $stat['total'];
        $total_sent += $stat['sent'];
        $total_failed += $stat['failed'];
        $total_not_sent += $stat['not_sent'];
    }
    
    ?>
    <div class="mwm-dashboard-widget">
        <?php if (empty($webhook_url)): ?>
            <div class="notice notice-warning inline" style="margin: 0 0 15px 0; padding: 10px;">
                <p><strong>⚠️ Webhook URL not configured</strong><br>
                <a href="<?php echo admin_url('admin.php?page=mwm-settings'); ?>">Configure Settings</a></p>
            </div>
        <?php endif; ?>
        
        <div class="mwm-stats" style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div class="stat-box" style="flex: 1; text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #0073aa;"><?php echo $total_entries; ?></div>
                <div style="font-size: 12px; color: #666;">Total Entries</div>
            </div>
            <div class="stat-box" style="flex: 1; text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #00a32a;"><?php echo $total_sent; ?></div>
                <div style="font-size: 12px; color: #666;">Sent</div>
            </div>
            <div class="stat-box" style="flex: 1; text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #d63638;"><?php echo $total_failed; ?></div>
                <div style="font-size: 12px; color: #666;">Failed</div>
            </div>
            <div class="stat-box" style="flex: 1; text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <div style="font-size: 24px; font-weight: bold; color: #f56e28;"><?php echo $total_not_sent; ?></div>
                <div style="font-size: 12px; color: #666;">Not Sent</div>
            </div>
        </div>
        
        <?php if ($total_not_sent > 0 || $total_failed > 0): ?>
            <div class="mwm-alerts" style="margin-bottom: 15px;">
                <?php if ($total_not_sent > 0): ?>
                    <div class="notice notice-warning inline" style="margin: 5px 0; padding: 8px;">
                        <p style="margin: 0;"><strong><?php echo $total_not_sent; ?> entries</strong> have not been sent to webhook</p>
                    </div>
                <?php endif; ?>
                <?php if ($total_failed > 0): ?>
                    <div class="notice notice-error inline" style="margin: 5px 0; padding: 8px;">
                        <p style="margin: 0;"><strong><?php echo $total_failed; ?> entries</strong> failed to send</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="mwm-actions" style="text-align: center;">
            <a href="<?php echo admin_url('admin.php?page=mwm-webhook-manager'); ?>" class="button button-primary">Manage Webhooks</a>
            <a href="<?php echo admin_url('admin.php?page=mwm-settings'); ?>" class="button">Settings</a>
            
            <?php if ($total_not_sent > 0): ?>
                <a href="<?php echo admin_url('admin.php?page=mwm-webhook-manager&status=not_sent'); ?>" 
                   class="button button-secondary" style="margin-top: 5px;">Send Pending (<?php echo $total_not_sent; ?>)</a>
            <?php endif; ?>
            
            <?php if ($total_failed > 0): ?>
                <a href="<?php echo admin_url('admin.php?page=mwm-webhook-manager&status=failed'); ?>" 
                   class="button button-secondary" style="margin-top: 5px;">Retry Failed (<?php echo $total_failed; ?>)</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($stats)): ?>
            <details style="margin-top: 15px;">
                <summary style="cursor: pointer; font-weight: bold;">Form Breakdown</summary>
                <table class="widefat" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th style="padding: 5px;">Form</th>
                            <th style="padding: 5px; text-align: center;">Total</th>
                            <th style="padding: 5px; text-align: center;">Sent</th>
                            <th style="padding: 5px; text-align: center;">Failed</th>
                            <th style="padding: 5px; text-align: center;">Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $form_id => $stat): ?>
                            <?php if ($stat['total'] > 0): ?>
                                <tr>
                                    <td style="padding: 5px;"><a href="<?php echo admin_url('admin.php?page=mwm-webhook-manager&form_id=' . $form_id); ?>"><?php echo esc_html($stat['form_name']); ?></a></td>
                                    <td style="padding: 5px; text-align: center;"><?php echo $stat['total']; ?></td>
                                    <td style="padding: 5px; text-align: center; color: #00a32a;"><?php echo $stat['sent']; ?></td>
                                    <td style="padding: 5px; text-align: center; color: #d63638;"><?php echo $stat['failed']; ?></td>
                                    <td style="padding: 5px; text-align: center; color: #f56e28;"><?php echo $stat['not_sent']; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </details>
        <?php endif; ?>
    </div>
    
    <style>
    .mwm-dashboard-widget .notice {
        border-left: 4px solid;
    }
    .mwm-dashboard-widget .stat-box {
        transition: transform 0.2s;
    }
    .mwm-dashboard-widget .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .mwm-dashboard-widget details summary {
        padding: 8px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .mwm-dashboard-widget details[open] summary {
        border-bottom: none;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
    }
    .mwm-dashboard-widget details table {
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    </style>
    <?php
}

/**
 * Dashboard widget control (configuration)
 */
function mwm_dashboard_widget_control() {
    // Handle settings update
    if (isset($_POST['mwm_widget_submit'])) {
        update_option('mwm_widget_show_stats', isset($_POST['show_stats']) ? 'yes' : 'no');
        update_option('mwm_widget_show_alerts', isset($_POST['show_alerts']) ? 'yes' : 'no');
    }
    
    $show_stats = get_option('mwm_widget_show_stats', 'yes');
    $show_alerts = get_option('mwm_widget_show_alerts', 'yes');
    ?>
    <p>
        <label>
            <input type="checkbox" name="show_stats" value="yes" <?php checked($show_stats, 'yes'); ?> />
            Show statistics
        </label><br>
        <label>
            <input type="checkbox" name="show_alerts" value="yes" <?php checked($show_alerts, 'yes'); ?> />
            Show alerts for pending/failed entries
        </label>
    </p>
    <input type="hidden" name="mwm_widget_submit" value="1" />
    <?php
}
