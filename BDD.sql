CREATE DATABASE  IF NOT EXISTS `2doavance` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `2doavance`;
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
-- Table structure for table `t_clientes`
--

DROP TABLE IF EXISTS `t_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `rfc` varchar(45) DEFAULT NULL,
  `razon_social` varchar(45) DEFAULT NULL,
  `codigo_postal` varchar(6) DEFAULT NULL,
  `numero_telefono` varchar(12) NOT NULL,
  `correo` varchar(45) NOT NULL,
  `calle` varchar(45) DEFAULT NULL,
  `colonia` varchar(30) DEFAULT NULL,
  `estado` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_telefono_UNIQUE` (`numero_telefono`),
  UNIQUE KEY `correo_UNIQUE` (`correo`),
  UNIQUE KEY `rfc_UNIQUE` (`rfc`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_clientes`
--

LOCK TABLES `t_clientes` WRITE;
/*!40000 ALTER TABLE `t_clientes` DISABLE KEYS */;
INSERT INTO `t_clientes` VALUES (1,'Juan Perez','JUAN911119ABC','Juan Perez S.A. de C.V.','30000','2239808126','juanperez@correo.com','Calle 78 #83','La Riveira','Puebla'),(2,'Maria Gonzalez','MARI800130ABC','Maria Gonzalez S.A. de C.V.','50000','2228137240','mariagonzalez@correo.com','Calle 142 #19','Centro','Puebla'),(3,'Carlos Ramirez','CARL970807ABC','Carlos Ramirez S.A. de C.V.','10000','2239497092','carlosramirez@correo.com','Calle 67 #44','Emiliano Zapata','Puebla'),(4,'Ana Torres','ANA720211ABC','Ana Torres S.A. de C.V.','40000','2285811130','anatorres@correo.com','Calle 116 #23','Centro','Puebla'),(5,'Pedro Sanchez','PEDR951017ABC','Pedro Sanchez S.A. de C.V.','20000','2241398821','pedrosanchez@correo.com','Calle 43 #12','Villa Juarez','Puebla'),(6,'Sofia Herrera','SOFI891215ABC','Sofia Herrera S.A. de C.V.','30000','2274737268','sofiaherrera@correo.com','Calle 81 #99','Centro','Puebla'),(7,'Luis Martinez','LUIS770524ABC','Luis Martinez S.A. de C.V.','30000','2255453899','luismartinez@correo.com','Calle 11 #17','La Paz','Puebla'),(8,'Laura Gutierrez','LAUR830414ABC','Laura Gutierrez S.A. de C.V.','30000','2263083862','lauragutierrez@correo.com','Calle 147 #44','Centro','Puebla'),(9,'Jose Fernandez','JOSE720113ABC','Jose Fernandez S.A. de C.V.','50000','2288663177','josefernandez@correo.com','Calle 18 #93','Angelopolis','Puebla'),(10,'Karla Mendoza','KARL930426ABC','Karla Mendoza S.A. de C.V.','10000','2258031491','karlamendoza@correo.com','Calle 20 #10','La Riveira','Puebla'),(11,'Ricardo Lopez','RICL850312ABC','Ricardo Lopez S.A. de C.V.','60000','2248209173','ricardolopez@correo.com','Calle 55 #21','Villa Juarez','Puebla'),(12,'Gabriela Ruiz','GABR920629ABC','Gabriela Ruiz S.A. de C.V.','70000','2269031284','gabrielaruiz@correo.com','Calle 33 #98','Centro','Puebla'),(13,'Fernando Castro','FERC951108ABC','Fernando Castro S.A. de C.V.','80000','2276128375','fernandocastro@correo.com','Calle 90 #67','Centro','Puebla'),(14,'Natalia Vega','NATV880921ABC','Natalia Vega S.A. de C.V.','90000','2250389471','nataliavega@correo.com','Calle 76 #12','La Paz','Puebla'),(15,'Alejandro Silva','ALES990515ABC','Alejandro Silva S.A. de C.V.','40000','2263017462','alejandrosilva@correo.com','Calle 21 #55','Centro','Puebla'),(16,'Patricia Mora','PATM970304ABC','Patricia Mora S.A. de C.V.','50000','2282743569','patriciamora@correo.com','Calle 84 #28','Centro','Puebla'),(17,'Javier Ortega','JAVO860718ABC','Javier Ortega S.A. de C.V.','60000','2274102928','javierortega@correo.com','Calle 52 #32','Emiliano Zapata','Puebla'),(18,'Monica Herrera','MONH940212ABC','Monica Herrera S.A. de C.V.','30000','2269814736','monicaherrera@correo.com','Calle 77 #11','Centro','Puebla'),(19,'Sergio Mendez','SERM890805ABC','Sergio Mendez S.A. de C.V.','10000','2281735041','sergiomendez@correo.com','Calle 39 #27','Villas Benavente','Puebla'),(20,'Beatriz Rojas','BEAR910924ABC','Beatriz Rojas S.A. de C.V.','20000','2290286152','beatrizrojas@correo.com','Calle 65 #19','Centro','Puebla'),(21,'Ana Martínez','ANMA950101ABC','Ana Martínez S.A. de C.V.','50000','2223345454','ana.martinez@correo.com','Calle 45 #12','Centro','Puebla');
/*!40000 ALTER TABLE `t_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_empleados`
--

DROP TABLE IF EXISTS `t_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_empleados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `salario` decimal(6,2) NOT NULL,
  `numero_telefono` varchar(12) NOT NULL,
  `correo` varchar(45) NOT NULL,
  `contrasena` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_telefono_UNIQUE` (`numero_telefono`),
  UNIQUE KEY `correo_UNIQUE` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_empleados`
--

LOCK TABLES `t_empleados` WRITE;
/*!40000 ALTER TABLE `t_empleados` DISABLE KEYS */;
INSERT INTO `t_empleados` VALUES (1,'Raul Mateo',9500.00,'512345678','raul.mateo@taqueria.com','raulpro123'),(2,'Ana Torres',7500.00,'898765432','ana.torres@taqueria.com','anator456'),(3,'Karla Fernandez',7500.00,'588990011','karla.fernandez@taqueria.com','karlfer789'),(4,'Cesar el Real Ramirez ',7500.00,'7729174398','cesar.real@taqueria.com','cesarRG4life'),(5,'María López',7500.00,'5512345678','maria.lopez@taqueria.com','marlop147');
/*!40000 ALTER TABLE `t_empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_facturas`
--

DROP TABLE IF EXISTS `t_facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_facturas` (
  `folio` int NOT NULL AUTO_INCREMENT,
  `id_vg` int NOT NULL,
  `cfdi` varchar(45) NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`folio`),
  KEY `FTA_VGL_FK` (`id_vg`),
  CONSTRAINT `FTA_VGL_FK` FOREIGN KEY (`id_vg`) REFERENCES `t_vender_general` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_facturas`
--

LOCK TABLES `t_facturas` WRITE;
/*!40000 ALTER TABLE `t_facturas` DISABLE KEYS */;
INSERT INTO `t_facturas` VALUES (100,1,'34FDF-234DS-123GHF','2024-06-19 06:00:00'),(101,2,'234ASF-312DFG-234HDJ','2024-01-12 06:00:00'),(102,3,'23GHY-325FDS-12GHF','2024-07-05 06:00:00'),(103,4,'23FDJ-233DFH-21FHD','2024-07-19 06:00:00'),(104,5,'32HFD-323GFD-12FDH','2024-11-12 06:00:00'),(105,6,'21DJS-32HDH-21FHR','2024-03-10 06:00:00'),(106,8,'ABC-XYZ-123','2024-03-26 01:30:00'),(107,26,'34FDF-234DS-123GHF','2024-04-04 19:00:00'),(108,27,'234ASF-312DFG-234HDJ','2024-04-07 22:50:00'),(109,28,'23GHY-325FDS-12GHF','2024-04-09 19:00:00'),(110,29,'23FDF-233DFH-21FHD','2024-04-13 19:59:00'),(111,30,'32HFD-323GFD-12FDH','2024-04-16 20:50:00'),(112,31,'21DJS-32HDH-21FHR','2024-04-19 01:10:00'),(113,32,'21DJS-32HDH-21FHR','2024-04-21 22:45:00'),(114,33,'ABC-XYZ-123','2024-04-24 18:45:00'),(115,34,'23FDF-233DFH-21FHD','2024-04-28 01:00:00'),(116,35,'34FDF-234DS-123GHF','2024-04-30 22:00:00'),(117,35,'34FDF-234DS-123GHF','2024-04-30 22:00:00'),(118,36,'34FDF-234DS-123GHF','2024-04-03 18:00:00'),(119,37,'234ASF-312DFG-234HDJ','2024-04-05 20:30:00'),(120,38,'23GHY-325FDS-12GHF','2024-04-09 00:00:00'),(121,39,'23FDF-233DFH-21FHD','2024-04-10 21:30:00'),(122,40,'32HFD-323GFD-12FDH','2024-04-12 22:00:00'),(123,41,'21DJS-32HDH-21FHR','2024-04-15 01:00:00'),(124,42,'21DJS-32HDH-21FHR','2024-04-18 02:30:00');
/*!40000 ALTER TABLE `t_facturas` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Table structure for table `t_necesitar_general`
--

DROP TABLE IF EXISTS `t_necesitar_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_necesitar_general` (
  `id_ng` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ng`),
  KEY `NGL_PDO_FK` (`id_producto`),
  CONSTRAINT `NGL_PDO_FK` FOREIGN KEY (`id_producto`) REFERENCES `t_productos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_necesitar_general`
--

LOCK TABLES `t_necesitar_general` WRITE;
/*!40000 ALTER TABLE `t_necesitar_general` DISABLE KEYS */;
INSERT INTO `t_necesitar_general` VALUES (1,27,'2024-01-04 06:00:00'),(2,30,'2024-01-04 06:00:00'),(3,19,'2024-01-05 06:00:00'),(4,16,'2024-02-05 06:00:00'),(5,14,'2024-02-05 06:00:00'),(6,28,'2024-12-27 06:00:00'),(7,6,'2024-12-27 06:00:00'),(8,10,'2024-03-20 20:30:00'),(9,1,'2024-04-04 19:00:00'),(10,7,'2024-04-04 19:00:00'),(11,35,'2024-04-04 19:00:00'),(12,5,'2024-04-07 23:00:00'),(13,16,'2024-04-07 23:00:00'),(14,36,'2024-04-07 23:00:00'),(15,9,'2024-04-09 19:00:00'),(16,37,'2024-04-09 19:00:00'),(17,21,'2024-04-13 20:00:00'),(18,22,'2024-04-13 20:00:00'),(19,27,'2024-04-16 21:00:00'),(20,28,'2024-04-16 21:00:00'),(21,3,'2024-04-19 01:30:00'),(22,26,'2024-04-19 01:30:00'),(23,11,'2024-04-21 23:15:00'),(24,32,'2024-04-21 23:15:00'),(25,20,'2024-04-24 18:55:00'),(26,34,'2024-04-24 18:55:00'),(27,21,'2024-04-28 01:00:00'),(28,22,'2024-04-28 01:00:00'),(29,5,'2024-04-30 22:30:00'),(30,14,'2024-04-30 22:30:00'),(31,38,'2024-04-30 22:30:00'),(32,9,'2024-04-03 17:30:00'),(33,28,'2024-04-03 17:30:00'),(34,26,'2024-04-03 17:30:00'),(35,38,'2024-04-05 20:00:00'),(36,35,'2024-04-05 20:00:00'),(37,27,'2024-04-05 20:00:00'),(38,5,'2024-04-08 23:30:00'),(39,14,'2024-04-08 23:30:00'),(40,20,'2024-04-08 23:30:00'),(41,27,'2024-04-10 21:00:00'),(42,15,'2024-04-10 21:00:00'),(43,14,'2024-04-12 21:30:00'),(44,9,'2024-04-12 21:30:00'),(45,38,'2024-04-15 00:30:00'),(46,5,'2024-04-18 02:00:00'),(47,20,'2024-04-18 02:00:00'),(48,1,'2025-05-08 19:00:00'),(49,7,'2025-05-08 19:00:00'),(50,35,'2025-05-08 19:00:00'),(51,5,'2025-05-08 23:00:00'),(52,16,'2025-05-08 23:00:00'),(53,36,'2025-05-08 23:00:00'),(54,9,'2025-05-08 19:00:00'),(55,37,'2025-05-08 19:00:00'),(56,21,'2025-05-08 20:00:00'),(57,22,'2025-05-08 20:00:00'),(345,10,'2026-04-05 06:23:48'),(346,11,'2026-04-05 06:23:48'),(347,3,'2026-04-05 06:23:48'),(348,8,'2026-04-05 06:23:48'),(349,23,'2026-04-05 06:23:48'),(350,20,'2026-04-05 06:23:48'),(351,24,'2026-04-05 06:23:48'),(352,14,'2026-04-05 06:23:48'),(353,16,'2026-04-05 06:23:48');
/*!40000 ALTER TABLE `t_necesitar_general` ENABLE KEYS */;
UNLOCK TABLES;

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
INSERT INTO `t_necesitar_particular` VALUES (1,17,1.00),(2,26,1.00),(3,1,0.20),(3,13,1.00),(3,18,0.20),(4,13,1.00),(4,18,0.20),(5,13,2.00),(5,18,0.20),(6,19,0.20),(7,1,0.10),(7,2,1.00),(8,1,1.00),(8,2,10.00),(8,3,0.20),(8,13,10.00),(9,1,0.25),(9,13,2.00),(10,6,1.00),(11,23,2.00),(12,1,0.20),(12,14,1.00),(13,13,1.00),(13,18,0.10),(14,24,1.00),(15,15,1.00),(16,25,0.07),(17,1,0.10),(17,13,2.00),(17,18,0.20),(18,1,0.10),(18,13,2.00),(18,18,0.20),(18,29,0.10),(19,17,1.00),(20,19,2.00),(21,1,0.13),(21,12,0.05),(21,13,1.00),(21,18,0.10),(22,10,0.05),(22,11,0.05),(22,21,0.05),(23,1,0.50),(23,2,8.00),(23,3,0.20),(23,13,8.00),(24,22,1.00),(25,1,0.13),(25,13,1.00),(25,18,0.10),(26,31,1.00),(27,1,0.10),(27,13,2.00),(27,18,0.20),(28,1,0.10),(28,13,2.00),(28,18,0.20),(28,29,0.10),(29,1,0.20),(29,14,1.00),(30,13,2.00),(30,18,0.25),(31,1,0.15),(31,13,1.00),(31,18,0.10),(32,15,1.00),(33,19,0.50),(34,10,0.03),(35,1,0.20),(35,13,1.00),(35,18,0.20),(35,21,0.05),(36,23,1.00),(37,17,1.00),(38,1,0.20),(38,14,1.00),(39,13,2.00),(39,18,0.20),(40,13,1.00),(40,18,0.10),(41,17,1.00),(42,1,0.20),(42,13,1.00),(42,18,0.20),(43,13,2.00),(43,18,0.20),(44,15,1.00),(45,1,0.35),(45,13,2.00),(45,18,0.40),(45,21,0.10),(46,1,0.20),(46,14,1.00),(47,13,1.00),(47,18,0.20),(48,1,0.15),(48,13,2.00),(49,6,1.00),(50,23,2.00),(51,1,0.20),(51,14,1.00),(52,13,1.00),(52,18,0.10),(53,24,1.00),(54,15,1.00),(55,25,0.07),(56,1,0.10),(56,13,2.00),(56,18,0.20),(57,1,0.10),(57,13,2.00),(57,18,0.20),(57,29,0.10),(345,1,1.00),(345,2,10.00),(345,3,0.15),(345,4,0.05),(345,5,0.10),(345,13,10.00),(346,1,0.50),(346,2,5.00),(346,3,0.08),(346,4,0.03),(346,5,0.05),(346,13,5.00),(347,1,0.20),(347,12,0.05),(347,13,1.00),(347,18,0.15),(348,1,0.15),(348,2,2.00),(348,12,0.05),(348,18,0.10),(349,2,2.00),(349,18,0.15),(350,1,0.15),(350,13,1.00),(350,18,0.15),(351,1,0.10),(351,2,2.00),(351,18,0.15),(352,13,3.00),(352,18,0.35),(353,13,1.00),(353,18,0.15);
/*!40000 ALTER TABLE `t_necesitar_particular` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Table structure for table `t_proovedores`
--

DROP TABLE IF EXISTS `t_proovedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_proovedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  `numero_telefono` varchar(45) NOT NULL,
  `correo` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo_UNIQUE` (`correo`),
  UNIQUE KEY `numero_telefono_UNIQUE` (`numero_telefono`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_proovedores`
--

LOCK TABLES `t_proovedores` WRITE;
/*!40000 ALTER TABLE `t_proovedores` DISABLE KEYS */;
INSERT INTO `t_proovedores` VALUES (1,'Carnes Selectas Don Chucho','5578943210','contacto@donchucho.com'),(2,'Tortillas El Maiz Dorado','8145678912','ventas@maizdorado.mx'),(3,'Verduras Frescas La Huerta','3399887766','pedidos@lahuertafresca.com'),(4,'Refrescos y Bebidas La Burbuja','8122334455','pedidos@laburbuja.com'),(5,'Tienda General San Miguel','3366778899','contacto@quesossanmiguel.mx'),(6,'Desechables y Empaques Rapidos','5511223344','ventas@empaquesrapidos.com'),(7,'Matriz','2222222222','matriz@taqueria.com'),(8,'Carbon El Tren','5551122334','ventas@eltren.mx');
/*!40000 ALTER TABLE `t_proovedores` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Table structure for table `t_vender_general`
--

DROP TABLE IF EXISTS `t_vender_general`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_vender_general` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `servicio_a_domicilio` varchar(5) NOT NULL,
  `id_empleado` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `CLE_VGL_FK_idx` (`id_cliente`),
  KEY `VGL_EMO_FK_idx` (`id_empleado`),
  CONSTRAINT `VGL_CLE_FK` FOREIGN KEY (`id_cliente`) REFERENCES `t_clientes` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `VGL_EMO_FK` FOREIGN KEY (`id_empleado`) REFERENCES `t_empleados` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_vender_general`
--

LOCK TABLES `t_vender_general` WRITE;
/*!40000 ALTER TABLE `t_vender_general` DISABLE KEYS */;
INSERT INTO `t_vender_general` VALUES (1,2,'2024-06-19 06:00:00','NO',2),(2,6,'2024-01-12 06:00:00','SI',2),(3,17,'2024-07-05 06:00:00','NO',4),(4,12,'2024-07-19 06:00:00','SI',3),(5,15,'2024-11-12 06:00:00','NO',3),(6,6,'2024-03-10 06:00:00','SI',4),(7,10,'2024-08-08 06:00:00','NO',2),(8,5,'2024-03-26 01:15:00','SI',5),(9,1,'2025-04-28 16:00:00','NO',1),(10,5,'2024-03-01 06:00:00','SI',1),(11,5,'2024-03-15 06:00:00','SI',1),(12,1,'2025-04-21 15:00:00','SI',1),(13,2,'2025-04-22 16:00:00','NO',1),(14,3,'2025-04-23 17:00:00','SI',1),(15,4,'2025-04-24 18:00:00','NO',1),(16,5,'2025-04-25 19:00:00','SI',1),(17,6,'2025-04-26 20:00:00','NO',1),(18,7,'2025-04-27 21:00:00','SI',1),(19,1,'2025-04-21 15:00:00','SI',1),(20,2,'2025-04-22 16:00:00','NO',1),(21,3,'2025-04-23 17:00:00','SI',1),(22,4,'2025-04-24 18:00:00','NO',1),(23,5,'2025-04-25 19:00:00','SI',1),(24,6,'2025-04-26 20:00:00','NO',1),(25,7,'2025-04-27 21:00:00','SI',1),(26,3,'2024-04-04 19:00:00','NO',2),(27,6,'2024-04-07 22:50:00','SI',1),(28,13,'2024-04-09 19:00:00','NO',3),(29,8,'2024-04-13 19:59:00','SI',1),(30,2,'2024-04-16 20:50:00','NO',5),(31,15,'2024-04-19 01:10:00','SI',3),(32,7,'2024-04-21 22:45:00','NO',1),(33,20,'2024-04-24 18:45:00','SI',2),(34,18,'2024-04-28 01:00:00','SI',2),(35,12,'2024-04-30 22:00:00','NO',4),(36,3,'2024-04-03 18:00:00','NO',2),(37,4,'2024-04-05 20:30:00','NO',1),(38,8,'2024-04-09 00:00:00','SI',4),(39,9,'2024-04-10 21:30:00','SI',3),(40,10,'2024-04-12 22:00:00','NO',4),(41,11,'2024-04-15 01:00:00','SI',5),(42,12,'2024-04-18 02:30:00','SI',3),(43,3,'2025-05-08 19:00:00','NO',2),(44,6,'2025-05-08 22:50:00','SI',1),(45,13,'2025-05-08 19:00:00','NO',3),(46,8,'2025-05-08 19:59:00','SI',1);
/*!40000 ALTER TABLE `t_vender_general` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Dumping events for database '2doavance'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-09 23:54:54
