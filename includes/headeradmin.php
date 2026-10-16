<?php
// No database include needed here
?>
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    :root {
        --border-color: #dee2e6;
        --text-color: #343a40;
        --text-muted: #6c757d;
    }

    /* HEADER */
    .main-header {
        position: relative;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1.5rem;
        height: 70px;
        background: linear-gradient(135deg, #78a0e6ff, #edeb74ff);
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    }

    /* LEFT */
    .header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .logo {
        width: 60px;
        height: auto;
    }

    .header-logo {
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .header-logo strong {
        font-size: 1.1rem;
    }

    .header-logo span {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* NAV */
    .header-nav {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-nav a,
    .header-nav button,
    .header-welcome {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.55rem 0.9rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.9rem;
        color: var(--text-color);
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 500;
    }

    .header-nav a:hover,
    .header-nav button:hover {
        background: #faf60cff;
    }

    /* WELCOME */
    .header-welcome {
        cursor: default;
        background: rgba(255, 255, 255, 0.35);
        font-weight: 600;
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
    }

    /* MOBILE */
    @media (max-width:768px) {
        .menu-toggle {
            display: block;
        }

        .header-nav .mobile-message-link {
            display: flex;
        }

        .header-nav {
            position: absolute;
            top: 70px;
            right: 1rem;
            width: 250px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
            padding: 12px;
            flex-direction: column;
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
            transition: 0.2s;
            z-index: 999;
        }

        .header-nav.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .header-nav a,
        .header-nav button,
        .header-welcome {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width:425px) {
        .main-header {
            padding: 0 1rem;
            height: 72px;
        }

        .logo {
            width: 56px;
        }

        .header-logo strong {
            font-size: 1rem;
        }

        .header-logo span {
            font-size: 0.7rem;
        }

        .header-nav {
            top: 72px;
            right: 12px;
            width: 220px;
        }

        .header-nav a,
        .header-nav button,
        .header-welcome {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tapuyan NHS - Employee Portal</title>
    <link rel="icon" type="image/png" href="../src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<header class="main-header">
    <div class="header-left">
        <img src="../src/images/logo.png" alt="Logo" class="logo">
        <div class="header-logo">
            <strong>Tapuyan National High School</strong>
            <span>Admin Dashboard</span>
        </div>
    </div>

    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Toggle admin sidebar"
        aria-controls="adminSidebar" aria-expanded="false">
        <i class="fa-solid fa-bars"></i>
    </button>
</header>

<script>
    const toggleBtn = document.getElementById('menuToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const sidebar = document.getElementById('adminSidebar');
            if (!sidebar) return;
            const isOpen = sidebar.classList.toggle('show');
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
</script>