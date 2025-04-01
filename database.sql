-- Create database
CREATE DATABASE IF NOT EXISTS alumni_tracer;
USE alumni_tracer;

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default courses
INSERT IGNORE INTO courses (course_name) VALUES 
('Bachelor of Science in Information Technology'),
('Bachelor of Science in Computer Science'),
('Bachelor of Science in Business Administration'),
('Bachelor of Science in Accountancy'),
('Bachelor of Science in Engineering');

-- Create alumni table
CREATE TABLE IF NOT EXISTS alumni (
    alumni_id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    middle_initial CHAR(1),
    last_name VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_graduated YEAR NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    job_title VARCHAR(100),
    company_name VARCHAR(100),
    company_address TEXT,
    work_position VARCHAR(100),
    is_course_related ENUM('Yes', 'No'),
    employment_status ENUM('Full-time', 'Part-time', 'Self-employed', 'Unemployed') NOT NULL,
    date_started DATE,
    is_current_job ENUM('Yes', 'No'),
    date_ended DATE,
    document_type ENUM('Alumni ID', 'Student ID', 'Government ID', 'Other') NOT NULL,
    document_upload VARCHAR(255),
    additional_info TEXT,
    signature_data TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    date_signed DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    salary DECIMAL(10,2),
    industry VARCHAR(100)
);

-- Create admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT IGNORE INTO admin (username, password) VALUES 
('admin', '$2a$12$5nzFc1ohdCCAf9Mozr.BHeFEizZGv5/NaDJDOufcy3pZP8waQkYt2'); -- password: admin

-- Create employment_history table
CREATE TABLE IF NOT EXISTS employment_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumni_id INT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    company_address TEXT NOT NULL,
    work_position VARCHAR(100) NOT NULL,
    is_course_related ENUM('Yes', 'No') NOT NULL,
    employment_status ENUM('Full-time', 'Part-time', 'Self-employed', 'Unemployed') NOT NULL,
    date_started DATE NOT NULL,
    is_current_job ENUM('Yes', 'No') NOT NULL,
    date_ended DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
);

-- Create skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create alumni_skills table
CREATE TABLE IF NOT EXISTS alumni_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumni_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('Beginner', 'Intermediate', 'Advanced', 'Expert') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
);

-- Create certifications table
CREATE TABLE IF NOT EXISTS certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumni_id INT NOT NULL,
    certification_name VARCHAR(255) NOT NULL,
    issuing_organization VARCHAR(255) NOT NULL,
    date_issued DATE NOT NULL,
    date_expired DATE,
    certification_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
);

-- Create alumni_activities table
CREATE TABLE IF NOT EXISTS alumni_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_name VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create alumni_activity_participants table
CREATE TABLE IF NOT EXISTS alumni_activity_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_id INT NOT NULL,
    alumni_id INT NOT NULL,
    status ENUM('Registered', 'Attended', 'Cancelled') NOT NULL DEFAULT 'Registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES alumni_activities(id) ON DELETE CASCADE,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
);

-- Create alumni_feedback table
CREATE TABLE IF NOT EXISTS alumni_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumni_id INT NOT NULL,
    feedback_type ENUM('General', 'Course', 'Employment', 'Event') NOT NULL,
    feedback_text TEXT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
);

-- Create alumni_documents table
CREATE TABLE IF NOT EXISTS alumni_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumni_id INT NOT NULL,
    document_type ENUM('Transcript', 'Diploma', 'Certificate', 'Other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES alumni(alumni_id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_alumni_email ON alumni(email);
CREATE INDEX idx_alumni_course ON alumni(course);
CREATE INDEX idx_alumni_year ON alumni(year_graduated);
CREATE INDEX idx_employment_alumni ON employment_history(alumni_id);
CREATE INDEX idx_skills_alumni ON alumni_skills(alumni_id);
CREATE INDEX idx_certifications_alumni ON certifications(alumni_id);
CREATE INDEX idx_activities_date ON alumni_activities(date);
CREATE INDEX idx_participants_activity ON alumni_activity_participants(activity_id);
CREATE INDEX idx_feedback_alumni ON alumni_feedback(alumni_id);
CREATE INDEX idx_documents_alumni ON alumni_documents(alumni_id);

-- Insert default skills
INSERT IGNORE INTO skills (skill_name) VALUES 
('Programming'),
('Database Management'),
('Web Development'),
('Project Management'),
('Communication'),
('Leadership'),
('Problem Solving'),
('Data Analysis'),
('Network Administration'),
('Cybersecurity'); 