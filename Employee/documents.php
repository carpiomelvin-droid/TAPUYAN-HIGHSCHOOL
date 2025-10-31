<?php
session_start();
include_once '../db/conn.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents | Tapuyan National High School</title>
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --success-color: #28a745;
            --success-hover: #218838;
            --danger-color: #dc3545;
            --danger-hover: #c82333;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-color: #343a40;
            --text-muted: #6c757d;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --border-radius: 8px;
            --header-height: 60px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            /* Prevent body from scrolling */
            overflow: hidden;
            height: 100vh;
        }

        /* --- Main Navigation Header --- */
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            height: var(--header-height);
        }

        .header-logo strong {
            font-size: 1.1rem;
        }

        .header-nav a,
        .header-nav button {
            text-decoration: none;
            color: var(--primary-color);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            margin-left: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: var(--border-radius);
            transition: background-color 0.2s;
        }

        .header-nav a:hover,
        .header-nav button:hover {
            background-color: #f1f1f1;
        }

        /* --- NEW: Active state for navigation --- */
        .header-nav a.active {
            background-color: var(--primary-color);
            color: #fff;
        }

        .header-nav a.active:hover {
            background-color: var(--primary-hover);
        }

        .btn-logout {
            color: var(--danger-color);
            border: 1px solid var(--danger-color) !important;
            background: none;
        }

        .btn-logout:hover {
            background-color: var(--danger-color) !important;
            color: #fff;
        }

        /* --- Button Base Styles --- */
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: var(--success-hover);
        }

        /* --- NEW: Main Content Area --- */
        .content-area {
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* --- NEW: Upload Card Styles --- */
        .upload-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .card-header p {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        /* --- NEW: File Upload Box --- */
        .file-upload-box {
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            position: relative;
            /* For the hidden input */
            background-color: #fdfdfd;
        }

        .file-upload-box:hover {
            border-color: var(--primary-color);
            background-color: #f8faff;
        }

        /* This is the magic: hide the input, make it cover the box */
        .file-upload-box input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        /* This is the text label */
        .file-upload-label {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-color);
            display: block;
            /* Make it fill the box */
            margin-top: 0.5rem;
            z-index: 1;
            /* Below the input */
            position: relative;
        }

        /* This is the <small> tag for the file name */
        .file-name {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 400;
            /* Ellipsis for long file names */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            z-index: 1;
            position: relative;
        }

        /* Simple Upload Icon */
        .file-upload-box::before {
            content: '📤';
            /* Simple upload emoji icon */
            font-size: 2.5rem;
            display: block;
            margin-bottom: 0.25rem;
            color: var(--primary-color);
            z-index: 1;
            position: relative;
        }

        /* Card Footer for the Save button */
        .card-footer {
            padding: 1.25rem 2rem;
            border-top: 1px solid var(--border-color);
            text-align: right;
            background-color: var(--light-bg);
        }

        /* --- Responsive Design --- */
        @media (max-width: 600px) {
            body {
                overflow: auto;
                /* Allow body to scroll on mobile */
                height: auto;
            }

            .main-header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
                height: auto;
                /* Let header expand */
            }

            .content-area {
                height: auto;
                padding: 1.5rem;
            }

            .upload-grid {
                grid-template-columns: 1fr;
                /* Stack on mobile */
                padding: 1.5rem;
            }

            .card-header,
            .card-footer {
                padding: 1rem 1.5rem;
            }
        }
    </style>

    <script>
        function showFileName(input) {
            // Find the <small> tag that is the next sibling
            const smallTag = input.nextElementSibling;

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const fileName = file.name;
                const fileSize = file.size; // Size in bytes
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (fileSize > maxSize) {
                    alert('Error: File is too large!\n\n"' + fileName + '" is ' + (fileSize / 1024 / 1024).toFixed(2) + ' MB.\nMax allowed size is 5MB.');
                    input.value = '';
                    smallTag.textContent = 'No file selected';
                } else {
                    smallTag.textContent = fileName;
                }
            } else {
                smallTag.textContent = 'No file selected';
            }
        }
    </script>
</head>

<body>
    <header class="main-header">
        <div class="header-logo">
            <strong>Tapuyan NHS</strong> Employee Portal
        </div>
        <nav class="header-nav">
            <a href="../Employee/home.php">Fill up Form</a>
            <a href="../Employee/documents.php" class="active">Upload documents</a>

            <form action="../controllers/logout.php" method="post" style="display: inline;">
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </nav>
    </header>

    <form action="../controllers/employeedocumentcontroller.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="EmployeeID" value="<?php echo $_SESSION['EmployeeID']; ?>">

        <main class="content-area">

            <div class="upload-card">
                <div class="card-header">
                    <h2>Upload Documents</h2>
                    <p>Allowed: .pdf, image files (jpg, png). Each file max-size should be 5MB.</p>
                </div>

                <div class="upload-grid">

                    <div class="file-upload-box">
                        <input id="prc_file" name="prc_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">PRC</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="dll_file" name="dll_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">DLL</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="saln_file" name="saln_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">SALN</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="ipcr_file" name="ipcr_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">IPCR</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="diploma_file" name="diploma_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">DIPLOMA</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="tor_file" name="tor_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">TOR</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="itr_file" name="itr_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">ITR</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="itr_file_2" name="itr_file_2" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">ITR (2nd copy)</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="service_record_file" name="service_record_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">SERVICE RECORD</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="appointment_file" name="appointment_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">APPOINTMENT</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="trainings_awards_file" name="trainings_awards_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">TRAININGS & AWARDS</span>
                    </div>

                    <div class="file-upload-box">
                        <input id="pds_file" name="pds_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                        <small class="file-name">No file selected</small>
                        <span class="file-upload-label">PDS</span>
                    </div>

                </div>
                <div class="card-footer">
                    <button id="btn-save" type="submit" name="save" class="btn btn-success">Save All Uploads</button>
                </div>

            </div>
        </main>
    </form>
</body>

</html>