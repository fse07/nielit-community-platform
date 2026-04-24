<?php
/**
 * Plugin Name: LiveKit Calls for BuddyPress
 * Description: Audio, video, and screen share with ringing notifications.
 * Version: 3.0
 */

if (!defined('ABSPATH')) exit;

define('LKC_API_KEY',    'API9hDtkdRpDa2h');
define('LKC_API_SECRET', 'EJ2PYqcKaH6iaWDw553lJ5xTHw6InQDbZVqf8xbaAwM');
define('LKC_WS_URL',     'wss://nielit-community.centralindia.cloudapp.azure.com/livekit');

// Load JS and CSS
add_action('wp_enqueue_scripts', function() {
    if (!is_user_logged_in()) return;

    wp_enqueue_script(
        'livekit-client',
        'https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js',
        [], '2.5.0', true
    );
    wp_enqueue_script(
        'lkc-calls',
        plugin_dir_url(__FILE__) . 'assets/calls.js',
        ['livekit-client', 'jquery', 'heartbeat'], '3.0', true
    );
    wp_enqueue_style(
        'lkc-calls-css',
        plugin_dir_url(__FILE__) . 'assets/calls.css',
        [], '3.0'
    );

    wp_localize_script('lkc-calls', 'LKC', [
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('lkc_nonce'),
        'ws_url'    => LKC_WS_URL,
        'user_id'   => get_current_user_id(),
        'user_name' => wp_get_current_user()->display_name,
    ]);
});

// Make heartbeat faster so incoming calls ring quickly
add_filter('heartbeat_settings', function($settings) {
    $settings['interval'] = 5; // check every 5 seconds
    return $settings;
});

// AJAX: caller initiates a call — creates DB row + returns token
add_action('wp_ajax_lkc_start_call', function() {
    global $wpdb;
    check_ajax_referer('lkc_nonce', 'nonce');

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $mode         = sanitize_text_field($_POST['mode'] ?? 'audio');
    $me           = get_current_user_id();

    if (!$recipient_id || $recipient_id == $me) {
        wp_send_json_error(['message' => 'Invalid recipient']);
    }

    $pair = [$me, $recipient_id];
    sort($pair);
    $room = 'room_' . implode('_', $pair);

    // Clean up old ringing entries from this caller
    $wpdb->delete('wp_lkc_calls', ['caller_id' => $me, 'status' => 'ringing']);

    // Insert the call invitation
    $wpdb->insert('wp_lkc_calls', [
        'caller_id'    => $me,
        'caller_name'  => wp_get_current_user()->display_name,
        'recipient_id' => $recipient_id,
        'room_name'    => $room,
        'call_mode'    => $mode,
        'status'       => 'ringing',
    ]);

    $token = lkc_generate_jwt((string)$me, wp_get_current_user()->display_name, $room);
    wp_send_json_success([
        'token'   => $token,
        'ws_url'  => LKC_WS_URL,
        'room'    => $room,
        'call_id' => $wpdb->insert_id,
    ]);
});

// AJAX: recipient accepts a call — returns their token
add_action('wp_ajax_lkc_accept_call', function() {
    global $wpdb;
    check_ajax_referer('lkc_nonce', 'nonce');

    $call_id = intval($_POST['call_id'] ?? 0);
    $me      = get_current_user_id();

    $call = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM wp_lkc_calls WHERE id = %d AND recipient_id = %d",
        $call_id, $me
    ));
    if (!$call) wp_send_json_error(['message' => 'Call not found']);

    $wpdb->update('wp_lkc_calls', ['status' => 'accepted'], ['id' => $call_id]);

    $token = lkc_generate_jwt((string)$me, wp_get_current_user()->display_name, $call->room_name);
    wp_send_json_success([
        'token'  => $token,
        'ws_url' => LKC_WS_URL,
        'room'   => $call->room_name,
        'mode'   => $call->call_mode,
    ]);
});

// AJAX: decline / end call
add_action('wp_ajax_lkc_end_call', function() {
    global $wpdb;
    check_ajax_referer('lkc_nonce', 'nonce');

    $call_id = intval($_POST['call_id'] ?? 0);
    $me      = get_current_user_id();

    $wpdb->update('wp_lkc_calls',
        ['status' => 'ended'],
        ['id' => $call_id]
    );
    wp_send_json_success();
});

// Heartbeat: push incoming calls to recipient's browser every ~5 sec
add_filter('heartbeat_received', function($response, $data) {
    global $wpdb;
    $me = get_current_user_id();
    if (!$me) return $response;

    // Expire rings older than 45 seconds
    $wpdb->query("UPDATE wp_lkc_calls SET status='missed' WHERE status='ringing' AND created_at < (NOW() - INTERVAL 45 SECOND)");

    $calls = $wpdb->get_results($wpdb->prepare(
        "SELECT id, caller_id, caller_name, room_name, call_mode
         FROM wp_lkc_calls
         WHERE recipient_id = %d AND status = 'ringing'
         ORDER BY created_at DESC LIMIT 1",
        $me
    ));

    $response['lkc_incoming'] = $calls;
    return $response;
}, 10, 2);

// JWT generator
function lkc_generate_jwt($identity, $name, $room) {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $now    = time();
    $payload = [
        'iss'   => LKC_API_KEY,
        'sub'   => $identity,
        'name'  => $name,
        'nbf'   => $now,
        'exp'   => $now + 3600,
        'video' => [
            'room' => $room, 'roomJoin' => true,
            'canPublish' => true, 'canSubscribe' => true,
        ],
    ];
    $b64 = function($v) { return rtrim(strtr(base64_encode($v), '+/', '-_'), '='); };
    $h = $b64(json_encode($header));
    $p = $b64(json_encode($payload));
    $sig = hash_hmac('sha256', "$h.$p", LKC_API_SECRET, true);
    return "$h.$p." . $b64($sig);
}

// Call modal + incoming call popup HTML
add_action('wp_footer', function() {
    if (!is_user_logged_in()) return;
    ?>
    <!-- Call-in-progress modal -->
    <div id="lkc-modal" style="display:none;">
      <div id="lkc-modal-inner">
        <div id="lkc-header">
          <span id="lkc-title">Call</span>
          <span id="lkc-status">Connecting…</span>
        </div>
        <div id="lkc-videos"></div>
        <div id="lkc-controls">
          <button id="lkc-mic"    title="Mute / Unmute">🎤</button>
          <button id="lkc-cam"    title="Camera On / Off">📷</button>
          <button id="lkc-screen" title="Share Screen">🖥️</button>
          <button id="lkc-hangup" title="End Call">📴</button>
        </div>
      </div>
    </div>

    <!-- Incoming call ringing popup -->
    <div id="lkc-incoming" style="display:none;">
      <div id="lkc-incoming-inner">
        <div id="lkc-ring-title">Incoming call…</div>
        <div id="lkc-ring-from"></div>
        <div id="lkc-ring-buttons">
          <button id="lkc-accept">✅ Accept</button>
          <button id="lkc-decline">❌ Decline</button>
        </div>
      </div>
    </div>

    <!-- Ringtone -->
    <audio id="lkc-ringtone" loop preload="auto"
      src="https://cdn.jsdelivr.net/gh/anars/blank-audio/1-second-of-silence.mp3"></audio>
    <?php
});

// Call buttons on user profile pages
add_action('bp_member_header_actions', function() {
    if (!is_user_logged_in() || !function_exists('bp_displayed_user_id')) return;
    $other = bp_displayed_user_id();
    $me    = get_current_user_id();
    if (!$other || $other == $me) return;

    echo '<div class="generic-button">';
    echo '<a href="#" class="lkc-start button" data-recipient="'.esc_attr($other).'" data-mode="audio">📞 Audio Call</a>';
    echo '</div>';
    echo '<div class="generic-button">';
    echo '<a href="#" class="lkc-start button" data-recipient="'.esc_attr($other).'" data-mode="video">🎥 Video Call</a>';
    echo '</div>';
});
