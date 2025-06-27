jQuery(document).ready(function ($) {
    // --- CORE TABLE FUNCTIONALITY ---

    // Select all checkbox functionality
    $(document).on("change", "#cb-select-all-1", function () {
        var isChecked = $(this).prop("checked");
        $('input[name="bulk_items[]"]').prop("checked", isChecked);
    });

    // Column options functionality
    $(document).on("click", "#aerp-column-options-button", function (e) {
        e.preventDefault();
        $(this).next("#aerp-column-options-dropdown").toggle();
    });

    // Close column options dropdown when clicking outside
    $(document).on("click", function (e) {
        if (!$(e.target).closest("#aerp-column-options-button, #aerp-column-options-dropdown").length) {
            $("#aerp-column-options-dropdown").hide();
        }
    });

    // Handle column preferences form submission
    $(document).on("submit", "#aerp-column-options-form", function (e) {
        e.preventDefault();
        var $form = $(this);
        var formData = $form.serializeArray();
        var allColumns = [];
        var checkedColumns = [];

        // Get all available columns
        $form.find('input[name="aerp_visible_columns[]"]').each(function () {
            allColumns.push($(this).val());
        });

        // Get checked columns
        $.each(formData, function (i, field) {
            if (field.name === "aerp_visible_columns[]") {
                checkedColumns.push(field.value);
            }
        });

        // Calculate hidden columns
        var hiddenColumns = allColumns.filter(function (col) {
            return $.inArray(col, checkedColumns) === -1;
        });

        // Save preferences via AJAX
        $.ajax({
            url: aerp_table_ajax.ajax_url,
            type: "POST",
            data: {
                action: "aerp_save_column_preferences",
                _ajax_nonce: aerp_table_ajax.nonce,
                hidden_columns: hiddenColumns,
                option_key: $form.find('input[name="option_key"]').val(),
            },
            success: function (response) {
                if (response.success) {
                    $form.closest("#aerp-column-options-dropdown").hide();
                    location.reload();
                } else {
                    console.error("Error saving column preferences:", response.data);
                }
            },
            error: function () {
                console.error("Failed to save column preferences");
            },
        });
    });

    // --- AJAX TABLE OPERATIONS ---

    // Handle form submissions
    $(document).on("submit", ".aerp-table-ajax-form", function (e) {
        e.preventDefault();
        reloadTable($(this));
    });

    // Live search with debouncing
    var searchTimeout;
    $(document).on("input", ".aerp-table-search-input", function (e) {
        e.preventDefault();
        clearTimeout(searchTimeout);
        var $form = $(this).closest("form");
        searchTimeout = setTimeout(function () {
            reloadTable($form);
        }, 400);
    });

    // Handle pagination
    $(document).on("click", ".pagination-links a, .aerp-pagination a", function (e) {
        e.preventDefault();
        var href = $(this).attr("href");
        var paged = 1;
        var match = href.match(/paged=(\d+)/);
        if (match) {
            paged = parseInt(match[1]);
        }
        var $form = $(this).closest(".aerp-table-wrapper").find("form.aerp-table-search-form, form.aerp-table-ajax-form").first();
        reloadTable($form, { paged: paged });
    });

    // Handle table sorting
    $(document).on("click", ".aerp-table-sort", function (e) {
        e.preventDefault();
        var $form = $(this).closest(".aerp-table-wrapper").find("form.aerp-table-search-form, form.aerp-table-ajax-form").first();
        var sortData = {
            orderby: $(this).data("orderby"),
            order: $(this).data("order"),
        };
        reloadTable($form, sortData);
    });

    // --- CORE AJAX FUNCTION ---
    var isLoading = false;

    function reloadTable($form, additionalData) {
        if (isLoading) {
            return;
        }
        isLoading = true;

        // Ưu tiên lấy từ data-table-wrapper nếu có
        var wrapperSelector = $form.data("table-wrapper");
        var $tableWrapper;
        if (wrapperSelector) {
            $tableWrapper = $(wrapperSelector);
            if ($tableWrapper.length === 0) {
                $tableWrapper = $form.closest('[id$="-table-wrapper"]');
            }
        } else {
            $tableWrapper = $form.closest('[id$="-table-wrapper"]');
        }
        if ($tableWrapper.length === 0) {
            $tableWrapper = $('[id$="-table-wrapper"]').first();
        }

        // Prepare data
        var data = $form.serializeArray();
        var ajaxAction = $form.data("ajax-action") || "aerp_crm_filter_customers";
        data.push({ name: "action", value: ajaxAction });
        
        var employeeId = $tableWrapper.data('employee-id');
        if (employeeId && !data.some(item => item.name === 'employee_id')) {
            data.push({ name: 'employee_id', value: employeeId });
        }
        
        if (additionalData) {
            $.each(additionalData, function(key, value) {
                // Remove existing key if present
                data = data.filter(item => item.name !== key);
                data.push({ name: key, value: value });
            });
        }
        
        // Execute AJAX request
        $.ajax({
            url: aerp_table_ajax.ajax_url,
            type: 'POST',
            data: $.param(data),
            beforeSend: function () {
                showLoadingOverlay($tableWrapper);
            },
            success: function (response) {
                if (response.success) {
                    $tableWrapper.html(response.data.html);
                } else {
                    console.error("Table reload failed:", response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX request failed:", error);
            },
            complete: function () {
                isLoading = false;
                hideLoadingOverlay($tableWrapper);
            }
        });
    }

    // --- UTILITY FUNCTIONS ---

    function showLoadingOverlay($wrapper) {
        $wrapper.css("position", "relative").append('<div class="aerp-table-loading-overlay"><div class="aerp-table-spinner"></div></div>');
    }

    function hideLoadingOverlay($wrapper) {
        $wrapper.find(".aerp-table-loading-overlay").remove();
    }
});

// jQuery(document).ready(function ($) {
//     $(".collapsible-menu-header").on("click", function () {
//         $(this).next(".collapsible-menu-content").slideToggle(200); // Adjust speed as needed
//         $(this).find(".fa-chevron-down, .fa-chevron-up").toggleClass("fa-chevron-down fa-chevron-up");
//     });
// });
