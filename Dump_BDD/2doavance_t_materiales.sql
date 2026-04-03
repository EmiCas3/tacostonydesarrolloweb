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
-- Table structure for table `t_materiales`
--

DROP TABLE IF EXISTS `t_materiales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_materiales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `existencias` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_materiales`
--

LOCK TABLES `t_materiales` WRITE;
/*!40000 ALTER TABLE `t_materiales` DISABLE KEYS */;
INSERT INTO `t_materiales` VALUES (1,'Carne de cerdo',180.04),(2,'Tortillas de maiz',39.00),(3,'Cebolla',58.80),(4,'Cilantro',1.00),(5,'Limones',31.00),(6,'Cremita',18.00),(7,'Servilletas',200.00),(8,'Envases desechables',50.00),(9,'Vasos desechables',50.00),(10,'Camaron',2.45),(11,'Perejil',2.95),(12,'Gine',9.65),(13,'Pan Arabe',2.00),(14,'Pan de Cemita',49.00),(15,'Falafel',21.00),(16,'Jocoque cremoso',3.00),(17,'Jocoque seco',39.00),(18,'Queso Gouda',1.15),(19,'Papas a la francesa',0.80),(20,'Catsup',4.00),(21,'Salsa de jitomate',4.50),(22,'Agua embotellada',39.00),(23,'Refrescos en lata',45.00),(24,'Cerveza',48.00),(25,'Cafe Soluble',5.51),(26,'Flan',39.00),(27,'Pan de torta',28.00),(28,'Paleta Helada',50.00),(29,'Chorizo',6.00),(30,'Carbon',220.00),(31,'Dedo de Novia',54.00);
/*!40000 ALTER TABLE `t_materiales` ENABLE KEYS */;
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
