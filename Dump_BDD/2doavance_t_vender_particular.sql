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
-- Table structure for table `t_vender_particular`
--

DROP TABLE IF EXISTS `t_vender_particular`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_vender_particular` (
  `id_vg` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` decimal(6,2) NOT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id_vg`,`id_producto`),
  KEY `vpr_pdo_fk_idx` (`id_producto`),
  CONSTRAINT `vpr_pdo_fk` FOREIGN KEY (`id_producto`) REFERENCES `t_productos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `vpr_vgl_fk` FOREIGN KEY (`id_vg`) REFERENCES `t_vender_general` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_vender_particular`
--

LOCK TABLES `t_vender_particular` WRITE;
/*!40000 ALTER TABLE `t_vender_particular` DISABLE KEYS */;
INSERT INTO `t_vender_particular` VALUES (1,1,5.00,195.00),(1,2,5.00,195.00),(1,28,5.00,225.00),(2,14,4.00,460.00),(2,18,2.00,130.00),(2,26,1.00,38.00),(2,31,1.00,27.00),(3,8,3.00,78.00),(3,14,1.00,115.00),(3,16,2.00,90.00),(3,20,1.00,65.00),(3,32,2.00,58.00),(4,10,2.00,800.00),(4,12,2.00,60.00),(4,18,4.00,260.00),(4,36,5.00,225.00),(5,6,4.00,96.00),(5,12,4.00,120.00),(5,19,3.00,195.00),(5,37,3.00,75.00),(6,13,2.00,80.00),(6,22,2.00,280.00),(6,25,5.00,140.00),(6,27,4.00,220.00),(6,31,4.00,108.00),(7,10,4.00,1600.00),(7,20,3.00,195.00),(7,25,3.00,84.00),(7,37,4.00,100.00),(8,1,3.00,117.00),(8,9,1.00,39.00),(8,35,1.00,29.00),(9,1,2.00,78.00),(9,2,3.00,117.00),(10,1,5.00,195.00),(10,2,2.00,78.00),(11,1,5.00,195.00),(11,2,2.00,78.00),(12,1,2.00,78.00),(13,1,3.00,117.00),(14,1,1.00,39.00),(15,1,4.00,156.00),(16,1,5.00,195.00),(17,1,6.00,234.00),(18,1,7.00,273.00),(19,1,2.00,78.00),(20,1,3.00,117.00),(21,1,1.00,39.00),(22,1,4.00,156.00),(23,1,5.00,195.00),(24,1,6.00,234.00),(25,1,7.00,273.00),(26,1,2.00,78.00),(26,7,1.00,37.00),(26,35,2.00,58.00),(27,5,1.00,40.00),(27,16,1.00,45.00),(27,36,1.00,45.00),(28,9,1.00,39.00),(28,37,1.00,25.00),(29,21,1.00,120.00),(29,22,1.00,140.00),(30,27,1.00,55.00),(30,28,2.00,90.00),(31,3,1.00,41.00),(31,26,1.00,38.00),(32,11,1.00,200.00),(32,32,1.00,29.00),(33,20,1.00,65.00),(33,34,1.00,40.00),(34,21,1.00,120.00),(34,22,1.00,140.00),(35,5,1.00,40.00),(35,14,1.00,115.00),(35,38,1.00,65.00),(36,9,1.00,39.00),(36,26,1.00,38.00),(36,28,2.00,90.00),(37,27,1.00,55.00),(37,35,2.00,58.00),(37,38,1.00,65.00),(38,5,1.00,40.00),(38,14,1.00,115.00),(38,20,1.00,65.00),(39,15,1.00,120.00),(39,27,1.00,55.00),(40,9,1.00,39.00),(40,14,1.00,115.00),(41,38,2.00,130.00),(42,5,2.00,80.00),(42,20,1.00,65.00),(43,1,2.00,78.00),(43,7,1.00,37.00),(43,35,2.00,58.00),(44,5,1.00,40.00),(44,16,1.00,45.00),(44,36,1.00,45.00),(45,9,1.00,39.00),(45,37,1.00,25.00),(46,21,1.00,120.00),(46,22,1.00,140.00);
/*!40000 ALTER TABLE `t_vender_particular` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_calcular_subtotal_insert` BEFORE INSERT ON `t_vender_particular` FOR EACH ROW BEGIN
    DECLARE v_precio DECIMAL(6,2);

    -- 1. Buscamos el precio del producto en la tabla t_productos
    SELECT precio INTO v_precio
    FROM t_productos
    WHERE id = NEW.id_producto;

    -- 2. Calculamos el subtotal (cantidad insertada * precio obtenido)
    SET NEW.subtotal = NEW.cantidad * v_precio;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-02 20:46:43
