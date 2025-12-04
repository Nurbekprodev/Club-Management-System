-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 05:20 PM
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
-- Database: `club-management`
--

-- --------------------------------------------------------

--
-- Table structure for table `clubs`
--

CREATE TABLE `clubs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `founded_year` year(4) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clubs`
--

INSERT INTO `clubs` (`id`, `name`, `description`, `category`, `location`, `contact_email`, `contact_phone`, `logo`, `founded_year`, `created_by`, `created_at`, `updated_at`) VALUES
(33, 'Tech Innovators Club', 'A club focused on emerging technologies and innovation.', 'Technology', 'Building A, Room 101', 'techinnovators@example.com', '010-1111-1111', '../uploads/club_logos/bdba473d7c9c046a.jpg', '2020', 55, '2025-11-24 15:08:14', '2025-12-02 00:20:18'),
(34, 'Cultural Harmony Club', 'Promoting cultural exchange and harmony.', 'Culture', 'Building B, Room 202', 'culturalharmony@example.com', '010-1111-2222', '../uploads/club_images/a089bded9c2ac61c.jpg', '2019', 55, '2025-11-24 15:08:14', '2025-12-02 00:21:55'),
(35, 'Sports United Club', 'Uniting students through various sports events.', 'Sports', 'Sports Complex Room 5', 'sportsunited@example.com', '010-1111-3333', '../includes/images/default_img.jpeg', '2018', 55, '2025-11-24 15:08:14', '2025-11-25 00:08:14');

-- --------------------------------------------------------

--
-- Table structure for table `club_members`
--

CREATE TABLE `club_members` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_members`
--

INSERT INTO `club_members` (`id`, `club_id`, `user_id`, `joined_at`, `status`) VALUES
(29, 34, 54, '2025-11-24 15:25:17', 'approved'),
(30, 35, 54, '2025-11-24 15:25:18', 'approved'),
(31, 33, 54, '2025-11-24 15:25:19', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_image` varchar(255) DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `club_id`, `created_by`, `title`, `description`, `date`, `created_at`, `event_image`, `event_time`, `venue`, `registration_deadline`, `max_participants`) VALUES
(65, 33, 55, 'Tech Expo', 'Showcase of latest tech projects.', '2025-02-10', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '14:00:00', 'Hall A', '2025-02-05', 100),
(66, 33, 55, 'AI Workshop', 'Introduction to AI concepts.', '2025-03-12', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '10:00:00', 'Lab 1', '2025-03-05', 50),
(67, 33, 55, 'Innovation Meetup', 'Meet and discuss tech ideas.', '2025-04-20', '2025-11-24 15:10:58', '../uploads/event_images/3dfb41747182c0e8.jpg', '16:00:00', 'Room 101', '2025-04-10', 80),
(68, 34, 55, 'Cultural Festival', 'Celebrating global cultures.', '2025-02-15', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '12:00:00', 'Main Grounds', '2025-02-10', 200),
(69, 34, 55, 'Art Exhibition', 'Showcasing artistic works.', '2025-03-05', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '13:00:00', 'Gallery 3', '2025-02-28', 150),
(70, 34, 55, 'Language Exchange', 'Exchange languages with peers.', '2025-04-02', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '11:00:00', 'Room 202', '2025-03-25', 70),
(71, 35, 55, 'Football Tournament', 'Inter-department football matches.', '2025-02-20', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '15:00:00', 'Sports Field', '2025-02-15', 120),
(72, 35, 55, 'Badminton Day', 'Friendly badminton games.', '2025-03-10', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '09:00:00', 'Sports Hall', '2025-03-05', 60),
(73, 35, 55, 'Fitness Workshop', 'Training tips and exercises.', '2025-04-18', '2025-11-24 15:10:58', '../includes/images/default_img.jpeg', '17:00:00', 'Gym Room 2', '2025-04-10', 90);

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO `event_registrations` (`id`, `event_id`, `member_id`, `status`, `registered_at`) VALUES
(39, 68, 54, 'approved', '2025-11-24 15:37:00'),
(40, 69, 54, 'approved', '2025-11-24 15:37:03'),
(41, 70, 54, 'approved', '2025-11-24 15:37:06'),
(42, 65, 54, 'approved', '2025-11-24 16:21:55'),
(43, 66, 54, 'approved', '2025-11-24 16:21:58'),
(44, 67, 54, 'approved', '2025-11-24 16:22:00'),
(45, 71, 54, 'approved', '2025-11-24 16:22:04'),
(46, 72, 54, 'approved', '2025-11-24 16:22:06'),
(47, 73, 54, 'approved', '2025-11-24 16:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_profiles`
--

CREATE TABLE `member_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `department` varchar(120) DEFAULT NULL,
  `year_of_study` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_profiles`
--

INSERT INTO `member_profiles` (`id`, `user_id`, `full_name`, `department`, `year_of_study`, `phone`, `dob`, `address`, `linkedin`, `instagram`, `skills`, `bio`, `profile_picture`, `created_at`, `updated_at`) VALUES
(0, 54, 'Nurbek Makhmadaminov', 'IC', '2nd', '+9222222222222', '2003-11-06', 'Busan', '', '', '', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry&amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;amp;#039;s standard dummy text ever since the 1600s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries,', 'img_692dbeb2b7f3d7.81843086.jpg', '2025-12-01 11:42:48', '2025-12-01 16:13:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_requests`
--

CREATE TABLE `role_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_role` enum('clubadmin','superadmin') NOT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','clubadmin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(54, 'member1', 'member1@gmail.com', '$2y$10$qyBO0zS.s3EDHMmcpap/OuJ/ddpWazAi37K/kwbL0UCftCON3wPP2', 'member', '2025-11-24 15:02:04'),
(55, 'admin1', 'admin1@gmail.com', '$2y$10$LIlYauwV9d.Wft7yVjCd.e98UzU9e804c8.PQNFx7OkgA0Dr7BYLy', 'clubadmin', '2025-11-24 15:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT '../includes/images/default_img.jpeg',
  `bio` text DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `social_link` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clubs`
--
ALTER TABLE `clubs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_created_by` (`created_by`);

--
-- Indexes for table `club_members`
--
ALTER TABLE `club_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `fk_events_created_by` (`created_by`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_event_member` (`event_id`,`member_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `club_id` (`club_id`);

--
-- Indexes for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clubs`
--
ALTER TABLE `clubs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `club_members`
--
ALTER TABLE `club_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `role_requests`
--
ALTER TABLE `role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clubs`
--
ALTER TABLE `clubs`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_members`
--
ALTER TABLE `club_members`
  ADD CONSTRAINT `club_members_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `club_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `memberships_ibfk_2` FOREIGN KEY (`club_id`) REFERENCES `clubs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_requests`
--
ALTER TABLE `role_requests`
  ADD CONSTRAINT `role_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
