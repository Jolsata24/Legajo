-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: milegajo
-- ------------------------------------------------------
-- Server version	8.0.36

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
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,'Recursos Humanos','Área encargada de la gestión de personal','2025-09-13 15:18:35'),(2,'Contabilidad','Área encargada de la contabilidad y finanzas','2025-09-13 15:18:35'),(3,'Logística','Área encargada de abastecimiento y control de bienes','2025-09-13 15:18:35'),(4,'Dirección','Despacho principal de la DREMH Pasco','2025-09-13 15:18:35'),(5,'Fiscalización','Area Fiscalización','2025-09-13 18:15:08'),(6,'Formalización','Area Formalización','2025-09-13 18:15:08'),(7,'Asuntos Ambientales Mineros','Area Ambiental','2025-09-13 18:15:08'),(8,'Fiscalización','Area Fiscalización','2025-09-13 18:15:23'),(9,'Formalización','Area Formalización','2025-09-13 18:15:23'),(10,'Asuntos Ambientales Mineros','Area Ambiental','2025-09-13 18:15:23');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_adjuntos`
--

DROP TABLE IF EXISTS `corr_adjuntos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_adjuntos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_correspondencia` bigint NOT NULL,
  `nombre_original` varchar(255) DEFAULT NULL,
  `ruta_storage` varchar(500) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `tamano` bigint DEFAULT NULL,
  `subido_por` int DEFAULT NULL,
  `subido_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_correspondencia` (`id_correspondencia`),
  KEY `subido_por` (`subido_por`),
  CONSTRAINT `corr_adjuntos_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  CONSTRAINT `corr_adjuntos_ibfk_2` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_adjuntos`
--

LOCK TABLES `corr_adjuntos` WRITE;
/*!40000 ALTER TABLE `corr_adjuntos` DISABLE KEYS */;
INSERT INTO `corr_adjuntos` VALUES (1,1,'solicitud_vacaciones.pdf','/storage/docs/solicitud_vacaciones.pdf','application/pdf',254000,4,'2025-09-13 15:18:35'),(2,2,'informe_gastos.xlsx','/storage/docs/informe_gastos.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',102400,3,'2025-09-13 15:18:35'),(3,3,'programa_capacitacion.pdf','/storage/docs/programa_capacitacion.pdf','application/pdf',300000,2,'2025-09-13 15:18:35');
/*!40000 ALTER TABLE `corr_adjuntos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_auditoria`
--

DROP TABLE IF EXISTS `corr_auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_auditoria` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `id_correspondencia` bigint DEFAULT NULL,
  `id_adjuntos` bigint DEFAULT NULL,
  `ip_origen` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `corr_auditoria_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_auditoria`
--

LOCK TABLES `corr_auditoria` WRITE;
/*!40000 ALTER TABLE `corr_auditoria` DISABLE KEYS */;
INSERT INTO `corr_auditoria` VALUES (1,4,'crear_correspondencia',1,NULL,'192.168.1.10','Mozilla/5.0','2025-09-13 15:18:36'),(2,3,'crear_correspondencia',2,NULL,'192.168.1.20','Mozilla/5.0','2025-09-13 15:18:36'),(3,2,'crear_correspondencia',3,NULL,'192.168.1.30','Mozilla/5.0','2025-09-13 15:18:36'),(4,1,'ver_correspondencia',2,NULL,'192.168.1.5','Mozilla/5.0','2025-09-13 15:18:36');
/*!40000 ALTER TABLE `corr_auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_comentarios`
--

DROP TABLE IF EXISTS `corr_comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_comentarios` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_correspondencia` bigint NOT NULL,
  `id_usuario` int NOT NULL,
  `comentario` text NOT NULL,
  `privado` tinyint(1) DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_correspondencia` (`id_correspondencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `corr_comentarios_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  CONSTRAINT `corr_comentarios_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_comentarios`
--

LOCK TABLES `corr_comentarios` WRITE;
/*!40000 ALTER TABLE `corr_comentarios` DISABLE KEYS */;
INSERT INTO `corr_comentarios` VALUES (1,1,1,'RRHH revisará la solicitud en los próximos días',1,'2025-09-13 15:18:36'),(2,2,1,'Se detectaron inconsistencias menores en el informe',0,'2025-09-13 15:18:36'),(3,3,4,'Confirmo asistencia a la capacitación',0,'2025-09-13 15:18:36');
/*!40000 ALTER TABLE `corr_comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_destinatarios`
--

DROP TABLE IF EXISTS `corr_destinatarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_destinatarios` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_correspondencia` bigint NOT NULL,
  `id_area` int DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `visto` tinyint(1) DEFAULT '0',
  `recibido_en` datetime DEFAULT NULL,
  `leido_en` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_correspondencia` (`id_correspondencia`),
  KEY `id_area` (`id_area`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `corr_destinatarios_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  CONSTRAINT `corr_destinatarios_ibfk_2` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id`),
  CONSTRAINT `corr_destinatarios_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_destinatarios`
--

LOCK TABLES `corr_destinatarios` WRITE;
/*!40000 ALTER TABLE `corr_destinatarios` DISABLE KEYS */;
INSERT INTO `corr_destinatarios` VALUES (1,1,1,NULL,0,NULL,NULL),(2,2,1,NULL,0,NULL,NULL),(3,2,4,1,0,NULL,NULL),(4,3,NULL,4,0,NULL,NULL),(5,3,3,NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `corr_destinatarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_estados`
--

DROP TABLE IF EXISTS `corr_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_estados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_estados`
--

LOCK TABLES `corr_estados` WRITE;
/*!40000 ALTER TABLE `corr_estados` DISABLE KEYS */;
INSERT INTO `corr_estados` VALUES (1,'Pendiente'),(2,'En revision'),(3,'Aprobado'),(4,'Rechazado'),(5,'Archivado'),(6,'Observado'),(7,'Derivado');
/*!40000 ALTER TABLE `corr_estados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_historial`
--

DROP TABLE IF EXISTS `corr_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_historial` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_correspondencia` bigint NOT NULL,
  `id_usuario` int NOT NULL,
  `accion` varchar(100) NOT NULL,
  `comentario` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `datos_json` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_correspondencia` (`id_correspondencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `corr_historial_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  CONSTRAINT `corr_historial_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_historial`
--

LOCK TABLES `corr_historial` WRITE;
/*!40000 ALTER TABLE `corr_historial` DISABLE KEYS */;
INSERT INTO `corr_historial` VALUES (1,1,4,'Enviado','Solicitud enviada a RRHH','2025-09-13 15:18:36',NULL),(2,2,3,'Enviado','Informe enviado a RRHH y Dirección','2025-09-13 15:18:36',NULL),(3,2,1,'Revisado','Informe revisado por Dirección','2025-09-13 15:18:36',NULL),(4,3,2,'Enviado','Memorando de capacitación enviado a todos','2025-09-13 15:18:36',NULL),(5,3,4,'Recibido','Empleado Ana López recibió el memorando','2025-09-13 15:18:36',NULL);
/*!40000 ALTER TABLE `corr_historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_notificaciones`
--

DROP TABLE IF EXISTS `corr_notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_notificaciones` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `id_correspondencia` bigint DEFAULT NULL,
  `id_usuario` int DEFAULT NULL,
  `tipo` enum('email','inapp') DEFAULT 'inapp',
  `mensaje` text,
  `leido` tinyint(1) DEFAULT '0',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_correspondencia` (`id_correspondencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `corr_notificaciones_ibfk_1` FOREIGN KEY (`id_correspondencia`) REFERENCES `correspondencia` (`id`),
  CONSTRAINT `corr_notificaciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_notificaciones`
--

LOCK TABLES `corr_notificaciones` WRITE;
/*!40000 ALTER TABLE `corr_notificaciones` DISABLE KEYS */;
INSERT INTO `corr_notificaciones` VALUES (1,1,2,'inapp','Nueva correspondencia: Solicitud de vacaciones',0,'2025-09-13 15:18:35'),(2,2,2,'inapp','Nueva correspondencia: Informe de gastos',0,'2025-09-13 15:18:35'),(3,2,1,'inapp','Nueva correspondencia: Informe de gastos',0,'2025-09-13 15:18:35'),(4,3,4,'inapp','Nueva correspondencia: Capacitación obligatoria',0,'2025-09-13 15:18:35'),(5,3,3,'inapp','Nueva correspondencia: Capacitación obligatoria',0,'2025-09-13 15:18:35'),(6,3,5,'inapp','Nueva correspondencia: Capacitación obligatoria',0,'2025-09-13 15:18:35'),(8,1,1,'inapp','Nueva solicitud de vacaciones enviada',0,'2025-09-13 15:18:36'),(9,2,2,'email','Se ha recibido un nuevo informe de gastos',0,'2025-09-13 15:18:36'),(10,3,4,'inapp','Ha recibido un memorando de capacitación',0,'2025-09-13 15:18:36');
/*!40000 ALTER TABLE `corr_notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corr_tipos`
--

DROP TABLE IF EXISTS `corr_tipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corr_tipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corr_tipos`
--

LOCK TABLES `corr_tipos` WRITE;
/*!40000 ALTER TABLE `corr_tipos` DISABLE KEYS */;
INSERT INTO `corr_tipos` VALUES (1,'Oficio','Documento formal dirigido a un área o persona'),(2,'Memorando','Comunicación interna breve'),(3,'Solicitud','Solicitud de información o recurso'),(4,'Informe','Informe técnico o administrativo');
/*!40000 ALTER TABLE `corr_tipos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correspondencia`
--

DROP TABLE IF EXISTS `correspondencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `correspondencia` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `numero_externo` varchar(100) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `descripcion` text,
  `id_tipo` int DEFAULT NULL,
  `id_estado` int DEFAULT '1',
  `id_origen_usuario` int NOT NULL,
  `id_origen_area` int DEFAULT NULL,
  `fecha_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_limite` date DEFAULT NULL,
  `prioridad` enum('Baja','Normal','Alta') DEFAULT 'Normal',
  `eliminado_logico` tinyint(1) DEFAULT '0',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_tipo` (`id_tipo`),
  KEY `id_estado` (`id_estado`),
  KEY `id_origen_usuario` (`id_origen_usuario`),
  KEY `id_origen_area` (`id_origen_area`),
  CONSTRAINT `correspondencia_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `corr_tipos` (`id`),
  CONSTRAINT `correspondencia_ibfk_2` FOREIGN KEY (`id_estado`) REFERENCES `corr_estados` (`id`),
  CONSTRAINT `correspondencia_ibfk_3` FOREIGN KEY (`id_origen_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `correspondencia_ibfk_4` FOREIGN KEY (`id_origen_area`) REFERENCES `areas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `correspondencia`
--

LOCK TABLES `correspondencia` WRITE;
/*!40000 ALTER TABLE `correspondencia` DISABLE KEYS */;
INSERT INTO `correspondencia` VALUES (1,'OF-001-2025','Solicitud de vacaciones','Empleado solicita 10 días de vacaciones en octubre.',3,1,4,2,'2025-09-13 15:18:35',NULL,'Normal',0,'2025-09-13 15:18:35'),(2,'OF-002-2025','Informe de gastos','Se adjunta informe de gastos del mes pasado.',4,2,3,3,'2025-09-13 15:18:35',NULL,'Alta',0,'2025-09-13 15:18:35'),(3,'OF-003-2025','Capacitación obligatoria','Se comunica capacitación para todo el personal.',2,1,2,1,'2025-09-13 15:18:35',NULL,'Normal',0,'2025-09-13 15:18:35');
/*!40000 ALTER TABLE `correspondencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_area_destino` int DEFAULT NULL,
  `id_seccion` int DEFAULT NULL,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `feedback` text,
  `revisado_por` int DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `destino_area` varchar(100) DEFAULT 'secretaria',
  `enviado_a_area_id` int DEFAULT NULL,
  `area_solicitada_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `fk_area_destino` (`id_area_destino`),
  KEY `fk_documentos_seccion` (`id_seccion`),
  KEY `fk_revisor` (`revisado_por`),
  KEY `fk_area_solicitada` (`area_solicitada_id`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_area_destino` FOREIGN KEY (`id_area_destino`) REFERENCES `areas` (`id`),
  CONSTRAINT `fk_area_solicitada` FOREIGN KEY (`area_solicitada_id`) REFERENCES `areas` (`id`),
  CONSTRAINT `fk_documentos_seccion` FOREIGN KEY (`id_seccion`) REFERENCES `secciones_legajo` (`id`),
  CONSTRAINT `fk_revisor` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
INSERT INTO `documentos` VALUES (1,4,'Taller.pdf','1757806109_82f5fd1024f1.pdf','application/pdf','2025-09-13 18:28:29',2,NULL,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(2,5,'DIFERENCIAS ENTRE TEMA Y PROBLEMA.pdf','1757807077_2e71ecff4305.pdf','application/pdf','2025-09-13 18:44:37',2,NULL,'observado','no hay diferencias',NULL,NULL,'secretaria',NULL,NULL),(3,5,'PLAN DE TRABAJO.pdf','1757807564_45c51e1504e0.pdf','application/pdf','2025-09-13 18:52:44',1,NULL,'rechazado','sin plan',NULL,NULL,'secretaria',NULL,NULL),(4,5,'MONOGRAFIA_II(GRUPAL).pdf','1757960820_82f4bc2e86b6.pdf','application/pdf','2025-09-15 13:27:00',2,NULL,'rechazado','falta imagenes',NULL,NULL,'secretaria',NULL,NULL),(5,5,'mi dni.pdf','1757961225_mi dni.pdf','dni','2025-09-15 13:33:45',7,NULL,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(6,5,'mi certificado.pdf','1757961244_mi certificado.pdf','certificado','2025-09-15 13:34:04',7,NULL,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(7,4,'GOBIERNO_DIGITAL.pdf','1757961277_7d6f14a7ea07.pdf','application/pdf','2025-09-15 13:34:37',2,NULL,'rechazado','subelo nuevamente',NULL,NULL,'secretaria',NULL,NULL),(8,4,'mi dni.pdf','1757961291_mi dni.pdf','dni','2025-09-15 13:34:51',10,NULL,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(9,4,'mi certificado.pdf','1757961305_mi certificado.pdf','certificado','2025-09-15 13:35:05',2,NULL,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(10,4,'Figure_1.png','1767332123_v2_6957591ba35f2.png','DNI','2026-01-02 00:35:23',4,1,'Aprobado','',12,'2026-01-02 01:52:54','secretaria',NULL,NULL),(11,4,'mi certificado.pdf','1758064382_68c9eefece0db.pdf','Certificado','2025-09-16 18:13:02',4,3,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(12,4,'mi certificado.pdf','1758064428_68c9ef2cb3df5.pdf','Certificado','2025-09-16 18:13:48',3,3,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(13,4,'mi certificado.pdf','1758064515_68c9ef83ea5e6.pdf','Certificado','2025-09-16 18:15:15',4,3,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(14,4,'Reglamento de practicas 2012 Vigente.pdf','1767340085_4_695778358869f.pdf','pdf','2026-01-02 02:48:05',NULL,3,'Pendiente',NULL,NULL,NULL,'secretaria',NULL,NULL),(15,5,'mi dni.pdf','1758065183_68c9f21f42fd9.pdf','DNI','2025-09-16 18:26:23',3,1,'observado',NULL,NULL,NULL,'secretaria',NULL,NULL),(16,5,'mi certificado.pdf','1758065314_68c9f2a206284.pdf','Certificado','2025-09-16 18:28:34',3,3,'Aprobado','',12,'2026-01-02 03:01:07','secretaria',NULL,NULL),(17,3,'mi dni.pdf','1758067215_68c9fa0fe69da.pdf','DNI','2025-09-16 19:00:15',2,1,'Aprobado','',12,'2026-01-02 02:56:40','secretaria',NULL,NULL),(18,2,'mi dni.pdf','1758068687_68c9ffcfaaae6.pdf','DNI','2025-09-16 19:24:47',7,1,'revisado','mal formato jolsata24',12,'2025-11-03 13:45:49','secretaria',NULL,NULL),(19,4,'mina.jpg','doc_68ca0f5156c54.jpg','application/pdf','2025-09-16 19:54:07',3,NULL,'Aprobado','',12,'2026-01-02 02:57:45','secretaria',NULL,NULL),(20,4,'mi dni.pdf','1758578256_68d1c65082a70.pdf','carnet','2025-09-22 16:57:36',3,1,'Aprobado','',12,'2026-01-02 01:53:28','secretaria',NULL,NULL),(21,5,'Actividad grupal.docx','1759452341_bfed6787defa.docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-10-02 19:45:41',1,NULL,'revisado','Buen trabajo',1,'2025-10-02 23:18:13','secretaria',NULL,NULL),(22,5,'Parte_faltante(Torres Lucas Luis J.).docx','doc_68df4da290f06.docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document','2025-10-02 20:02:41',1,NULL,'Aprobado','',12,'2026-01-02 02:56:10','secretaria',NULL,NULL),(23,1,'Actividad domiciliaria.docx','1759728993_68e3556105f4d.docx','Compensancion','2025-10-06 00:36:33',NULL,6,'revisado',NULL,NULL,NULL,'secretaria',NULL,NULL),(24,1,'Mi dni.pdf','1759729180_68e3561c0c3d5.pdf','DNI','2025-10-06 00:39:40',NULL,1,'revisado',NULL,NULL,NULL,'secretaria',NULL,NULL),(25,14,'Demo_DREMH.docx','1759734286_68e36a0e4a7df_Demo_DREMH.docx','Solicitud de vacaciones','2025-10-06 02:04:46',4,NULL,'Aprobado','dmaknfoqebien',12,'2026-01-02 02:55:18','secretaria',NULL,NULL),(26,14,'Documentacion_Seguridad.docx','1759735819_68e3700b8e068_Documentacion_Seguridad.docx','Compensancion','2025-10-06 02:30:19',2,NULL,'revisado','gracias',12,'2025-10-06 02:31:20','secretaria',NULL,NULL),(27,13,'I am mogger cat #mogger #cat #cut #cat #aura #.heic','1759789022_68e43fde3ef4c_I am mogger cat #mogger #cat #cut #cat #aura #.heic','Compensancion','2025-10-06 17:13:34',2,NULL,'revisado','perfecto',12,'2025-10-06 17:17:41','secretaria',NULL,NULL),(28,4,'fondov1.png','1762448642_690cd503000f0.png','DNI','2025-11-06 12:04:03',4,1,'Aprobado','',12,'2026-01-02 01:53:08','secretaria',NULL,NULL),(29,4,'500354059_1059953622866296_3453023037699482230_n.jpg','1762449481_690cd8499e80d_500354059_1059953622866296_3453023037699482230_n.jpg','Imagen','2025-11-06 12:08:38',7,NULL,'Aprobado','',12,'2026-01-02 01:52:42','secretaria',NULL,NULL),(30,4,'c.png','1762449327_690cd7afb7e22.png','Imagen2025','2025-11-06 12:15:27',1,1,'Aprobado','muy bien compañero',12,'2026-01-02 01:52:59','secretaria',NULL,NULL),(31,4,'Documento.pdf','1767336418_4_695769e2bb963.pdf','pdf','2026-01-02 01:46:58',NULL,6,'Pendiente',NULL,NULL,NULL,'secretaria',NULL,NULL),(32,4,'Documento.pdf','1767336992_69576c205c5d9.pdf','pdf','2026-01-02 01:56:32',NULL,NULL,'Pendiente',NULL,NULL,NULL,'secretaria',NULL,NULL),(33,4,'Documento.pdf','1767337361_4_69576d919c7c4.pdf','pdf','2026-01-02 02:02:41',NULL,NULL,'Pendiente',NULL,NULL,NULL,'secretaria',NULL,NULL),(34,4,'Documento.pdf','1767337764_4_69576f24e7111.pdf','pdf','2026-01-02 02:09:24',NULL,NULL,'Pendiente',NULL,NULL,NULL,'secretaria',NULL,NULL),(35,4,'Documento.pdf','1767337859_4_69576f8366dea.pdf','pdf','2026-01-02 02:10:59',8,NULL,'Aprobado','',12,'2026-01-02 02:11:08','secretaria',NULL,NULL),(36,4,'Documento.pdf','1767337980_4_69576ffcd9c1c.pdf','pdf','2026-01-02 02:13:00',4,NULL,'Aprobado','',12,'2026-01-02 02:13:14','secretaria',NULL,NULL),(37,4,'Documento.pdf','1767338266_4_6957711ac78ec.pdf','pdf','2026-01-02 02:17:46',3,NULL,'Aprobado','',12,'2026-01-02 02:17:53','secretaria',NULL,NULL),(38,4,'Documento.pdf','1767340891_4_69577b5b2cf2b.pdf','pdf','2026-01-02 03:01:31',7,NULL,'Aprobado','',12,'2026-01-02 03:01:44','secretaria',NULL,NULL);
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos_historial`
--

DROP TABLE IF EXISTS `documentos_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos_historial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_documento` int NOT NULL,
  `id_usuario_accion` int DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `descripcion` text,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_documento` (`id_documento`),
  KEY `id_usuario_accion` (`id_usuario_accion`),
  CONSTRAINT `documentos_historial_ibfk_1` FOREIGN KEY (`id_documento`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documentos_historial_ibfk_2` FOREIGN KEY (`id_usuario_accion`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos_historial`
--

LOCK TABLES `documentos_historial` WRITE;
/*!40000 ALTER TABLE `documentos_historial` DISABLE KEYS */;
INSERT INTO `documentos_historial` VALUES (1,25,14,'CREADO','El empleado ha subido el documento para revisión de Secretaría.','2025-10-06 02:04:46'),(2,25,12,'ASIGNADO','Documento asignado al área \'Dirección\' por Secretaría.','2025-10-06 02:05:35'),(3,25,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'RECHAZADO\' por Maria Perez. Feedback: \'mal formato jolsata24 manda manda\'','2025-10-06 02:06:01'),(4,25,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'bien\'','2025-10-06 02:09:24'),(5,26,14,'CREADO','El empleado ha subido el documento para revisión de Secretaría.','2025-10-06 02:30:19'),(6,26,12,'ASIGNADO','Documento asignado al área \'Contabilidad\' por Secretaría.','2025-10-06 02:30:51'),(7,26,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'gracias\'','2025-10-06 02:31:20'),(8,1,12,'ASIGNADO','Documento asignado al área \'Contabilidad\' por Secretaría.','2025-10-06 17:11:43'),(9,5,12,'ASIGNADO','Documento asignado al área \'Asuntos Ambientales Mineros\' por Secretaría.','2025-10-06 17:11:45'),(10,6,12,'ASIGNADO','Documento asignado al área \'Asuntos Ambientales Mineros\' por Secretaría.','2025-10-06 17:11:47'),(11,8,12,'ASIGNADO','Documento asignado al área \'Asuntos Ambientales Mineros\' por Secretaría.','2025-10-06 17:11:49'),(12,9,12,'ASIGNADO','Documento asignado al área \'Contabilidad\' por Secretaría.','2025-10-06 17:11:51'),(13,10,12,'ASIGNADO','Documento asignado al área \'Dirección\' por Secretaría.','2025-10-06 17:11:52'),(14,11,12,'ASIGNADO','Documento asignado al área \'Dirección\' por Secretaría.','2025-10-06 17:11:54'),(15,13,12,'ASIGNADO','Documento asignado al área \'Dirección\' por Secretaría.','2025-10-06 17:11:57'),(16,12,12,'ASIGNADO','Documento asignado al área \'Logística\' por Secretaría.','2025-10-06 17:11:59'),(17,14,12,'ASIGNADO','Documento asignado al área \'Recursos Humanos\' por Secretaría.','2025-10-06 17:12:02'),(18,22,12,'ASIGNADO','Documento asignado al área \'Recursos Humanos\' por Secretaría.','2025-10-06 17:12:04'),(19,27,13,'CREADO','El empleado ha subido el documento para revisión de Secretaría.','2025-10-06 17:13:34'),(20,27,12,'ASIGNADO','Documento asignado al área \'Contabilidad\' por Secretaría.','2025-10-06 17:14:05'),(21,27,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'OBSERVADO\' por Maria Perez.','2025-10-06 17:15:54'),(22,27,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'RECHAZADO\' por Maria Perez. Feedback: \'Pasalo en otro formato\'','2025-10-06 17:16:07'),(23,27,13,'REEMPLAZADO','El empleado ha reemplazado el archivo del documento. Vuelve a estar pendiente de revisión.','2025-10-06 17:17:02'),(24,27,12,'ASIGNADO','Documento asignado al área \'Contabilidad\' por Secretaría.','2025-10-06 17:17:21'),(25,27,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'perfecto\'','2025-10-06 17:17:41'),(26,18,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'mal formato jolsata24\'','2025-11-03 13:45:49'),(27,29,4,'CREADO','El empleado ha subido el documento para revisión de Secretaría.','2025-11-06 12:08:38'),(28,29,12,'ASIGNADO','Documento asignado al área \'Asuntos Ambientales Mineros\' por Secretaría.','2025-11-06 12:09:49'),(29,29,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'buen formato\'','2025-11-06 12:11:32'),(30,29,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'REVISADO\' por Maria Perez. Feedback: \'buen formato\'','2025-11-06 12:11:34'),(31,29,12,'CAMBIO_ESTADO','El estado del documento fue cambiado a \'RECHAZADO\' por Maria Perez. Feedback: \'falta contenido\'','2025-11-06 12:16:50'),(32,29,4,'REEMPLAZADO','El empleado ha reemplazado el archivo del documento. Vuelve a estar pendiente de revisión.','2025-11-06 12:18:01'),(33,29,12,'ASIGNADO','Documento asignado al área \'Asuntos Ambientales Mineros\' por Secretaría.','2025-11-06 12:18:54'),(34,28,12,'ASIGNADO','Documento asignado al área \'Dirección\' por Secretaría.','2025-11-06 12:34:48'),(35,28,12,'DERIVACION','Documento derivado al área: Dirección','2026-01-01 23:23:37'),(36,30,12,'CAMBIO_ESTADO','Estado cambiado a RECHAZADO. Feedback: dwqdqw','2026-01-01 23:30:44'),(37,30,12,'CAMBIO_ESTADO','Estado cambiado a OBSERVADO. Feedback: dwqdqw','2026-01-01 23:30:49'),(38,30,12,'CAMBIO_ESTADO','Estado cambiado a RECHAZADO. Feedback: dwqdqw','2026-01-01 23:31:16'),(39,30,12,'DERIVACION','Derivado al área: Asuntos Ambientales Mineros','2026-01-01 23:34:40'),(40,30,12,'CAMBIO_ESTADO','Estado cambiado a VALIDADO. Feedback: dwqdqw','2026-01-01 23:39:58'),(41,30,12,'CAMBIO_ESTADO','Estado cambiado a APROBADO. Feedback: muy bien compañero','2026-01-01 23:40:16'),(42,30,12,'DERIVACION','Derivado al área: Recursos Humanos','2026-01-01 23:40:25'),(43,30,12,'CAMBIO_ESTADO','Estado cambiado a APROBADO. Feedback: muy bien compañero','2026-01-01 23:40:33'),(44,10,4,'REEMPLAZO','El usuario reemplazó el archivo (Corrección enviada).','2026-01-02 00:35:23'),(45,29,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Asuntos Ambientales Mineros','2026-01-02 01:52:42'),(46,10,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Dirección','2026-01-02 01:52:54'),(47,30,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Recursos Humanos. Feedback: muy bien compañero','2026-01-02 01:52:59'),(48,28,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Dirección','2026-01-02 01:53:08'),(49,20,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Logística','2026-01-02 01:53:28'),(50,32,12,'GESTION','Estado cambiado a OBSERVADO. Feedback: revisa el anexo 05','2026-01-02 01:55:15'),(51,32,4,'REEMPLAZADO','Archivo reemplazado por el empleado. Estado reiniciado a Pendiente.','2026-01-02 01:56:32'),(52,33,12,'GESTION','Estado cambiado a OBSERVADO. Feedback: revisa el añexo 05','2026-01-02 02:02:09'),(53,33,4,'REEMPLAZADO','Archivo corregido y reemplazado por el empleado.','2026-01-02 02:02:41'),(54,34,12,'GESTION','Estado cambiado a OBSERVADO. Feedback: anexo 05','2026-01-02 02:07:57'),(55,34,4,'REEMPLAZADO','Archivo corregido y reenviado por el empleado.','2026-01-02 02:09:24'),(56,35,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Fiscalización','2026-01-02 02:11:08'),(57,36,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Dirección','2026-01-02 02:13:14'),(58,37,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Logística','2026-01-02 02:17:53'),(59,14,12,'GESTION','Estado cambiado a OBSERVADO. Feedback: otro ´pls','2026-01-02 02:35:25'),(60,14,4,'REEMPLAZADO','Archivo corregido y reenviado por el empleado.','2026-01-02 02:45:37'),(61,14,12,'GESTION','Estado cambiado a OBSERVADO. Feedback: otro ´pls','2026-01-02 02:45:57'),(62,14,4,'REEMPLAZADO','Archivo corregido y reenviado por el empleado.','2026-01-02 02:48:05'),(63,25,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Dirección. Feedback: dmaknfoqebien','2026-01-02 02:55:18'),(64,22,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Recursos Humanos','2026-01-02 02:56:10'),(65,17,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Contabilidad','2026-01-02 02:56:40'),(66,19,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Logística','2026-01-02 02:57:45'),(67,16,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Logística','2026-01-02 03:01:07'),(68,38,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Asuntos Ambientales Mineros','2026-01-02 03:01:38'),(69,38,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Asuntos Ambientales Mineros','2026-01-02 03:01:42'),(70,38,12,'GESTION','Estado cambiado a APROBADO. Derivado a: Asuntos Ambientales Mineros','2026-01-02 03:01:44');
/*!40000 ALTER TABLE `documentos_historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `legajo_personal`
--

DROP TABLE IF EXISTS `legajo_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legajo_personal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `seccion` tinyint NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `legajo_personal_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legajo_personal`
--

LOCK TABLES `legajo_personal` WRITE;
/*!40000 ALTER TABLE `legajo_personal` DISABLE KEYS */;
/*!40000 ALTER TABLE `legajo_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` int NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `enlace` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario_destino` (`id_usuario_destino`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES (1,14,'Secretaría ha asignado tu documento al área \'Dirección\'.',1,'../empleado/mi_legajo.php','2025-10-06 02:05:35'),(2,14,'Tu documento ha sido actualizado al estado: \'RECHAZADO\' por Maria Perez.',1,'../empleado/mi_legajo.php','2025-10-06 02:06:01'),(3,14,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',1,'../empleado/mi_legajo.php','2025-10-06 02:09:24'),(4,14,'Secretaría ha asignado tu documento al área \'Contabilidad\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 02:30:51'),(5,14,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',0,'../empleado/ver_documento_enviado.php?id=26','2025-10-06 02:31:20'),(6,4,'Secretaría ha asignado tu documento al área \'Contabilidad\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:43'),(7,5,'Secretaría ha asignado tu documento al área \'Asuntos Ambientales Mineros\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:45'),(8,5,'Secretaría ha asignado tu documento al área \'Asuntos Ambientales Mineros\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:47'),(9,4,'Secretaría ha asignado tu documento al área \'Asuntos Ambientales Mineros\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:49'),(10,4,'Secretaría ha asignado tu documento al área \'Contabilidad\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:51'),(11,4,'Secretaría ha asignado tu documento al área \'Dirección\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:52'),(12,4,'Secretaría ha asignado tu documento al área \'Dirección\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:54'),(13,4,'Secretaría ha asignado tu documento al área \'Dirección\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:57'),(14,4,'Secretaría ha asignado tu documento al área \'Logística\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:11:59'),(15,3,'Has recibido un nuevo documento para revisar en tu área.',0,'../secretaria/secretaria_documentos_area.php?area_id=3','2025-10-06 17:11:59'),(16,4,'Secretaría ha asignado tu documento al área \'Recursos Humanos\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:12:02'),(17,5,'Secretaría ha asignado tu documento al área \'Recursos Humanos\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:12:04'),(18,13,'Secretaría ha asignado tu documento al área \'Contabilidad\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:14:05'),(19,13,'Tu documento ha sido actualizado al estado: \'OBSERVADO\' por Maria Perez.',0,'../empleado/ver_documento_enviado.php?id=27','2025-10-06 17:15:54'),(20,13,'Tu documento ha sido actualizado al estado: \'RECHAZADO\' por Maria Perez.',0,'../empleado/ver_documento_enviado.php?id=27','2025-10-06 17:16:07'),(21,12,'Un empleado ha actualizado un documento que requiere tu revisión.',0,'../secretaria/secretaria_documentos.php','2025-10-06 17:17:02'),(22,13,'Secretaría ha asignado tu documento al área \'Contabilidad\'.',0,'../empleado/ver_documento_enviado.php?id=','2025-10-06 17:17:21'),(23,13,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',0,'../empleado/ver_documento_enviado.php?id=27','2025-10-06 17:17:41'),(24,2,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',0,'../empleado/ver_documento_enviado.php?id=18','2025-11-03 13:45:49'),(25,4,'Secretaría ha asignado tu documento al área \'Asuntos Ambientales Mineros\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-11-06 12:09:49'),(26,4,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',1,'../empleado/ver_documento_enviado.php?id=29','2025-11-06 12:11:32'),(27,4,'Tu documento ha sido actualizado al estado: \'REVISADO\' por Maria Perez.',1,'../empleado/ver_documento_enviado.php?id=29','2025-11-06 12:11:34'),(28,4,'Tu documento ha sido actualizado al estado: \'RECHAZADO\' por Maria Perez.',1,'../empleado/ver_documento_enviado.php?id=29','2025-11-06 12:16:50'),(29,12,'Un empleado ha actualizado un documento que requiere tu revisión.',0,'../secretaria/secretaria_documentos.php','2025-11-06 12:18:01'),(30,4,'Secretaría ha asignado tu documento al área \'Asuntos Ambientales Mineros\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-11-06 12:18:54'),(31,4,'Secretaría ha asignado tu documento al área \'Dirección\'.',1,'../empleado/ver_documento_enviado.php?id=','2025-11-06 12:34:48'),(32,4,'⚠️ Tu documento ha sido RECHAZADO. Por favor revisa el feedback.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:30:44'),(33,4,'⚠️ Tu documento ha sido OBSERVADO. Por favor revisa el feedback.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:30:49'),(34,4,'⚠️ Tu documento ha sido RECHAZADO. Por favor revisa el feedback.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:31:16'),(35,4,'? Tu documento ha sido derivado al área de Asuntos Ambientales Mineros.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:34:40'),(36,4,'⚠️ Tu documento ha sido VALIDADO. Por favor revisa el feedback.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:39:58'),(37,4,'✅ Tu documento ha sido APROBADO correctamente por Secretaría.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:40:16'),(38,4,'? Tu documento ha sido derivado al área de Recursos Humanos.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:40:25'),(39,4,'✅ Tu documento ha sido APROBADO correctamente por Secretaría.',1,'../empleado/ver_documento_enviado.php?id=30','2026-01-01 23:40:33'),(40,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Asuntos Ambientales Mineros</strong>.',0,'../empleado/ver_documento_enviado.php?id=29','2026-01-02 01:52:42'),(41,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Dirección</strong>.',0,'../empleado/ver_documento_enviado.php?id=10','2026-01-02 01:52:54'),(42,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Recursos Humanos</strong>.',0,'../empleado/ver_documento_enviado.php?id=30','2026-01-02 01:52:59'),(43,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Dirección</strong>.',0,'../empleado/ver_documento_enviado.php?id=28','2026-01-02 01:53:08'),(44,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Logística</strong>.',0,'../empleado/ver_documento_enviado.php?id=20','2026-01-02 01:53:28'),(45,4,'⚠️ Tu documento ha sido OBSERVADO.',1,'../empleado/ver_documento_enviado.php?id=32','2026-01-02 01:55:15'),(46,12,'Corrección enviada: Documento.pdf',0,'../secretaria/ver_documento.php?id=32','2026-01-02 01:56:32'),(47,4,'⚠️ Tu documento ha sido OBSERVADO.',0,'../empleado/ver_documento_enviado.php?id=33','2026-01-02 02:02:09'),(48,12,'Corrección recibida: Documento.pdf',0,'../secretaria/ver_documento.php?id=33','2026-01-02 02:02:41'),(49,4,'⚠️ Tu documento ha sido OBSERVADO.',0,'../empleado/ver_documento_enviado.php?id=34','2026-01-02 02:07:57'),(50,12,'Corrección recibida: Documento.pdf',0,'../secretaria/ver_documento.php?id=34','2026-01-02 02:09:24'),(51,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Fiscalización</strong>.',1,'../empleado/ver_documento_enviado.php?id=35','2026-01-02 02:11:08'),(52,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Dirección</strong>.',0,'../empleado/ver_documento_enviado.php?id=36','2026-01-02 02:13:14'),(53,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Logística</strong>.',0,'../empleado/ver_documento_enviado.php?id=37','2026-01-02 02:17:53'),(54,4,'⚠️ Tu documento ha sido OBSERVADO.',0,'../empleado/ver_documento_enviado.php?id=14','2026-01-02 02:35:25'),(55,12,'Corrección recibida: Documento.pdf',0,'../secretaria/ver_documento.php?id=14','2026-01-02 02:45:37'),(56,4,'⚠️ Tu documento ha sido OBSERVADO.',0,'../empleado/ver_documento_enviado.php?id=14','2026-01-02 02:45:57'),(57,12,'Corrección recibida: Reglamento de practicas 2012 Vigente.pdf',0,'../secretaria/ver_documento.php?id=14','2026-01-02 02:48:05'),(58,14,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Dirección</strong>.',0,'../empleado/ver_documento_enviado.php?id=25','2026-01-02 02:55:18'),(59,5,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Recursos Humanos</strong>.',0,'../empleado/ver_documento_enviado.php?id=22','2026-01-02 02:56:10'),(60,3,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Contabilidad</strong>.',0,'../empleado/ver_documento_enviado.php?id=17','2026-01-02 02:56:40'),(61,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Logística</strong>.',0,'../empleado/ver_documento_enviado.php?id=19','2026-01-02 02:57:45'),(62,5,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Logística</strong>.',0,'../empleado/ver_documento_enviado.php?id=16','2026-01-02 03:01:07'),(63,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Asuntos Ambientales Mineros</strong>.',0,'../empleado/ver_documento_enviado.php?id=38','2026-01-02 03:01:38'),(64,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Asuntos Ambientales Mineros</strong>.',0,'../empleado/ver_documento_enviado.php?id=38','2026-01-02 03:01:42'),(65,4,'✅ Tu documento ha sido APROBADO y ha sido enviado al área de: <strong>Asuntos Ambientales Mineros</strong>.',0,'../empleado/ver_documento_enviado.php?id=38','2026-01-02 03:01:44');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `secciones_legajo`
--

DROP TABLE IF EXISTS `secciones_legajo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `secciones_legajo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `secciones_legajo`
--

LOCK TABLES `secciones_legajo` WRITE;
/*!40000 ALTER TABLE `secciones_legajo` DISABLE KEYS */;
INSERT INTO `secciones_legajo` VALUES (1,'Información personal y familiar','Datos personales, familiares y de contacto del trabajador'),(2,'Incorporación','Documentos relacionados con la contratación e ingreso a la empresa'),(3,'Formación académica y capacitación','Estudios, títulos, cursos y capacitaciones'),(4,'Experiencia Laboral','Experiencia laboral previa o interna'),(5,'Movimientos de personal','Traslados, ascensos y cambios internos'),(6,'Compensaciones','Sueldos, beneficios e incentivos'),(7,'Evaluación de desempeño','Evaluaciones, progresión de carrera y desplazamientos'),(8,'Reconocimientos y sanciones disciplinarias','Premios, méritos o sanciones aplicadas'),(9,'Relaciones laborales','Acuerdos laborales individuales y colectivos'),(10,'SST y Bienestar Social','Documentos de seguridad, salud ocupacional y bienestar social'),(11,'Desvinculación','Documentos relacionados a la salida de la empresa'),(12,'Otros','Cualquier otro documento no categorizado');
/*!40000 ALTER TABLE `secciones_legajo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `id_area` int DEFAULT NULL,
  `rol` enum('admin','rrhh','jefe_area','empleado','secretaria') DEFAULT 'empleado',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT 'uploads/usuarios/default.png',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `id_area` (`id_area`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador General','admin@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',4,'admin',1,'2025-09-13 15:18:35','user_1_1766375971.png',NULL,NULL),(2,'María Pérez','mperez@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',1,'rrhh',1,'2025-09-13 15:18:35','uploads/usuarios/default.png',NULL,NULL),(3,'Juan Torres','jtorres@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',3,'jefe_area',1,'2025-09-13 15:18:35','uploads/usuarios/default.png',NULL,NULL),(4,'Ana López','alopez@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',2,'empleado',1,'2025-09-13 15:18:35','user_4_1762448561.png',NULL,NULL),(5,'Carlos Ramos','cramos@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',3,'empleado',1,'2025-09-13 15:18:35','user_5_1759785271.png',NULL,NULL),(12,'Maria Perez','secretaria@dremh.gob.pe','$2y$10$4labOhYPrECEBamNxsYABemdmzqAVuAR4KPjR9Dbd3mAB3ZBZDxYK',1,'secretaria',1,'2025-10-01 10:18:28','user_12_1762448675.JPG',NULL,NULL),(13,'Luis Josue','jolsata@gmail.com','$2y$10$dp6f2HqWTTCNpZUkpiPCHu.mB4bLOQtkUTzHo/Zf/nfj3NvpbNXCW',3,'empleado',1,'2025-10-06 00:49:09','user_13_1759785311.jpg',NULL,NULL),(14,'Jolsata24','2144403228@undac.edu.pe','$2y$10$XK6VXA2e0nA1onq4wPPJS.2Sa9iZmVki.7yanwZsczEYe1gvAbgOS',2,'empleado',1,'2025-10-06 01:16:24','user_1759731384_68e35eb854486.png',NULL,NULL),(17,'Luis Josue','jolsata24@gmail.com','$2y$10$TbZ17GLkH1Rl9M02hrwMUeYBCjxDWAt6.xx2CwRu47XwzJmJjhVya',8,'empleado',1,'2025-10-06 17:49:00',NULL,NULL,NULL),(20,'Luis Josue','turnitinexperthelper@gmail.com','$2y$10$Xuj7iIvA.1WlhSxzdf7lTuOv9Ip9UKGElnxXXuTqrLx6vfrRWqyDm',6,'jefe_area',1,'2025-10-06 18:03:43','uploads/usuarios/default.png',NULL,NULL),(21,'Hazle','Hazler@gmail.com','$2y$10$xur6tAWxjgSS.n8EZ7RmzuZVmLG9zFguCIMVzRWyvR1JKM.AaDC5K',7,'empleado',1,'2026-01-01 22:42:12',NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-02  3:08:34
