<?php
$activeAdminView = $activeAdminView ?? 'announcements';
$adminUnreadMessages = 0;
$adminEmployeeId = (int) ($_SESSION['EmployeeID'] ?? 0);

if ($adminEmployeeId > 0 && isset($conn) && $conn instanceof mysqli) {
    $adminUnreadStmt = $conn->prepare("SELECT COUNT(*) AS unread_count
        FROM messages
        WHERE receiver_id = ?
          AND receiver_role = 'admin'
          AND is_read = 0
          AND receiver_deleted = 0");

    if ($adminUnreadStmt) {
        $adminUnreadStmt->bind_param('i', $adminEmployeeId);
        $adminUnreadStmt->execute();
        $adminUnreadRow = $adminUnreadStmt->get_result()->fetch_assoc();
        $adminUnreadMessages = (int) ($adminUnreadRow['unread_count'] ?? 0);
        $adminUnreadStmt->close();
    }
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <nav class="sidebar-scrollable">
        <!-- ==========================================
           SECTION 1: STAFF & HR MANAGEMENT
           ========================================== -->
        <div class="nav-section">
            <span class="section-label">Portal Navigation</span>
            <ul>
                <li>
                    <a href="index.php?view=announcements" data-admin-view="announcements"
                        class="nav-item <?php echo $activeAdminView === 'announcements' ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color:#2563eb;">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                        <span class="nav-label">Announcements</span>
                    </a>
                </li>
                <li>
                    <a href="index.php?view=personnel" data-admin-view="personnel"
                        class="nav-item <?php echo $activeAdminView === 'personnel' ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color:#3b82f6;">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <span class="nav-label">Personnel Administration</span>
                    </a>
                </li>
                <!--  default -->
                <li>
                    <button type="button" id="create-account" class="nav-item">
                        <div class="nav-icon" style="color:#7c3aed;">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <span class="nav-label">Create Employee</span>
                        <i class="fa-solid fa-chevron-right nav-arrow"></i>
                    </button>
                </li>

                <?php $isLeavePage = in_array(basename($_SERVER['PHP_SELF']), ['leave_admin.php', 'leave_credits.php', 'recent_leave_files.php'], true); ?>
                <li class="nav-group <?php echo $isLeavePage ? 'open' : ''; ?>">
                    <button type="button" class="nav-item nav-group-toggle"
                        aria-expanded="<?php echo $isLeavePage ? 'true' : 'false'; ?>"
                        aria-controls="leaveAdministrationSubmenu">
                        <div class="nav-icon" style="color:#16a34a;">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <span class="nav-label">Leave Administration</span>
                        <i class="fa-solid fa-chevron-down nav-arrow nav-group-arrow"></i>
                    </button>
                    <ul class="nav-submenu" id="leaveAdministrationSubmenu">
                        <li><a href="leave_admin.php" class="nav-submenu-item <?php echo basename($_SERVER['PHP_SELF']) === 'leave_admin.php' ? 'active' : ''; ?>"><i
                                    class="fa-solid fa-file-arrow-up"></i><span>Upload Leave File</span></a></li>
                        <li><a href="leave_credits.php" class="nav-submenu-item <?php echo basename($_SERVER['PHP_SELF']) === 'leave_credits.php' ? 'active' : ''; ?>"><i
                                    class="fa-solid fa-chart-column"></i><span>Current Leave Credits</span></a></li>
                        <li><a href="recent_leave_files.php" class="nav-submenu-item <?php echo basename($_SERVER['PHP_SELF']) === 'recent_leave_files.php' ? 'active' : ''; ?>"><i
                                    class="fa-solid fa-clock-rotate-left"></i><span>Recent Leave Files</span></a></li>
                    </ul>
                </li>
                <li>
                    <!-- ../controllers/export_employees.php -->
                    <a href="../controllers/export_employees.php" class="nav-item excel-export">
                        <div class="nav-icon excel-icon">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <span class="nav-label">Export Staff Details</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ==========================================
           SECTION 2: LOGISTICS & FINANCE
           ========================================== -->
        <div class="nav-section">
            <span class="section-label">Logistics & Finance</span>
            <ul>
                <li>
                    <a href="inventory.php" class="nav-item">
                        <div class="nav-icon" style="color:#d97706;">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <span class="nav-label">School Inventory</span>
                        <i class="fa-solid fa-chevron-right nav-arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="procurement.php" class="nav-item">
                        <div class="nav-icon" style="color:#0d9488;">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <span class="nav-label">Procurement & Liquidation</span>
                        <i class="fa-solid fa-chevron-right nav-arrow"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ==========================================
           SECTION 3: RECORDS & ISSUANCES
           ========================================== -->
        <div class="nav-section">
            <span class="section-label">Records & Issuances</span>
            <ul>
                <li>
                    <a href="admin_messages.php"
                        class="nav-item <?php echo ($activeAdminView === 'messages' || in_array(basename($_SERVER['PHP_SELF']), ['admin_messages.php', 'admin_sent_messages.php', 'admin_send_message.php', 'reply_message.php', 'view_admin_message.php'], true)) ? 'active' : ''; ?>">
                        <div class="nav-icon" style="color:#dc2626;">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <span class="nav-label">Messages</span>
                        <span class="message-count-badge" id="admin-unread-badge"
                            aria-label="<?php echo $adminUnreadMessages; ?> unread messages"
                            <?php echo $adminUnreadMessages === 0 ? 'hidden' : ''; ?>><?php echo $adminUnreadMessages; ?></span>
                    </a>
                </li>
                <li>
                    <a href="memorandums.php" class="nav-item">
                        <div class="nav-icon" style="color:#eab308;">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <span class="nav-label">Memorandum & Circulars</span>
                        <i class="fa-solid fa-chevron-right nav-arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="https://forms.office.com/pages/responsepage.aspx?id=gKvjQCQgo0W_dnoHYaJNKRlVZHVfuCNFutknbQvZy89UQksyQTVWTDM0Q1NPUTk4SzBJQTI0S1hQTy4u&route=shorturl"
                        target="_blank" rel="noopener noreferrer" class="nav-item">
                        <div class="nav-icon" style="color:#6366f1;"><i class="fa-solid fa-file-circle-question"></i>
                        </div>
                        <span class="nav-label">Request Documents</span>
                        <i class="fa-solid fa-arrow-up-right-from-square nav-arrow"></i>
                    </a>
                </li>
                <li>
                    <a href="https://hrdepedboacappointment.setmore.com" target="_blank" rel="noopener noreferrer"
                        class="nav-item">
                        <div class="nav-icon" style="color:#0891b2;"><i class="fa-solid fa-calendar-check"></i></div>
                        <span class="nav-label">Appointment</span>
                        <i class="fa-solid fa-arrow-up-right-from-square nav-arrow"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ==========================================
           SECTION 4: ACCOUNT & SETTINGS
           ========================================== -->
        <div class="nav-section">
            <span class="section-label">Account & Settings</span>
            <ul>
                <li>
                    <button type="button" id="openEditModal" class="nav-item">
                        <div class="nav-icon" style="color:#0284c7;">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <span class="nav-label">Edit My Account</span>
                        <i class="fa-solid fa-chevron-right nav-arrow"></i>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <div class="sidebar-footer">
        <form action="../controllers/logout.php" method="post">
            <button type="submit" class="logout-btn">
                <div class="nav-icon">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </div>
                <span class="nav-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>

<style>
    .admin-sidebar {
        width: 270px;
        min-width: 270px;
        height: calc(100vh - 70px);
        position: fixed;
        top: 70px;
        left: 0;
        background: #ffffff;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        z-index: 100;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        transition: transform .25s ease;
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

    .nav-section {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .section-label {
        font-size: .7rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
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
        font-family: inherit;
        font-size: .85rem;
        font-weight: 600;
        text-decoration: none;
        text-align: left;
        border: none;
        cursor: pointer;
        transition: all .15s ease;
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
        font-size: .85rem;
        flex-shrink: 0;
    }

    .nav-label {
        flex: 1;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .nav-arrow {
        margin-left: 6px;
        color: #cbd5e1;
        font-size: .68rem;
    }

    .nav-group-toggle {
        appearance: none;
    }

    .nav-group-arrow {
        transition: transform .2s ease;
    }

    .nav-group.open .nav-group-arrow {
        transform: rotate(180deg);
    }

    .nav-submenu {
        display: none !important;
        gap: 2px !important;
        margin: 2px 0 2px 36px !important;
        padding-left: 10px !important;
        border-left: 1px solid #e2e8f0;
    }

    .nav-group.open .nav-submenu {
        display: flex !important;
    }

    .nav-submenu-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 8px;
        color: #64748b;
        border-radius: 6px;
        font-size: .78rem;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        transition: background .15s ease, color .15s ease;
    }

    .nav-submenu-item:hover,
    .nav-submenu-item:focus-visible,
    .nav-submenu-item.active {
        color: #2563eb;
        background: #eff6ff;
        outline: none;
    }

    .nav-submenu-item i {
        width: 14px;
        color: #94a3b8;
        font-size: .72rem;
        text-align: center;
    }

    .message-count-badge {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #dc2626;
        color: #ffffff;
        font-size: .68rem;
        font-weight: 700;
        line-height: 1;
        flex-shrink: 0;
    }

    .message-count-badge[hidden] {
        display: none;
    }

    .excel-icon {
        color: #16a34a;
    }

    .sidebar-footer {
        padding: 12px 16px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }

    .sidebar-footer form {
        margin: 0;
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
        font-size: .85rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }

    .logout-btn:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    .logout-btn .nav-icon {
        background: #ffffff;
        color: #dc2626;
    }

    @media (max-width: 900px) {
        .admin-sidebar {
            transform: translateX(-100%);
            box-shadow: 4px 0 15px rgba(0, 0, 0, .1);
        }

        .admin-sidebar.show {
            transform: translateX(0);
        }
    }
</style>

<script>
    (function () {
        const leaveGroupToggle = document.querySelector('.nav-group-toggle');
        if (leaveGroupToggle) {
            leaveGroupToggle.addEventListener('click', function () {
                const leaveGroup = this.closest('.nav-group');
                const isOpen = leaveGroup.classList.toggle('open');
                this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }

        const badge = document.getElementById('admin-unread-badge');
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