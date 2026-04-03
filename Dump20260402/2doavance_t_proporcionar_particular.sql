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
-- Table structure for table `t_proporcionar_particular`
--

DROP TABLE IF EXISTS `t_proporcionar_particular`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_proporcionar_particular` (
  `id_pg` int NOT NULL,
  `id_material` int NOT NULL,
  `cantidad` decimal(6,2) NOT NULL,
  `costo` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id_pg`,`id_material`),
  KEY `ppr_mtl_fk_idx` (`id_material`),
  CONSTRAINT `ppr_mtl_fk` FOREIGN KEY (`id_material`) REFERENCES `t_materiales` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ppr_pgl_fk` FOREIGN KEY (`id_pg`) REFERENCES `t_proporcionar_general` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_proporcionar_particular`
--

LOCK TABLES `t_proporcionar_particular` WRITE;
/*!40000 ALTER TABLE `t_proporcionar_particular` DISABLE KEYS */;
INSERT INTO `t_proporcionar_particular` VALUES (1,6,5.00,4.00),(1,16,6.00,5.00),(1,17,10.00,3.00),(1,26,11.00,3.64),(2,1,160.00,90.00),(3,2,50.00,0.02),(3,13,70.00,0.03),(4,10,2.00,100.00),(4,18,2.00,75.00),(4,21,2.00,15.00),(5,6,4.00,5.00),(5,16,7.00,4.29),(5,17,8.00,3.75),(5,26,10.00,4.00),(6,30,20.00,8.00),(7,1,190.00,0.47),(7,29,2.00,50.00),(8,1,170.00,0.53),(8,29,1.00,100.00),(9,2,60.00,0.17),(9,13,60.00,0.03),(10,2,60.00,0.17),(10,13,60.00,0.03),(11,3,20.00,0.10),(11,4,0.50,50.00),(11,5,10.00,0.50),(11,11,0.50,2.00),(12,22,40.00,0.25),(12,23,40.00,0.30),(12,24,20.00,0.90),(13,7,200.00,0.03),(13,10,1.00,200.00),(13,18,2.00,40.00),(13,19,2.00,35.00),(13,20,1.00,20.00),(13,21,3.00,10.00),(13,25,5.00,8.00),(13,28,15.00,1.67),(13,31,25.00,0.60),(14,8,50.00,0.04),(14,9,50.00,0.04),(15,6,12.00,1.67),(15,12,4.00,50.00),(15,14,30.00,0.07),(15,15,15.00,1.67),(15,16,15.00,2.00),(15,17,20.00,1.50),(15,26,14.00,1.14),(15,27,30.00,0.07),(16,30,80.00,1.00);
/*!40000 ALTER TABLE `t_proporcionar_particular` ENABLE KEYS */;
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
