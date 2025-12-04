-- ClubWeave sample dataset
-- Generated: 2025-12-04
-- Contains: users (4 admins + 4 members), admin_profiles, member_profiles, clubs (12), events (48)
-- Upload folders referenced: ../uploads/club_images/ and ../uploads/event_images/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==================================================================
-- TABLES (CREATE IF NOT EXISTS) -- Minimal compatible schema (assumes InnoDB)
-- ==================================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','clubadmin','member') DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admin_profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `department` VARCHAR(120) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `member_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clubs` (
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL,
  `club_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `date` date NOT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (Other tables such as club_members, event_registrations, memberships, role_requests, user_profiles
-- may already exist in your project; this dump focuses on users, profiles, clubs and events.)

-- ==================================================================
-- USERS: 4 admins + 4 members (bcrypt hashed passwords)
-- ==================================================================

INSERT INTO `users` (`id`,`name`,`email`,`password`,`role`,`created_at`) VALUES
(1,'admin1','admin1@example.com','$2b$12$cLjol1qFjFNiD4dNKYgW1ewCO87BxWcxySajQAPlHG4gvZI6gxpNq','clubadmin','2025-11-24 10:00:00'),
(2,'admin2','admin2@example.com','$2b$12$8WKYWN2yHgA7hRtId4.kSupm13KLH9RrncTPG8VpaCDjQvzqEz55K','clubadmin','2025-11-24 10:01:00'),
(3,'admin3','admin3@example.com','$2b$12$CnZd0WrUJmzIU3YHQ9lWne3wr5iAMd3blQMDN4lt4hNBf/hPxF7Yy','clubadmin','2025-11-24 10:02:00'),
(4,'admin4','admin4@example.com','$2b$12$Esmf1twzwjFLOiGTs24K5OJIGiFqly1SeNnFMi9H1zUun8xlqKKJG','clubadmin','2025-11-24 10:03:00'),
(5,'member1','member1@example.com','$2b$12$uNIBw6v/A/Z9hbdLBMvWleRgqIzjVtMn9v6L3nzhR1wwCGHGVNq7W','member','2025-11-24 10:10:00'),
(6,'member2','member2@example.com','$2b$12$VfEs1lEfyXDZ8ZYsjbw45uujFEti46b3VoHZ63.Vxj7VZ1PiDcSYu','member','2025-11-24 10:11:00'),
(7,'member3','member3@example.com','$2b$12$PCB3X2Hh3olSbuOU8ZXqe.vBVzx7Izh102nIlTCqdJsH/uZ0J8FAO','member','2025-11-24 10:12:00'),
(8,'member4','member4@example.com','$2b$12$r6ELAXuRdLtHD3qSNX3f3utyBayElU9s5Ji8XT4OR2FuOHxBtZS2.','member','2025-11-24 10:13:00');

-- ==================================================================
-- ADMIN PROFILES (for admins 1..4)
-- ==================================================================

INSERT INTO `admin_profiles` (`user_id`,`full_name`,`department`,`phone`,`profile_picture`) VALUES
(1,'Alice Park','Student Affairs','+82-10-1111-0001','admin_1.jpg'),
(2,'Brian Kim','Community Outreach','+82-10-1111-0002','admin_2.jpg'),
(3,'Choi Minho','Events & Programs','+82-10-1111-0003','admin_3.jpg'),
(4,'Dawon Lee','Clubs Coordination','+82-10-1111-0004','admin_4.jpg');

-- ==================================================================
-- MEMBER PROFILES (for members 5..8)
-- ==================================================================

INSERT INTO `member_profiles` (`user_id`,`full_name`,`department`,`year_of_study`,`phone`,`dob`,`address`,`linkedin`,`instagram`,`skills`,`bio`,`profile_picture`) VALUES
(5,'Nurbek Makhmadaminov','Information & Computing','2nd','+82-10-2222-0001','2003-11-06','Busan','https://linkedin.com/in/nurbek','@nurbek','C, C++, SQL','Enthusiastic about systems programming and ML.','member_1.jpg'),
(6,'Sora Kim','Computer Science','3rd','+82-10-2222-0002','2002-04-18','Seoul','https://linkedin.com/in/sora','@sora','JavaScript, React','Frontend developer and UI/UX enthusiast.','member_2.jpg'),
(7,'Jinwoo Park','Design','1st','+82-10-2222-0003','2004-07-09','Daegu','https://linkedin.com/in/jinwoo','@jinwoo','Graphic Design, Figma','Visual designer focusing on brand identity.','member_3.jpg'),
(8,'Mina Lee','Business','4th','+82-10-2222-0004','2001-01-22','Incheon','https://linkedin.com/in/mina','@mina','Marketing, Events','Organizes campus events and partnerships.','member_4.jpg');

-- ==================================================================
-- CLUBS: 12 clubs (3 per admin)
-- Each club's created_by points to its admin (1..4)
-- ==================================================================

INSERT INTO `clubs` (`id`,`name`,`description`,`category`,`location`,`contact_email`,`contact_phone`,`logo`,`founded_year`,`created_by`,`created_at`) VALUES
(1,'Tech Innovators Club','Exploring emerging technologies, hackathons and projects.','Technology','Building A, Room 101','techinnovators@univ.edu','010-9000-0001','../uploads/club_images/tech_innovators.jpg','2020',1,'2025-11-20 09:00:00'),
(2,'AI & ML Society','Workshops and study groups for machine learning topics.','Technology','Building A, Room 102','aiml@univ.edu','010-9000-0002','../uploads/club_images/ai_ml.jpg','2019',1,'2025-11-20 09:10:00'),
(3,'Robotics Lab Club','Hands-on robotics projects, competitions, and mentoring.','Technology','Engineering Lab 3','robotics@univ.edu','010-9000-0003','../uploads/club_images/robotics.jpg','2018',1,'2025-11-20 09:20:00'),

(4,'Cultural Harmony Club','Promoting cultural exchange and arts on campus.','Culture','Building B, Room 202','cultural@univ.edu','010-9000-0101','../uploads/club_images/cultural.jpg','2017',2,'2025-11-21 10:00:00'),
(5,'Language Exchange Group','Weekly language practice meetups and events.','Culture','International House','langexchange@univ.edu','010-9000-0102','../uploads/club_images/language.jpg','2016',2,'2025-11-21 10:10:00'),
(6,'Arts & Crafts Society','Workshops, galleries and collaborative art projects.','Arts','Art Studio 1','arts@univ.edu','010-9000-0103','../uploads/club_images/arts.jpg','2015',2,'2025-11-21 10:20:00'),

(7,'Sports United Club','Organizes intramural and inter-department sports.','Sports','Sports Complex Room 5','sports@univ.edu','010-9000-0201','../uploads/club_images/sports.jpg','2018',3,'2025-11-22 11:00:00'),
(8,'Fitness & Wellness','Fitness workshops, wellness talks, and classes.','Sports','Gym Room 2','wellness@univ.edu','010-9000-0202','../uploads/club_images/wellness.jpg','2019',3,'2025-11-22 11:10:00'),
(9,'Outdoor Adventure Club','Hikes, camping trips and outdoor skills training.','Sports','Recreation Office','outdoor@univ.edu','010-9000-0203','../uploads/club_images/outdoor.jpg','2014',3,'2025-11-22 11:20:00'),

(10,'Entrepreneurs Circle','Startups, business workshops and pitch nights.','Business','Business Building Room 12','entrepreneurs@univ.edu','010-9000-0301','../uploads/club_images/entrepreneurs.jpg','2016',4,'2025-11-23 12:00:00'),
(11,'Marketing & Media Club','Marketing case studies, campaigns and media projects.','Business','Media Lab','marketing@univ.edu','010-9000-0302','../uploads/club_images/marketing.jpg','2017',4,'2025-11-23 12:10:00'),
(12,'Finance & Investment Group','Workshops on finance, investing and competitions.','Business','Finance Lab','finance@univ.edu','010-9000-0303','../uploads/club_images/finance.jpg','2015',4,'2025-11-23 12:20:00');

-- Set auto_increment for clubs
ALTER TABLE `clubs` AUTO_INCREMENT = 13;

-- ==================================================================
-- EVENTS: 4 events per club (48 events)
-- Dates chosen across 2026 for variety; adjust as needed
-- event_image uses ../uploads/event_images/ or default
-- ==================================================================

INSERT INTO `events` (`id`,`club_id`,`created_by`,`title`,`description`,`date`,`event_image`,`event_time`,`venue`,`registration_deadline`,`max_participants`,`created_at`) VALUES
-- Club 1 (Tech Innovators Club)
(1,1,1,'Tech Expo 2026','Showcase of latest student tech projects and demos.','2026-02-10','../uploads/event_images/tech_expo_2026.jpg','14:00:00','Hall A','2026-02-05',200,'2025-11-20 09:30:00'),
(2,1,1,'Hackathon Spring 2026','48-hour hackathon focusing on sustainability projects.','2026-03-18','../uploads/event_images/hackathon_spring.jpg','09:00:00','Lab 2','2026-03-10',300,'2025-11-20 09:32:00'),
(3,1,1,'IoT Workshop','Hands-on IoT workshop: sensors and microcontrollers.','2026-04-12','../uploads/event_images/iot_workshop.jpg','10:00:00','Lab 3','2026-04-05',50,'2025-11-20 09:34:00'),
(4,1,1,'Student Project Fair','Present semester-long team projects to industry mentors.','2026-05-05','../uploads/event_images/project_fair.jpg','13:00:00','Hall B','2026-04-28',150,'2025-11-20 09:36:00'),

-- Club 2 (AI & ML Society)
(5,2,1,'Intro to Neural Networks','Beginner-friendly session on neural network basics.','2026-01-25','../uploads/event_images/nn_intro.jpg','11:00:00','Lab 1','2026-01-20',60,'2025-11-20 09:40:00'),
(6,2,1,'AI Ethics Panel','Panel discussion on ethics in AI and data privacy.','2026-03-01','../uploads/event_images/ai_ethics.jpg','15:00:00','Auditorium','2026-02-22',200,'2025-11-20 09:42:00'),
(7,2,1,'Hands-on TensorFlow','Practical TensorFlow workshop for models.','2026-04-02','../uploads/event_images/tensorflow_workshop.jpg','10:00:00','Lab 1','2026-03-25',40,'2025-11-20 09:44:00'),
(8,2,1,'ML Study Jam','Weekly study meetup for ML coursework.','2026-05-10','../uploads/event_images/ml_study.jpg','18:00:00','Study Room','2026-05-05',30,'2025-11-20 09:46:00'),

-- Club 3 (Robotics Lab Club)
(9,3,1,'Robotics 101','Intro to robotics: motors, controllers, and basics.','2026-02-05','../uploads/event_images/robotics101.jpg','09:30:00','Engineering Lab','2026-01-28',40,'2025-11-20 09:50:00'),
(10,3,1,'Autonomous Vehicles Night','Demo of student-built autonomous robots.','2026-03-15','../uploads/event_images/auto_vehicles.jpg','17:00:00','Lab 5','2026-03-01',150,'2025-11-20 09:52:00'),
(11,3,1,'Robot Soccer Tournament','Inter-department robot soccer competition.','2026-04-20','../uploads/event_images/robot_soccer.jpg','13:00:00','Gym','2026-04-10',12,'2025-11-20 09:54:00'),
(12,3,1,'Sensor Fusion Workshop','Integrating sensors for robust robotics systems.','2026-05-18','../uploads/event_images/sensor_fusion.jpg','10:00:00','Lab 3','2026-05-10',45,'2025-11-20 09:56:00'),

-- Club 4 (Cultural Harmony Club)
(13,4,2,'Cultural Festival 2026','A day celebrating music, food and performances.','2026-02-15','../uploads/event_images/cultural_fest.jpg','12:00:00','Main Grounds','2026-02-10',500,'2025-11-21 10:30:00'),
(14,4,2,'Traditional Dance Workshop','Learn traditional folk dances.','2026-03-05','../uploads/event_images/dance_workshop.jpg','14:00:00','Studio 2','2026-02-28',80,'2025-11-21 10:32:00'),
(15,4,2,'Cultural Talk Series','Guest speakers on cultural exchange.','2026-04-08','../uploads/event_images/cultural_talk.jpg','16:00:00','Auditorium','2026-04-01',120,'2025-11-21 10:34:00'),
(16,4,2,'International Food Fair','Sample foods from around the world.','2026-05-12','../uploads/event_images/food_fair.jpg','11:00:00','Cafeteria','2026-05-05',300,'2025-11-21 10:36:00'),

-- Club 5 (Language Exchange Group)
(17,5,2,'Spanish Conversation Hour','Casual Spanish conversation practice.','2026-02-18','../uploads/event_images/spanish_hour.jpg','18:00:00','Intl House','2026-02-10',30,'2025-11-21 10:40:00'),
(18,5,2,'Japanese Reading Club','Group reading and discussion in Japanese.','2026-03-22','../uploads/event_images/japanese_read.jpg','17:00:00','Library Room 4','2026-03-15',25,'2025-11-21 10:42:00'),
(19,5,2,'Language Tandem Meetup','Pair up to practice languages with peers.','2026-04-27','../uploads/event_images/tandem.jpg','19:00:00','Cafe Lounge','2026-04-20',40,'2025-11-21 10:44:00'),
(20,5,2,'French Workshop','Basics of conversational French.','2026-05-30','../uploads/event_images/french_workshop.jpg','10:00:00','Room 201','2026-05-22',30,'2025-11-21 10:46:00'),

-- Club 6 (Arts & Crafts Society)
(21,6,2,'Watercolor Workshop','Beginner watercolor techniques.','2026-02-12','../uploads/event_images/watercolor.jpg','14:00:00','Art Studio','2026-02-05',25,'2025-11-21 10:50:00'),
(22,6,2,'Student Gallery Night','Exhibition of student artworks.','2026-03-18','../uploads/event_images/gallery_night.jpg','18:00:00','Gallery 1','2026-03-10',200,'2025-11-21 10:52:00'),
(23,6,2,'Crafts for Charity','Make crafts to donate to local causes.','2026-04-16','../uploads/event_images/crafts_charity.jpg','11:00:00','Studio 3','2026-04-08',50,'2025-11-21 10:54:00'),
(24,6,2,'Printmaking Workshop','Intro to printmaking techniques.','2026-05-14','../uploads/event_images/printmaking.jpg','13:00:00','Print Lab','2026-05-07',30,'2025-11-21 10:56:00'),

-- Club 7 (Sports United Club)
(25,7,3,'Inter-department Football','Football tournament with departments competing.','2026-02-20','../uploads/event_images/football_tourn.jpg','15:00:00','Sports Field','2026-02-15',120,'2025-11-22 11:30:00'),
(26,7,3,'Basketball Night','Pickup basketball matches and skills clinic.','2026-03-12','../uploads/event_images/basketball.jpg','19:00:00','Gym','2026-03-05',80,'2025-11-22 11:32:00'),
(27,7,3,'Fitness Bootcamp','Outdoor bootcamp sessions for fitness.','2026-04-09','../uploads/event_images/bootcamp.jpg','08:00:00','Outdoor Field','2026-04-01',60,'2025-11-22 11:34:00'),
(28,7,3,'Badminton Day','Friendly badminton tournament.','2026-05-10','../uploads/event_images/badminton.jpg','09:00:00','Sports Hall','2026-05-03',60,'2025-11-22 11:36:00'),

-- Club 8 (Fitness & Wellness)
(29,8,3,'Yoga Morning','Sunrise yoga and mindfulness.','2026-02-28','../uploads/event_images/yoga.jpg','07:30:00','Gym Studio','2026-02-20',40,'2025-11-22 11:40:00'),
(30,8,3,'Nutrition Talk','Healthy eating for students.','2026-03-25','../uploads/event_images/nutrition.jpg','16:00:00','Lecture Hall','2026-03-18',120,'2025-11-22 11:42:00'),
(31,8,3,'Stress Management Workshop','Tools for mental well-being during exams.','2026-04-30','../uploads/event_images/stress_mgmt.jpg','14:00:00','Counseling Room','2026-04-22',50,'2025-11-22 11:44:00'),
(32,8,3,'Run Club Meetup','Group run around campus trails.','2026-05-20','../uploads/event_images/run_club.jpg','06:30:00','Campus Gate','2026-05-15',100,'2025-11-22 11:46:00'),

-- Club 9 (Outdoor Adventure Club)
(33,9,3,'Beginner Hike','Weekend hike to nearby trails.','2026-02-14','../uploads/event_images/beginner_hike.jpg','08:00:00','Main Gate','2026-02-07',40,'2025-11-22 11:50:00'),
(34,9,3,'Camping Essentials','Learn safe camping and knot skills.','2026-03-28','../uploads/event_images/camping.jpg','09:00:00','Outdoor Center','2026-03-20',30,'2025-11-22 11:52:00'),
(35,9,3,'Rock Climbing Intro','Intro to indoor rock climbing techniques.','2026-04-18','../uploads/event_images/climbing.jpg','13:00:00','Climbing Wall','2026-04-10',20,'2025-11-22 11:54:00'),
(36,9,3,'Navigation Workshop','Map & compass navigation basics.','2026-05-16','../uploads/event_images/navigation.jpg','10:00:00','Outdoors Lab','2026-05-08',25,'2025-11-22 11:56:00'),

-- Club 10 (Entrepreneurs Circle)
(37,10,4,'Startup Weekend','Pitch, build and present startups in 48 hours.','2026-02-26','../uploads/event_images/startup_weekend.jpg','09:00:00','Innovation Hub','2026-02-18',200,'2025-11-23 12:30:00'),
(38,10,4,'Business Model Workshop','Design your business model canvas.','2026-03-12','../uploads/event_images/bmc.jpg','14:00:00','Business Lab','2026-03-05',60,'2025-11-23 12:32:00'),
(39,10,4,'Investor Pitch Night','Student startups pitch to mentors.','2026-04-22','../uploads/event_images/pitch_night.jpg','18:00:00','Auditorium','2026-04-15',150,'2025-11-23 12:34:00'),
(40,10,4,'Lean Startup Seminar','Validated learning and rapid experiments.','2026-05-28','../uploads/event_images/lean_startup.jpg','10:00:00','Lecture Hall','2026-05-20',100,'2025-11-23 12:36:00'),

-- Club 11 (Marketing & Media Club)
(41,11,4,'Branding 101','Intro to brand strategy and identity.','2026-02-08','../uploads/event_images/branding.jpg','13:00:00','Media Lab','2026-02-01',40,'2025-11-23 12:40:00'),
(42,11,4,'Social Media Campaigns','Plan and run effective campaigns.','2026-03-19','../uploads/event_images/social_campaign.jpg','15:00:00','Media Lab','2026-03-10',60,'2025-11-23 12:42:00'),
(43,11,4,'Content Creation Workshop','Video and podcast creation basics.','2026-04-25','../uploads/event_images/content_creation.jpg','11:00:00','Studio','2026-04-18',30,'2025-11-23 12:44:00'),
(44,11,4,'Analytics for Marketers','Understand data and campaign metrics.','2026-05-09','../uploads/event_images/analytics.jpg','14:00:00','Computer Lab','2026-05-02',50,'2025-11-23 12:46:00'),

-- Club 12 (Finance & Investment Group)
(45,12,4,'Introduction to Investing','Basics of stocks, bonds and ETFs.','2026-02-05','../uploads/event_images/investing.jpg','16:00:00','Finance Lab','2026-01-28',80,'2025-11-23 12:50:00'),
(46,12,4,'Personal Finance 101','Budgeting and saving for students.','2026-03-02','../uploads/event_images/personal_finance.jpg','17:00:00','Lecture Hall','2026-02-22',120,'2025-11-23 12:52:00'),
(47,12,4,'Investment Case Study','Analyze real world investment cases.','2026-04-12','../uploads/event_images/case_study.jpg','13:00:00','Seminar Room','2026-04-05',40,'2025-11-23 12:54:00'),
(48,12,4,'Stocks Simulation Contest','Compete in a simulated trading contest.','2026-05-18','../uploads/event_images/trading_sim.jpg','10:00:00','Finance Lab','2026-05-10',80,'2025-11-23 12:56:00');

-- Set auto_increment for events
ALTER TABLE `events` AUTO_INCREMENT = 49;

COMMIT;
