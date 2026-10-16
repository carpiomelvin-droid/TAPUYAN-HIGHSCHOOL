<?php
include_once './db/conn.php';
$landingAnnouncements = [];
$landingResult = $conn->query("SELECT a.announcement_id, a.title, a.category, a.body, a.image_name, a.created_at,
    COALESCE(NULLIF(d.name, ''), NULLIF(ad.username, ''), 'Administration') AS creator_name,
    d.avatar_image AS creator_avatar, d.avatar_mime_type AS creator_avatar_mime
    FROM announcements a
    INNER JOIN admin ad
        ON ad.EmployeeID = a.created_by
       AND ad.role = 'superadmin'
    LEFT JOIN employee_details d ON d.EmployeeID = ad.EmployeeID
    WHERE a.is_published = 1 ORDER BY a.created_at DESC LIMIT 6");
if ($landingResult) {
    while ($landingRow = $landingResult->fetch_assoc()) {
        $landingAnnouncements[] = $landingRow;
    }
}
// Batch-load up to 10 gallery images per landing announcement.
$landingGalleries = [];
if (!empty($landingAnnouncements)) {
    $landingIds = array_values(array_unique(array_filter(array_map(static fn($row) => (int) $row['announcement_id'], $landingAnnouncements))));
    if (!empty($landingIds)) {
        $landingIdList = implode(',', $landingIds);
        $landingGalleryResult = $conn->query("SELECT image_id, announcement_id, image_name FROM announcement_images WHERE announcement_id IN ({$landingIdList}) ORDER BY announcement_id ASC, sort_order ASC, image_id ASC");
        if ($landingGalleryResult) {
            while ($landingGalleryRow = $landingGalleryResult->fetch_assoc()) {
                $landingGalleryAnnouncementId = (int) $landingGalleryRow['announcement_id'];
                if (!isset($landingGalleries[$landingGalleryAnnouncementId])) {
                    $landingGalleries[$landingGalleryAnnouncementId] = [];
                }
                if (count($landingGalleries[$landingGalleryAnnouncementId]) < 10) {
                    $landingGalleries[$landingGalleryAnnouncementId][] = $landingGalleryRow;
                }
            }
        }
    }
}
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Tapuyan NHS Employee Portal</title>
    <link rel="icon" type="image/png" href="src/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f172a;
            --primary-light: #1e293b;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --yellow-accent: #facc15;
            --yellow-hover: #eab308;
            --bg-light: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.25);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ==========================================
           FIXED HEADER NAVIGATION
           ========================================== */
        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 76px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            box-shadow: var(--shadow-sm);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-text strong {
            font-size: 1.1rem;
            color: var(--primary);
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            list-style: none;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        /* CTA Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.65rem 1.4rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: var(--transition);
        }

        .btn-login {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: var(--shadow-sm);
        }

        .btn-login:hover {
            background-color: var(--accent);
            transform: translateY(-1px);
        }

        .btn-hero {
            background-color: var(--yellow-accent);
            color: #0f172a;
            font-size: 1rem;
            padding: 0.85rem 2rem;
            box-shadow: var(--shadow-md);
        }

        .btn-hero:hover {
            background-color: var(--yellow-hover);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.6);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: #ffffff;
        }

        /* Hamburger Toggle for Mobile */
        .menu-toggle {
            display: none;
            font-size: 1.5rem;
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
        }

        /* ==========================================
           HERO SECTION
           ========================================== */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 100px 20px 60px;
            background: url('./src/images/bg.jpg') no-repeat center center/contain;
            z-index: 1;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(30, 41, 59, 0.75));
            z-index: -1;
            height: 89%;
        }

        .hero-content {
            max-width: 800px;
            color: #ffffff;
        }

        .hero-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hero h1 {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 1.2rem;
            letter-spacing: -1px;
        }

        .hero p {
            font-size: 1.15rem;
            color: #cbd5e1;
            margin-bottom: 2.5rem;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ==========================================
           CONTENT SECTIONS
           ========================================== */
        .section {
            padding: 80px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .section-title p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2.5rem;
        }

        .info-card {
            background: var(--surface);
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .info-card h3 {
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-card h3 i {
            color: var(--accent);
        }

        /* Announcement media: full-width banner + thumbnail strip */
        .ann-media-banner {
            position: relative;
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            margin: .75rem 0;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #0f172a;
            isolation: isolate;
        }

        .ann-media-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: var(--ann-bg);
            background-size: cover;
            background-position: center;
            filter: blur(22px) brightness(.65);
            transform: scale(1.12);
        }

        .ann-media-banner img {
            position: relative;
            z-index: 1;
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .ann-media-banner:hover img {
            transform: scale(1.01);
        }

        .ann-media-banner img {
            transition: transform .25s ease;
        }

        .ann-media-count {
            position: absolute;
            right: .6rem;
            bottom: .6rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .6rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, .75);
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
        }

        .ann-media-thumbs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
            gap: .5rem;
            margin: 0 0 .75rem;
        }

        .ann-media-thumbs a {
            display: block;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: #f8fafc;
        }

        .ann-media-thumbs img {
            display: block;
            width: 100%;
            height: 76px;
            object-fit: cover;
            transition: transform .2s ease;
        }

        .ann-media-thumbs a:hover img {
            transform: scale(1.05);
        }

        /* ==========================================
           MODAL & ROLE SELECTION STYLES
           ========================================== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .modal-overlay.show {
            display: flex;
            opacity: 1;
        }

        .login-card {
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .32);
            padding: 2rem;
            width: min(100%, 440px);
            max-height: min(680px, calc(100vh - 2rem));
            overflow-y: auto;
            position: relative;
            transform: scale(0.96) translateY(-10px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .modal-overlay.show .login-card {
            transform: scale(1) translateY(0);
        }

        .btn-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1.35rem;
            line-height: 1;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .btn-close:hover,
        .btn-close:focus-visible {
            color: var(--primary);
            background: #e2e8f0;
            border-color: #cbd5e1;
            outline: none;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-header h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* --- Role Selection Grid (Step 1) --- */
        .role-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .role-option {
            width: 100%;
            background: var(--bg-light);
            border: 2px solid var(--border);
            border-radius: 8px;
            padding: 1.35rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            font: inherit;
        }

        .role-option:hover,
        .role-option:focus-visible {
            border-color: var(--accent);
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            outline: none;
        }

        .role-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .role-emp .role-icon {
            background: #dbeafe;
            color: var(--accent);
        }

        .role-admin .role-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .role-option strong {
            font-size: 0.95rem;
            color: var(--primary);
            display: block;
        }

        .role-option span {
            font-size: 0.75rem;
            color: var(--text-muted);
            line-height: 1.3;
        }

        /* --- Login Form View (Step 2) --- */
        #loginFormView {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--text-muted);
            background: none;
            border: none;
            cursor: pointer;
            margin-bottom: 1.25rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .back-link:hover,
        .back-link:focus-visible {
            color: var(--accent);
            outline: none;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .badge-emp {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-admin {
            background: #fef3c7;
            color: #b45309;
        }

        .form-login {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .input-group {
            position: relative;
            width: 100%;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 42px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--bg-light);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .password-field input {
            padding-right: 3.25rem;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: .45rem;
            display: grid;
            place-items: center;
            width: 2.25rem;
            height: 2.25rem;
            margin: 0 !important;
            padding: 0 !important;
            color: var(--text-muted) !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 6px !important;
            transform: translateY(-50%);
            line-height: 1;
            text-transform: none !important;
            letter-spacing: 0 !important;
            font-size: .9rem !important;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            color: var(--accent) !important;
            background: #eff6ff !important;
            outline: none;
        }

        .form-login button {
            background-color: var(--primary);
            color: #ffffff;
            text-transform: uppercase;
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: var(--transition);
            margin-top: 0.5rem;
        }

        .form-login>button:hover,
        .form-login>button:focus-visible {
            background-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .2);
        }

        @media (max-width: 480px) {
            .modal-overlay {
                align-items: flex-end;
                padding: .65rem;
            }

            .login-card {
                padding: 1.5rem;
                border-radius: 12px 12px 8px 8px;
                max-height: calc(100vh - 1.3rem);
            }

            .role-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Footer */
        footer {
            background: var(--primary);
            color: #94a3b8;
            text-align: center;
            padding: 2rem;
            font-size: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 900px) {
            .nav-links {
                position: fixed;
                top: 76px;
                right: -100%;
                width: 280px;
                height: calc(100vh - 76px);
                background: var(--surface);
                flex-direction: column;
                padding: 2rem;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                transition: right 0.3s ease;
                align-items: flex-start;
            }

            .nav-links.active {
                right: 0;
            }

            .menu-toggle {
                display: block;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .grid-2,
            .role-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <header class="main-header">
        <div>
            <img src="./src/images/logo.png" alt="" style="width: 65px; object-fit: contain;">
        </div>
        <a href="index.php" class="brand-logo">

        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars" id="menuIcon"></i>
        </button>

        <ul class="nav-links" id="navLinks">
            <li><a href="#about" onclick="closeMenu()">Vision & Mission</a></li>
            <li><a href="#policy" onclick="closeMenu()">Quality Policy</a></li>
            <li><a href="#news" onclick="closeMenu()">Announcements</a></li>
            <li><a href="#contact" onclick="closeMenu()">Contact Us</a></li>
            <li>
                <button type="button" class="btn btn-login" onclick="openLoginModal(); closeMenu();">
                    <i class="fa-solid fa-right-to-bracket"></i> Log In
                </button>
            </li>
        </ul>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Tapuyan National High School</h1>
            <p>Empowering educators and streamlining administrative workflows. Access your employee records, submit
                documents, and manage appointments in one unified portal.</p>
            <div class="hero-buttons">
                <button type="button" class="btn btn-hero" onclick="openLoginModal()">
                    <i class="fa-solid fa-user-lock"></i> Portal Login
                </button>
                <a href="#about" class="btn btn-outline">
                    <i class="fa-solid fa-circle-info"></i> Learn More
                </a>
            </div>
        </div>
    </section>

    <section id="about" class="section">
        <div class="section-title">
            <h2>Our Purpose</h2>
            <p>Guided by the Department of Education's core principles</p>
        </div>
        <div class="grid-2">
            <div class="info-card">
                <h3><i class="fa-solid fa-eye"></i> Our Vision</h3>
                <p>We dream of Filipinos who passionately love their country and whose values and competencies enable
                    them to realize their full potential and contribute meaningfully to building the nation. As a
                    learner-centered public institution, the Department of Education continuously improves itself to
                    better serve its stakeholders.</p>
            </div>
            <div class="info-card">
                <h3><i class="fa-solid fa-bullseye"></i> Our Mission</h3>
                <p>To protect and promote the right of every Filipino to quality, equitable, culture-based, and complete
                    basic education where students learn in a child-friendly, gender-sensitive, safe, and motivating
                    environment.</p>
            </div>
        </div>
    </section>

    <section id="policy" class="section"
        style="background-color: #ffffff; max-width: 100%; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <div style="max-width: 1000px; margin: 0 auto; text-align: center;">
            <div class="section-title">
                <h2>DepEd Quality Policy</h2>
                <p>Commitment to Excellence in Service</p>
            </div>
            <p style="font-size: 1.1rem; color: var(--primary-light); line-height: 1.8;">
                "The Department of Education is committed to provide learners with quality basic education that is
                accessible, inclusive, and empowering, centered on foundational skills and values, through continuously
                improving governance and management systems, while adhering to statutory and regulatory requirements."
            </p>
        </div>
    </section>

    <section id="news" class="section">
        <div class="section-title">
            <h2>News Feed & Announcements</h2>
            <p>Stay updated with the latest memorandum and school activities</p>
        </div>
        <?php if (empty($landingAnnouncements)): ?>
            <div class="info-card" style="text-align: center; padding: 3rem;">
                <i class="fa-regular fa-bell" style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;"></i>
                <h3 style="justify-content: center;">No New Announcements</h3>
                <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto;">Official memorandums, scheduled
                    system maintenances, and school events will be posted here regularly.</p>
            </div>
        <?php else: ?>
            <div style="display:grid; gap:1rem; max-width:900px; margin:0 auto;">
                <?php foreach ($landingAnnouncements as $landingAnnouncement): ?>
                    <article class="info-card" style="padding:1.5rem;">
                        <?php
                        $landingCreatorAvatar = 'src/images/avatar.png';
                        if (!empty($landingAnnouncement['creator_avatar']) && !empty($landingAnnouncement['creator_avatar_mime'])) {
                            $landingCreatorAvatar = 'data:' . e($landingAnnouncement['creator_avatar_mime']) . ';base64,' . base64_encode($landingAnnouncement['creator_avatar']);
                        }
                        ?>
                        <div style="display:flex; align-items:center; gap:.65rem; margin:.8rem 0;">
                            <img src="<?php echo e($landingCreatorAvatar); ?>" alt=""
                                style="width:2.45rem; height:2.45rem; object-fit:cover; border-radius:50%; border:1px solid #dbeafe; background:#eff6ff;">
                            <div style="color:var(--text-muted); font-size:.78rem; line-height:1.35;">
                                <div><strong
                                        style="color:var(--text-main); font-size:.86rem;"><?php echo e($landingAnnouncement['creator_name']); ?></strong>
                                    posted an announcement.</div>
                                <time datetime="<?php echo e(date('c', strtotime($landingAnnouncement['created_at']))); ?>">
                                    <?php echo e(date('M j, Y g:i A', strtotime($landingAnnouncement['created_at']))); ?>
                                </time>
                            </div>
                        </div>
                        <div
                            style="display:flex; align-items:center; gap:.45rem; color:var(--accent); font-size:.78rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase;">
                            <?php echo e($landingAnnouncement['category']); ?>
                        </div>
                        <h3 style="margin:.5rem 0;"> <?php echo e($landingAnnouncement['title']); ?></h3>
                        <?php
                        $landingGallery = $landingGalleries[(int) $landingAnnouncement['announcement_id']] ?? [];
                        $landingLegacyUrl = !empty($landingAnnouncement['image_name'])
                            ? 'controllers/announcement_image.php?id=' . (int) $landingAnnouncement['announcement_id']
                            : '';
                        $landingMainUrl = !empty($landingGallery)
                            ? 'controllers/announcement_image.php?image_id=' . (int) $landingGallery[0]['image_id']
                            : $landingLegacyUrl;
                        ?>
                        <?php if ($landingMainUrl !== ''): ?>
                            <a class="ann-media-banner" href="<?php echo $landingMainUrl; ?>" target="_blank"
                                rel="noopener" style="--ann-bg:url('<?php echo $landingMainUrl; ?>');"
                                title="Click to view full image">
                                <img src="<?php echo $landingMainUrl; ?>"
                                    alt="<?php echo e($landingAnnouncement['title']); ?>" loading="lazy">
                                <?php if (count($landingGallery) > 1): ?>
                                    <span class="ann-media-count"><i class="fa-solid fa-images"></i>
                                        <?php echo count($landingGallery); ?> photos</span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                        <?php if (count($landingGallery) > 1): ?>
                            <div class="ann-media-thumbs">
                                <?php foreach (array_slice($landingGallery, 1) as $landingImage): ?>
                                    <?php $thumbUrl = 'controllers/announcement_image.php?image_id=' . (int) $landingImage['image_id']; ?>
                                    <a href="<?php echo $thumbUrl; ?>" target="_blank" rel="noopener"
                                        title="<?php echo e($landingImage['image_name'] ?? ''); ?>">
                                        <img src="<?php echo $thumbUrl; ?>"
                                            alt="<?php echo e($landingAnnouncement['title']); ?>" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <p style="color:var(--text-muted); white-space:pre-line;"><?php echo e($landingAnnouncement['body']); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="contact" class="section" style="max-width: 800px;">
        <div class="section-title">
            <h2>Contact Us</h2>
            <p>Get in touch with the school administration</p>
        </div>
        <div class="info-card" style="display: flex; flex-direction: column; gap: 1rem; text-align: center;">
            <div><strong><i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 8px;"></i>
                    Location:</strong> Tapuyan, Gasan, Marinduque</div>
            <div><strong><i class="fa-solid fa-envelope" style="color: var(--accent); margin-right: 8px;"></i>
                    Email:</strong> 301565@deped.gov.ph</div>
            <div><strong><i class="fa-solid fa-clock" style="color: var(--accent); margin-right: 8px;"></i> Office
                    Hours:</strong> Monday – Friday, 8:00 AM – 5:00 PM</div>
        </div>
    </section>

    <footer>
        <p>&copy;
            <?php echo date("Y"); ?> Tapuyan National High School. All rights reserved. | Employee Management
            System
        </p>
    </footer>

    <div id="loginModal" class="modal-overlay">
        <div class="login-card">
            <button type="button" class="btn-close" onclick="closeLoginModal()">&times;</button>

            <div id="roleSelectionView">
                <div class="login-header">
                    <h2>Select Account Type</h2>
                    <p>Choose your role to access the appropriate portal</p>
                </div>

                <div class="role-grid">
                    <button type="button" class="role-option role-emp" onclick="selectRole('employee')">
                        <div class="role-icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <strong>Employee Login</strong>
                        <span>Faculty & Staff members</span>
                    </button>

                    <button type="button" class="role-option role-admin" onclick="selectRole('admin')">
                        <div class="role-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <strong>Admin Login</strong>
                        <span>System Administrators</span>
                    </button>
                </div>
            </div>

            <div id="loginFormView">
                <button type="button" class="back-link" onclick="showRoleSelection()">
                    <i class="fa-solid fa-arrow-left"></i> Back to Role Selection
                </button>

                <div class="login-header" style="margin-bottom: 1.5rem;">
                    <span id="roleBadge" class="role-badge badge-emp">Employee Portal</span>
                    <p>Please sign in with your authorized username and password</p>
                </div>

                <form id="portalLoginForm" action="./controllers/adminlogincontroller.php" method="post"
                    class="form-login">
                    <div class="input-group">
                        <i class="fa fa-user"></i>
                        <input type="text" name="username" placeholder="Enter username" required
                            autocomplete="username">
                    </div>

                    <div class="input-group password-field">
                        <i class="fa fa-lock"></i>
                        <input id="portal-password" type="password" name="password" placeholder="Enter password"
                            required autocomplete="current-password">
                        <button type="button" class="password-toggle" data-password-target="portal-password"
                            aria-label="Show password" title="Show password">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    <button type="submit">Log in to Portal</button>
                </form>
            </div>

        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <style>
            .top-alert-banner {
                position: fixed;
                top: 24px;
                left: 50%;
                transform: translateX(-50%) translateY(-20px);
                background: #ffffff;
                border: 1px solid #e2e8f0;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                padding: 12px 20px;
                border-radius: 50px;
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 99999;
                opacity: 0;
                animation: slideDownFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                max-width: 90%;
            }

            .top-alert-icon {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.85rem;
                flex-shrink: 0;
            }

            .alert-warning {
                border-left: 4px solid #facc15;
            }

            .alert-warning .top-alert-icon {
                background: #fef9c3;
                color: #ca8a04;
            }

            .alert-danger {
                border-left: 4px solid #ef4444;
            }

            .alert-danger .top-alert-icon {
                background: #fee2e2;
                color: #ef4444;
            }

            .top-alert-text {
                font-size: 0.875rem;
                font-weight: 600;
                color: #0f172a;
            }

            .top-alert-close {
                background: none;
                border: none;
                color: #94a3b8;
                cursor: pointer;
                font-size: 1rem;
                padding: 0 4px;
                transition: color 0.2s;
            }

            .top-alert-close:hover {
                color: #0f172a;
            }

            @keyframes slideDownFadeIn {
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }

            @keyframes fadeOutUp {
                to {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-20px);
                }
            }
        </style>

        <?php if ($_GET['error'] === 'wrong_role'): ?>
            <div id="minimalAlert" class="top-alert-banner alert-warning">
                <div class="top-alert-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <span class="top-alert-text">Incorrect portal selected. Please choose the appropriate role to sign in.</span>
                <button type="button" class="top-alert-close" onclick="dismissAlert()">&times;</button>
            </div>
        <?php elseif ($_GET['error'] === 'invalid_password' || $_GET['error'] === 'not_found'): ?>
            <div id="minimalAlert" class="top-alert-banner alert-danger">
                <div class="top-alert-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                <span class="top-alert-text">Invalid username or password. Please try again.</span>
                <button type="button" class="top-alert-close" onclick="dismissAlert()">&times;</button>
            </div>
        <?php endif; ?>

        <script>
            function dismissAlert() {
                const alertBox = document.getElementById('minimalAlert');
                if (alertBox) {
                    alertBox.style.animation = 'fadeOutUp 0.3s forwards';
                    setTimeout(() => {
                        alertBox.style.display = 'none';
                    }, 300);
                }
                // Clean the address bar URL without reloading the page
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // Auto-dismiss after 4.5 seconds
            setTimeout(() => {
                dismissAlert();
            }, 4500);

            // Automatically re-open the role selection modal so the user can try again immediately!
            setTimeout(() => {
                if (typeof openLoginModal === 'function') {
                    openLoginModal();
                }
            }, 400);
        </script>
    <?php endif; ?>

    <script>
        const modal = document.getElementById('loginModal');
        const roleView = document.getElementById('roleSelectionView');
        const formView = document.getElementById('loginFormView');
        const roleBadge = document.getElementById('roleBadge');

        const menuToggle = document.getElementById('menuToggle');
        const menuIcon = document.getElementById('menuIcon');
        const navLinks = document.getElementById('navLinks');

        // Modal Open & Close Controls
        function openLoginModal() {
            showRoleSelection(); // Always reset to Step 1 when modal opens
            modal.style.display = 'flex';
            void modal.offsetWidth; // Trigger reflow for animation
            modal.classList.add('show');
        }

        function closeLoginModal() {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 250);
        }

        // Switch between Step 1 (Roles) and Step 2 (Form)
        function showRoleSelection() {
            roleView.style.display = 'block';
            formView.style.display = 'none';
        }

        function selectRole(role) {
            roleView.style.display = 'none';
            formView.style.display = 'block';

            const loginForm = document.getElementById('portalLoginForm');

            // 🟢 DYNAMICALLY ROUTE EACH ROLE TO ITS DEDICATED CONTROLLER
            if (role === 'admin') {
                roleBadge.textContent = 'Admin Portal';
                roleBadge.className = 'role-badge badge-admin';
                loginForm.action = './controllers/adminlogincontroller.php';
            } else {
                roleBadge.textContent = 'Employee Portal';
                roleBadge.className = 'role-badge badge-emp';
                loginForm.action = './controllers/employeelogincontroller.php';
            }
        }

        // Close modal when clicking on the dark blurred backdrop
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeLoginModal();
            }
        });

        // Close modal when pressing the Escape (ESC) key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeLoginModal();
            }
        });

        // Mobile Hamburger Menu Toggle
        menuToggle.addEventListener('click', () => {
            const isActive = navLinks.classList.toggle('active');
            menuIcon.className = isActive ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        });

        function closeMenu() {
            if (navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                menuIcon.className = 'fa-solid fa-bars';
            }
        }

        document.addEventListener('click', function (event) {
            const toggle = event.target.closest('.password-toggle');
            if (!toggle) return;
            const input = document.getElementById(toggle.dataset.passwordTarget);
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            toggle.querySelector('i').className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            toggle.title = isHidden ? 'Hide password' : 'Show password';
        });
    </script>
</body>

</html>