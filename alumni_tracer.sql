-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 02:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alumni_tracer`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2a$12$5nzFc1ohdCCAf9Mozr.BHeFEizZGv5/NaDJDOufcy3pZP8waQkYt2', '2025-04-01 06:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `middle_initial` char(1) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_graduated` year(4) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `work_position` varchar(100) DEFAULT NULL,
  `is_course_related` enum('Yes','No') DEFAULT NULL,
  `employment_status` enum('Full-time','Part-time','Self-employed','Unemployed') NOT NULL,
  `date_started` date DEFAULT NULL,
  `is_current_job` enum('Yes','No') DEFAULT NULL,
  `date_ended` date DEFAULT NULL,
  `document_type` enum('Alumni ID','Student ID','Government ID','Other') NOT NULL,
  `document_upload` varchar(255) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `signature_data` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_signed` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `salary` decimal(10,2) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni`
--

INSERT INTO `alumni` (`alumni_id`, `student_number`, `first_name`, `middle_name`, `middle_initial`, `last_name`, `course`, `year_graduated`, `email`, `phone`, `address`, `job_title`, `company_name`, `company_address`, `work_position`, `is_course_related`, `employment_status`, `date_started`, `is_current_job`, `date_ended`, `document_type`, `document_upload`, `additional_info`, `signature_data`, `password`, `date_signed`, `created_at`, `updated_at`, `salary`, `industry`) VALUES
(1, '204-0524', 'Angelyn', 'Quisel', 'Q', 'De La Rosa', 'Bachelor of Science in Information Technology', '2025', 'jomarirebadulla@gmail.com', '09943794277', 'Metro Manila, Sampaloc Manila', '', '', '', '', '', 'Unemployed', '0000-00-00', '', '0000-00-00', 'Government ID', 'uploads/204-0524.jpg', '', 'data:image/png;base64,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', '$2a$12$5nzFc1ohdCCAf9Mozr.BHeFEizZGv5/NaDJDOufcy3pZP8waQkYt2', '2025-04-01 00:52:39', '2025-03-31 22:52:40', '2025-03-31 23:21:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `alumni_activities`
--

CREATE TABLE `alumni_activities` (
  `id` int(11) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_activity_participants`
--

CREATE TABLE `alumni_activity_participants` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `status` enum('Registered','Attended','Cancelled') NOT NULL DEFAULT 'Registered',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_documents`
--

CREATE TABLE `alumni_documents` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `document_type` enum('Transcript','Diploma','Certificate','Other') NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_feedback`
--

CREATE TABLE `alumni_feedback` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `feedback_type` enum('General','Course','Employment','Event') NOT NULL,
  `feedback_text` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alumni_skills`
--

CREATE TABLE `alumni_skills` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `certification_name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) NOT NULL,
  `date_issued` date NOT NULL,
  `date_expired` date DEFAULT NULL,
  `certification_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `created_at`) VALUES
(1, 'Bachelor of Science in Information Technology', '2025-04-01 06:15:17'),
(2, 'Bachelor of Science in Computer Science', '2025-04-01 06:15:17'),
(3, 'Bachelor of Science in Business Administration', '2025-04-01 06:15:17'),
(4, 'Bachelor of Science in Accountancy', '2025-04-01 06:15:17'),
(5, 'Bachelor of Science in Engineering', '2025-04-01 06:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `employment_history`
--

CREATE TABLE `employment_history` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `company_address` text NOT NULL,
  `work_position` varchar(100) NOT NULL,
  `is_course_related` enum('Yes','No') NOT NULL,
  `employment_status` enum('Full-time','Part-time','Self-employed','Unemployed') NOT NULL,
  `date_started` date NOT NULL,
  `is_current_job` enum('Yes','No') NOT NULL,
  `date_ended` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `skill_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `skill_name`, `created_at`) VALUES
(1, 'Programming', '2025-04-01 06:15:18'),
(2, 'Database Management', '2025-04-01 06:15:18'),
(3, 'Web Development', '2025-04-01 06:15:18'),
(4, 'Project Management', '2025-04-01 06:15:18'),
(5, 'Communication', '2025-04-01 06:15:18'),
(6, 'Leadership', '2025-04-01 06:15:18'),
(7, 'Problem Solving', '2025-04-01 06:15:18'),
(8, 'Data Analysis', '2025-04-01 06:15:18'),
(9, 'Network Administration', '2025-04-01 06:15:18'),
(10, 'Cybersecurity', '2025-04-01 06:15:18');

-- --------------------------------------------------------

--
-- Table structure for table `work_history`
--

CREATE TABLE `work_history` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_address` text NOT NULL,
  `work_position` varchar(100) NOT NULL,
  `is_course_related` enum('Yes','No') NOT NULL,
  `employment_status` varchar(50) NOT NULL,
  `date_started` date NOT NULL,
  `is_current_job` enum('Yes','No') NOT NULL,
  `date_ended` date DEFAULT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_alumni_email` (`email`),
  ADD KEY `idx_alumni_course` (`course`),
  ADD KEY `idx_alumni_year` (`year_graduated`);

--
-- Indexes for table `alumni_activities`
--
ALTER TABLE `alumni_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activities_date` (`date`);

--
-- Indexes for table `alumni_activity_participants`
--
ALTER TABLE `alumni_activity_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_id` (`alumni_id`),
  ADD KEY `idx_participants_activity` (`activity_id`);

--
-- Indexes for table `alumni_documents`
--
ALTER TABLE `alumni_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documents_alumni` (`alumni_id`);

--
-- Indexes for table `alumni_feedback`
--
ALTER TABLE `alumni_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_feedback_alumni` (`alumni_id`);

--
-- Indexes for table `alumni_skills`
--
ALTER TABLE `alumni_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `skill_id` (`skill_id`),
  ADD KEY `idx_skills_alumni` (`alumni_id`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_certifications_alumni` (`alumni_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_name` (`course_name`);

--
-- Indexes for table `employment_history`
--
ALTER TABLE `employment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employment_alumni` (`alumni_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skill_name` (`skill_name`);

--
-- Indexes for table `work_history`
--
ALTER TABLE `work_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_id` (`alumni_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `alumni_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alumni_activities`
--
ALTER TABLE `alumni_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_activity_participants`
--
ALTER TABLE `alumni_activity_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_documents`
--
ALTER TABLE `alumni_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_feedback`
--
ALTER TABLE `alumni_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alumni_skills`
--
ALTER TABLE `alumni_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=486;

--
-- AUTO_INCREMENT for table `employment_history`
--
ALTER TABLE `employment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `work_history`
--
ALTER TABLE `work_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alumni_activity_participants`
--
ALTER TABLE `alumni_activity_participants`
  ADD CONSTRAINT `alumni_activity_participants_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `alumni_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_activity_participants_ibfk_2` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_documents`
--
ALTER TABLE `alumni_documents`
  ADD CONSTRAINT `alumni_documents_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_feedback`
--
ALTER TABLE `alumni_feedback`
  ADD CONSTRAINT `alumni_feedback_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `alumni_skills`
--
ALTER TABLE `alumni_skills`
  ADD CONSTRAINT `alumni_skills_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alumni_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certifications`
--
ALTER TABLE `certifications`
  ADD CONSTRAINT `certifications_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `employment_history`
--
ALTER TABLE `employment_history`
  ADD CONSTRAINT `employment_history_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;

--
-- Constraints for table `work_history`
--
ALTER TABLE `work_history`
  ADD CONSTRAINT `work_history_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
