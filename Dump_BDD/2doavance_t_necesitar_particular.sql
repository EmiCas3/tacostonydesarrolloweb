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
-- Table structure for table `t_necesitar_particular`
--

DROP TABLE IF EXISTS `t_necesitar_particular`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_necesitar_particular` (
  `id_ng` int NOT NULL,
  `id_material` int NOT NULL,
  `cantidad` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_ng`,`id_material`),
  KEY `npr_mtl_fk_idx` (`id_material`),
  KEY `npt_mtls_fk_idx` (`id_material`),
  KEY `npr_npg_fk` (`id_ng`) /*!80000 INVISIBLE */,
  KEY `npr_mtls_fk_idx` (`id_material`),
  CONSTRAINT `npr_mtls_fk` FOREIGN KEY (`id_material`) REFERENCES `t_materiales` (`id`),
  CONSTRAINT `npr_npg_fk` FOREIGN KEY (`id_ng`) REFERENCES `t_necesitar_general` (`id_ng`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_necesitar_particular`
--

LOCK TABLES `t_necesitar_particular` WRITE;
/*!40000 ALTER TABLE `t_necesitar_particular` DISABLE KEYS */;
INSERT INTO `t_necesitar_particular` VALUES (1,17,1.00),(2,26,1.00),(3,1,0.20),(3,13,1.00),(3,18,0.20),(4,13,1.00),(4,18,0.20),(5,13,2.00),(5,18,0.20),(6,19,0.20),(7,1,0.10),(7,2,1.00),(8,1,1.00),(8,2,10.00),(8,3,0.20),(8,13,10.00),(9,1,0.25),(9,13,2.00),(10,6,1.00),(11,23,2.00),(12,1,0.20),(12,14,1.00),(13,13,1.00),(13,18,0.10),(14,24,1.00),(15,15,1.00),(16,25,0.07),(17,1,0.10),(17,13,2.00),(17,18,0.20),(18,1,0.10),(18,13,2.00),(18,18,0.20),(18,29,0.10),(19,17,1.00),(20,19,2.00),(21,1,0.13),(21,12,0.05),(21,13,1.00),(21,18,0.10),(22,10,0.05),(22,11,0.05),(22,21,0.05),(23,1,0.50),(23,2,8.00),(23,3,0.20),(23,13,8.00),(24,22,1.00),(25,1,0.13),(25,13,1.00),(25,18,0.10),(26,31,1.00),(27,1,0.10),(27,13,2.00),(27,18,0.20),(28,1,0.10),(28,13,2.00),(28,18,0.20),(28,29,0.10),(29,1,0.20),(29,14,1.00),(30,13,2.00),(30,18,0.25),(31,1,0.15),(31,13,1.00),(31,18,0.10),(32,15,1.00),(33,19,0.50),(34,10,0.03),(35,1,0.20),(35,13,1.00),(35,18,0.20),(35,21,0.05),(36,23,1.00),(37,17,1.00),(38,1,0.20),(38,14,1.00),(39,13,2.00),(39,18,0.20),(40,13,1.00),(40,18,0.10),(41,17,1.00),(42,1,0.20),(42,13,1.00),(42,18,0.20),(43,13,2.00),(43,18,0.20),(44,15,1.00),(45,1,0.35),(45,13,2.00),(45,18,0.40),(45,21,0.10),(46,1,0.20),(46,14,1.00),(47,13,1.00),(47,18,0.20),(48,1,0.15),(48,13,2.00),(49,6,1.00),(50,23,2.00),(51,1,0.20),(51,14,1.00),(52,13,1.00),(52,18,0.10),(53,24,1.00),(54,15,1.00),(55,25,0.07),(56,1,0.10),(56,13,2.00),(56,18,0.20),(57,1,0.10),(57,13,2.00),(57,18,0.20),(57,29,0.10);
/*!40000 ALTER TABLE `t_necesitar_particular` ENABLE KEYS */;
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
