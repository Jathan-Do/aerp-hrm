<?php

class AERP_Salary_Manager
{
    /**
     * Tính lương cho một nhân viên theo tháng
     */
    public static function calculate_salary($employee_id, $month)
    {
        global $wpdb;

        $month_start = date('Y-m-01 00:00:00', strtotime($month));
        $month_end   = date('Y-m-t 23:59:59', strtotime($month));

        // 1. Lấy cấu hình lương trong khoảng thời gian
        $today = date('Y-m-d'); // hoặc truyền vào ngày cần tính
        $config = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_salary_config
        WHERE employee_id = %d
          AND start_date <= %s
          AND end_date >= %s
        ORDER BY start_date DESC
        LIMIT 1
    ", $employee_id, $today, $today));

        if (!$config) return false;

        $base      = floatval($config->base_salary);
        $allowance = floatval($config->allowance);

        // 2. Chấm công: tính tổng lương ngày công
        // --- BẮT ĐẦU SỬA LOGIC ---
        // 2.1. Số ngày làm việc chuẩn trong tháng (trừ T7, CN)
        $start = new DateTime($month_start);
        $end = new DateTime($month_end);
        $work_days_standard = 0;
        for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
            $w = (int)$d->format('N'); // 6: T7, 7: CN
            if ($w < 6) $work_days_standard++;
        }

        // 2.2. Lấy các dòng chấm công trong tháng
        $attendance = $wpdb->get_results($wpdb->prepare("
            SELECT shift, work_ratio FROM {$wpdb->prefix}aerp_hrm_attendance
            WHERE employee_id = %d AND work_date BETWEEN %s AND %s
        ", $employee_id, $month_start, $month_end));

        $off_days = 0;
        $ot_total = 0;
        foreach ($attendance as $row) {
            if ($row->shift === 'off' && floatval($row->work_ratio) == 0) {
                $off_days++;
            } elseif ($row->shift === 'ot' && floatval($row->work_ratio) > 0) {
                $ot_total += floatval($row->work_ratio);
            }
        }

        // 2.3. Số ngày công thực tế
        $actual_work_days = $work_days_standard - $off_days;
        // 2.4. Tổng hệ số tăng ca (có thể cộng vào lương riêng hoặc vào actual_work_days tuỳ chính sách)
        // Ở đây cộng vào lương riêng:
        $salary_per_day = ($base + $allowance) / $work_days_standard;
        $total_salary = $actual_work_days * $salary_per_day + $ot_total * $salary_per_day;
        $work_days = $work_days_standard;
        // --- KẾT THÚC SỬA LOGIC ---

        // 3. Thưởng & phạt thủ công
        $adjustments = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_adjustments
        WHERE employee_id = %d AND date_effective BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));

        $bonus = 0;
        $deduction = 0;
        foreach ($adjustments as $a) {
            if ($a->type === 'reward') $bonus += floatval($a->amount);
            elseif ($a->type === 'fine') $deduction += floatval($a->amount);
        }

        // 4. Thưởng KPI theo task
        $total_score = (int)$wpdb->get_var($wpdb->prepare("
        SELECT SUM(score) FROM {$wpdb->prefix}aerp_hrm_tasks
        WHERE employee_id = %d AND status = 'done'
        AND deadline BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end));

        $kpi_bonus = 0;
        $kpi_levels  = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aerp_hrm_kpi_settings ORDER BY min_score DESC");
        foreach ($kpi_levels  as $level) {
            if ($total_score >= $level->min_score) {
                $kpi_bonus = floatval($level->reward_amount);
                break;
            }
        }
        $bonus += $kpi_bonus;

        // 5. Tạm ứng lương
        $advance = floatval($wpdb->get_var($wpdb->prepare("
        SELECT SUM(amount) FROM {$wpdb->prefix}aerp_hrm_advance_salaries
        WHERE employee_id = %d AND advance_date BETWEEN %s AND %s
    ", $employee_id, $month_start, $month_end))) ?: 0;

        // 6. Thưởng động từ hook (tết, sinh nhật...)
        $auto_bonus = apply_filters('aerp_hrm_auto_bonus', 0, $employee_id, $month);

        // 7. Tính điểm từ vi phạm & cộng tiền phạt
        $disciplines = $wpdb->get_results($wpdb->prepare("
            SELECT dr.penalty_point, dr.fine_amount FROM {$wpdb->prefix}aerp_hrm_disciplinary_logs dl
            INNER JOIN {$wpdb->prefix}aerp_hrm_disciplinary_rules dr ON dr.id = dl.rule_id
            WHERE dl.employee_id = %d AND dl.date_violation BETWEEN %s AND %s
        ", $employee_id, $month_start, $month_end));

        $total_points = 100;
        $violation_deduction = 0;

        foreach ($disciplines as $v) {
            $total_points -= intval($v->penalty_point);
            $violation_deduction += floatval($v->fine_amount);
        }
        $total_points = max(0, $total_points);
        $deduction += $violation_deduction; // ✅ cộng vào trừ lương


        // ✅ Reset lại điểm vào bảng nhân sự
        $wpdb->update(
            $wpdb->prefix . 'aerp_hrm_employees',
            ['current_points' => $total_points],
            ['id' => $employee_id]
        );

        // 8. Xếp loại theo điểm
        $ranking = '--';
        $ranks = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}aerp_hrm_ranking_settings
        ORDER BY min_point DESC
    ");
        foreach ($ranks as $r) {
            if ($total_points >= $r->min_point) {
                $ranking = $r->rank_code;
                break;
            }
        }

        // 10. Tổng lương
        $final_salary = $total_salary + $bonus + $auto_bonus - $deduction - $advance;

        // 11. Ghi vào bảng lương (update nếu đã có, insert nếu chưa)
        $data = [
            'employee_id'      => $employee_id,
            'salary_month'     => $month_start,
            'base_salary'      => $base,
            'bonus'            => $bonus,
            'deduction'        => $deduction,
            'adjustment'       => 0,
            'advance_paid'     => $advance,
            'work_days'        => $work_days,
            'off_days'         => $off_days,
            'ot_days'          => $ot_total,
            'auto_bonus'       => $auto_bonus,
            'final_salary'     => $final_salary,
            'ranking'          => $ranking,
            'points_total'     => $total_points,
            'created_at'       => (new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s')
        ];

        // Kiểm tra đã có bản ghi lương cho nhân viên-tháng này chưa
        $salary_row = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}aerp_hrm_salaries WHERE employee_id = %d AND salary_month = %s",
            $employee_id,
            $month_start
        ));

        if ($salary_row) {
            // Đã có, update
            $wpdb->update($wpdb->prefix . 'aerp_hrm_salaries', $data, [
                'id' => $salary_row->id
            ]);
        } else {
            // Chưa có, insert
            $wpdb->insert($wpdb->prefix . 'aerp_hrm_salaries', $data);
        }
    }
}
