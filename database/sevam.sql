/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.18-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sevam
-- ------------------------------------------------------
-- Server version	10.11.18-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES
(1,'Sunil Joshi','sunil.joshi@mumbaicaterers.com','+91 99887 76655','Interested in joining as hotel partner','We operate a 50-room hotel with a daily banquet hall. We would like to register 3 of our kitchens under Sevam to reduce food waste.','2026-09-27 16:04:21');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_categories`
--

DROP TABLE IF EXISTS `food_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_categories`
--

LOCK TABLES `food_categories` WRITE;
/*!40000 ALTER TABLE `food_categories` DISABLE KEYS */;
INSERT INTO `food_categories` VALUES
(1,'Cooked Meals','Freshly prepared hot wholesome meals including rice, dal, and gravies','active','2026-09-27 16:04:21'),
(2,'Rice & Biryani','Surplus steamed rice, jeera rice, pulao, and vegetarian or mild biryanis','active','2026-09-27 16:04:21'),
(3,'Rotis & Breads','Surplus freshly made chapatis, rotis, parathas, and naans','active','2026-09-27 16:04:21'),
(4,'Vegetable Curries','Dry and gravy vegetable curries, paneer dishes, and mixed lentils','active','2026-09-27 16:04:21'),
(5,'Bakery & Packed Goods','Fresh breads, buns, sandwiches, and sealed packaged dry foods','active','2026-09-27 16:04:21'),
(6,'Catering & Event Surplus','High-volume banquet surplus food packed hygienically after functions','active','2026-09-27 16:04:21');
/*!40000 ALTER TABLE `food_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_listings`
--

DROP TABLE IF EXISTS `food_listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_listings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `food_name` varchar(150) NOT NULL,
  `food_type` enum('Veg','Non-Veg','Vegan','Jain') DEFAULT 'Veg',
  `quantity` decimal(10,2) NOT NULL,
  `available_quantity` decimal(10,2) NOT NULL,
  `quantity_unit` varchar(30) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `prep_date` date NOT NULL,
  `available_date` date NOT NULL,
  `available_start_time` time NOT NULL,
  `available_end_time` time NOT NULL,
  `expiry_date` date NOT NULL,
  `expiry_time` time NOT NULL,
  `food_description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `pickup_info` text NOT NULL,
  `status` enum('Available','Pending','Accepted','Completed','Expired','Cancelled','Unavailable') DEFAULT 'Available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_food_provider` (`provider_id`),
  KEY `fk_food_category` (`category_id`),
  CONSTRAINT `fk_food_category` FOREIGN KEY (`category_id`) REFERENCES `food_categories` (`id`),
  CONSTRAINT `fk_food_provider` FOREIGN KEY (`provider_id`) REFERENCES `food_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_listings`
--

LOCK TABLES `food_listings` WRITE;
/*!40000 ALTER TABLE `food_listings` DISABLE KEYS */;
INSERT INTO `food_listings` VALUES
(1,1,2,'Steamed Basmati Rice & Dal Makhani','Veg',25.00,15.00,'kg',600.00,'2026-09-27','2026-09-27','17:00:00','21:30:00','2026-09-28','02:00:00','Surplus from our lunch batch, maintained in heated commercial food warmers. Clean, rich basmati rice and fresh slow-cooked black dal makhani.','images/hero_food_share.jpg','Pickup directly from rear service entrance of Annapurna Kitchen. Bring food grade containers/vessels.','Available','2026-09-27 16:04:21'),
(2,1,3,'Fresh Tawa Chapatis (Soft Wheat Rotis)','Veg',150.00,150.00,'pieces',300.00,'2026-09-27','2026-09-27','18:00:00','22:00:00','2026-09-28','09:00:00','150 freshly baked soft whole wheat chapatis lightly coated with pure ghee. Wrapped in aluminium foil packs of 25 each.','images/provider_kitchen.jpg','Collect from kitchen counter with your sanitized thermal bags.','Available','2026-09-27 16:04:21'),
(3,2,4,'Paneer Butter Masala & Mixed Vegetable Subzi','Veg',18.00,18.00,'kg',850.00,'2026-09-27','2026-09-27','19:00:00','23:00:00','2026-09-28','03:00:00','Fresh surplus from an afternoon banquet. Premium cottage cheese in tomato cashew gravy and seasonal mixed vegetables.','images/community_distrib.jpg','Royal Feast loading bay gate 2. Contact supervisor Mr. Manoj upon arrival.','Available','2026-09-27 16:04:21'),
(4,1,1,'Vegetable Pulao','Veg',12.00,7.00,'kg',350.00,'2026-09-27','2026-09-27','18:00:00','21:00:00','2026-09-28','02:00:00','Fragrant basmati rice cooked with fresh seasonal vegetables and roasted spices','images/provider_kitchen.jpg','Annapurna kitchen rear door','Available','2026-09-27 16:12:31');
/*!40000 ALTER TABLE `food_listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_providers`
--

DROP TABLE IF EXISTS `food_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(80) NOT NULL,
  `state` varchar(80) NOT NULL,
  `business_type` varchar(80) NOT NULL,
  `fssai_status` enum('Certified','Not Certified') DEFAULT 'Not Certified',
  `fssai_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_provider_user` (`user_id`),
  CONSTRAINT `fk_provider_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_providers`
--

LOCK TABLES `food_providers` WRITE;
/*!40000 ALTER TABLE `food_providers` DISABLE KEYS */;
INSERT INTO `food_providers` VALUES
(1,2,'Annapurna Commercial Kitchen','Ramesh Sharma','+91 98201 12345','Shop 14, Lotus Grand, Link Road, Andheri West','Mumbai','Maharashtra','Restaurant & Catering','Certified','10018022007892','2026-09-27 16:04:21'),
(2,3,'Royal Feast Banquets','Vikramaditya Mehta','+91 98110 54321','Plot 42, Civil Lines Road','New Delhi','Delhi','Banquet & Events','Certified','10020011004318','2026-09-27 16:04:21'),
(3,6,'Green Leaf Kitchen','Anand Verma','919876543210','12 MG Road','Pune','Maharashtra','Restaurant','Certified','10019022001122','2026-09-27 16:13:27');
/*!40000 ALTER TABLE `food_providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `food_requests`
--

DROP TABLE IF EXISTS `food_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `food_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `food_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `requested_quantity` decimal(10,2) NOT NULL,
  `requested_date` date NOT NULL,
  `requested_time` time NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected','Completed') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_request_food` (`food_id`),
  KEY `fk_request_group` (`group_id`),
  CONSTRAINT `fk_request_food` FOREIGN KEY (`food_id`) REFERENCES `food_listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_request_group` FOREIGN KEY (`group_id`) REFERENCES `social_working_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `food_requests`
--

LOCK TABLES `food_requests` WRITE;
/*!40000 ALTER TABLE `food_requests` DISABLE KEYS */;
INSERT INTO `food_requests` VALUES
(1,1,1,10.00,'2026-09-27','18:30:00','We are organizing an evening food drive for families residing near the station shelter. We can arrive with clean steel containers by 6:30 PM.','Accepted','2026-09-27 16:04:21','2026-09-27 16:04:21'),
(2,4,1,5.00,'2026-09-27','19:00:00','Hope Foundation evening shelter distribution','Accepted','2026-09-27 16:12:48','2026-09-27 16:13:07');
/*!40000 ALTER TABLE `food_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `icon` varchar(50) DEFAULT 'food',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES
(1,'Surplus Food Listing','Commercial kitchens, caterers, and restaurants list surplus food with real-time quantities, units, and clear time slots.','list','active','2026-09-27 16:04:21'),
(2,'Verified Partner Network','Transparent profiles showcasing FSSAI food safety status, business details, and NGO Darpan registration info.','shield','active','2026-09-27 16:04:21'),
(3,'Direct Coordination','Transparent request workflows with automatic quantity balancing, eliminating middleman cuts and unnecessary delays.','handshake','active','2026-09-27 16:04:21'),
(4,'Waste Prevention Tracking','Real-time dashboard metrics tracking rescued meals, active food slots, and completed community handovers.','chart','active','2026-09-27 16:04:21');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_working_groups`
--

DROP TABLE IF EXISTS `social_working_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_working_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_name` varchar(150) NOT NULL,
  `representative_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(80) NOT NULL,
  `state` varchar(80) NOT NULL,
  `organization_type` varchar(80) NOT NULL,
  `ngo_status` enum('Registered','Community Volunteer Group','Trust','Society') DEFAULT 'Registered',
  `darpan_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_group_user` (`user_id`),
  CONSTRAINT `fk_group_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_working_groups`
--

LOCK TABLES `social_working_groups` WRITE;
/*!40000 ALTER TABLE `social_working_groups` DISABLE KEYS */;
INSERT INTO `social_working_groups` VALUES
(1,4,'Hope Welfare Foundation','Priya Deshmukh','+91 98331 88765','Building 4, Sector 7, Vashi','Navi Mumbai','Maharashtra','Registered NGO','Registered','MH/2021/0291823','2026-09-27 16:04:21'),
(2,5,'Seva Youth Volunteer Circle','Amitabh Verma','+91 98102 44321','12 Community Centre, Hauz Khas','New Delhi','Delhi','Community Volunteer Group','Community Volunteer Group','DL/2022/0119834','2026-09-27 16:04:21');
/*!40000 ALTER TABLE `social_working_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','provider','group') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'admin','admin@sevam.org','$2y$10$lUuKXDPVotPJoLWFzXYWduUXFMAR3NM6Mz0oRhqc0lL.VjOyrQCaW','admin','active','2026-09-27 16:04:21'),
(2,'annapurna_kitchen','provider@annapurna.com','$2y$10$jcoxcFsTXuuPxESzgU/LnOPsT6/axlct.xPKT9Db.iyQOWav33/b6','provider','active','2026-09-27 16:04:21'),
(3,'royal_caterers','info@royalcaterers.in','$2y$10$jcoxcFsTXuuPxESzgU/LnOPsT6/axlct.xPKT9Db.iyQOWav33/b6','provider','active','2026-09-27 16:04:21'),
(4,'hope_foundation','contact@hopefoundation.org','$2y$10$K1UJQVG39Vd61v2VIPJY0e20YEDEebZckE1.gx1mhHXdZK3ENJEQW','group','active','2026-09-27 16:04:21'),
(5,'seva_youth_circle','action@sevayouth.org','$2y$10$K1UJQVG39Vd61v2VIPJY0e20YEDEebZckE1.gx1mhHXdZK3ENJEQW','group','active','2026-09-27 16:04:21'),
(6,'greenleaf_kitchen','anand@greenleaf.com','$2y$10$lYAXskJomtyPIuR7TfHw6eQ0xP89Pi9v4PSm6VLGr.ZR4lcMZlJg6','provider','active','2026-09-27 16:13:27');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-27 16:13:35
