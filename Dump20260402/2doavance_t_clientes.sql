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
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-02 20:46:44
