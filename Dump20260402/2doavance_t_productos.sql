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
-- Table structure for table `t_productos`
--

DROP TABLE IF EXISTS `t_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_productos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_productos`
--

LOCK TABLES `t_productos` WRITE;
/*!40000 ALTER TABLE `t_productos` DISABLE KEYS */;
INSERT INTO `t_productos` VALUES (1,'Taco Arabe',39.00),(2,'Torta Arabe',39.00),(3,'Tony Especial Arabe',41.00),(4,'Tony Especial Torta',41.00),(5,'Cemita con Carne Arabe',40.00),(6,'Taco Oriental',24.00),(7,'Cremita',37.00),(8,'Tony Especial Oriental',26.00),(9,'Falafel',39.00),(10,'Kilo de Carne Arabe',400.00),(11,'1/2 de Kilo de Arabe',200.00),(12,'Salsa / Gine 100 ml',30.00),(13,'Salsa / Gine 250 ml',40.00),(14,'Queso Fundido',115.00),(15,'Queso Fundido Preparado',120.00),(16,'Quesadilla',45.00),(17,'Quesadilla Arabe con Gine',67.00),(18,'Torta Arabe con Queso',65.00),(19,'Cemita Arabe con Queso',65.00),(20,'Quesadilla Arabe',65.00),(21,'Queso fundido con Carne',120.00),(22,'Queso Tony Especial',140.00),(23,'Quesadilla Maiz',35.00),(24,'Quesadilla Oriental',40.00),(25,'Cebollitas',28.00),(26,'Caldo de Camaron',38.00),(27,'Jocoque Seco',55.00),(28,'Papas a la Francesa',45.00),(29,'Jocoque Cremoso',48.00),(30,'Flan Horneado',34.00),(31,'Paleta Helada',27.00),(32,'Agua Embotellada',29.00),(33,'Cemita Especial',58.00),(34,'Dedo de Novia',40.00),(35,'Refresco Lata',29.00),(36,'Cerveza',45.00),(37,'Cafe Soluble',25.00),(38,'Pizza Arabe',65.00);
/*!40000 ALTER TABLE `t_productos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-02 20:46:44
