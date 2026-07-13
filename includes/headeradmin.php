<?php
include_once '../db/conn.php';
?>
<header class="header-wrapper">
    <img src="../src/images/bgpng.png" alt="Header Background" class="header-bg">

    <div class="header-overlay"></div>

    <div class="header-content">
        <div class="header-text">
            <h1>Admin Dashboard</h1>
            <p>Manage employees and records efficiently</p>
        </div>

        <div class="header-welcome">
            <span>
                Welcome,
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!
            </span>
        </div>
    </div>
</header>

<style>
    .header-wrapper {
        position: relative;
        width: 100%;
        height: 240px;
        overflow: hidden;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        margin-bottom: 1.5rem;
        background: #000;
    }

    .header-bg {
        width: 100%;
        height: 100%;
        object-fit: fill;
        display: block;
    }

    .header-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
                rgba(0, 0, 0, 0.65) 0%,
                rgba(0, 0, 0, 0.35) 50%,
                rgba(0, 0, 0, 0.2) 100%);
        z-index: 1;
    }

    .header-content {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        color: #fff;
    }

    .header-text h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
        letter-spacing: 0.5px;
    }

    .header-text p {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }

    .header-welcome {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: 0.85rem 1.2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .header-wrapper {
            height: 170px;
        }

        .header-content {
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
        }

        .header-text p {
            font-size: 0.85rem;
        }

        .header-welcome {
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }
    }

    @media (max-width: 480px) {
        .header-wrapper {
            height: 150px;
            border-radius: 0 0 14px 14px;
        }

        .header-content {
            padding: 1rem;
        }

        .header-text h1 {
            font-size: 1.2rem;
        }

        .header-text p {
            font-size: 0.8rem;
        }

        .header-welcome {
            width: 100%;
            white-space: normal;
            font-size: 0.85rem;
        }
    }
</style>