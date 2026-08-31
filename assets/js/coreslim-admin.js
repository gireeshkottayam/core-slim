/* CoreSlim Admin JS - instant toggle save, presets, export/import */
(function ($) {
    'use strict';

    function showFeedback(msg, ok) {
        var el = $('.coreslim-feedback');
        el.removeClass('ok err').addClass(ok ? 'ok' : 'err').text(msg).show();
        if (ok) {
            setTimeout(function () { el.hide(); }, 3000);
        }
    }

    /* Save a single toggle immediately on change */
    $('.coreslim-row').on('change', '.coreslim-toggle input, select[data-select]', function () {
        var formData = {};
        $('.coreslim-toggle input').each(function () {
            formData[$(this).data('key')] = $(this).is(':checked') ? '1' : '0';
        });
        $('select[data-select]').each(function () {
            formData[$(this).data('select')] = $(this).val();
        });
        $.post(CORE_SLIM.ajax_url, {
            action: 'core_slim_save_all',
            nonce: CORE_SLIM.nonce,
            settings: formData
        }).done(function (res) {
            if (res && res.success) {
                showFeedback('Settings saved.', true);
            } else {
                showFeedback('Failed to save settings.', false);
            }
        });
    });

    /* Preset buttons */
    $('.coreslim-preset-btn').on('click', function () {
        var preset = $(this).data('preset');
        var btn = $(this);
        btn.prop('disabled', true);
        $.post(CORE_SLIM.ajax_url, {
            action: 'core_slim_preset',
            nonce: CORE_SLIM.nonce,
            preset: preset
        }).done(function (res) {
            if (res && res.success) {
                applySettings(res.data.settings);
                showFeedback('Preset applied: ' + preset + '.', true);
            } else {
                showFeedback('Failed to apply preset.', false);
            }
        }).always(function () {
            btn.prop('disabled', false);
        });
    });

    /* Export */
    $('.coreslim-export-btn').on('click', function () {
        $.post(CORE_SLIM.ajax_url, {
            action: 'core_slim_export',
            nonce: CORE_SLIM.nonce
        }).done(function (res) {
            if (res && res.success) {
                $('#coreslim-transfer-area').val(res.data.json);
                showFeedback('JSON exported to the textarea. Copy it to back up.', true);
            } else {
                showFeedback('Export failed.', false);
            }
        });
    });

    /* Import */
    $('.coreslim-import-btn').on('click', function () {
        var json = $('#coreslim-transfer-area').val();
        if (!json) {
            showFeedback('Paste JSON to import first.', false);
            return;
        }
        $.post(CORE_SLIM.ajax_url, {
            action: 'core_slim_import',
            nonce: CORE_SLIM.nonce,
            json: json
        }).done(function (res) {
            if (res && res.success) {
                applySettings(res.data.settings);
                showFeedback('Settings imported.', true);
            } else {
                showFeedback('Import failed. Invalid JSON.', false);
            }
        });
    });

    /* Reflect server settings into the UI */
    function applySettings(settings) {
        $.each(settings, function (key, val) {
            var check = $('.coreslim-toggle input[data-key="' + key + '"]');
            if (check.length) {
                check.prop('checked', val ? true : false);
                return;
            }
            var sel = $('select[data-select="' + key + '"]');
            if (sel.length) {
                sel.val(String(val));
            }
        });
    }

    /* Also handle the native form submit to persist without AJAX */
    $('#coreslim-form').on('submit', function (e) {
        e.preventDefault();
        var formData = {};
        $('.coreslim-toggle input').each(function () {
            formData[$(this).data('key')] = $(this).is(':checked') ? '1' : '0';
        });
        $('select[data-select]').each(function () {
            formData[$(this).data('select')] = $(this).val();
        });
        $.post(CORE_SLIM.ajax_url, {
            action: 'core_slim_save_all',
            nonce: CORE_SLIM.nonce,
            settings: formData
        }).done(function (res) {
            if (res && res.success) {
                showFeedback('Settings saved.', true);
            } else {
                showFeedback('Failed to save settings.', false);
            }
        });
    });
})(jQuery);
