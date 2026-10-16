<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$activeEmployeeView = $activeEmployeeView ?? $currentPage;
$employeeUnreadMessages = 0;
$employeeId = (int) ($_SESSION['EmployeeID'] ?? 0);

if ($employeeId > 0 && isset($conn) && $conn instanceof mysqli) {
    $employeeUnreadStmt = $conn->prepare("SELECT COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = ?
          AND receiver_role = 'employee'
          AND is_read = 0
          AND receiver_deleted = 0");

    if ($employeeUnreadStmt) {
        $employeeUnreadStmt->bind_param('i', $employeeId);
        $employeeUnreadStmt->execute();
        $employeeUnreadRow = $employeeUnreadStmt->get_result()->fetch_assoc();
        $employeeUnreadMessages = (int) ($employeeUnreadRow['unread_count'] ?? 0);
        $employeeUnreadStmt->close();
    }
}
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-scrollable">

        <!-- PORTAL NAVIGATION -->
        <div class="nav-section">
            <span class="section-label">Portal Navigation</span>
            <ul>
                <!-- 1st Item: Announcement (Top Default) -->
                <li>
                    <a href="../Employee/announcements.php" data-employee-view="announcements.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'announcements.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #2563eb;"><i class="fa-solid fa-bullhorn"></i></div>
                        <span class="nav-label">Announcement</span>
                    </a>
                </li>
                <!-- 2nd Item: Personnel Administration (Personal Info Page) -->
                <li>
                    <a href="../Employee/home.php" data-employee-view="profile"
                        class="nav-item <?php echo ($activeEmployeeView === 'profile') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #3b82f6;"><i class="fa-solid fa-id-card"></i></div>
                        <span class="nav-label">Personnel Administration</span>
                    </a>
                </li>
                <li>
                    <a href="../Employee/employee_inbox.php" data-employee-view="employee_inbox.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'employee_inbox.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #dc2626;"><i class="fa-solid fa-envelope"></i></div>
                        <span class="nav-label">Messages</span>
                        <span class="nav-badge" id="employee-unread-badge"
                            aria-label="<?php echo $employeeUnreadMessages; ?> unread messages"
                            <?php echo $employeeUnreadMessages === 0 ? 'hidden' : ''; ?>><?php echo $employeeUnreadMessages; ?></span>
                    </a>
                </li>
                <li>
                    <a href="../Employee/documents.php" data-employee-view="documents.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'documents.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #eab308;"><i class="fa-solid fa-folder-open"></i></div>
                        <span class="nav-label">Documents</span>
                    </a>
                </li>
                <li>
                    <a href="../Employee/ris.php" data-employee-view="ris.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'ris.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #0284c7;"><i class="fa-solid fa-file-invoice"></i></div>
                        <span class="nav-label">RIS Form</span>
                    </a>
                </li>
                <li>
                    <a href="../Employee/leave.php" data-employee-view="leave.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'leave.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #16a34a;"><i class="fa-solid fa-calendar-days"></i></div>
                        <span class="nav-label">Leave Credits</span>
                    </a>
                </li>
                <li>
                    <a href="../Employee/inventory.php" data-employee-view="inventory.php"
                        class="nav-item <?php echo ($activeEmployeeView === 'inventory.php') ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color: #d97706;"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <span class="nav-label">Inventory</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- HR SERVICES -->
        <div class="nav-section">
            <span class="section-label">HR Services</span>
            <ul>
                <li>
                    <a href="https://forms.office.com/pages/responsepage.aspx?id=gKvjQCQgo0W_dnoHYaJNKRlVZHVfuCNFutknbQvZy89UQksyQTVWTDM0Q1NPUTk4SzBJQTI0S1hQTy4u&route=shorturl"
                        target="_blank" rel="noopener noreferrer" class="nav-item">
                        <div class="nav-icon" style="color: #6366f1;"><i class="fa-solid fa-file-circle-question"></i>
                        </div>
                        <span class="nav-label">Req. Docs</span>
                        <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>
                    </a>
                </li>
                <li>
                    <a href="https://hrdepedboacappointment.setmore.com" target="_blank" rel="noopener noreferrer"
                        class="nav-item">
                        <div class="nav-icon" style="color: #0891b2;"><i class="fa-solid fa-calendar-check"></i></div>
                        <span class="nav-label">Appointment</span>
                        <i class="fa-solid fa-arrow-up-right-from-square external-icon"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ACCOUNT CONTROLS -->
        <div class="nav-section">
            <span class="section-label">Account</span>
            <ul>
                <li>
                    <button type="button" class="nav-item" onclick="openPasswordModal()">
                        <div class="nav-icon" style="color: #0284c7;"><i class="fa-solid fa-key"></i></div>
                        <span class="nav-label">Password</span>
                    </button>
                </li>
            </ul>
        </div>

    </div>

    <!-- SIDEBAR FOOTER -->
    <div class="sidebar-footer">
        <button type="button" class="logout-btn" onclick="openLogoutModal()">
            <div class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></div>
            <span class="nav-label">Sign Out</span>
        </button>
    </div>
</aside>

<style>
    /* --- SIDEBAR CONTAINER --- */
    .app-sidebar {
        width: 270px;
        min-width: 270px;
        height: calc(100vh - 65px);
        position: fixed;
        top: 65px;
        left: 0;
        background: #ffffff;
        border-right: 1px solid var(--border-color, #e2e8f0);
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        z-index: 100;
        transition: transform 0.25s ease;
    }

    .sidebar-header {
        padding: 1.25rem 1.25rem 0.5rem;
    }

    .sidebar-title {
        font-size: 0.7rem;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin: 0;
    }

    .sidebar-scrollable {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .sidebar-scrollable::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-scrollable::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    /* --- NAV SECTIONS & ITEMS --- */
    .nav-section {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .section-label {
        font-size: 0.7rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0 8px;
    }

    .nav-section ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .nav-item {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 8px 10px;
        border-radius: 8px;
        background: transparent;
        color: #334155;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        box-sizing: border-box;
    }

    .nav-item:hover {
        background: #f1f5f9;
        color: #0f172a;
        transform: translateX(2px);
    }

    .nav-item.active {
        background: #eff6ff;
        color: #2563eb;
    }

    .nav-icon {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .nav-label {
        flex: 1;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-badge {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dc2626;
        color: #ffffff;
        font-size: 0.68rem;
        font-weight: 700;
        line-height: 1;
        flex-shrink: 0;
    }

    .nav-badge[hidden] {
        display: none;
    }

    .external-icon {
        font-size: 0.68rem;
        color: #cbd5e1;
        margin-left: 6px;
    }

    /* --- SIDEBAR FOOTER --- */
    .sidebar-footer {
        padding: 12px 16px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 8px 10px;
        border-radius: 8px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fee2e2;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .logout-btn:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    .logout-btn .nav-icon {
        background: #ffffff;
        color: #dc2626;
    }

    /* --- RESPONSIVE SIDEBAR --- */
    @media (max-width: 1024px) {
        .app-sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            height: calc(100vh - 65px);
            transform: translateX(-100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .app-sidebar.show {
            transform: translateX(0);
        }
    }
</style>

<script>
    (function () {
        const badge = document.getElementById('employee-unread-badge');
        if (!badge || typeof EventSource === 'undefined') return;

        const messageStream = new EventSource('../db/sse_messages.php');
        messageStream.onmessage = function (event) {
            const unreadCount = Math.max(0, parseInt(event.data, 10) || 0);
            badge.textContent = unreadCount;
            badge.hidden = unreadCount === 0;
            badge.setAttribute('aria-label', unreadCount + ' unread ' + (unreadCount === 1 ? 'message' : 'messages'));
        };
    })();
</script>