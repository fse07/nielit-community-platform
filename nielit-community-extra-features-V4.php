<?php
/**
 * Plugin Name: Nielit Community Extra Features
 * Description: Like/Dislike reactions + Ask a Question / Create a Post modal.
 * Version: 1.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================
   ASSETS
   ========================================================= */

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'dashicons' );
} );

/* =========================================================
   LIKE / DISLIKE — STYLES  (wp_head, CSS only — no script here)
   ========================================================= */

add_action( 'wp_head', function () { ?>
    <style>
        /* ── Like / Dislike buttons ── */
        .nielit-reaction-wrapper { display: inline-flex; gap: 5px; vertical-align: middle; margin-right: 8px; }
        .nielit-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 45px; height: 34px; border: 1px solid #ddd; border-radius: 4px;
            background: #fff; color: #555 !important; text-decoration: none !important; cursor: pointer;
            padding: 0 8px; font-family: sans-serif;
        }
        .nielit-btn:hover { background: #f9f9f9; }
        .nielit-btn.active.is-like    { border-color: #0073aa; color: #0073aa !important; background: #e7f2f7; }
        .nielit-btn.active.is-dislike { border-color: #d63638; color: #d63638 !important; background: #fbeaea; }
        .nielit-btn .dashicons { font-size: 17px; margin-right: 4px; }
        .count-num { font-size: 13px; font-weight: 600; }

        /* ── Intercept the default BP "What's new" form ── */
        #whats-new-form { cursor: pointer; user-select: none; }
        #whats-new-form * { pointer-events: none; }
    </style>
<?php } );

/* =========================================================
   LIKE / DISLIKE — SCRIPT  (wp_footer, registered once)
   ========================================================= */

add_action( 'wp_footer', function () { ?>
    <script>
        jQuery(document).ready(function($) {
            $(document).on('click', '.nielit-btn', function(e) {
                e.preventDefault();
                var $btn     = $(this);
                var $wrapper = $btn.closest('.nielit-reaction-wrapper');

                $btn.css('opacity', '0.5');

                $.ajax({
                    url: '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>',
                    type: 'POST',
                    data: {
                        action: 'nielit_react_final',
                        id:     $btn.data('id'),
                        type:   $btn.data('type')
                    },
                    success: function(res) {
                        $btn.css('opacity', '1');
                        if (res.success) {
                            $wrapper.find('.l-count').text(res.data.l);
                            $wrapper.find('.d-count').text(res.data.d);

                            if ($btn.hasClass('is-like')) {
                                $btn.toggleClass('active');
                                $wrapper.find('.is-dislike').removeClass('active');
                            } else {
                                $btn.toggleClass('active');
                                $wrapper.find('.is-like').removeClass('active');
                            }
                        }
                    }
                });
            });
        });
    </script>
<?php }, 10 );

/* =========================================================
   LIKE / DISLIKE — RENDER BUTTONS IN ACTIVITY FEED
   ========================================================= */

add_action( 'bp_activity_entry_meta', function () {
    if ( ! is_user_logged_in() ) return;

    $aid  = bp_get_activity_id();
    $uid  = get_current_user_id();

    $l    = (int) bp_activity_get_meta( $aid, 'n_l' ) ?: 0;
    $d    = (int) bp_activity_get_meta( $aid, 'n_d' ) ?: 0;
    $is_l = bp_activity_get_meta( $aid, 'u_l_' . $uid );
    $is_d = bp_activity_get_meta( $aid, 'u_d_' . $uid );

    echo '<div class="nielit-reaction-wrapper">';
    echo '<a href="#" class="nielit-btn is-like ' . ( $is_l ? 'active' : '' ) . '" data-id="' . esc_attr( $aid ) . '" data-type="l">
            <span class="dashicons dashicons-thumbs-up"></span><span class="count-num l-count">' . $l . '</span>
          </a>';
    echo '<a href="#" class="nielit-btn is-dislike ' . ( $is_d ? 'active' : '' ) . '" data-id="' . esc_attr( $aid ) . '" data-type="d">
            <span class="dashicons dashicons-thumbs-down"></span><span class="count-num d-count">' . $d . '</span>
          </a>';
    echo '</div>';
} );

/* =========================================================
   LIKE / DISLIKE — AJAX HANDLER
   ========================================================= */

add_action( 'wp_ajax_nielit_react_final', function () {
    $aid  = intval( $_POST['id'] );
    $type = sanitize_text_field( $_POST['type'] );
    $uid  = get_current_user_id();

    if ( ! $aid || ! $uid ) wp_send_json_error();

    $l = (int) bp_activity_get_meta( $aid, 'n_l' ) ?: 0;
    $d = (int) bp_activity_get_meta( $aid, 'n_d' ) ?: 0;

    if ( $type === 'l' ) {
        if ( bp_activity_get_meta( $aid, 'u_l_' . $uid ) ) {
            bp_activity_delete_meta( $aid, 'u_l_' . $uid );
            $l = max( 0, $l - 1 );
        } else {
            if ( bp_activity_get_meta( $aid, 'u_d_' . $uid ) ) {
                bp_activity_delete_meta( $aid, 'u_d_' . $uid );
                $d = max( 0, $d - 1 );
            }
            bp_activity_update_meta( $aid, 'u_l_' . $uid, 1 );
            $l++;
        }
    } else {
        if ( bp_activity_get_meta( $aid, 'u_d_' . $uid ) ) {
            bp_activity_delete_meta( $aid, 'u_d_' . $uid );
            $d = max( 0, $d - 1 );
        } else {
            if ( bp_activity_get_meta( $aid, 'u_l_' . $uid ) ) {
                bp_activity_delete_meta( $aid, 'u_l_' . $uid );
                $l = max( 0, $l - 1 );
            }
            bp_activity_update_meta( $aid, 'u_d_' . $uid, 1 );
            $d++;
        }
    }

    bp_activity_update_meta( $aid, 'n_l', $l );
    bp_activity_update_meta( $aid, 'n_d', $d );

    wp_send_json_success( [ 'l' => $l, 'd' => $d ] );
} );

/* =========================================================
   POST MODAL — TOPIC SUGGESTIONS
   (extend this list as needed)
   ========================================================= */

function nielit_get_topic_suggestions() {
    return [
        'Developer', 'Flow', 'Certifications', 'Admin', 'Sales',
        'Marketing', 'Analytics', 'Support', 'Apex', 'Lightning',
        'Integration', 'Security', 'Reports', 'Automation', 'API',
        'Community', 'Training', 'Events', 'Announcements', 'Feedback',
    ];
}

/* =========================================================
   POST MODAL — HTML + CSS + JS
   ========================================================= */

add_action( 'wp_footer', function () {
    if ( ! is_user_logged_in() ) return;

    $nonce   = wp_create_nonce( 'nielit_community_post_nonce' );
    $topics  = nielit_get_topic_suggestions();
    ?>

    <!-- ═══════════════ POST MODAL CSS ═══════════════ -->
    <style>
    /* Overlay */
    #nielit-modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,.52); z-index: 99998;
        align-items: center; justify-content: center;
        padding: 16px; box-sizing: border-box;
    }
    #nielit-modal-overlay.open { display: flex; }

    /* Modal box */
    #nielit-post-modal {
        background: #fff; border-radius: 8px; width: 100%; max-width: 640px;
        max-height: 92vh; overflow-y: auto;
        box-shadow: 0 6px 30px rgba(0,0,0,.22);
        display: flex; flex-direction: column;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
    }

    /* Header */
    .nielit-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 22px; border-bottom: 1px solid #e5e7eb; flex-shrink: 0;
    }
    .nielit-modal-header h2 { margin: 0; font-size: 17px; font-weight: 700; color: #1a2537; }
    #nielit-close-modal-btn {
        background: none; border: none; font-size: 24px; line-height: 1;
        color: #6b7280; cursor: pointer; padding: 2px 6px; border-radius: 4px;
    }
    #nielit-close-modal-btn:hover { background: #f3f4f6; color: #111; }

    /* Body */
    .nielit-modal-body { padding: 18px 22px; flex: 1; }

    /* Post-type cards */
    .nielit-section-label {
        font-size: 12px; font-weight: 600; color: #6b7280;
        text-transform: uppercase; letter-spacing: .5px;
        margin-bottom: 8px; display: block;
    }
    .nielit-type-cards { display: flex; gap: 10px; margin-bottom: 18px; }
    .nielit-type-card {
        flex: 1; border: 2px solid #d1d5db; border-radius: 7px;
        padding: 11px 14px; cursor: pointer;
        display: flex; align-items: flex-start; gap: 10px;
        transition: border-color .15s, background .15s;
    }
    .nielit-type-card.active { border-color: #1b6ec2; background: #eff6ff; }
    .nielit-type-card input[type="radio"] {
        margin-top: 3px; accent-color: #1b6ec2; flex-shrink: 0;
        width: 15px; height: 15px;
    }
    .nielit-type-card-text strong { display: block; font-size: 13px; font-weight: 700; color: #1a2537; margin-bottom: 3px; }
    .nielit-type-card-text span   { font-size: 12px; color: #6b7280; line-height: 1.4; }

    /* Field groups */
    .nielit-field-group  { margin-bottom: 14px; }
    .nielit-field-label  { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; }
    .nielit-field-label .req { color: #dc2626; margin-right: 2px; }
    .nielit-field-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; }
    .nielit-guidelines-link { font-size: 12px; color: #1b6ec2; text-decoration: none; }
    .nielit-guidelines-link:hover { text-decoration: underline; }

    /* Question input */
    #nielit-question-input {
        width: 100%; padding: 9px 11px; border: 1px solid #d1d5db; border-radius: 5px;
        font-size: 14px; box-sizing: border-box; color: #111; outline: none;
        transition: border-color .15s;
    }
    #nielit-question-input:focus { border-color: #1b6ec2; box-shadow: 0 0 0 2px rgba(27,110,194,.12); }
    #nielit-question-input::placeholder { color: #9ca3af; }

    /* Char counter */
    .nielit-char-count { text-align: right; font-size: 11px; color: #9ca3af; margin-top: 3px; }

    /* Rich-text editor */
    .nielit-editor-wrapper {
        border: 1px solid #d1d5db; border-radius: 5px; overflow: hidden;
        transition: border-color .15s;
    }
    .nielit-editor-wrapper:focus-within { border-color: #1b6ec2; box-shadow: 0 0 0 2px rgba(27,110,194,.12); }
    .nielit-editor-toolbar {
        display: flex; align-items: center; flex-wrap: wrap; gap: 1px;
        padding: 5px 8px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
    }
    .nielit-tb-btn {
        background: none; border: none; border-radius: 4px; padding: 4px 7px;
        cursor: pointer; font-size: 13px; color: #374151; line-height: 1;
        transition: background .1s; display: inline-flex; align-items: center;
        justify-content: center; min-width: 26px; height: 26px;
    }
    .nielit-tb-btn:hover { background: #e5e7eb; }
    .nielit-tb-sep { width: 1px; height: 18px; background: #d1d5db; margin: 0 3px; flex-shrink: 0; }
    #nielit-details-editor {
        min-height: 120px; padding: 11px 12px; outline: none;
        font-size: 14px; color: #374151; line-height: 1.65; word-break: break-word;
    }
    #nielit-details-editor:empty::before {
        content: attr(data-placeholder); color: #9ca3af; pointer-events: none;
    }

    /* Topics / tag input */
    .nielit-topics-box {
        border: 1px solid #d1d5db; border-radius: 5px; padding: 6px 9px;
        display: flex; flex-wrap: wrap; align-items: center; gap: 5px;
        cursor: text; min-height: 42px; box-sizing: border-box; transition: border-color .15s;
    }
    .nielit-topics-box:focus-within { border-color: #1b6ec2; box-shadow: 0 0 0 2px rgba(27,110,194,.12); }
    #nielit-topic-input {
        border: none; outline: none; font-size: 14px; color: #374151;
        flex: 1; min-width: 150px; background: transparent; padding: 2px 0;
    }
    #nielit-topic-input::placeholder { color: #9ca3af; }
    .nielit-tag {
        display: inline-flex; align-items: center; gap: 4px;
        background: #eff6ff; color: #1b6ec2; border: 1px solid #bfdbfe;
        border-radius: 20px; padding: 2px 10px 2px 12px;
        font-size: 12px; font-weight: 500; white-space: nowrap;
    }
    .nielit-tag-remove {
        background: none; border: none; cursor: pointer; color: #93c5fd;
        font-size: 16px; line-height: 1; padding: 0;
        display: flex; align-items: center; transition: color .1s;
    }
    .nielit-tag-remove:hover { color: #dc2626; }

    /* Autocomplete dropdown */
    #nielit-topic-suggestions {
        position: absolute; background: #fff;
        border: 1px solid #d1d5db; border-radius: 6px;
        box-shadow: 0 4px 14px rgba(0,0,0,.12);
        z-index: 100001; max-height: 160px; overflow-y: auto; display: none;
    }
    .nielit-sugg-item { padding: 8px 13px; cursor: pointer; font-size: 13px; color: #374151; transition: background .1s; }
    .nielit-sugg-item:hover { background: #f3f4f6; }
    .nielit-sugg-item mark { background: none; color: #1b6ec2; font-weight: 700; }

    /* Footer */
    .nielit-modal-footer {
        padding: 12px 22px; border-top: 1px solid #e5e7eb;
        display: flex; justify-content: flex-end; flex-shrink: 0;
    }
    #nielit-submit-btn {
        background: #9ca3af; color: #fff; border: none; border-radius: 5px;
        padding: 10px 22px; font-size: 14px; font-weight: 600;
        cursor: not-allowed; transition: background .15s; white-space: nowrap;
    }
    #nielit-submit-btn.ready            { background: #1b6ec2; cursor: pointer; }
    #nielit-submit-btn.ready:hover      { background: #155da0; }
    </style>

    <!-- ═══════════════ POST MODAL HTML ═══════════════ -->
    <div id="nielit-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="nielit-modal-title">
        <div id="nielit-post-modal">

            <!-- Header -->
            <div class="nielit-modal-header">
                <h2 id="nielit-modal-title">Get help from the Community</h2>
                <button id="nielit-close-modal-btn" title="Close" aria-label="Close">×</button>
            </div>

            <!-- Body -->
            <div class="nielit-modal-body">

                <!-- Post Type -->
                <span class="nielit-section-label">Post Type</span>
                <div class="nielit-type-cards">

                    <label class="nielit-type-card active" id="nielit-card-question">
                        <input type="radio" name="nielit_post_type" value="question" checked>
                        <div class="nielit-type-card-text">
                            <strong>Ask a Question</strong>
                            <span>Need help with a technical issue? Ask the community.</span>
                        </div>
                    </label>

                    <label class="nielit-type-card" id="nielit-card-post">
                        <input type="radio" name="nielit_post_type" value="post">
                        <div class="nielit-type-card-text">
                            <strong>Create a Post</strong>
                            <span>Have advice or an announcement? Discuss it with the community.</span>
                        </div>
                    </label>

                </div>

                <!-- Question (shown only in Question mode) -->
                <div class="nielit-field-group" id="nielit-question-group">
                    <label class="nielit-field-label" for="nielit-question-input">
                        <span class="req">*</span> Question
                    </label>
                    <input type="text" id="nielit-question-input"
                           placeholder="Ask a question..." maxlength="255"
                           autocomplete="off">
                    <div class="nielit-char-count"><span id="nielit-q-num">0</span>/255</div>
                </div>

                <!-- Details (rich text) -->
                <div class="nielit-field-group">
                    <div class="nielit-field-header">
                        <label class="nielit-field-label" style="margin:0;">Details</label>
                        <a href="#" class="nielit-guidelines-link">ℹ️ Question Guidelines</a>
                    </div>
                    <div class="nielit-editor-wrapper">
                        <div class="nielit-editor-toolbar">
                            <button class="nielit-tb-btn" data-cmd="bold"                title="Bold"><b>B</b></button>
                            <button class="nielit-tb-btn" data-cmd="italic"              title="Italic"><em>I</em></button>
                            <button class="nielit-tb-btn" data-cmd="underline"           title="Underline"><u>U</u></button>
                            <button class="nielit-tb-btn" data-cmd="strikeThrough"       title="Strikethrough"><s>S</s></button>
                            <div class="nielit-tb-sep"></div>
                            <button class="nielit-tb-btn" data-cmd="insertUnorderedList" title="Bullet list">•—</button>
                            <button class="nielit-tb-btn" data-cmd="insertOrderedList"   title="Numbered list">1—</button>
                            <div class="nielit-tb-sep"></div>
                            <button class="nielit-tb-btn" id="nielit-tb-link"  title="Insert link">🔗</button>
                            <button class="nielit-tb-btn" id="nielit-tb-code"  title="Code block">&lt;/&gt;</button>
                            <button class="nielit-tb-btn" id="nielit-tb-image" title="Insert image">📷</button>
                        </div>
                        <div id="nielit-details-editor"
                             contenteditable="true"
                             data-placeholder="Add some details or context..."></div>
                    </div>
                    <div class="nielit-char-count"><span id="nielit-d-num">0</span>/9000</div>
                </div>

                <!-- Topics -->
                <div class="nielit-field-group" style="position:relative;">
                    <label class="nielit-field-label">
                        <span class="req">*</span> Add Topics
                    </label>
                    <div class="nielit-topics-box" id="nielit-topics-box">
                        <input type="text" id="nielit-topic-input"
                               placeholder="e.g. Developer, Flow, or Certifications"
                               autocomplete="off">
                    </div>
                    <div id="nielit-topic-suggestions"></div>
                </div>

            </div><!-- /.nielit-modal-body -->

            <!-- Footer -->
            <div class="nielit-modal-footer">
                <button id="nielit-submit-btn">Ask the Community</button>
            </div>

        </div>
    </div>

    <!-- ═══════════════ POST MODAL JS ═══════════════ -->
    <script>
    (function () {
        'use strict';

        var AJAX_URL    = '<?php echo esc_js( admin_url('admin-ajax.php') ); ?>';
        var NONCE       = '<?php echo esc_js( $nonce ); ?>';
        var SUGGESTIONS = <?php echo json_encode( $topics ); ?>;
        var MAX_Q       = 255;
        var MAX_D       = 9000;

        /* ── DOM refs ── */
        var overlay    = document.getElementById('nielit-modal-overlay');
        var titleEl    = document.getElementById('nielit-modal-title');
        var closeBtn   = document.getElementById('nielit-close-modal-btn');
        var submitBtn  = document.getElementById('nielit-submit-btn');
        var cardQ      = document.getElementById('nielit-card-question');
        var cardP      = document.getElementById('nielit-card-post');
        var qGroup     = document.getElementById('nielit-question-group');
        var qInput     = document.getElementById('nielit-question-input');
        var qNum       = document.getElementById('nielit-q-num');
        var editor     = document.getElementById('nielit-details-editor');
        var dNum       = document.getElementById('nielit-d-num');
        var topicsBox  = document.getElementById('nielit-topics-box');
        var topicInput = document.getElementById('nielit-topic-input');
        var suggBox    = document.getElementById('nielit-topic-suggestions');

        var currentType = 'question';
        var tags        = [];

        /* ── OPEN / CLOSE ── */
        function openModal() {
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () {
                if (currentType === 'question') qInput.focus();
                else editor.focus();
            }, 80);
        }
        function closeModal() {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
            hideSugg();
        }

        closeBtn.addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        /* ── INTERCEPT BP "WHAT'S NEW" FORM ── */
        document.addEventListener('DOMContentLoaded', function () {
            var bpTargets = document.querySelectorAll(
                '#whats-new-form, .activity-update-form, #new-post, #whats-new, #whats-new-textarea'
            );
            bpTargets.forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openModal();
                });
            });
        });

        /* Expose so theme buttons can also trigger the modal */
        window.nielitOpenPostModal = openModal;

        /* ── TYPE SWITCHING ── */
        function setType(type) {
            currentType = type;
            if (type === 'question') {
                cardQ.classList.add('active');
                cardP.classList.remove('active');
                cardQ.querySelector('input').checked = true;
                qGroup.style.display  = 'block';
                titleEl.textContent   = 'Get help from the Community';
                submitBtn.textContent = 'Ask the Community';
            } else {
                cardP.classList.add('active');
                cardQ.classList.remove('active');
                cardP.querySelector('input').checked = true;
                qGroup.style.display  = 'none';
                titleEl.textContent   = 'Post to the Community';
                submitBtn.textContent = 'Post to the Community';
            }
            validate();
        }

        cardQ.addEventListener('click', function () { setType('question'); });
        cardP.addEventListener('click', function () { setType('post'); });

        /* ── CHARACTER COUNTING ── */
        qInput.addEventListener('input', function () {
            qNum.textContent = this.value.length;
            validate();
        });

        editor.addEventListener('input', function () {
            var len = (this.innerText || '').replace(/\n$/, '').length;
            if (len > MAX_D) {
                var sel   = window.getSelection();
                var range = sel.getRangeAt(0);
                this.innerText = (this.innerText || '').substring(0, MAX_D);
                range = document.createRange();
                range.selectNodeContents(this);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
                len = MAX_D;
            }
            dNum.textContent = len;
            validate();
        });

        /* ── VALIDATION → enable submit ── */
        function validate() {
            var ok        = false;
            var hasTopics = tags.length > 0;

            if (currentType === 'question') {
                ok = qInput.value.trim().length > 0 && hasTopics;
            } else {
                ok = ((editor.innerText || '').trim().length > 0) && hasTopics;
            }

            submitBtn.disabled = !ok;
            submitBtn.classList.toggle('ready', ok);
        }

        /* ── RICH-TEXT TOOLBAR ── */
        document.querySelectorAll('.nielit-tb-btn[data-cmd]').forEach(function (btn) {
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
                editor.focus();
                document.execCommand(this.getAttribute('data-cmd'), false, null);
            });
        });

        document.getElementById('nielit-tb-link').addEventListener('mousedown', function (e) {
            e.preventDefault();
            editor.focus();
            var url = prompt('Enter URL (include https://):');
            if (url) document.execCommand('createLink', false, url);
        });

        document.getElementById('nielit-tb-code').addEventListener('mousedown', function (e) {
            e.preventDefault();
            editor.focus();
            document.execCommand('formatBlock', false, 'pre');
        });

        document.getElementById('nielit-tb-image').addEventListener('mousedown', function (e) {
            e.preventDefault();
            var url = prompt('Enter image URL:');
            if (url) document.execCommand('insertImage', false, url);
        });

        /* ── TOPICS / TAGS ── */
        topicsBox.addEventListener('click', function () { topicInput.focus(); });

        topicInput.addEventListener('input', function () {
            var val = this.value.trim();
            val ? showSugg(val) : hideSugg();
        });

        topicInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                var val = this.value.replace(/,/g, '').trim();
                if (val) addTag(val);
            } else if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
                removeTag(tags.length - 1);
            }
        });

        topicInput.addEventListener('blur', function () {
            setTimeout(hideSugg, 150);
        });

        function addTag(text) {
            var clean = text.trim();
            if (!clean || tags.indexOf(clean) !== -1) return;
            tags.push(clean);

            var chip = document.createElement('div');
            chip.className   = 'nielit-tag';
            chip.dataset.tag = clean;
            chip.innerHTML   = clean +
                ' <button class="nielit-tag-remove" type="button" title="Remove">×</button>';
            chip.querySelector('.nielit-tag-remove').addEventListener('click', function () {
                removeTag(tags.indexOf(clean));
            });
            topicsBox.insertBefore(chip, topicInput);

            topicInput.value = '';
            topicInput.placeholder = '';
            hideSugg();
            validate();
        }

        function removeTag(index) {
            if (index < 0 || index >= tags.length) return;
            var text = tags[index];
            tags.splice(index, 1);
            var chip = topicsBox.querySelector('.nielit-tag[data-tag="' + CSS.escape(text) + '"]');
            if (chip) chip.remove();
            if (tags.length === 0) topicInput.placeholder = 'e.g. Developer, Flow, or Certifications';
            validate();
        }

        function showSugg(val) {
            var lower    = val.toLowerCase();
            var filtered = SUGGESTIONS.filter(function (s) {
                return s.toLowerCase().indexOf(lower) > -1 && tags.indexOf(s) === -1;
            });
            if (!filtered.length) { hideSugg(); return; }

            suggBox.innerHTML = '';
            filtered.slice(0, 8).forEach(function (s) {
                var item = document.createElement('div');
                item.className = 'nielit-sugg-item';
                var idx = s.toLowerCase().indexOf(lower);
                item.innerHTML =
                    s.substring(0, idx) +
                    '<mark>' + s.substring(idx, idx + val.length) + '</mark>' +
                    s.substring(idx + val.length);
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    addTag(s);
                });
                suggBox.appendChild(item);
            });

            var rect = topicsBox.getBoundingClientRect();
            suggBox.style.top   = (topicsBox.offsetTop + topicsBox.offsetHeight + 2) + 'px';
            suggBox.style.left  = topicsBox.offsetLeft + 'px';
            suggBox.style.width = topicsBox.offsetWidth + 'px';
            suggBox.style.display = 'block';
        }

        function hideSugg() { suggBox.style.display = 'none'; }

        /* ── SUBMIT ── */
        submitBtn.addEventListener('click', function () {
            if (!this.classList.contains('ready')) return;

            var originalLabel = this.textContent;
            this.disabled    = true;
            this.textContent = 'Posting…';
            this.classList.remove('ready');

            var formData = new FormData();
            formData.append('action',    'nielit_community_post');
            formData.append('nonce',     NONCE);
            formData.append('post_type', currentType);
            formData.append('question',  qInput.value.trim());
            formData.append('details',   editor.innerHTML);
            formData.append('topics',    JSON.stringify(tags));

            fetch(AJAX_URL, { method: 'POST', body: formData })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        closeModal();
                        window.location.reload();
                    } else {
                        alert('Could not post. Please try again.');
                        resetSubmitBtn(originalLabel);
                    }
                })
                .catch(function () {
                    alert('Network error. Please try again.');
                    resetSubmitBtn(originalLabel);
                });
        });

        function resetSubmitBtn(label) {
            submitBtn.textContent = label;
            submitBtn.disabled    = false;
            submitBtn.classList.add('ready');
        }

    })();
    </script>

<?php }, 20 );

/* =========================================================
   POST MODAL — AJAX HANDLER
   ========================================================= */

add_action( 'wp_ajax_nielit_community_post', function () {
    check_ajax_referer( 'nielit_community_post_nonce', 'nonce' );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not logged in' );
    }

    $post_type  = sanitize_text_field( $_POST['post_type'] ?? 'post' );
    $question   = sanitize_text_field( $_POST['question']  ?? '' );
    $details    = wp_kses_post(         $_POST['details']  ?? '' );
    $topics_raw = json_decode( stripslashes( $_POST['topics'] ?? '[]' ), true );
    $topics     = is_array( $topics_raw ) ? array_map( 'sanitize_text_field', $topics_raw ) : [];

    /* Build activity content */
    $content = '';
    if ( $post_type === 'question' && ! empty( $question ) ) {
        $content = '<p><strong>' . esc_html( $question ) . '</strong></p>';
        if ( ! empty( $details ) ) {
            $content .= $details;
        }
    } else {
        $content = $details;
    }

    /* Append topic hashtags */
    if ( ! empty( $topics ) ) {
        $hashtags = implode( ' ', array_map( function ( $t ) {
            return '<span style="color:#1b6ec2;font-weight:500;">#' . esc_html( preg_replace( '/\s+/', '', $t ) ) . '</span>';
        }, $topics ) );
        $content .= '<p style="margin-top:8px;font-size:13px;">' . $hashtags . '</p>';
    }

    if ( empty( trim( wp_strip_all_tags( $content ) ) ) ) {
        wp_send_json_error( 'Empty content' );
    }

    $activity_id = bp_activity_add( [
        'user_id'   => get_current_user_id(),
        'content'   => $content,
        'type'      => 'activity_update',
        'component' => buddypress()->activity->id,
    ] );

    if ( $activity_id ) {
        bp_activity_update_meta( $activity_id, 'nielit_post_type', $post_type );

        if ( $post_type === 'question' && ! empty( $question ) ) {
            bp_activity_update_meta( $activity_id, 'nielit_question', $question );
        }
        if ( ! empty( $topics ) ) {
            bp_activity_update_meta( $activity_id, 'nielit_topics', implode( ',', $topics ) );
        }

        wp_send_json_success( [ 'activity_id' => $activity_id ] );
    } else {
        wp_send_json_error( 'BuddyPress failed to save activity' );
    }
} );
