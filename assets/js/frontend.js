jQuery(function ($) {
    // ===== Thêm task
    $("#taskPopup .aerp-hrm-task-popup-close").on("click", () => $("#taskPopup").removeClass("active"));
    $("[data-open-aerp-hrm-task-popup]").on("click", () => $("#taskPopup").addClass("active"));

    // ===== Sửa task
    $("#editTaskPopup .aerp-hrm-task-popup-close").on("click", () => $("#editTaskPopup").removeClass("active"));

    // ===== Thêm thưởng/phạt
    $("#aerp-adjustment-popup .aerp-popup-close").on("click", () => $("#aerp-adjustment-popup").removeClass("active"));
    $("[data-open-adjustment-popup]").on("click", () => $("#aerp-adjustment-popup").addClass("active"));

    window.openEditTaskPopup = function (task) {
        $("#edit_task_id").val(task.id);
        $("#edit_task_title").val(task.task_title);
        $("#edit_task_desc").val(task.task_desc);
        $("#edit_task_deadline").val(task.deadline);
        $("#edit_task_score").val(task.score !== undefined ? task.score : "");
        $("#editTaskPopup").addClass("active");
    };

    // window.closeaerp-hrm-adjustmentPopup = function() {
    //   $('#aerp-hrm-adjustmentPopup').removeClass('active');
    // };

    // ===== Click ngoài popup để đóng
    $(document).on("click", function (e) {
        if (
            $("#taskPopup").hasClass("active") &&
            !$(e.target).closest("#taskPopup .aerp-hrm-task-popup-inner, [data-open-aerp-hrm-task-popup]").length
        ) {
            $("#taskPopup").removeClass("active");
        }
        if (
            $("#editTaskPopup").hasClass("active") &&
            !$(e.target).closest('#editTaskPopup .aerp-hrm-task-popup-inner, [onclick^="openEditTaskPopup"]').length
        ) {
            $("#editTaskPopup").removeClass("active");
        }
        if (
            $("#aerp-adjustment-popup").hasClass("active") &&
            !$(e.target).closest("#aerp-adjustment-popup .aerp-popup-content, [data-open-adjustment-popup]").length
        ) {
            $("#aerp-adjustment-popup").removeClass("active");
        }
        // Đóng popup bình luận khi click ngoài
        if (
            $("#taskCommentPopup").hasClass("active") &&
            !$(e.target).closest("#taskCommentPopup .aerp-hrm-task-popup-inner, .aerp-task-comment-btn").length
        ) {
            $("#taskCommentPopup").removeClass("active");
        }
    });

    // ===== Toast
    $("#aerp-hrm-toast button").on("click", function () {
        $("#aerp-hrm-toast").remove();
    });

    if ($("#aerp-hrm-toast").length) {
        setTimeout(
            () =>
                $("#aerp-hrm-toast").fadeOut(300, function () {
                    $(this).remove();
                }),
            5000
        );
    }

    // ===== Đóng popup cho tất cả các popup sử dụng .aerp-popup-close
    $(".aerp-popup-close").on("click", function () {
        $(this).closest(".aerp-popup, .aerp-hrm-task-popup").removeClass("active");
        $(this).closest(".aerp-popup-content, .aerp-hrm-task-popup-inner").parent().removeClass("active");
        // Hỗ trợ cho cả popup dạng .aerp-popup và .aerp-hrm-task-popup
        $("#taskPopup, #editTaskPopup, #aerp-adjustment-popup").removeClass("active");
    });

    // ===== Popup bình luận task (AJAX)
    $(".aerp-task-comment-btn").on("click", function () {
        var taskId = $(this).data("task-id");
        var taskTitle = $(this).data("task-title");
        var $popup = $("#taskCommentPopup");
        var $content = $("#taskCommentPopupContent");
        $("#taskCommentPopupTitle").text("Bình luận: " + taskTitle);
        $content.html(
            '<div style="padding:30px;text-align:center"><span class="dashicons dashicons-update spin"></span> Đang tải...</div>'
        );
        $popup.addClass("active");
        $.post(aerpFrontend.ajaxurl, { action: "aerp_get_task_comments", task_id: taskId }, function (res) {
            if (res.success) {
                $content.html(res.data.html);
            } else {
                $content.html('<div class="aerp-no-comments">Không lấy được bình luận</div>');
            }
        });
    });
    $("#taskCommentPopup .aerp-hrm-task-popup-close").on("click", function () {
        $("#taskCommentPopup").removeClass("active");
    });
});
