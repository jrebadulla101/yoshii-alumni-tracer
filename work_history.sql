-- Create work_history table
CREATE TABLE IF NOT EXISTS work_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_address TEXT NOT NULL,
    work_position VARCHAR(100) NOT NULL,
    is_course_related ENUM('Yes', 'No') NOT NULL,
    employment_status VARCHAR(50) NOT NULL,
    date_started DATE NOT NULL,
    is_current_job ENUM('Yes', 'No') NOT NULL,
    date_ended DATE,
    salary VARCHAR(50),
    industry VARCHAR(255),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 