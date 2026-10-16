<?php
session_start();
include_once '../db/conn.php';

if (!isset($_SESSION['EmployeeID'])) {
    header('Location: ../index.php');
    exit();
}

$leaveReportView = $leaveReportView ?? 'credits';
$isCreditsView = $leaveReportView === 'credits';
$currentYear = (int) date('Y');
$rows = [];

if ($isCreditsView) {
    $stmt = $conn->prepare("SELECT a.EmployeeID, COALESCE(NULLIF(d.name, ''), a.username) AS employee_name, COALESCE(c.vacation_entitlement, 15) AS vacation_entitlement, COALESCE(c.sick_entitlement, 15) AS sick_entitlement, COALESCE(c.other_entitlement, 0) AS other_entitlement, COALESCE(SUM(CASE WHEN lr.leave_type = 'vacation' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS vacation_used, COALESCE(SUM(CASE WHEN lr.leave_type = 'sick' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS sick_used, COALESCE(SUM(CASE WHEN lr.leave_type = 'other' AND lr.status = 'approved' THEN lr.days ELSE 0 END), 0) AS other_used FROM admin a LEFT JOIN employee_details d ON d.EmployeeID = a.EmployeeID LEFT JOIN employee_leave_credits c ON c.EmployeeID = a.EmployeeID AND c.leave_year = ? LEFT JOIN leave_records lr ON lr.EmployeeID = a.EmployeeID AND lr.leave_year = ? WHERE a.role = 'staff' GROUP BY a.EmployeeID, employee_name, c.vacation_entitlement, c.sick_entitlement, c.other_entitlement ORDER BY employee_name ASC");
    if ($stmt) {
        $stmt->bind_param('ii', $currentYear, $currentYear);
    }
} else {
    $stmt = $conn->prepare("SELECT lr.leave_id, lr.file_name, lr.period_start, lr.period_end, lr.days, lr.status, lr.uploaded_at, COALESCE(NULLIF(ed.name, ''), a.username) AS employee_name FROM leave_records lr INNER JOIN admin a ON a.EmployeeID = lr.EmployeeID LEFT JOIN employee_details ed ON ed.EmployeeID = lr.EmployeeID ORDER BY lr.uploaded_at DESC LIMIT 20");
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
}

$pageTitle = $isCreditsView ? 'Current Leave Credits' : 'Recent Leave Files';
$pageDescription = $isCreditsView
    ? 'Review current staff leave balances for ' . $currentYear . '.'
    : 'Review created leave documents, status, and delivery actions.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Tapuyan National High School</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --header-height: 70px;
            --sidebar-width: 270px;
            --primary: #0f172a;
            --accent: #2563eb;
            --background: #f1f5f9;
            --surface: #fff;
            --border: #e2e8f0;
            --text: #334155;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            overflow: hidden;
            color: var(--text);
            background: var(--background);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .leave-report-main {
            height: calc(100vh - var(--header-height));
            margin-left: var(--sidebar-width);
            padding: 2rem;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .eyebrow {
            display: block;
            margin-bottom: .3rem;
            color: var(--accent);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            color: var(--primary);
            font-size: 1.65rem;
        }

        .page-header p {
            margin-top: .35rem;
            color: var(--muted);
            font-size: .9rem;
        }

        .page-icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            color: var(--accent);
            background: #eff6ff;
            border-radius: 8px;
        }

        .panel {
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .search {
            display: flex;
            align-items: center;
            gap: .5rem;
            width: min(380px, 100%);
            padding: .6rem .75rem;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .search input {
            width: 100%;
            border: 0;
            outline: 0;
            color: var(--text);
            font: inherit;
        }

        .page-info,
        .muted {
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
            padding: .85rem 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: .8rem;
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

        .status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .25rem .5rem;
            border-radius: 999px;
            font-size: .7rem;
            font-weight: 700;
        }

        .status.sent {
            color: #15803d;
            background: #f0fdf4;
        }

        .status.pending {
            color: #a16207;
            background: #fefce8;
        }

        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            min-height: 34px;
            padding: .45rem .7rem;
            color: #fff;
            background: var(--accent);
            border: 0;
            border-radius: 6px;
            font-size: .76rem;
            font-weight: 700;
            cursor: pointer;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
        }

        .pagination .button {
            color: #475569;
            background: #f1f5f9;
            border: 1px solid var(--border);
        }

        .pagination .button:disabled {
            cursor: not-allowed;
            opacity: .45;
        }

        @media (max-width: 900px) {
            body {
                overflow: auto;
            }

            .leave-report-main {
                height: auto;
                min-height: calc(100vh - var(--header-height));
                margin-left: 0;
                padding: 1.25rem;
            }
        }

        @media (max-width: 640px) {

            .page-header,
            .toolbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-info {
                align-self: flex-start;
            }
        }
    </style>
</head>

<body>
    <?php include_once '../includes/headeradmin.php'; ?>
    <?php $activeAdminView = 'leave'; ?>
    <?php include_once '../includes/sidenav.php'; ?>

    <main class="leave-report-main">
        <header class="page-header">
            <div>
                <span class="eyebrow">Leave Administration</span>
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p><?php echo htmlspecialchars($pageDescription); ?></p>
            </div>
            <div class="page-icon"><i
                    class="fa-solid <?php echo $isCreditsView ? 'fa-chart-column' : 'fa-clock-rotate-left'; ?>"></i>
            </div>
        </header>

        <section class="panel">
            <?php if ($isCreditsView): ?>
                <div class="toolbar">
                    <label class="search"><i class="fa-solid fa-magnifying-glass"></i><input id="creditSearch" type="search"
                            placeholder="Search employee"></label>
                    <span id="creditPageInfo" class="page-info"></span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Vacation</th>
                                <th>Sick</th>
                                <th>Other</th>
                            </tr>
                        </thead>
                        <tbody id="creditTableBody">
                            <?php if (!$rows): ?>
                                <tr>
                                    <td colspan="4">No staff employees found.</td>
                                </tr>
                            <?php else:
                                foreach ($rows as $credit): ?>
                                    <tr
                                        data-search="<?php echo htmlspecialchars(strtolower($credit['employee_name'] . ' ' . $credit['EmployeeID'])); ?>">
                                        <td><?php echo htmlspecialchars($credit['employee_name']); ?></td>
                                        <td><?php echo number_format(max(0, (float) $credit['vacation_entitlement'] - (float) $credit['vacation_used']), 2); ?>
                                            remaining / <?php echo number_format((float) $credit['vacation_entitlement'], 2); ?>
                                        </td>
                                        <td><?php echo number_format(max(0, (float) $credit['sick_entitlement'] - (float) $credit['sick_used']), 2); ?>
                                            remaining / <?php echo number_format((float) $credit['sick_entitlement'], 2); ?></td>
                                        <td><?php echo number_format(max(0, (float) $credit['other_entitlement'] - (float) $credit['other_used']), 2); ?>
                                            remaining / <?php echo number_format((float) $credit['other_entitlement'], 2); ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <button type="button" class="button" id="creditPrev"><i class="fa-solid fa-chevron-left"></i>
                        Previous</button>
                    <button type="button" class="button" id="creditNext">Next <i
                            class="fa-solid fa-chevron-right"></i></button>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Document</th>
                                <th>Leave Period</th>
                                <th>Uploaded</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$rows): ?>
                                <tr>
                                    <td colspan="6">No leave files have been uploaded yet.</td>
                                </tr>
                            <?php else:
                                foreach ($rows as $leave): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leave['file_name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M j, Y', strtotime($leave['period_start'])) . ' - ' . date('M j, Y', strtotime($leave['period_end'])) . ' (' . $leave['days'] . ' day(s))'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars(date('M j, Y', strtotime($leave['uploaded_at']))); ?></td>
                                        <td><span
                                                class="status <?php echo $leave['status'] === 'approved' ? 'sent' : 'pending'; ?>"><i
                                                    class="fa-solid <?php echo $leave['status'] === 'approved' ? 'fa-circle-check' : 'fa-clock'; ?>"></i><?php echo htmlspecialchars($leave['status'] === 'approved' ? 'Sent' : ucfirst($leave['status'])); ?></span>
                                        </td>
                                        <td class="actions">
                                            <a class="button"
                                                href="download_leave_file.php?id=<?php echo (int) $leave['leave_id']; ?>"
                                                title="View and download leave file"><i class="fa-solid fa-eye"></i> View</a>
                                            <?php if ($leave['status'] === 'pending'): ?>
                                                <form action="../controllers/admin_send_leave.php" method="post">
                                                    <input type="hidden" name="leave_id"
                                                        value="<?php echo (int) $leave['leave_id']; ?>">
                                                    <button class="button" type="submit" title="Send leave document to employee"><i
                                                            class="fa-solid fa-paper-plane"></i> Send Leave</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php if ($isCreditsView): ?>
        <script>
            const rows = Array.from(document.querySelectorAll('#creditTableBody tr[data-search]'));
            const search = document.getElementById('creditSearch');
            const info = document.getElementById('creditPageInfo');
            const previous = document.getElementById('creditPrev');
            const next = document.getElementById('creditNext');
            let page = 1;
            const pageSize = 10;
            function renderPage() {
                const term = search.value.trim().toLowerCase();
                const matches = rows.filter(row => row.dataset.search.includes(term));
                const pageCount = Math.max(1, Math.ceil(matches.length / pageSize));
                page = Math.max(1, Math.min(page, pageCount));
                rows.forEach(row => row.hidden = true);
                matches.slice((page - 1) * pageSize, page * pageSize).forEach(row => row.hidden = false);
                info.textContent = matches.length ? `Page ${page} of ${pageCount} · ${matches.length} employee(s)` : 'No matching employees';
                previous.disabled = page <= 1 || !matches.length;
                next.disabled = page >= pageCount || !matches.length;
            }
            search.addEventListener('input', () => { page = 1; renderPage(); });
            previous.addEventListener('click', () => { page--; renderPage(); });
            next.addEventListener('click', () => { page++; renderPage(); });
            renderPage();
        </script>
    <?php endif; ?>
</body>

</html>