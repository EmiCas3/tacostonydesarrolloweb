-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: 2doavance
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `t_proporcionar_general`
--

DROP TABLE IF EXISTS `t_proporcionar_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_proporcionar_general` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_provedoor` int NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pgl_pvr_fk_idx` (`id_provedoor`),
  CONSTRAINT `pgl_pvr_fk` FOREIGN KEY (`id_provedoor`) REFERENCES `t_proovedores` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_proporcionar_general`
--

LOCK TABLES `t_proporcionar_general` WRITE;
/*!40000 ALTER TABLE `t_proporcionar_general` DISABLE KEYS */;
INSERT INTO `t_proporcionar_general` VALUES (1,7,'2024-01-02 06:00:00'),(2,1,'2024-01-02 06:00:00'),(3,2,'2024-01-03 06:00:00'),(4,5,'2024-02-04 06:00:00'),(5,7,'2024-12-20 06:00:00'),(6,8,'2024-03-15 06:00:00'),(7,1,'2024-04-01 06:00:00'),(8,1,'2024-04-15 06:00:00'),(9,2,'2024-04-02 06:00:00'),(10,2,'2024-04-16 06:00:00'),(11,3,'2024-04-04 06:00:00'),(12,4,'2024-04-05 06:00:00'),(13,5,'2024-04-06 06:00:00'),(14,6,'2024-04-07 06:00:00'),(15,7,'2024-04-08 06:00:00'),(16,8,'2024-04-09 06:00:00');
/*!40000 ALTER TABLE `t_proporcionar_general` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-02 20:46:43
