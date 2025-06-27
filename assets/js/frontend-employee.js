document.addEventListener("DOMContentLoaded", function () {
    function updatePermissionCheckboxes() {
        // Lấy tất cả role đang được check
        let checkedRoles = Array.from(document.querySelectorAll(".role-checkbox:checked")).map((cb) => cb.getAttribute("data-role-id"));
        // Tập hợp tất cả permission_id đã có qua role
        let permsViaRole = new Set();
        checkedRoles.forEach((roleId) => {
            if (rolePermissionsMap[roleId]) {
                rolePermissionsMap[roleId].forEach((pid) => permsViaRole.add(String(pid)));
            }
        });
        // Cập nhật trạng thái các checkbox quyền đặc biệt
        document.querySelectorAll(".perm-checkbox").forEach((cb) => {
            let pid = cb.getAttribute("data-perm-id");
            if (permsViaRole.has(pid)) {
                cb.checked = false;
                cb.disabled = true;
                cb.parentElement.style.color = "#888";
                if (!cb.parentElement.querySelector(".perm-via-role")) {
                    let span = document.createElement("span");
                    span.className = "perm-via-role";
                    span.style = "color:#888; font-weight:bold; font-size:12px; margin-left:4px;";
                    span.innerText = "(Đã có qua nhóm quyền)";
                    cb.parentElement.appendChild(span);
                }
            } else {
                cb.disabled = false;
                cb.parentElement.style.color = "";
                let span = cb.parentElement.querySelector(".perm-via-role");
                if (span) span.remove();
            }
        });
    }
    // Gắn sự kiện
    document.querySelectorAll(".role-checkbox").forEach((cb) => {
        cb.addEventListener("change", updatePermissionCheckboxes);
    });
    // Khởi tạo lần đầu
    updatePermissionCheckboxes();
});
jQuery(function ($) {
    // ✅ Chuyển tab Upload / Thủ công
    $(".tab-switcher a").on("click", function (e) {
        e.preventDefault();
        $(".tab-switcher a").removeClass("active");
        $(this).addClass("active");

        const target = $(this).data("target");
        $(".attachment-tab").hide();
        $("#tab-" + target).show();
    });

    // Chọn file từ thư viện Media
    $("#select_file").on("click", function (e) {
        e.preventDefault();

        const frame = wp.media({
            title: "Chọn hoặc tải lên file",
            button: { text: "Chọn" },
            multiple: false,
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            $("#file_url").val(attachment.url);
            $("#file_name").val(attachment.filename);
            $("#file_type").val(attachment.subtype || attachment.type);
        });

        frame.open();
    });
});