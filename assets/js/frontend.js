jQuery(function ($) {
  // ===== Thêm task
  $('#taskPopup .aerp-hrm-task-popup-close').on('click', () => $('#taskPopup').removeClass('active'));
  $('[data-open-aerp-hrm-task-popup]').on('click', () => $('#taskPopup').addClass('active'));

  // ===== Sửa task
  $('#editTaskPopup .aerp-hrm-task-popup-close').on('click', () => $('#editTaskPopup').removeClass('active'));

  // ===== Thêm thưởng/phạt
  $('#aerp-hrm-adjustmentPopup .aerp-hrm-task-popup-close').on('click', () => $('#aerp-hrm-adjustmentPopup').removeClass('active'));
  $('[data-open-adjustment-popup]').on('click', () => $('#aerp-hrm-adjustmentPopup').addClass('active'));

  window.openEditTaskPopup = function (task) {
    $('#edit_task_id').val(task.id);
    $('#edit_task_title').val(task.task_title);
    $('#edit_task_desc').val(task.task_desc);
    $('#edit_task_deadline').val(task.deadline);
    $('#edit_task_score').val(task.score !== undefined ? task.score : '');
    $('#editTaskPopup').addClass('active');
  };

  // window.closeaerp-hrm-adjustmentPopup = function() {
  //   $('#aerp-hrm-adjustmentPopup').removeClass('active');
  // };

  // ===== Click ngoài popup để đóng
  $(document).on('click', function (e) {
    if ($('#taskPopup').hasClass('active') && !$(e.target).closest('#taskPopup .aerp-hrm-task-popup-inner, [data-open-aerp-hrm-task-popup]').length) {
      $('#taskPopup').removeClass('active');
    }
    if ($('#editTaskPopup').hasClass('active') && !$(e.target).closest('#editTaskPopup .aerp-hrm-task-popup-inner, [onclick^="openEditTaskPopup"]').length) {
      $('#editTaskPopup').removeClass('active');
    }
    if ($('#aerp-hrm-adjustmentPopup').hasClass('active') && !$(e.target).closest('#aerp-hrm-adjustmentPopup .aerp-hrm-task-popup-inner, [data-open-adjustment-popup]').length) {
      $('#aerp-hrm-adjustmentPopup').removeClass('active');
    }
  });

  // ===== Toast
  $('#aerp-hrm-toast button').on('click', function () {
    $('#aerp-hrm-toast').remove();
  });

  if ($('#aerp-hrm-toast').length) {
    setTimeout(() => $('#aerp-hrm-toast').fadeOut(300, function () { $(this).remove(); }), 5000);
  }
});
