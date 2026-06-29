-- Database schema for Custom Subscription-based Dog Directory Platform

CREATE TABLE IF NOT EXISTS `wp_dog_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `features` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_dog_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wp_user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'visitor',
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_dog_dogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(191) NOT NULL,
  `breed` varchar(191) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `description` text NOT NULL,
  `front_image` varchar(255) NOT NULL,
  `side_image` varchar(255) NOT NULL,
  `gallery` text DEFAULT NULL,
  `color` varchar(100) DEFAULT '',
  `weight` varchar(100) DEFAULT '',
  `registration_number` varchar(100) DEFAULT '',
  `awards` text DEFAULT NULL,
  `health_info` text DEFAULT NULL,
  `pedigree` text DEFAULT NULL,
  `kennel` varchar(191) DEFAULT '',
  `country` varchar(100) DEFAULT 'United States',
  `city` varchar(100) DEFAULT '',
  `phone` varchar(100) DEFAULT '',
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_dog_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `transaction_id` varchar(191) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TRUNCATE / Initialize Plans
TRUNCATE TABLE `wp_dog_plans`;
INSERT INTO `wp_dog_plans` (`id`, `name`, `price`, `duration`, `features`) VALUES
(1, 'Gold Membership', 12.00, 'Monthly', 'Add up to 10 Dogs,2 Featured Listings,Unlimited Browsing,Full Profile Management'),
(2, 'Platinum Membership', 99.00, 'Yearly', 'Add up to 50 Dogs,10 Featured Listings,Unlimited Browsing,Full Profile Management');

-- Populate Dummy Users
INSERT IGNORE INTO `wp_dog_users` (`id`, `wp_user_id`, `name`, `email`, `role`, `subscription_id`, `created_at`) VALUES
(1, 1, 'Admin User', 'admin@example.com', 'admin', NULL, NOW()),
(2, NULL, 'Jane Doe', 'jane@example.com', 'subscriber', 1, NOW() - INTERVAL 10 DAY),
(3, NULL, 'John Smith', 'john@example.com', 'subscriber', 2, NOW() - INTERVAL 30 DAY),
(4, NULL, 'Emily Davis', 'emily@example.com', 'subscriber', 1, NOW() - INTERVAL 5 DAY),
(5, NULL, 'Bobby Brown', 'bobby@example.com', 'visitor', NULL, NOW() - INTERVAL 1 DAY);

-- Populate Dummy Payments
TRUNCATE TABLE `wp_dog_payments`;
INSERT INTO `wp_dog_payments` (`id`, `user_id`, `amount`, `payment_method`, `status`, `transaction_id`, `created_at`) VALUES
(1, 2, 12.00, 'Stripe CC', 'completed', 'txn_stripe_mock_12345a', NOW() - INTERVAL 10 DAY),
(2, 3, 99.00, 'Stripe CC', 'completed', 'txn_stripe_mock_12345b', NOW() - INTERVAL 30 DAY),
(3, 4, 12.00, 'Stripe CC', 'completed', 'txn_stripe_mock_12345c', NOW() - INTERVAL 5 DAY);

-- Populate Dummy Dogs
TRUNCATE TABLE `wp_dog_dogs`;
INSERT INTO `wp_dog_dogs` (`id`, `user_id`, `name`, `breed`, `gender`, `dob`, `age`, `description`, `front_image`, `side_image`, `gallery`, `color`, `weight`, `registration_number`, `awards`, `health_info`, `pedigree`, `kennel`, `country`, `city`, `phone`, `views`, `created_at`) VALUES
(1, 2, 'Max', 'Golden Retriever', 'Male', '2023-04-12', 3, 'Max is a highly energetic Golden Retriever who loves swimming and playing fetch. He has excellent retrieval instinct.', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-1.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-2.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-3.webp', 'Golden', '72 lbs', 'AKC-987654', 'Best of Breed (2025 California Dog Show)', 'OFA Hips: Excellent, Eyes: Clear', 'Sire: Gold Rush Champion, Dam: Sweet Honey Bee', 'Sunshine Kennels', 'United States', 'Los Angeles', '310-555-0192', 154, NOW() - INTERVAL 10 DAY),
(2, 3, 'Bella', 'German Shepherd', 'Female', '2024-02-18', 2, 'Bella is an intelligent and protective German Shepherd, currently training in agility. She is quick to learn new commands.', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-4.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-5.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-6.webp', 'Black & Tan', '65 lbs', 'AKC-123456', 'Agility Novice Title (2025 Texas Agility Cup)', 'Elbows: Normal, DNA: Clear', 'Sire: Von Bismarck King, Dam: Von Kaiser Queen', 'Vanguard Shepherds', 'United States', 'Houston', '713-555-0143', 210, NOW() - INTERVAL 30 DAY),
(3, 3, 'Charlie', 'Poodle', 'Male', '2022-09-05', 4, 'Charlie is a Standard Poodle with an elegant coat and friendly demeanor. He is great with kids and other pets.', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-7.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-8.webp', '', 'White', '58 lbs', 'AKC-246810', 'Grand Champion (2024 Florida Royal Poodle Show)', 'Cardiac: Normal, Thyroid: Normal', 'Sire: Starry Night Prince, Dam: White Velvet Queen', 'Majestic Poodles', 'United States', 'Miami', '305-555-0177', 98, NOW() - INTERVAL 25 DAY),
(4, 4, 'Lucy', 'French Bulldog', 'Female', '2025-06-01', 1, 'Lucy is a playful French Bulldog puppy. She loves cuddling and taking short walks in the park.', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-9.webp', 'http://localhost:8000/wp-content/uploads/2023/08/post-img-10.webp', '', 'Fawn', '22 lbs', 'AKC-135791', 'Best Puppy (2025 East Coast Bulldog Meet)', 'Patella: Normal', 'Sire: Frenchie Pierre, Dam: Frenchie Chloe', 'Royal Frenchies', 'United States', 'New York', '212-555-0188', 342, NOW() - INTERVAL 5 DAY);
