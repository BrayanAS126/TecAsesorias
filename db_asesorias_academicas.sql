/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: db_asesorias_academicas
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos` (
  `id_alumno` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `semestre` tinyint(3) unsigned NOT NULL,
  `no_control` varchar(20) NOT NULL,
  `genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_alumno`),
  UNIQUE KEY `idx_usuario_alumno` (`id_usuario`),
  UNIQUE KEY `idx_no_control` (`no_control`),
  CONSTRAINT `fk_alumno_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES
(1,5,'Brandon Asesor',5,'ITIC2025001','Masculino','Ingeniería en Tecnologías de la Información y Comunicaciones','2000-01-01','555-1234567');
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `asesores`
--

DROP TABLE IF EXISTS `asesores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asesores` (
  `id_asesor` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` int(10) unsigned NOT NULL,
  `validado` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_compensacion` enum('creditos','servicio_social') NOT NULL,
  `progreso` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_asesor`),
  UNIQUE KEY `idx_asesor_alumno` (`id_alumno`),
  CONSTRAINT `fk_asesor_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asesores`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `asesores` WRITE;
/*!40000 ALTER TABLE `asesores` DISABLE KEYS */;
INSERT INTO `asesores` VALUES
(1,1,1,'creditos',0.00);
/*!40000 ALTER TABLE `asesores` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_asesores_insert
AFTER INSERT ON asesores
FOR EACH ROW
BEGIN
    UPDATE usuarios u
    JOIN alumnos a ON a.id_usuario = u.id_usuario
    SET u.tipo_usuario = 'Asesor'
    WHERE a.id_alumno = NEW.id_alumno
      AND u.tipo_usuario = 'Alumno';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER after_asesores_delete
AFTER DELETE ON asesores
FOR EACH ROW
BEGIN
    UPDATE usuarios u
    JOIN alumnos a ON a.id_usuario = u.id_usuario
    SET u.tipo_usuario = 'Alumno'
    WHERE a.id_alumno = OLD.id_alumno
      AND u.tipo_usuario = 'Asesor';
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `asesores_materias`
--

DROP TABLE IF EXISTS `asesores_materias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asesores_materias` (
  `id_asesor` int(10) unsigned NOT NULL,
  `id_materia` int(10) unsigned NOT NULL,
  `tema` varchar(255) NOT NULL,
  PRIMARY KEY (`id_asesor`,`id_materia`),
  KEY `fk_am_materia` (`id_materia`),
  CONSTRAINT `fk_am_asesor` FOREIGN KEY (`id_asesor`) REFERENCES `asesores` (`id_asesor`) ON DELETE CASCADE,
  CONSTRAINT `fk_am_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asesores_materias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `asesores_materias` WRITE;
/*!40000 ALTER TABLE `asesores_materias` DISABLE KEYS */;
INSERT INTO `asesores_materias` VALUES
(1,1,'Todos los temas de cálculo diferencial');
/*!40000 ALTER TABLE `asesores_materias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `disponibilidad_asesor`
--

DROP TABLE IF EXISTS `disponibilidad_asesor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `disponibilidad_asesor` (
  `id_disponibilidad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_asesor` int(10) unsigned NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `modalidad` enum('Presencial','En línea') NOT NULL DEFAULT 'Presencial',
  `id_salon` int(10) unsigned DEFAULT NULL,
  `enlace_virtual` varchar(255) DEFAULT NULL,
  `cupo` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_disponibilidad`),
  UNIQUE KEY `idx_asesor_disp` (`id_asesor`,`id_disponibilidad`),
  KEY `idx_busqueda_dia` (`dia_semana`),
  KEY `idx_modalidad_disp` (`modalidad`),
  KEY `fk_disp_salon` (`id_salon`),
  CONSTRAINT `fk_disp_asesor` FOREIGN KEY (`id_asesor`) REFERENCES `asesores` (`id_asesor`) ON DELETE CASCADE,
  CONSTRAINT `fk_disp_salon` FOREIGN KEY (`id_salon`) REFERENCES `salones` (`id_salon`),
  CONSTRAINT `chk_horario` CHECK (`hora_inicio` < `hora_fin`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disponibilidad_asesor`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `disponibilidad_asesor` WRITE;
/*!40000 ALTER TABLE `disponibilidad_asesor` DISABLE KEYS */;
INSERT INTO `disponibilidad_asesor` VALUES
(1,1,'Lunes','13:00:00','14:00:00','Presencial',1,NULL,1),
(2,1,'Miércoles','13:00:00','14:00:00','Presencial',1,NULL,1),
(3,1,'Viernes','13:00:00','14:00:00','Presencial',1,NULL,1);
/*!40000 ALTER TABLE `disponibilidad_asesor` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `docentes`
--

DROP TABLE IF EXISTS `docentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `docentes` (
  `id_docente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `horario` varchar(255) DEFAULT NULL,
  `id_materia_encargada` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_docente`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  KEY `fk_docente_materia` (`id_materia_encargada`),
  CONSTRAINT `fk_docente_materia` FOREIGN KEY (`id_materia_encargada`) REFERENCES `materias` (`id_materia`),
  CONSTRAINT `fk_docente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docentes`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `docentes` WRITE;
/*!40000 ALTER TABLE `docentes` DISABLE KEYS */;
/*!40000 ALTER TABLE `docentes` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `historial_asesorias`
--

DROP TABLE IF EXISTS `historial_asesorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_asesorias` (
  `id_historial` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` int(10) unsigned NOT NULL,
  `id_asesor` int(10) unsigned NOT NULL,
  `id_materia` int(10) unsigned NOT NULL,
  `fecha_asesoria` date NOT NULL,
  `estado_final` enum('Pendiente','Aceptada','Rechazada','Completada') NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_asesorias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `historial_asesorias` WRITE;
/*!40000 ALTER TABLE `historial_asesorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `historial_asesorias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `materias`
--

DROP TABLE IF EXISTS `materias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `materias` (
  `id_materia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(20) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `semestre_materia` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id_materia`),
  UNIQUE KEY `idx_clave` (`clave`),
  KEY `idx_semestre_busqueda` (`semestre_materia`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `materias` WRITE;
/*!40000 ALTER TABLE `materias` DISABLE KEYS */;
INSERT INTO `materias` VALUES
(1,'ACF-0901','Cálculo diferencial',1),
(2,'AEF-1032','Fundamento de programación',1),
(3,'TIF-1019','Matemáticas discretas I',1),
(4,'ACA-1017','Taller de ética',1),
(5,'ACC-0906','Fundamentos de investigación',1),
(6,'TIP-1017','Introducción a las TIC´s',1),
(7,'ACF-0902','Cálculo integral',2),
(8,'AEB-1054','Programación orientada a objetos',2),
(9,'TIF-1020','Matemáticas discretas II',2),
(10,'ACF-09038','Álgebra lineal',2),
(11,'AEF-1052','Probabilidad y estadística',2),
(12,'TIF-1009','Contabilidad y costos',2),
(13,'ACA-0909','Taller de investigación I',2),
(14,'TID-1012','Estructuras y organización de datos',3),
(15,'TIF-1021','Matemáticas para toma de decisiones',3),
(16,'AEF-1031','Fundamentos de bases de datos',3),
(17,'TIC-1011','Electricidad y magnetismo',3),
(18,'TIC-1002','Administración gerencial',3),
(19,'TIE-1018','Matemáticas aplicadas a comunicaciones',3),
(20,'TIB-1024','Programación II',4),
(21,'TIF-1013','Fundamentos de redes',4),
(22,'AEA-1063','Taller de base de datos',4),
(23,'TID-1008','Circuitos eléctrico y electrónicos',4),
(24,'TIC-1014','Ingeniería de Software',4),
(25,'TID-1004','Análisis de señales y sistemas de comunicación',4),
(26,'TIF-1001','Administración de proyectos',5),
(27,'TIF-1025','Redes de computadoras',5),
(28,'TIF-1007','Base de datos distribuidas',5),
(29,'TIC-1010','Arquitectura de computadoras',5),
(30,'TIC-1027','Taller de ingeniería de Software',5),
(31,'TIF-1030','Telecomunicaciones',5),
(32,'TID-1010','Desarrollo e emprendedores',6),
(33,'AEB-1055','Programación web',6),
(34,'AEC-1061','Sistemas operativos I',6),
(35,'ACD-0908','Desarrollo sustentable',6),
(36,'TIC-1028','Tecnologías inalámbricas',6),
(37,'ACA-0910','Taller de investigación II',6),
(38,'AEB-1011','Desarrollo de aplicaciones para dispositivos móviles',7),
(39,'TIC-1022','Negocios electrónicos I',7),
(40,'AED-1062','Sistemas operativos II',7),
(41,'TIF-1026','Redes emergentes',7),
(42,'TIH-1004','Interacción humano computadora',7),
(43,'TIB-1003','Administración y seguridad de redes',7),
(44,'TIC-1015','Ingeniería del conocimiento',8),
(45,'TIC-1023','Negocios electrónicos II',8),
(46,'TIC-1006','Auditoría en tecnologías de la información',8);
/*!40000 ALTER TABLE `materias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `tipo_evento` enum('Solicitud','Aceptación','Rechazo','Recordatorio') NOT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `fk_notif_usuario` (`id_usuario`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `salones`
--

DROP TABLE IF EXISTS `salones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `salones` (
  `id_salon` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_salon` varchar(50) NOT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `capacidad` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_salon`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salones`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `salones` WRITE;
/*!40000 ALTER TABLE `salones` DISABLE KEYS */;
INSERT INTO `salones` VALUES
(1,'q101','Tomas Aquino',10);
/*!40000 ALTER TABLE `salones` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `solicitudes_asesoria`
--

DROP TABLE IF EXISTS `solicitudes_asesoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_asesoria` (
  `id_solicitud` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_alumno` int(10) unsigned NOT NULL,
  `id_asesor` int(10) unsigned NOT NULL,
  `id_materia` int(10) unsigned NOT NULL,
  `id_disponibilidad` int(10) unsigned NOT NULL,
  `fecha_asesoria` date NOT NULL,
  `estado` enum('Pendiente','Aceptada','Rechazada','Completada','Cancelada') NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `razon_cancelacion` text DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  UNIQUE KEY `idx_doble_reservacion` (`id_alumno`,`fecha_asesoria`,`id_disponibilidad`),
  KEY `idx_fecha_asesoria` (`fecha_asesoria`),
  KEY `fk_sol_materia` (`id_materia`),
  KEY `fk_sol_disp` (`id_disponibilidad`),
  KEY `fk_sol_asesor_materia` (`id_asesor`,`id_materia`),
  KEY `fk_sol_asesor_disp` (`id_asesor`,`id_disponibilidad`),
  CONSTRAINT `fk_sol_alumno` FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`),
  CONSTRAINT `fk_sol_asesor` FOREIGN KEY (`id_asesor`) REFERENCES `asesores` (`id_asesor`),
  CONSTRAINT `fk_sol_asesor_disp` FOREIGN KEY (`id_asesor`, `id_disponibilidad`) REFERENCES `disponibilidad_asesor` (`id_asesor`, `id_disponibilidad`),
  CONSTRAINT `fk_sol_asesor_materia` FOREIGN KEY (`id_asesor`, `id_materia`) REFERENCES `asesores_materias` (`id_asesor`, `id_materia`),
  CONSTRAINT `fk_sol_disp` FOREIGN KEY (`id_disponibilidad`) REFERENCES `disponibilidad_asesor` (`id_disponibilidad`),
  CONSTRAINT `fk_sol_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_asesoria`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `solicitudes_asesoria` WRITE;
/*!40000 ALTER TABLE `solicitudes_asesoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_asesoria` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `correo_institucional` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `tipo_usuario` enum('Alumno','Asesor','Administrador','Docente') NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `idx_correo_unico` (`correo_institucional`),
  KEY `idx_tipo_usuario` (`tipo_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES
(2,'admin@tectijuana.edu.mx','$2y$12$N7Pn7YXaw46sdMvPPVfKjec5M.ZFl31TN13QDo4MKyIFgz2WZ9pPK','Administrador','2026-05-21 17:09:36'),
(3,'asesor@tectijuana.edu.mx','$2y$12$t1WvpdCaX7iXDgABWETh9u.iZjZlCfLnVm37TUeSFwN7873w7l3vG','Asesor','2026-05-21 17:09:37'),
(4,'alumno@tectijuana.edu.mx','$2y$12$jthSupMAKHNUAegOPYl4q.Il3cdOD3NwbGaGKHX6hF3l7kVsZT4C2','Alumno','2026-05-21 17:09:37'),
(5,'brandon_asesor@tectijuana.edu.mx','$2y$12$u/kMn5nlVucDbnzK81gbDOAfhzHvB0bf54eLeDXgXqsdMMuViLlxm','Asesor','2026-05-23 13:25:59'),
(6,'docente@tectijuana.edu.mx','$2y$10$r1iknMLD5RcSDW7UX.kP1u8NpbmHtcsWb2nMVGOfRWUTezC7Xx/Zq','Docente','2026-05-25 12:00:00');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-23 13:43:52