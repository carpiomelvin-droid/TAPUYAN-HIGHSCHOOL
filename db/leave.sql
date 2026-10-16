-- Run once in the Tapuyan_NHS_IMS database.
CREATE TABLE IF NOT EXISTS employee_leave_credits (
    credit_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EmployeeID INT NOT NULL,
    leave_year YEAR NOT NULL,
    vacation_entitlement DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    sick_entitlement DECIMAL(8,2) NOT NULL DEFAULT 15.00,
    other_entitlement DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (credit_id),
    UNIQUE KEY uq_employee_leave_year (EmployeeID, leave_year),
    KEY idx_leave_credits_employee (EmployeeID),
    CONSTRAINT fk_leave_credits_employee FOREIGN KEY (EmployeeID) REFERENCES admin(EmployeeID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS leave_records (
    leave_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    EmployeeID INT NOT NULL,
    uploaded_by INT NOT NULL,
    leave_year YEAR NOT NULL,
    leave_type ENUM('vacation','sick','other') NOT NULL DEFAULT 'vacation',
    document_type VARCHAR(100) NOT NULL,
    reference_number VARCHAR(100) NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    days DECIMAL(8,2) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    remarks TEXT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_data LONGBLOB NOT NULL,
    mime_type VARCHAR(150) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (leave_id),
    KEY idx_leave_records_employee_year (EmployeeID, leave_year, status),
    CONSTRAINT fk_leave_records_employee FOREIGN KEY (EmployeeID) REFERENCES admin(EmployeeID) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_leave_records_uploader FOREIGN KEY (uploaded_by) REFERENCES admin(EmployeeID) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
