<?php
/**
 * StaffTime - Auto Attendance Helper
 * Runs when Admin opens Dashboard / Attendance page
 * After closing_time + 30 minutes:
 * 1. Auto check-out staff who forgot to check out
 * 2. Mark absent staff who never checked in
 * 3. Mark approved leave as leave
 */

function runAutoAttendance($pdo, $school_id) {
    try {
        // Get school times
        $stmt = $pdo->prepare("SELECT late_time, closing_time FROM schools WHERE id = ?");
        $stmt->execute([$school_id]);
        $school = $stmt->fetch();

        if (!$school) {
            return;
        }

        $closing_time = $school['closing_time'] ?? '14:00:00';
        if (strlen($closing_time) === 5) {
            $closing_time .= ':00';
        }

        $today = date('Y-m-d');
        $now   = date('H:i:s');

        // Auto-run only after closing time + 30 minutes
        $auto_time = date('H:i:s', strtotime($closing_time . ' +30 minutes'));

        if ($now < $auto_time) {
            // Too early — do nothing yet
            return;
        }

        // Prevent running many times in same request using a simple session flag
        $flag_key = 'auto_attendance_' . $today . '_' . $school_id;
        if (!empty($_SESSION[$flag_key])) {
            return;
        }

        // ===============================
        // 1. AUTO CHECK-OUT
        // Staff who checked in but no check-out
        // ===============================
        $stmt = $pdo->prepare("
            UPDATE attendances 
            SET check_out_time = ?
            WHERE school_id = ?
              AND attendance_date = ?
              AND check_in_time IS NOT NULL
              AND check_out_time IS NULL
              AND status IN ('present', 'late')
        ");
        $stmt->execute([$closing_time, $school_id, $today]);

        // ===============================
        // 2. AUTO MARK ABSENT / LEAVE
        // ===============================

        // All active staff
        $staffStmt = $pdo->prepare("
            SELECT id FROM users 
            WHERE school_id = ? AND role = 'staff' AND is_active = 1
        ");
        $staffStmt->execute([$school_id]);
        $all_staff = $staffStmt->fetchAll(PDO::FETCH_COLUMN);

        // Already have attendance today
        $attStmt = $pdo->prepare("
            SELECT user_id FROM attendances 
            WHERE school_id = ? AND attendance_date = ?
        ");
        $attStmt->execute([$school_id, $today]);
        $already = $attStmt->fetchAll(PDO::FETCH_COLUMN);

        // Approved leave today
        $leaveStmt = $pdo->prepare("
            SELECT user_id FROM leaves 
            WHERE school_id = ?
              AND status = 'approved'
              AND start_date <= ?
              AND end_date >= ?
        ");
        $leaveStmt->execute([$school_id, $today, $today]);
        $on_leave = $leaveStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($all_staff as $sid) {
            if (in_array($sid, $already)) {
                continue;
            }

            if (in_array($sid, $on_leave)) {
                $insert = $pdo->prepare("
                    INSERT INTO attendances 
                    (school_id, user_id, attendance_date, status, marked_by)
                    VALUES (?, ?, ?, 'leave', 'system')
                ");
                $insert->execute([$school_id, $sid, $today]);
            } else {
                $insert = $pdo->prepare("
                    INSERT INTO attendances 
                    (school_id, user_id, attendance_date, status, marked_by)
                    VALUES (?, ?, ?, 'absent', 'system')
                ");
                $insert->execute([$school_id, $sid, $today]);
            }
        }

        // Mark as done for today (this browser session)
        $_SESSION[$flag_key] = true;

    } catch (Exception $e) {
        // Fail silently so dashboard still loads
    }
}
?>
