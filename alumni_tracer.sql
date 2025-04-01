-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2025 at 08:10 AM
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
(1, 'admin', '$2a$12$eRB3mbTsh5Ic0vsscvvt4ef9jek5TLcySRzq/..jdmOzCfWYpwVmq', '2025-04-01 09:52:36');

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
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
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
(1, 'Bachelor of Science in Information Technology', '2025-04-01 09:52:36'),
(2, 'Bachelor of Science in Computer Science', '2025-04-01 09:52:36'),
(5, 'Bachelor of Science in Business Administration', '2025-04-01 14:09:07'),
(6, 'Bachelor of Science in Accountancy', '2025-04-01 14:09:07'),
(7, 'Bachelor of Science in Engineering', '2025-04-01 14:09:07');

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
(1, 'Programming', '2025-04-01 09:52:36'),
(2, 'Database Management', '2025-04-01 09:52:36'),
(3, 'Web Development', '2025-04-01 09:52:36'),
(4, 'Project Management', '2025-04-01 09:52:36'),
(5, 'Communication', '2025-04-01 09:52:36'),
(6, 'Leadership', '2025-04-01 09:52:36'),
(7, 'Problem Solving', '2025-04-01 09:52:36'),
(8, 'Data Analysis', '2025-04-01 09:52:36'),
(9, 'Network Administration', '2025-04-01 09:52:36'),
(10, 'Cybersecurity', '2025-04-01 09:52:36');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `alumni_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

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
