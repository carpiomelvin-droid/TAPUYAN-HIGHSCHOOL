<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$employeeId = (int) $_SESSION['EmployeeID'];
$currentYear = (int) date('Y');
$employeeName = $_SESSION['name'] ?? 'Employee';
$nameStmt = $conn->prepare("SELECT COALESCE(NULLIF(d.name, ''), a.username) AS employee_name FROM admin a LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID WHERE a.EmployeeID = ? LIMIT 1");
if ($nameStmt) {
    $nameStmt->bind_param('i', $employeeId);
    $nameStmt->execute();
    $nameRow = $nameStmt->get_result()->fetch_assoc();
    $employeeName = $nameRow['employee_name'] ?? $employeeName;
    $nameStmt->close();
}

$credit = ['vacation_entitlement' => 15, 'sick_entitlement' => 15, 'other_entitlement' => 0, 'vacation_used' => 0, 'sick_used' => 0, 'other_used' => 0];
$creditStmt = $conn->prepare("SELECT COALESCE(c.vacation_entitlement, 15) AS vacation_entitlement, COALESCE(c.sick_entitlement, 15) AS sick_entitlement, COALESCE(c.other_entitlement, 0) AS other_entitlement, COALESCE(SUM(CASE WHEN lr.leave_type = 'vacation' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS vacation_used, COALESCE(SUM(CASE WHEN lr.leave_type = 'sick' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS sick_used, COALESCE(SUM(CASE WHEN lr.leave_type = 'other' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS other_used FROM admin a LEFT JOIN employee_leave_credits c ON c.EmployeeID = a.EmployeeID AND c.leave_year = ? LEFT JOIN leave_records lr ON lr.EmployeeID = a.EmployeeID AND lr.leave_year = ? WHERE a.EmployeeID = ? GROUP BY c.vacation_entitlement, c.sick_entitlement, c.other_entitlement");
if ($creditStmt) {
    $creditStmt->bind_param('iii', $currentYear, $currentYear, $employeeId);
    $creditStmt->execute();
    $credit = array_merge($credit, $creditStmt->get_result()->fetch_assoc() ?: []);
    $creditStmt->close();
}

$records = [];
$recordStmt = $conn->prepare("SELECT leave_id, leave_type, document_type, file_name, period_start, period_end, days, status, remarks, uploaded_at FROM leave_records WHERE EmployeeID = ? ORDER BY uploaded_at DESC");
if ($recordStmt) {
    $recordStmt->bind_param('i', $employeeId);
    $recordStmt->execute();
    $recordResult = $recordStmt->get_result();
    while ($row = $recordResult->fetch_assoc())
        $records[] = $row;
    $recordStmt->close();
}

$balance = function (string $type) use ($credit): float {
    return max(0, (float) $credit[$type . '_entitlement'] - (float) $credit[$type . '_used']);
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Credits | Tapuyan National High School</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --navy: #0f172a;
            --blue: #2563eb;
            --green: #15803d;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: #334155;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .leave-main {
            padding: 32px;
            min-height: calc(100vh - 65px);
        }

        .heading {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 24px;
        }

        .heading h1 {
            margin: 0;
            color: var(--navy);
            font-size: 1.65rem;
        }

        .heading p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .year {
            padding: 9px 13px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--blue);
            font-weight: 700;
            font-size: .82rem;
        }

        .credit-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .credit-card,
        .panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(15, 23, 42, .04);
        }

        .credit-card {
            padding: 20px;
        }

        .credit-card h2 {
            margin: 0 0 12px;
            color: var(--muted);
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .credit-number {
            color: var(--navy);
            font-size: 2rem;
            font-weight: 750;
        }

        .credit-number small {
            color: var(--muted);
            font-size: .82rem;
            font-weight: 500;
        }

        .credit-card p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: .78rem;
        }

        .panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
        }

        .panel-header h2 {
            margin: 0;
            color: var(--navy);
            font-size: 1rem;
        }

        .panel-header p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: .78rem;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px 16px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: .82rem;
        }

        th {
            color: var(--muted);
            background: #f8fafc;
            font-size: .7rem;
            text-transform: uppercase;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--blue);
            text-decoration: none;
            font-weight: 650;
        }

        .file-link:hover {
            text-decoration: underline;
        }

        .status {
            display: inline-flex;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .status.approved {
            color: var(--green);
            background: #f0fdf4;
        }

        .status.pending {
            color: #a16207;
            background: #fefce8;
        }

        .status.rejected {
            color: #b91c1c;
            background: #fef2f2;
        }

        .empty {
            padding: 28px 20px;
            color: var(--muted);
            text-align: center;
            font-size: .85rem;
        }

        @media(max-width:900px) {
            .leave-main {
                margin-left: 0;
                padding: 20px;
            }

            .credit-grid {
                grid-template-columns: 1fr;
            }

            .heading {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php $activeEmployeeView = 'leave.php';
    ?>
    <main class="leave-main">
        <header class="heading">
            <div>
                <h1>My Leave Credits</h1>
                <p><?php echo htmlspecialchars($employeeName); ?> · Approved leave records are deducted automatically;
                    pending and rejected records remain visible for tracking.
                </p>
            </div><span class="year"><i class="fa-solid fa-calendar"></i> <?php echo $currentYear; ?> balance</span>
        </header>
        <section class="credit-grid">
            <?php foreach (['vacation' => 'Vacation Leave', 'sick' => 'Sick Leave', 'other' => 'Other Leave'] as $type => $label): ?>
                <article class="credit-card">
                    <h2><?php echo $label; ?></h2>
                    <div class="credit-number"><?php echo number_format($balance($type), 2); ?> <small>days
                            remaining</small></div>
                    <p><?php echo number_format((float) $credit[$type . '_used'], 2); ?> used of
                        <?php echo number_format((float) $credit[$type . '_entitlement'], 2); ?> days
                    </p>
                </article>
            <?php endforeach; ?>
        </section>
        <section class="panel">
            <div class="panel-header">
                <h2>Files From Administration</h2>
                <p>Leave documents assigned to your employee account.</p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Leave period</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$records): ?>
                            <tr>
                                <td colspan="5" class="empty">No leave files have been created for you yet.</td>
                            </tr><?php else:
                            foreach ($records as $record): ?>
                                <tr>
                                    <td><a class="file-link"
                                            href="download_leave_file.php?id=<?php echo (int) $record['leave_id']; ?>"><i
                                                class="fa-solid fa-download"></i><?php echo htmlspecialchars($record['file_name']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M j, Y', strtotime($record['period_start'])) . ' - ' . date('M j, Y', strtotime($record['period_end'])) . ' (' . $record['days'] . ' day(s))'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst($record['leave_type'])); ?></td>
                                    <td><span
                                            class="status <?php echo htmlspecialchars($record['status']); ?>"><?php echo htmlspecialchars(ucfirst($record['status'])); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M j, Y', strtotime($record['uploaded_at']))); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>