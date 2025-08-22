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

    // Handle column preferences checkbox change - save immediately via AJAX
    $(document).on("change", "#aerp-column-options-form input[name='aerp_visible_columns[]']", function () {
        var $checkbox = $(this);
        var $form = $checkbox.closest("#aerp-column-options-form");
        var allColumns = [];
        var checkedColumns = [];

        // Show loading state on checkbox
        var $originalCheckbox = $checkbox.clone();
        // $checkbox.prop('disabled', true).addClass('opacity-50');

        // Get all available columns
        $form.find('input[name="aerp_visible_columns[]"]').each(function () {
            allColumns.push($(this).val());
        });

        // Get checked columns
        $form.find('input[name="aerp_visible_columns[]"]:checked').each(function () {
            checkedColumns.push($(this).val());
        });

        // Calculate hidden columns
        var hiddenColumns = allColumns.filter(function (col) {
            return $.inArray(col, checkedColumns) === -1;
        });

        // Lưu ngay preferences qua AJAX và giữ dropdown mở
        keepColumnDropdownOpen = true;
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
                    // Reload table to reflect changes immediately
                    var $tableWrapper = $checkbox.closest('.aerp-table-wrapper');
                    if ($tableWrapper.length > 0) {
                        var $searchForm = $tableWrapper.find('form.aerp-table-search-form, form.aerp-table-ajax-form').first();
                        if ($searchForm.length > 0) {
                            reloadTable($searchForm);
                        } else {
                            // Fallback: reload page if no search form found
                            location.reload();
                        }
                    } else {
                        // Fallback: reload page if no table wrapper found
                        location.reload();
                    }
                } else {
                    console.error("Error saving column preferences:", response.data);
                    // Revert checkbox state if save failed
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                }
            },
            error: function () {
                console.error("Failed to save column preferences");
                // Revert checkbox state if save failed
                $checkbox.prop('checked', !$checkbox.prop('checked'));
            },
        });
    });

    // --- AJAX TABLE OPERATIONS ---

    // Handle form submissions
    $(document).on("submit", ".aerp-table-ajax-form", function (e) {
        e.preventDefault();
        reloadTable($(this));
    });

    // Live search with debouncing và sau 1s
    var aerpTableSearchTimeout;
    $(document).on("input", ".aerp-table-search-input", function (e) {
        e.preventDefault();
        var $input = $(this);
        var $form = $input.closest("form");
        var val = $input.val() || "";
        clearTimeout(aerpTableSearchTimeout);
        if (val.length >= 10) {
            aerpTableSearchTimeout = setTimeout(function () {
                reloadTable($form);
            }, 1000); // 1s delay
        }
    });

    // Handle pagination
    $(document).on("click", ".pagination-links a, .pagination-links.aerp-pagination a", function (e) {
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
    // Ghi nhớ trạng thái dropdown để giữ mở sau khi reload AJAX
    var keepColumnDropdownOpen = false;
    var columnDropdownScrollTop = 0;

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
        
        // Trước khi reload: nếu dropdown đang mở thì ghi nhớ để mở lại
        var $dropdown = $("#aerp-column-options-dropdown");
        keepColumnDropdownOpen = $dropdown.is(":visible");
        if (keepColumnDropdownOpen) {
            columnDropdownScrollTop = $dropdown.scrollTop();
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
                    // Sau khi reload: nếu cần giữ mở dropdown thì mở lại
                    if (keepColumnDropdownOpen) {
                        var $btn = $("#aerp-column-options-button");
                        var $newDropdown = $("#aerp-column-options-dropdown");
                        if ($newDropdown.length) {
                            $newDropdown.show();
                            // Khôi phục scroll
                            $newDropdown.scrollTop(columnDropdownScrollTop || 0);
                        } else if ($btn.length) {
                            // Fallback: toggle để đảm bảo mở
                            $btn.next("#aerp-column-options-dropdown").show();
                        }
                    }
                    // Phát sự kiện tuỳ chỉnh để các handler khác có thể hook vào sau reload
                    $(document).trigger('aerp:tableReloaded', [$tableWrapper]);
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

jQuery(document).ready(function ($) {
    // Đóng tất cả menu con khi load
    $(".collapsible-menu-content").hide();

    // Mở menu con nếu có nav-link active bên trong
    $(".collapsible-menu-content").each(function () {
        var $content = $(this);
        if ($content.find('.nav-link.active').length > 0) {
            $content.show();
            // Đổi icon thành fa-chevron-up cho menu đang mở
            var $header = $content.prev(".collapsible-menu-header");
            $header.find(".fa-chevron-down, .fa-chevron-left").removeClass("fa-chevron-down fa-chevron-left").addClass("fa-chevron-up");
        }
    });
    $(".collapsible-menu-header").off("click.aerpMenuToggle").on("click.aerpMenuToggle", function () {
        var $header = $(this);
        var $content = $header.next(".collapsible-menu-content");
        $content.stop(true, true).slideToggle(200, function() {
            // Optionally, you can do something after toggle
        });
        $header.find(".fa-chevron-down, .fa-chevron-left, .fa-chevron-up").each(function() {
            if ($(this).hasClass("fa-chevron-down") || $(this).hasClass("fa-chevron-left")) {
                $(this).removeClass("fa-chevron-down fa-chevron-left").addClass("fa-chevron-up");
            } else if ($(this).hasClass("fa-chevron-up")) {
                $(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
            }
        });
    });

    //Auto close alert noti
    if ($(".alert.alert-success").length) {
        setTimeout(
            () =>
                $(".alert.alert-success").fadeOut(300, function () {
                    $(this).remove();
                }),
            5000
        );
    }
});