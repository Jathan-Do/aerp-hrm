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

        // SỬA: Lấy ngày đầu tháng cần tính lương, không lấy ngày hiện tại
        $target_date = date('Y-m-01', strtotime($month));
        // Lấy cấu hình hợp lệ theo ngày; hỗ trợ cấu hình không giới hạn (0000-00-00 hoặc NULL)
        $table_cfg = $wpdb->prefix . 'aerp_hrm_salary_config';
        // ƯU TIÊN cấu hình bao phủ target_date; nếu không có thì mới fallback cấu hình không giới hạn
        $sql_cfg = "SELECT * FROM {$table_cfg}
            WHERE employee_id = %d AND (
                (start_date <= %s AND end_date >= %s)
                OR ( (start_date IS NULL OR start_date = '0000-00-00' OR start_date = '')
                     AND (end_date IS NULL OR end_date = '0000-00-00' OR end_date = '') )
            )
            ORDER BY
                CASE WHEN (start_date <= %s AND end_date >= %s) THEN 0 ELSE 1 END ASC,
                start_date DESC,
                created_at DESC
            LIMIT 1";
        $config = $wpdb->get_row($wpdb->prepare($sql_cfg, $employee_id, $target_date, $target_date, $target_date, $target_date));

        if (!$config) return false;

        $salary_mode = $config->salary_mode ?? 'fixed';
        $commission_scheme_id = isset($config->commission_scheme_id) ? intval($config->commission_scheme_id) : 0;

        $base      = floatval($config->base_salary);
        $allowance = floatval($config->allowance);

        // 2. Chấm công: tính tổng lương ngày công
        // --- BẮT ĐẦU SỬA LOGIC ---
        // 2.1. Số ngày làm việc chuẩn trong tháng (trừ T7, CN)
        $today = new DateTime();
        $start = new DateTime($month_start);
        $end = new DateTime($month_end);
        $now_month = $today->format('Y-m');
        $target_month = (new DateTime($month_start))->format('Y-m');
        // Lấy cấu hình làm việc thứ 7 từ công ty
        $company_info = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}aerp_hrm_company_info LIMIT 1");
        $work_saturday = $company_info->work_saturday ?? 'off';
        // Số ngày công chuẩn của cả tháng (dùng để chia lương/ngày)
        $work_days_standard_full_month = 0;
        for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
            $w = (int)$d->format('N');
            if ($w < 6) {
                $work_days_standard_full_month++;
            } elseif ($w == 6) {
                if ($work_saturday === 'full') {
                    $work_days_standard_full_month++;
                } elseif ($work_saturday === 'half') {
                    $work_days_standard_full_month += 0.5;
                }
            }
            // Chủ nhật (w==7) luôn nghỉ
        }
        // Số ngày công chuẩn tính đến hiện tại (dùng để tính số ngày công thực tế)
        if ($target_month > $now_month) {
            // Tháng tương lai
            $work_days_standard = 0;
        } elseif ($target_month == $now_month) {
            // Tháng hiện tại: chỉ tính đến hôm nay
            $end_cur = $today;
            $work_days_standard = 0;
            for ($d = clone $start; $d <= $end_cur; $d->modify('+1 day')) {
                $w = (int)$d->format('N');
                if ($w < 6) $work_days_standard++;
            }
        } else {
            // Tháng quá khứ: đủ tháng
            $work_days_standard = $work_days_standard_full_month;
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
        $salary_per_day = ($base + $allowance) / ($work_days_standard_full_month ?: 1);
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

        // 9b. Hoa hồng theo lợi nhuận đơn hàng (nếu plugin Order hoạt động và có cấu hình)
        $commission_amount = 0;
        $order_active = (function_exists('aerp_order_init'));
        if (!$order_active && function_exists('is_plugin_active')) {
            $order_active = is_plugin_active('aerp-order/aerp-order.php');
        } else if (!$order_active) {
            // Try to include plugin.php to use is_plugin_active
            if (file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
                $order_active = is_plugin_active('aerp-order/aerp-order.php');
            }
        }

        if ($order_active && in_array($salary_mode, ['piecework','both'], true) && $commission_scheme_id) {
            // Verify order tables exist (đúng tên bảng của plugin Order)
            $tbl_orders = $wpdb->prefix . 'aerp_order_orders';
            $tbl_items = $wpdb->prefix . 'aerp_order_items';
            $tbl_contents = $wpdb->prefix . 'aerp_order_content_lines';
            $has_orders   = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl_orders)) === $tbl_orders;
            $has_items    = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl_items)) === $tbl_items;
            $has_contents = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tbl_contents)) === $tbl_contents;

            if ($has_orders && $has_items && $has_contents) {
                // Lấy các đơn trong tháng của nhân viên
                $orders = $wpdb->get_results($wpdb->prepare(
                    "SELECT id, employee_id, order_date, cost FROM {$tbl_orders} WHERE employee_id = %d AND order_date BETWEEN %s AND %s",
                    $employee_id, $month_start, $month_end
                ));

                if ($orders) {
                    // Load single-interval commission scheme
                    $scheme = $wpdb->get_row($wpdb->prepare(
                        "SELECT min_profit, max_profit, percent FROM {$wpdb->prefix}aerp_hrm_commission_schemes WHERE id = %d",
                        $commission_scheme_id
                    ));
                    foreach ($orders as $o) {
                        $order_id = intval($o->id);
                        $content_total = (float)$wpdb->get_var($wpdb->prepare(
                            "SELECT COALESCE(SUM(total_price),0) FROM {$tbl_contents} WHERE order_id = %d",
                            $order_id
                        ));
                        $items_total = (float)$wpdb->get_var($wpdb->prepare(
                            "SELECT COALESCE(SUM(quantity * unit_price),0) FROM {$tbl_items} WHERE order_id = %d",
                            $order_id
                        ));
                        $external_cost_total = (float)$wpdb->get_var($wpdb->prepare(
                            "SELECT COALESCE(SUM(external_cost),0) FROM {$tbl_items} WHERE order_id = %d AND purchase_type = 'external'",
                            $order_id
                        ));
                        $order_cost = (float)($o->cost ?? 0);
                        $profit = $content_total - $order_cost - $items_total - $external_cost_total;

                        if ($profit <= 0 || empty($scheme)) {
                            continue;
                        }
                        $min = (float)$scheme->min_profit;
                        $max = isset($scheme->max_profit) ? (float)$scheme->max_profit : null;
                        $percent = ($profit >= $min && ($max === null || $profit <= $max)) ? (float)$scheme->percent : 0;
                        if ($percent > 0) {
                            $commission_amount += $profit * ($percent / 100);
                        }
                    }
                }
            }
        }

        // 10. Tổng lương
        $final_salary = $total_salary + $bonus + $auto_bonus - $deduction - $advance;
        if (in_array($salary_mode, ['piecework','both'], true)) {
            $final_salary += $commission_amount;
        }

        // 11. Ghi vào bảng lương (update nếu đã có, insert nếu chưa)
        $data = [
            'employee_id'      => $employee_id,
            'salary_month'     => $month_start,
            'base_salary'      => $base,
            'salary_per_day'   => $salary_per_day,
            'bonus'            => $bonus,
            'deduction'        => $deduction,
            'adjustment'       => 0,
            'advance_paid'     => $advance,
            'work_days'        => $work_days_standard_full_month,
            'actual_work_days' => $actual_work_days,
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
