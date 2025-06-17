document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('cb-select-all-1');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="bulk_items[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
}); 
jQuery(document).ready(function($) {
    // Toggle column options dropdown
    $('#aerp-column-options-button').on('click', function(e) {
        e.preventDefault();
        $('#aerp-column-options-dropdown').toggle();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#aerp-column-options-dropdown').length && !$(e.target).is('#aerp-column-options-button')) {
            $('#aerp-column-options-dropdown').hide();
        }
    });

    $('#aerp-column-options-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var hiddenColumns = [];
        var allColumns = [];
        $('input[name="aerp_visible_columns[]"]').each(function() {
            allColumns.push($(this).val());
        });
        var optionKey = $('input[name="option_key"]').val();

        // Determine which columns are NOT checked (i.e., hidden)
        var checkedColumns = [];
        $.each(formData, function(i, field) {
            if (field.name === 'aerp_visible_columns[]') {
                checkedColumns.push(field.value);
            }
        });

        $.each(allColumns, function(i, columnKey) {
            if ($.inArray(columnKey, checkedColumns) === -1) {
                hiddenColumns.push(columnKey);
            }
        });

        $.ajax({
            url: aerp_table_ajax.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'aerp_save_column_preferences',
                _ajax_nonce: aerp_table_ajax.nonce,
                hidden_columns: hiddenColumns,
                option_key: optionKey
            },
            success: function(response) {
                if (response.success) {
                    // Tùy chọn đã được lưu thành công, không cần alert
                    $('#aerp-column-options-dropdown').hide(); // Hide dropdown on success
                    location.reload(); // Reload to apply changes
                } else {
                    console.error('Lỗi: ' + response.data); // Ghi log lỗi vào console
                }
            },
            error: function() {
                console.error('Có lỗi xảy ra khi lưu tùy chọn.'); // Ghi log lỗi vào console
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('.collapsible-menu-header').on('click', function() {
        $(this).next('.collapsible-menu-content').slideToggle(200); // Adjust speed as needed
        $(this).find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
    });
}); 