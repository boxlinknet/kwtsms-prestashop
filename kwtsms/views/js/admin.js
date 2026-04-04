/**
 * kwtSMS - Admin JavaScript
 *
 * Handles:
 *   - Copy cron URL to clipboard (Gateway tab)
 *   - Integration toggle label update (Settings tab)
 *   - Confirm dialog before clearing logs (Logs tab)
 *
 * Requires jQuery (available in PrestaShop 8 back office by default).
 *
 * @author    kwtSMS <support@kwtsms.com>
 * @copyright kwtSMS
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0
 */

/* global jQuery, document */

(function ($) {
    'use strict';

    // =========================================================================
    // Copy Cron URL to Clipboard
    // =========================================================================

    /**
     * Copy the value of #kwtsms-cron-url to the clipboard.
     * Shows brief visual feedback on the copy button.
     *
     * @param {HTMLElement} btn - The button element that was clicked.
     */
    function copyCronUrl(btn) {
        var input = document.getElementById('kwtsms-cron-url');
        if (!input) {
            return;
        }

        // Prefer modern Clipboard API; fall back to execCommand.
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value).then(function () {
                showCopySuccess(btn);
            }).catch(function () {
                legacyCopy(input, btn);
            });
        } else {
            legacyCopy(input, btn);
        }
    }

    /**
     * Fallback copy using select + execCommand.
     *
     * @param {HTMLInputElement} input
     * @param {HTMLElement}      btn
     */
    function legacyCopy(input, btn) {
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
            showCopySuccess(btn);
        } catch (e) {
            // Nothing to do - browser may have blocked it.
        }
        // Deselect
        if (window.getSelection) {
            window.getSelection().removeAllRanges();
        }
    }

    /**
     * Briefly change the copy button label to confirm success.
     *
     * @param {HTMLElement} btn
     */
    function showCopySuccess(btn) {
        var $btn = $(btn);
        var originalHtml = $btn.html();
        $btn.html('<i class="icon-check"></i> Copied!');
        $btn.addClass('btn-copy-success').removeClass('btn-default');
        setTimeout(function () {
            $btn.html(originalHtml);
            $btn.removeClass('btn-copy-success').addClass('btn-default');
        }, 2000);
    }

    // Expose globally so the inline onclick in gateway.tpl can call it.
    window.kwtsmsCopyCronUrl = copyCronUrl;

    // =========================================================================
    // Integration Toggles: live badge update (Settings tab)
    // =========================================================================

    /**
     * When a checkbox inside .kwtsms-toggle-label changes, update the
     * status badge next to it to reflect the new state without a page reload.
     */
    function bindToggleLabels() {
        $(document).on('change', '.kwtsms-toggle-label input[type="checkbox"]', function () {
            var $checkbox = $(this);
            var $label = $checkbox.closest('.kwtsms-toggle-label');
            var $badge = $label.find('.label-kwtsms-ok, .label-kwtsms-err');

            if ($checkbox.is(':checked')) {
                $badge.removeClass('label-kwtsms-err').addClass('label-kwtsms-ok').text('On');
            } else {
                $badge.removeClass('label-kwtsms-ok').addClass('label-kwtsms-err').text('Off');
            }
        });
    }

    // =========================================================================
    // Clear Logs Confirmation (Logs tab)
    // =========================================================================

    /**
     * Attach a confirmation dialog to the Clear All Logs form before submit.
     * The template already has an inline onsubmit, but this provides a
     * jQuery-based fallback / override that works consistently.
     */
    function bindClearLogsConfirm() {
        $(document).on('submit', 'form[data-kwtsms-clear-logs]', function (e) {
            if (!window.confirm('Are you sure you want to delete ALL log entries? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    }

    // =========================================================================
    // Chip Click: copy placeholder to active textarea cursor position
    // =========================================================================

    /**
     * Clicking a placeholder chip inserts its text at the cursor position
     * of the most recently focused textarea within the same column.
     */
    function bindChipInsert() {
        var $lastTextarea = null;

        // Track the last focused textarea
        $(document).on('focus', '.kwtsms-placeholder-chips').on('focus', 'textarea', function () {
            $lastTextarea = $(this);
        });

        $(document).on('click', '.kwtsms-chip', function () {
            var chip = $(this).text();
            if (!$lastTextarea || !$lastTextarea.length) {
                // Fall back: look for a textarea in the same column
                var $col = $(this).closest('.col-md-6');
                $lastTextarea = $col.find('textarea');
            }

            if (!$lastTextarea || !$lastTextarea.length) {
                return;
            }

            var textarea = $lastTextarea[0];
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var val = textarea.value;
            textarea.value = val.substring(0, start) + chip + val.substring(end);
            textarea.selectionStart = textarea.selectionEnd = start + chip.length;
            textarea.focus();
        });
    }

    // =========================================================================
    // Tab URL management
    //
    // PrestaShop reloads the page when switching tabs (server-side rendering),
    // so there is no client-side tab switching to manage here.
    // The active tab is determined by the "tab" query parameter.
    // =========================================================================

    // =========================================================================
    // Bootstrap
    // =========================================================================

    $(document).ready(function () {
        bindToggleLabels();
        bindClearLogsConfirm();
        bindChipInsert();

        // Replace inline onclick on the cron copy button with a cleaner handler.
        // The gateway.tpl button uses an inline onclick - this replaces it.
        $(document).on('click', '#kwtsms-cron-copy-btn', function (e) {
            e.preventDefault();
            copyCronUrl(this);
        });
    });

}(jQuery));
