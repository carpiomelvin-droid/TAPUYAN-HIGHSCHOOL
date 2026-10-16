<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tapuyan NHS - Employee Portal</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
    :root {
        --border-color: #dee2e6;
        --text-color: #343a40;
        --text-muted: #6c757d;
        --danger: #ef4444;
        --danger-hover: #dc2626;
        --bg-light: #f8fafc;
    }

    /* --- Announcement Container --- */
    .announcement-wrapper {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        width: 100%;
        font-family: inherit;
        box-sizing: border-box;
        transition: all 0.25s ease-in-out;
    }

    .announcement-tabs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        padding: 0 1.25rem;
    }

    /* --- Tabs --- */
    .ann-tabs {
        display: flex;
        gap: 4px;
        overflow-x: auto;
    }

    .ann-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        font-size: 0.82rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .ann-tab:hover {
        color: #0f172a;
        background: rgba(241, 245, 249, 0.6);
    }

    .ann-tab.active {
        color: #0f172a;
        border-bottom-color: #2563eb;
        background: #ffffff;
    }

    .ann-count-badge {
        background: #ef4444;
        color: #ffffff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 10px;
    }

    .ann-close-btn {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 0.95rem;
        cursor: pointer;
        padding: 6px 8px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .ann-close-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    /* --- Announcement Content Body --- */
    .announcement-body {
        padding: 12px 1.25rem;
    }

    .ann-pane {
        display: none;
        align-items: center;
        gap: 12px;
    }

    .ann-pane.active {
        display: flex;
    }

    .ann-message {
        font-size: 0.88rem;
        color: #334155;
        margin: 0;
        line-height: 1.4;
    }

    /* --- Category Pills --- */
    .ann-pill {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.03em;
        flex-shrink: 0;
    }

    .pill-blue {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .pill-red {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .pill-green {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    /* --- Mobile Responsiveness --- */
    @media (max-width: 640px) {
        .ann-pane {
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
        }

        .ann-tab {
            padding: 8px 10px;
            font-size: 0.78rem;
        }
    }

    .notification-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        color: #343a40;
        font-size: 1rem;
        text-decoration: none;
        transition: 0.2s;
    }

    .notification-btn:hover {
        background: #f8f9fa;
    }

    .notif-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #dc3545;
        color: white;
        font-size: 0.65rem;
        padding: 2px 5px;
        border-radius: 50%;
        font-weight: bold;
        min-width: 16px;
        text-align: center;
    }

    /* HEADER */
    .main-header {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1.25rem;
        height: 65px;
        background: linear-gradient(135deg, #78a0e6ff, #edeb74ff);
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    }

    /* LEFT BRANDING */
    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo {
        width: 52px;
    }

    .header-logo {
        display: flex;
        flex-direction: column;
    }

    .header-logo strong {
        font-size: 1.05rem;
        color: #0f172a;
        line-height: 1.2;
    }

    .header-logo span {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    /* COMPACT NAV BAR */
    .header-nav {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .header-nav a,
    .header-nav button {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 0.42rem 0.65rem;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-color);
        border: none;
        background: none;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .header-nav a:hover,
    .header-nav button:hover:not(.btn-logout) {
        background: rgba(250, 246, 12, 0.75);
        transform: translateY(-1px);
    }

    /* LOGOUT BUTTON IN NAV */
    .btn-logout {
        color: var(--danger) !important;
        font-weight: 700 !important;
        margin-left: 4px;
    }

    .btn-logout:hover {
        background: #fef2f2 !important;
        color: var(--danger-hover) !important;
        transform: translateY(-1px);
    }

    /* HAMBURGER */
    .menu-toggle,
    .mobile-message-link {
        display: none;
    }

    .menu-toggle {
        font-size: 1.3rem;
        background: none;
        border: none;
        cursor: pointer;
        color: #0f172a;
    }

    /* MOBILE RESPONSIVENESS */
    @media (max-width: 1024px) {
        .menu-toggle {
            display: block;
        }

        .header-nav .mobile-message-link {
            display: flex;
        }

        .header-nav {
            position: absolute;
            top: 65px;
            right: 1rem;
            width: 240px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
            padding: 10px;
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: 0.2s ease;
            z-index: 999;
            border: 1px solid var(--border-color);
        }

        .header-nav.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .header-nav a,
        .header-nav button {
            width: 100%;
            padding: 0.65rem 0.85rem;
            font-size: 0.9rem;
        }

        .btn-logout {
            margin-left: 0;
            border-top: 1px solid var(--border-color);
            border-radius: 0 0 6px 6px;
            margin-top: 4px;
            padding-top: 0.75rem;
        }
    }

    /* ==========================================
       MODAL STYLES (LOGOUT & PASSWORD)
       ========================================== */
    .custom-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        opacity: 0;
        transition: opacity 0.25s ease;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .custom-modal.show {
        display: flex;
        opacity: 1;
    }

    .modal-box {
        background: #ffffff;
        padding: 2rem;
        border-radius: 18px;
        width: 100%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transform: scale(0.95);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }

    .custom-modal.show .modal-box {
        transform: scale(1);
    }

    .modal-icon-badge {
        width: 58px;
        height: 58px;
        background: #fee2e2;
        color: var(--danger);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.15);
    }

    .modal-icon-badge.password-badge {
        background: #e0f2fe;
        color: #0284c7;
        box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.15);
    }

    .modal-box h3 {
        color: #1e293b;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .modal-box p {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }

    .modal-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .btn-modal {
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .btn-confirm {
        background: var(--danger);
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        width: 100%;
    }

    .btn-confirm:hover {
        background: var(--danger-hover);
        transform: translateY(-1px);
    }

    .btn-primary-modal {
        background: #0284c7;
        color: #ffffff;
        border: none;
        width: 100%;
    }

    .btn-primary-modal:hover {
        background: #0369a1;
        transform: translateY(-1px);
    }

    /* Password Modal Form Inputs */
    .form-group-modal {
        margin-bottom: 1rem;
        text-align: left;
    }

    .form-group-modal label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    /* 🟢 NEW: Password Input Wrapper & Toggle Eye Button */
    .password-wrapper {
        position: relative;
        width: 100%;
    }

    .password-wrapper input {
        width: 100%;
        padding: 0.65rem 2.5rem 0.65rem 0.85rem;
        /* Extra padding on the right for icon */
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .password-wrapper input:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }

    .toggle-password-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        margin: 0;
        padding: 0;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        line-height: 1;
        font-size: 0.95rem;
        transition: color 0.2s ease;
    }

    .toggle-password-btn:hover {
        color: #0284c7;
    }

    #passwordAlert {
        display: none;
        padding: 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        margin-bottom: 1rem;
        text-align: center;
        font-weight: 600;
    }

    .alert-error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #f87171;
    }

    .alert-success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }
</style>

<header class="main-header">
    <div class="header-left">
        <img src="../src/images/logo.png" class="logo" alt="School Logo">
        <div class="header-logo">
            <strong>Tapuyan National High School</strong>
            <span>Employee Portal</span>
        </div>
    </div>

    <!-- Toggle Sidenav Button for Mobile -->
    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle Sidebar Navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="custom-modal">
    <div class="modal-box">
        <div class="modal-icon-badge">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>

        <h3>Sign Out</h3>
        <p>Are you sure you want to log out of your portal session?</p>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeLogoutModal()">
                Cancel
            </button>

            <form action="../controllers/logout.php" method="post" style="margin: 0;">
                <button type="submit" class="btn-modal btn-confirm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

<!-- CHANGE PASSWORD MODAL -->
<div id="passwordModal" class="custom-modal">
    <div class="modal-box">
        <div class="modal-icon-badge password-badge">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h3>Change Password</h3>
        <p style="margin-bottom: 1rem;">Update your account credentials securely.</p>

        <div id="passwordAlert"></div>

        <form id="changePasswordForm" onsubmit="handlePasswordChange(event)">

            <div class="form-group-modal">
                <label for="current_password">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" name="current_password" required
                        placeholder="Enter current password">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('current_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group-modal">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required
                        placeholder="Min. 6 characters">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group-modal" style="margin-bottom: 1.5rem;">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required
                        placeholder="Repeat new password">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('confirm_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closePasswordModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-modal btn-primary-modal" id="btnSubmitPassword">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<div class="announcement-wrapper" id="announcementWrapper">
</div>
<div id="logoutModal" class="custom-modal">
    <div class="modal-box">
        <div class="modal-icon-badge">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>

        <h3>Sign Out</h3>
        <p>Are you sure you want to log out of your portal session?</p>

        <div class="modal-actions">
            <button type="button" class="btn-modal btn-cancel" onclick="closeLogoutModal()">
                Cancel
            </button>

            <form action="../controllers/logout.php" method="post" style="margin: 0;">
                <button type="submit" class="btn-modal btn-confirm">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>

<div id="passwordModal" class="custom-modal">
    <div class="modal-box">
        <div class="modal-icon-badge password-badge">
            <i class="fa-solid fa-lock"></i>
        </div>

        <h3>Change Password</h3>
        <p style="margin-bottom: 1rem;">Update your account credentials securely.</p>

        <div id="passwordAlert"></div>

        <form id="changePasswordForm" onsubmit="handlePasswordChange(event)">

            <div class="form-group-modal">
                <label for="current_password">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" name="current_password" required
                        placeholder="Enter current password">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('current_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group-modal">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required
                        placeholder="Min. 6 characters">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('new_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group-modal" style="margin-bottom: 1.5rem;">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required
                        placeholder="Repeat new password">
                    <button type="button" class="toggle-password-btn" onclick="togglePassword('confirm_password', this)"
                        title="Show/Hide Password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closePasswordModal()">
                    Cancel
                </button>
                <button type="submit" class="btn-modal btn-primary-modal" id="btnSubmitPassword">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    // Sidebar Mobile Toggle Logic
    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            const sidebar = document.getElementById('appSidebar');
            if (sidebar) sidebar.classList.toggle('show');
        });
    }

    // Modal Controls
    const logoutModal = document.getElementById('logoutModal');
    const passwordModal = document.getElementById('passwordModal');
    const passwordAlert = document.getElementById('passwordAlert');
    const passwordForm = document.getElementById('changePasswordForm');

    function openLogoutModal() {
        logoutModal.style.display = 'flex';
        void logoutModal.offsetWidth;
        logoutModal.classList.add('show');
    }

    function closeLogoutModal() {
        logoutModal.classList.remove('show');
        setTimeout(() => { logoutModal.style.display = 'none'; }, 250);
    }

    function openPasswordModal() {
        passwordAlert.style.display = 'none';
        passwordForm.reset();

        ['current_password', 'new_password', 'confirm_password'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.type = 'password';
        });
        document.querySelectorAll('.toggle-password-btn i').forEach(icon => {
            icon.className = 'fa-solid fa-eye';
        });

        passwordModal.style.display = 'flex';
        void passwordModal.offsetWidth;
        passwordModal.classList.add('show');
    }

    function closePasswordModal() {
        passwordModal.classList.remove('show');
        setTimeout(() => { passwordModal.style.display = 'none'; }, 250);
    }

    function togglePassword(inputId, btnElement) {
        const input = document.getElementById(inputId);
        const icon = btnElement.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    window.addEventListener('click', function (e) {
        if (e.target === logoutModal) closeLogoutModal();
        if (e.target === passwordModal) closePasswordModal();
    });

    async function handlePasswordChange(event) {
        event.preventDefault();

        const submitBtn = document.getElementById('btnSubmitPassword');
        const formData = new FormData(passwordForm);

        passwordAlert.style.display = 'none';
        passwordAlert.className = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

        try {
            const response = await fetch('../controllers/update_password.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            passwordAlert.textContent = result.message;
            passwordAlert.style.display = 'block';

            if (result.status === 'success') {
                passwordAlert.className = 'alert-success';
                passwordForm.reset();
                setTimeout(() => {
                    closePasswordModal();
                }, 1500);
            } else {
                passwordAlert.className = 'alert-error';
            }
        } catch (error) {
            passwordAlert.textContent = 'An unexpected error occurred. Please try again.';
            passwordAlert.style.display = 'block';
            passwordAlert.className = 'alert-error';
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Password';
        }
    }
</script>