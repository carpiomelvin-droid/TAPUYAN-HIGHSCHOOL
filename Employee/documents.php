<?php
session_start();
include_once '../db/conn.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    button {
        margin-top: 20px;
        padding: 10px;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: 0.2s;
    }

    button:hover {
        background-color: #0056b3;
    }
</style>
<style>
    #btn-form,
    #btn-uploaDoc {
        border: none;
        background-color: transparent;
        color: #007bff;
        font-size: 0.9em;
    }

    .header-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    #btn-save {
        width: 100px;
        font-size: 0.8em;
    }
</style>
<script>
    function showFileName(input) {
        // Find the <small> tag that is the next sibling of the input's parent <div>
        const smallTag = input.nextElementSibling;

        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const fileName = file.name;
            const fileSize = file.size; // Size in bytes
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (fileSize > maxSize) {
                // 1. Alert the user
                alert('Error: File is too large!\n\n"' + fileName + '" is ' + (fileSize / 1024 / 1024).toFixed(2) + ' MB.\nMax allowed size is 5MB.');

                // 2. Clear the invalid file from the input
                input.value = '';

                // 3. Reset the text
                smallTag.textContent = 'No file selected';
            } else {
                // File is valid, show the name
                smallTag.textContent = fileName;
            }
        } else {
            // No file selected
            smallTag.textContent = 'No file selected';
        }
    }
</script>


<body>
    <!--  employee docments form -->
    <form action="../controllers/logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
    <div class="header-btn">
        <div>
            <button id="btn-form"><a href="../Employee/home.php">Fill up Form</a></button>
            <button id="btn-uploaDoc"><a href="../Employee/documents.php">Upload documents</a></button>
        </div>
    </div>
    <form action="../controllers/employeedocumentcontroller.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="EmployeeID" value="<?php echo $_SESSION['EmployeeID']; ?>">
        <div id="upload-form">
            <h3>Upload Documents (image or PDF)</h3>
            <small>Allowed: .pdf, image files (jpg, png). Each file max-size should be 5MB.</small>
            <div class="files">
                <div>
                    <label for="prc_file">PRC (img/pdf)</label>
                    <input id="prc_file" name="prc_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="dll_file">DLL (img/pdf)</label>
                    <input id="dll_file" name="dll_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="saln_file">SALN (img/pdf)</label>
                    <input id="saln_file" name="saln_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="ipcr_file">IPCR (img/pdf)</label>
                    <input id="ipcr_file" name="ipcr_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="diploma_file">DIPLOMA (img/pdf)</label>
                    <input id="diploma_file" name="diploma_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="tor_file">TOR (img/pdf)</label>
                    <input id="tor_file" name="tor_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="itr_file">ITR (img/pdf)</label>
                    <input id="itr_file" name="itr_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="itr_file_2">ITR (2nd copy - img/pdf)</label>
                    <input id="itr_file_2" name="itr_file_2" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="service_record_file">SERVICE RECORD (img/pdf)</label>
                    <input id="service_record_file" name="service_record_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="appointment_file">APPOINTMENT (img/pdf)</label>
                    <input id="appointment_file" name="appointment_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="trainings_awards_file">TRAININGS AND AWARDS (img/pdf)</label>
                    <input id="trainings_awards_file" name="trainings_awards_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
                <div>
                    <label for="pds_file">PDS (img/pdf)</label>
                    <input id="pds_file" name="pds_file" type="file" accept=".pdf, image/*" onchange="showFileName(this)">
                    <small class="file-name">No file selected</small>
                </div>
            </div>
        </div>
        </div>
        <div>
            <button id="btn-save" id="hide-btn-save" type="submit" name="save">Save</button>
        </div>
    </form>
</body>

</html>