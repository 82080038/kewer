-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: kewer
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `akun`
--

DROP TABLE IF EXISTS `akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `akun` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('aset','kewajiban','ekuitas','pendapatan','beban') NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `saldo_normal` enum('debit','kredit') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`kode`),
  KEY `idx_tipe` (`tipe`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akun`
--

LOCK TABLES `akun` WRITE;
/*!40000 ALTER TABLE `akun` DISABLE KEYS */;
INSERT INTO `akun` VALUES ('1-1001','Kas Pusat','aset','Kas','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-1002','Kas Cabang','aset','Kas','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-1003','Kas Petugas','aset','Kas','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-2001','Piutang Pinjaman','aset','Piutang','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-2002','Piutang Bunga','aset','Piutang','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-2003','Piutang Denda','aset','Piutang','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-3001','Perlengkapan Kantor','aset','Aset Tetap','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('1-3002','Kendaraan Operasional','aset','Aset Tetap','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('2-1001','Simpanan Nasabah','kewajiban','Simpanan','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('2-2001','Hutang Bank','kewajiban','Hutang','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('2-3001','Kas Bon Karyawan','kewajiban','Hutang','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('3-1001','Modal Pusat','ekuitas','Modal','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('3-2001','Laba Tahun Berjalan','ekuitas','Laba','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('3-2002','Laba Tahun Lalu','ekuitas','Laba','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('4-1001','Pendapatan Bunga Pinjaman','pendapatan','Bunga','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('4-1002','Pendapatan Denda Keterlambatan','pendapatan','Denda','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('4-1003','Pendapatan Jasa Administrasi','pendapatan','Jasa','kredit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('5-1001','Beban Bunga Bank','beban','Bunga','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('5-1002','Beban Operasional','beban','Operasional','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('5-1003','Beban Gaji Karyawan','beban','Gaji','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('5-1004','Beban Transportasi','beban','Transportasi','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30'),('5-1005','Beban Perlengkapan','beban','Perlengkapan','debit',1,'2026-04-28 06:34:30','2026-04-28 06:34:30');
/*!40000 ALTER TABLE `akun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `angsuran`
--

DROP TABLE IF EXISTS `angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `frekuensi` enum('harian','mingguan','bulanan') DEFAULT NULL,
  `no_angsuran` int(11) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `pokok` decimal(12,2) NOT NULL,
  `bunga` decimal(12,2) NOT NULL,
  `total_angsuran` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) DEFAULT 0.00,
  `status` enum('belum','lunas','telat','dibatalkan') NOT NULL DEFAULT 'belum',
  `tanggal_bayar` date DEFAULT NULL,
  `cara_bayar` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `denda_persentase` decimal(8,4) DEFAULT NULL COMMENT 'Persentase denda yang diterapkan',
  `denda_terhitung` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai denda yang dihitung otomatis',
  `denda_dibebaskan` decimal(12,2) DEFAULT NULL COMMENT 'Nilai denda yang di-waive',
  `denda_alasan` varchar(255) DEFAULT NULL COMMENT 'Alasan pembebasan denda',
  `denda_waived_by` int(11) DEFAULT NULL COMMENT 'User ID yang melakukan waive',
  `denda_waived_at` datetime DEFAULT NULL,
  `hari_telat_saat_bayar` int(11) DEFAULT NULL COMMENT 'Jumlah hari telat saat pembayaran dilakukan',
  `total_bayar_akhir` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total bayar = total_angsuran + denda_terhitung - denda_dibebaskan',
  PRIMARY KEY (`id`),
  KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  KEY `idx_angsuran_jatuh_tempo` (`jatuh_tempo`),
  KEY `idx_angsuran_pinjaman_status` (`pinjaman_id`,`status`),
  KEY `idx_angsuran_cabang_status` (`cabang_id`,`status`),
  KEY `idx_angsuran_frekuensi` (`frekuensi`),
  KEY `idx_angsuran_jatuh_tempo_status` (`jatuh_tempo`,`status`),
  CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
  CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `chk_angsuran_jatuh_tempo` CHECK (`jatuh_tempo` >= '2000-01-01')
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `angsuran`
--

LOCK TABLES `angsuran` WRITE;
/*!40000 ALTER TABLE `angsuran` DISABLE KEYS */;
INSERT INTO `angsuran` VALUES (1,16,5,'harian',1,'2026-05-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(2,16,5,'harian',2,'2026-06-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(3,16,5,'harian',3,'2026-07-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(4,16,5,'harian',4,'2026-08-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(5,16,5,'harian',5,'2026-09-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(6,16,5,'harian',6,'2026-10-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(7,16,5,'harian',7,'2026-11-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(8,16,5,'harian',8,'2026-12-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(9,16,5,'harian',9,'2027-01-30',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(10,16,5,'harian',10,'2027-02-28',200000.00,40000.00,240000.00,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(11,16,6,'mingguan',1,'2026-05-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(12,16,6,'mingguan',2,'2026-06-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(13,16,6,'mingguan',3,'2026-07-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(14,16,6,'mingguan',4,'2026-08-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(15,16,6,'mingguan',5,'2026-09-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(16,16,6,'mingguan',6,'2026-10-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(17,16,6,'mingguan',7,'2026-11-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(18,16,6,'mingguan',8,'2026-12-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(19,16,6,'mingguan',9,'2027-01-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(20,16,6,'mingguan',10,'2027-02-28',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(21,16,6,'mingguan',11,'2027-03-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(22,16,6,'mingguan',12,'2027-04-30',416666.67,75000.00,491666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(23,16,7,'bulanan',1,'2026-05-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(24,16,7,'bulanan',2,'2026-06-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(25,16,7,'bulanan',3,'2026-07-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(26,16,7,'bulanan',4,'2026-08-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(27,16,7,'bulanan',5,'2026-09-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(28,16,7,'bulanan',6,'2026-10-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(29,16,7,'bulanan',7,'2026-11-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(30,16,7,'bulanan',8,'2026-12-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(31,16,7,'bulanan',9,'2027-01-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(32,16,7,'bulanan',10,'2027-02-28',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(33,16,7,'bulanan',11,'2027-03-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(34,16,7,'bulanan',12,'2027-04-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(35,16,7,'bulanan',13,'2027-05-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(36,16,7,'bulanan',14,'2027-06-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(37,16,7,'bulanan',15,'2027-07-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(38,16,7,'bulanan',16,'2027-08-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(39,16,7,'bulanan',17,'2027-09-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(40,16,7,'bulanan',18,'2027-10-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(41,16,7,'bulanan',19,'2027-11-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(42,16,7,'bulanan',20,'2027-12-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(43,16,7,'bulanan',21,'2028-01-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(44,16,7,'bulanan',22,'2028-02-29',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(45,16,7,'bulanan',23,'2028-03-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00),(46,16,7,'bulanan',24,'2028-04-30',416666.67,100000.00,516666.67,0.00,0.00,'belum',NULL,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30',NULL,0.00,NULL,NULL,NULL,NULL,NULL,0.00);
/*!40000 ALTER TABLE `angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_log_user` (`user_id`),
  KEY `idx_audit_log_action` (`action`),
  KEY `idx_audit_log_table` (`table_name`),
  KEY `idx_audit_log_created` (`created_at`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_confirm_settings`
--

DROP TABLE IF EXISTS `auto_confirm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_confirm_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `plafon_threshold` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tenor_limit` int(11) NOT NULL DEFAULT 0,
  `max_risk_score` int(11) NOT NULL DEFAULT 10,
  `require_nasabah_history` tinyint(1) NOT NULL DEFAULT 1,
  `min_nasabah_history_months` int(11) NOT NULL DEFAULT 3,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_enabled` (`enabled`),
  CONSTRAINT `auto_confirm_settings_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auto_confirm_settings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auto_confirm_settings_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_confirm_settings`
--

LOCK TABLES `auto_confirm_settings` WRITE;
/*!40000 ALTER TABLE `auto_confirm_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_confirm_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_log`
--

DROP TABLE IF EXISTS `blacklist_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blacklist_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `aksi` enum('blacklist','unblacklist') NOT NULL,
  `alasan` text NOT NULL,
  `dilakukan_oleh` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_blacklist_nasabah` (`nasabah_id`),
  KEY `idx_blacklist_aksi` (`aksi`),
  KEY `blacklist_log_ibfk_2` (`dilakukan_oleh`),
  CONSTRAINT `blacklist_log_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blacklist_log_ibfk_2` FOREIGN KEY (`dilakukan_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blacklist_log`
--

LOCK TABLES `blacklist_log` WRITE;
/*!40000 ALTER TABLE `blacklist_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `blacklist_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bos_registrations`
--

DROP TABLE IF EXISTS `bos_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bos_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `nama_perusahaan` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_status` (`status`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bos_registrations`
--

LOCK TABLES `bos_registrations` WRITE;
/*!40000 ALTER TABLE `bos_registrations` DISABLE KEYS */;
INSERT INTO `bos_registrations` VALUES (4,'testbos','$2y$10$TRJ32ul3HlIfrBUDfNO09Ofs0KpbVbprsycgGB2BXkMmi1PP4ugla','Test Bos','testbos@example.com','08123456789','Test Perusahaan','Jalan Test No. 123, RT/RW 001/002',3,31,402,8197,'approved',NULL,'2026-04-28 12:10:04','2026-04-28 12:23:36',1,'2026-04-28 12:10:04','2026-04-28 12:23:36'),(5,'testbos1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Test Bos 1','testbos1@example.com','081234567891','PT Test Bos 1','Jalan Test 1, Jakarta',NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-29 02:55:01',NULL,NULL,'2026-04-29 02:55:01','2026-04-29 02:55:01'),(6,'testbos2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Test Bos 2','testbos2@example.com','081234567892','PT Test Bos 2','Jalan Test 2, Surabaya',NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-29 02:55:01',NULL,NULL,'2026-04-29 02:55:01','2026-04-29 02:55:01'),(7,'testbos3','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Test Bos 3','testbos3@example.com','081234567893','PT Test Bos 3','Jalan Test 3, Bandung',NULL,NULL,NULL,NULL,'pending',NULL,'2026-04-29 02:55:01',NULL,NULL,'2026-04-29 02:55:01','2026-04-29 02:55:01');
/*!40000 ALTER TABLE `bos_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branch_managers`
--

DROP TABLE IF EXISTS `branch_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branch_managers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `manager_user_id` int(11) NOT NULL,
  `manager_type` enum('manager_cabang','admin_cabang','petugas_cabang') NOT NULL,
  `appointed_by_bos_id` int(11) NOT NULL,
  `can_add_employees` tinyint(1) DEFAULT 1,
  `can_manage_branch` tinyint(1) DEFAULT 1,
  `appointed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cabang_id` (`cabang_id`),
  KEY `idx_manager_user_id` (`manager_user_id`),
  KEY `idx_appointed_by_bos_id` (`appointed_by_bos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branch_managers`
--

LOCK TABLES `branch_managers` WRITE;
/*!40000 ALTER TABLE `branch_managers` DISABLE KEYS */;
/*!40000 ALTER TABLE `branch_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_cabang` varchar(10) NOT NULL,
  `nama_cabang` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kota` varchar(50) DEFAULT NULL,
  `provinsi` varchar(50) DEFAULT NULL,
  `kode_pos` varchar(10) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `is_headquarters` tinyint(1) DEFAULT 0,
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_cabang` (`kode_cabang`),
  KEY `idx_owner_bos_id` (`owner_bos_id`),
  KEY `idx_created_by_user_id` (`created_by_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (16,'HQ001','Kantor Pusat','Jalan Pusat No. 1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'aktif',1,31,NULL,'2026-04-29 18:42:30','2026-04-29 18:47:34');
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consolidated_reports`
--

DROP TABLE IF EXISTS `consolidated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consolidated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` enum('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  `report_date` date NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_cabang` int(11) NOT NULL DEFAULT 0,
  `total_nasabah` int(11) NOT NULL DEFAULT 0,
  `total_pinjaman_aktif` int(11) NOT NULL DEFAULT 0,
  `total_pinjaman_lunas` int(11) NOT NULL DEFAULT 0,
  `total_plafon_aktif` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_angsuran_dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_penunggakan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_kas` decimal(15,2) NOT NULL DEFAULT 0.00,
  `generated_by` int(11) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `generated_by` (`generated_by`),
  KEY `idx_report_type_date` (`report_type`,`report_date`),
  KEY `idx_period` (`period_start`,`period_end`),
  CONSTRAINT `consolidated_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consolidated_reports`
--

LOCK TABLES `consolidated_reports` WRITE;
/*!40000 ALTER TABLE `consolidated_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `consolidated_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_cash_reconciliation`
--

DROP TABLE IF EXISTS `daily_cash_reconciliation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_cash_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kas_awal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_penerimaan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_pengeluaran` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kas_akhir` decimal(12,2) NOT NULL DEFAULT 0.00,
  `kas_fisik` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selisih` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `prepared_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cabang_tanggal` (`cabang_id`,`tanggal`),
  KEY `prepared_by` (`prepared_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `daily_cash_reconciliation_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_cash_reconciliation_ibfk_2` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `daily_cash_reconciliation_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_cash_reconciliation`
--

LOCK TABLES `daily_cash_reconciliation` WRITE;
/*!40000 ALTER TABLE `daily_cash_reconciliation` DISABLE KEYS */;
/*!40000 ALTER TABLE `daily_cash_reconciliation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delegated_permissions`
--

DROP TABLE IF EXISTS `delegated_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delegated_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegator_id` int(11) NOT NULL,
  `delegatee_id` int(11) NOT NULL,
  `permission_scope` enum('employee_crud','branch_crud','branch_employee_crud','all_operations') NOT NULL,
  `scope_limitation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scope_limitation`)),
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_delegator_id` (`delegator_id`),
  KEY `idx_delegatee_id` (`delegatee_id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delegated_permissions`
--

LOCK TABLES `delegated_permissions` WRITE;
/*!40000 ALTER TABLE `delegated_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `delegated_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `denda_settings`
--

DROP TABLE IF EXISTS `denda_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `denda_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `frekuensi` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'bulanan',
  `tipe_denda` enum('persentase','nominal') NOT NULL DEFAULT 'nominal',
  `nilai_denda` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Persentase dari angsuran ATAU nominal per hari',
  `grace_period` int(11) NOT NULL DEFAULT 0 COMMENT 'Hari toleransi sebelum denda dihitung',
  `denda_maksimal` decimal(12,2) DEFAULT NULL COMMENT 'Batas maksimal denda (null = tanpa batas)',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_denda_cabang` (`cabang_id`),
  KEY `idx_denda_frekuensi` (`frekuensi`),
  CONSTRAINT `denda_settings_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `denda_settings`
--

LOCK TABLES `denda_settings` WRITE;
/*!40000 ALTER TABLE `denda_settings` DISABLE KEYS */;
INSERT INTO `denda_settings` VALUES (1,NULL,'harian','nominal',1000.00,1,50000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42'),(2,NULL,'mingguan','nominal',5000.00,2,100000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42'),(3,NULL,'bulanan','persentase',2.00,3,500000.00,'aktif','2026-04-16 17:03:42','2026-04-16 17:03:42');
/*!40000 ALTER TABLE `denda_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_risk`
--

DROP TABLE IF EXISTS `family_risk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `nama_kepala_keluarga` varchar(100) NOT NULL,
  `alamat_keluarga` text NOT NULL,
  `tingkat_risiko` enum('rendah','sedang','tinggi','sangat_tinggi') NOT NULL DEFAULT 'rendah',
  `total_pinjaman_gagal` int(11) DEFAULT 0,
  `total_nasabah_bermasalah` int(11) DEFAULT 0,
  `tanggal_ditandai` date NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_family_risk_cabang` (`cabang_id`),
  KEY `idx_family_risk_risiko` (`tingkat_risiko`),
  KEY `idx_family_risk_alamat` (`alamat_keluarga`(255)),
  CONSTRAINT `family_risk_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_risk`
--

LOCK TABLES `family_risk` WRITE;
/*!40000 ALTER TABLE `family_risk` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_risk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `field_officer_activities`
--

DROP TABLE IF EXISTS `field_officer_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `field_officer_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `activity_type` enum('survey_nasabah','input_pinjaman','kutip_angsuran','follow_up','promosi','edukasi','lainnya') NOT NULL,
  `nasabah_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `activity_date` date NOT NULL,
  `activity_time` time NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cabang_id` (`cabang_id`),
  KEY `nasabah_id` (`nasabah_id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `idx_petugas_cabang` (`petugas_id`,`cabang_id`),
  KEY `idx_activity_date` (`activity_date`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `field_officer_activities_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_officer_activities_ibfk_2` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `field_officer_activities_ibfk_3` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE SET NULL,
  CONSTRAINT `field_officer_activities_ibfk_4` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE SET NULL,
  CONSTRAINT `field_officer_activities_ibfk_5` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `field_officer_activities`
--

LOCK TABLES `field_officer_activities` WRITE;
/*!40000 ALTER TABLE `field_officer_activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `field_officer_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_jurnal` varchar(50) NOT NULL,
  `tanggal_jurnal` date NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `cabang_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_tanggal` (`tanggal_jurnal`),
  KEY `idx_cabang` (`cabang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal`
--

LOCK TABLES `jurnal` WRITE;
/*!40000 ALTER TABLE `jurnal` DISABLE KEYS */;
/*!40000 ALTER TABLE `jurnal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_detail`
--

DROP TABLE IF EXISTS `jurnal_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jurnal_id` int(11) NOT NULL,
  `akun_kode` varchar(20) NOT NULL,
  `akun_nama` varchar(100) NOT NULL,
  `debit` decimal(20,2) DEFAULT 0.00,
  `kredit` decimal(20,2) DEFAULT 0.00,
  `referensi_tipe` varchar(50) DEFAULT NULL COMMENT 'pinjaman, angsuran, pembayaran, pengeluaran, kas, dll',
  `referensi_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_jurnal_id` (`jurnal_id`),
  KEY `idx_akun` (`akun_kode`),
  KEY `idx_referensi` (`referensi_tipe`,`referensi_id`),
  CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_detail`
--

LOCK TABLES `jurnal_detail` WRITE;
/*!40000 ALTER TABLE `jurnal_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `jurnal_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_bon`
--

DROP TABLE IF EXISTS `kas_bon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_bon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `kode_kasbon` varchar(20) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_pemberian` date DEFAULT NULL,
  `tanggal_potong` date DEFAULT NULL,
  `jumlah` decimal(12,0) NOT NULL,
  `tenor_bulan` int(11) DEFAULT 1,
  `potongan_per_bulan` decimal(12,0) DEFAULT 0,
  `potongan_ke` int(11) DEFAULT 0,
  `sisa_bon` decimal(12,0) DEFAULT 0,
  `tujuan` text DEFAULT NULL,
  `status` enum('pengajuan','disetujui','diberikan','dipotong','selesai','ditolak') DEFAULT 'pengajuan',
  `catatan` text DEFAULT NULL,
  `disetujui_oleh` int(11) DEFAULT NULL,
  `tanggal_disetujui` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_kasbon` (`kode_kasbon`),
  KEY `disetujui_oleh` (`disetujui_oleh`),
  KEY `idx_kasbon_cabang` (`cabang_id`),
  KEY `idx_kasbon_karyawan` (`karyawan_id`),
  KEY `idx_kasbon_status` (`status`),
  KEY `idx_kasbon_tanggal` (`tanggal_pengajuan`),
  CONSTRAINT `kas_bon_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_bon_ibfk_2` FOREIGN KEY (`karyawan_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_bon_ibfk_3` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_bon`
--

LOCK TABLES `kas_bon` WRITE;
/*!40000 ALTER TABLE `kas_bon` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_bon` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_kasbon_before_insert
BEFORE INSERT ON kas_bon
FOR EACH ROW
BEGIN
    SET NEW.potongan_ke = 0;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kas_bon_potongan`
--

DROP TABLE IF EXISTS `kas_bon_potongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_bon_potongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kas_bon_id` int(11) NOT NULL,
  `bulan_potong` varchar(7) NOT NULL,
  `jumlah_potong` decimal(12,0) NOT NULL,
  `tanggal_potong` date NOT NULL,
  `potong_oleh` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_potongan` (`kas_bon_id`,`bulan_potong`),
  KEY `potong_oleh` (`potong_oleh`),
  KEY `idx_potongan_kasbon` (`kas_bon_id`),
  KEY `idx_potongan_bulan` (`bulan_potong`),
  CONSTRAINT `kas_bon_potongan_ibfk_1` FOREIGN KEY (`kas_bon_id`) REFERENCES `kas_bon` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_bon_potongan_ibfk_2` FOREIGN KEY (`potong_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_bon_potongan`
--

LOCK TABLES `kas_bon_potongan` WRITE;
/*!40000 ALTER TABLE `kas_bon_potongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_bon_potongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_petugas`
--

DROP TABLE IF EXISTS `kas_petugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_petugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `petugas_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `saldo_awal` decimal(12,0) DEFAULT 0,
  `total_terima` decimal(12,0) DEFAULT 0,
  `total_disetor` decimal(12,0) DEFAULT 0,
  `saldo_akhir` decimal(12,0) DEFAULT 0,
  `status` enum('lengkap','kurang','lebih') DEFAULT 'lengkap',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kas_petugas_cabang` (`cabang_id`),
  KEY `idx_kas_petugas_petugas` (`petugas_id`),
  KEY `idx_kas_petugas_tanggal` (`tanggal`),
  KEY `idx_kas_petugas_petugas_tanggal` (`petugas_id`,`tanggal`),
  CONSTRAINT `kas_petugas_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_petugas_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_petugas`
--

LOCK TABLES `kas_petugas` WRITE;
/*!40000 ALTER TABLE `kas_petugas` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_petugas` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_kas_petugas_before_insert
BEFORE INSERT ON kas_petugas
FOR EACH ROW
BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_kas_petugas_before_update
BEFORE UPDATE ON kas_petugas
FOR EACH ROW
BEGIN
    SET NEW.saldo_akhir = NEW.saldo_awal + NEW.total_terima - NEW.total_disetor;
    
    
    IF NEW.total_disetor = 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir > 0 THEN
        SET NEW.status = 'kurang';
    ELSEIF NEW.saldo_akhir < 0 THEN
        SET NEW.status = 'lebih';
    ELSE
        SET NEW.status = 'lengkap';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `kas_petugas_setoran`
--

DROP TABLE IF EXISTS `kas_petugas_setoran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_petugas_setoran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total_kas_petugas` decimal(12,2) NOT NULL,
  `total_setoran` decimal(12,2) NOT NULL,
  `selisih` decimal(12,2) NOT NULL DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_petugas_tanggal` (`petugas_id`,`tanggal`),
  KEY `idx_cabang_tanggal` (`cabang_id`,`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `kas_petugas_setoran_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_petugas_setoran_ibfk_2` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_petugas_setoran_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_petugas_setoran`
--

LOCK TABLES `kas_petugas_setoran` WRITE;
/*!40000 ALTER TABLE `kas_petugas_setoran` DISABLE KEYS */;
/*!40000 ALTER TABLE `kas_petugas_setoran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `labarugi`
--

DROP TABLE IF EXISTS `labarugi`;
/*!50001 DROP VIEW IF EXISTS `labarugi`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `labarugi` AS SELECT 
 1 AS `kategori`,
 1 AS `kode`,
 1 AS `nama`,
 1 AS `saldo_akhir`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_risk_log`
--

DROP TABLE IF EXISTS `loan_risk_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_risk_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `jenis_risiko` enum('gagal_bayar','macet','keluarga_bermasalah','blacklist_keluarga','lainnya') NOT NULL,
  `tingkat_risiko` enum('rendah','sedang','tinggi','sangat_tinggi') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tindakan_diambil` text DEFAULT NULL,
  `tanggal_kejadian` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  KEY `idx_loan_risk_cabang` (`cabang_id`),
  KEY `idx_loan_risk_nasabah` (`nasabah_id`),
  KEY `idx_loan_risk_jenis` (`jenis_risiko`),
  KEY `idx_loan_risk_tanggal` (`tanggal_kejadian`),
  CONSTRAINT `loan_risk_log_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_risk_log_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loan_risk_log_ibfk_3` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_risk_log`
--

LOCK TABLES `loan_risk_log` WRITE;
/*!40000 ALTER TABLE `loan_risk_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `loan_risk_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah`
--

DROP TABLE IF EXISTS `nasabah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nasabah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `kode_nasabah` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `alamat_rumah` text DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` int(10) unsigned DEFAULT NULL,
  `hubungan_keluarga` text DEFAULT NULL,
  `ktp` varchar(16) NOT NULL,
  `telp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_usaha` varchar(50) DEFAULT NULL,
  `lokasi_pasar` varchar(100) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `referensi_nasabah_id` int(11) DEFAULT NULL,
  `db_orang_user_id` int(11) DEFAULT NULL,
  `db_orang_address_id` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif','blacklist') NOT NULL DEFAULT 'aktif',
  `skor_risiko_keluarga` int(11) DEFAULT 0,
  `catatan_risiko` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_nasabah` (`kode_nasabah`),
  UNIQUE KEY `ktp` (`ktp`),
  UNIQUE KEY `idx_nasabah_email` (`email`),
  KEY `idx_nasabah_cabang` (`cabang_id`),
  KEY `idx_nasabah_ktp` (`ktp`),
  KEY `idx_referensi_nasabah` (`referensi_nasabah_id`),
  KEY `idx_nasabah_cabang_status` (`cabang_id`,`status`),
  KEY `idx_province_id` (`province_id`),
  KEY `idx_regency_id` (`regency_id`),
  KEY `idx_district_id` (`district_id`),
  KEY `idx_village_id` (`village_id`),
  KEY `idx_db_orang_user` (`db_orang_user_id`),
  KEY `idx_db_orang_address` (`db_orang_address_id`),
  CONSTRAINT `nasabah_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah`
--

LOCK TABLES `nasabah` WRITE;
/*!40000 ALTER TABLE `nasabah` DISABLE KEYS */;
INSERT INTO `nasabah` VALUES (7,16,'NSB001','Budi Santoso',NULL,NULL,'Pasar Induk Blok A No. 10',NULL,NULL,NULL,NULL,NULL,NULL,'3201010101010001','6281111111111',NULL,'Pedagang Sayur','Pasar Induk',NULL,NULL,NULL,NULL,NULL,'aktif',0,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30'),(8,16,'NSB002','Siti Aminah',NULL,NULL,'Pasar Induk Blok B No. 5',NULL,NULL,NULL,NULL,NULL,NULL,'3201010101010002','6282222222222',NULL,'Pedagang Buah','Pasar Induk',NULL,NULL,NULL,NULL,NULL,'aktif',0,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30'),(9,16,'NSB003','Ahmad Yani',NULL,NULL,'Pasar Induk Blok C No. 15',NULL,NULL,NULL,NULL,NULL,NULL,'3201010101010003','6283333333333',NULL,'Warung Sembako','Pasar Induk',NULL,NULL,NULL,NULL,NULL,'aktif',0,NULL,'2026-04-29 18:42:30','2026-04-29 18:42:30');
/*!40000 ALTER TABLE `nasabah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah_family_link`
--

DROP TABLE IF EXISTS `nasabah_family_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nasabah_family_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `jenis_hubungan` enum('ayah','ibu','suami','istri','anak','saudara','kerabat','lainnya') NOT NULL,
  `nama_keluarga` varchar(100) NOT NULL,
  `ktp_keluarga` varchar(16) DEFAULT NULL,
  `alamat_keluarga` text DEFAULT NULL,
  `telp_keluarga` varchar(15) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_family_link_nasabah` (`nasabah_id`),
  KEY `idx_family_link_ktp` (`ktp_keluarga`),
  CONSTRAINT `nasabah_family_link_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah_family_link`
--

LOCK TABLES `nasabah_family_link` WRITE;
/*!40000 ALTER TABLE `nasabah_family_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `nasabah_family_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah_orang_mapping`
--

DROP TABLE IF EXISTS `nasabah_orang_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nasabah_orang_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `db_orang_user_id` int(11) DEFAULT NULL,
  `db_orang_address_id` int(11) DEFAULT NULL,
  `mapped_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `mapped_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nasabah_id` (`nasabah_id`),
  KEY `db_orang_user_id` (`db_orang_user_id`),
  KEY `db_orang_address_id` (`db_orang_address_id`),
  KEY `mapped_by` (`mapped_by`),
  CONSTRAINT `nasabah_orang_mapping_ibfk_1` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nasabah_orang_mapping_ibfk_2` FOREIGN KEY (`mapped_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah_orang_mapping`
--

LOCK TABLES `nasabah_orang_mapping` WRITE;
/*!40000 ALTER TABLE `nasabah_orang_mapping` DISABLE KEYS */;
/*!40000 ALTER TABLE `nasabah_orang_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `neraca`
--

DROP TABLE IF EXISTS `neraca`;
/*!50001 DROP VIEW IF EXISTS `neraca`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `neraca` AS SELECT 
 1 AS `kategori`,
 1 AS `kode`,
 1 AS `nama`,
 1 AS `saldo_akhir`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `neraca_saldo`
--

DROP TABLE IF EXISTS `neraca_saldo`;
/*!50001 DROP VIEW IF EXISTS `neraca_saldo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `neraca_saldo` AS SELECT 
 1 AS `kode`,
 1 AS `nama`,
 1 AS `tipe`,
 1 AS `kategori`,
 1 AS `saldo_normal`,
 1 AS `total_debit`,
 1 AS `total_kredit`,
 1 AS `saldo_akhir`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `angsuran_id` int(11) NOT NULL,
  `kode_pembayaran` varchar(20) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `cara_bayar` enum('tunai','transfer','digital') NOT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `petugas_id` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `denda_dibayar` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah denda yang dibayar',
  `denda_waived` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah denda yang di-waive',
  `total_pembayaran` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total = pokok + bunga + denda_dibayar',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pembayaran` (`kode_pembayaran`),
  KEY `cabang_id` (`cabang_id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `petugas_id` (`petugas_id`),
  KEY `idx_pembayaran_pinjaman` (`pinjaman_id`),
  KEY `idx_pembayaran_pinjaman_tanggal` (`pinjaman_id`,`tanggal_bayar`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
  CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`),
  CONSTRAINT `pembayaran_ibfk_4` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengeluaran`
--

DROP TABLE IF EXISTS `pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `kategori` enum('gaji','lembur','bonus','operasional','belanja','lainnya') NOT NULL,
  `sub_kategori` varchar(50) DEFAULT NULL,
  `jumlah` decimal(12,0) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `petugas_id` (`petugas_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_pengeluaran_cabang` (`cabang_id`),
  KEY `idx_pengeluaran_kategori` (`kategori`),
  KEY `idx_pengeluaran_tanggal` (`tanggal`),
  KEY `idx_pengeluaran_status` (`status`),
  KEY `idx_pengeluaran_cabang_status` (`cabang_id`,`status`),
  KEY `idx_pengeluaran_kategori_tanggal` (`kategori`,`tanggal`),
  CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengeluaran_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pengeluaran_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengeluaran`
--

LOCK TABLES `pengeluaran` WRITE;
/*!40000 ALTER TABLE `pengeluaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengeluaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_audit_log`
--

DROP TABLE IF EXISTS `permission_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `old_value` tinyint(1) DEFAULT NULL,
  `new_value` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_target_user_id` (`target_user_id`),
  KEY `idx_permission_id` (`permission_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_audit_log`
--

LOCK TABLES `permission_audit_log` WRITE;
/*!40000 ALTER TABLE `permission_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(100) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kategori` varchar(100) NOT NULL DEFAULT 'general',
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`),
  KEY `idx_kode` (`kode`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.read','View Dashboard','general','View dashboard statistics','2026-04-28 05:26:12'),(2,'nasabah.read','View Nasabah','nasabah','View nasabah list','2026-04-28 05:26:12'),(3,'manage_nasabah','Manage Nasabah','nasabah','Create, edit, delete nasabah','2026-04-28 05:26:12'),(4,'pinjaman.read','View Pinjaman','pinjaman','View pinjaman list','2026-04-28 05:26:12'),(5,'manage_pinjaman','Manage Pinjaman','pinjaman','Create, edit, delete pinjaman','2026-04-28 05:26:12'),(6,'pinjaman.approve','Approve Pinjaman','pinjaman','Approve pinjaman applications','2026-04-28 05:26:12'),(7,'angsuran.read','View Angsuran','angsuran','View angsuran list','2026-04-28 05:26:12'),(8,'manage_pembayaran','Manage Pembayaran','angsuran','Record payments','2026-04-28 05:26:12'),(9,'angsuran.create','Catat Aktivitas Lapangan','aktivitas','Akses untuk mencatat aktivitas lapangan','2026-04-28 05:26:12'),(10,'kas_petugas.read','View Kas Petugas','kas','View kas petugas','2026-04-28 05:26:12'),(11,'kas_petugas.update','Update Kas Petugas','kas','Approve setoran','2026-04-28 05:26:12'),(12,'kas.read','View Cash','kas','View cash reconciliation','2026-04-28 05:26:12'),(13,'kas.update','Update Cash','kas','Approve reconciliation','2026-04-28 05:26:12'),(14,'pinjaman.auto_confirm','Auto Confirm Settings','settings','Manage auto-confirm','2026-04-28 05:26:12'),(15,'users.create','Create Users','users','Create new users','2026-04-28 05:26:12'),(16,'users.read','View Users','users','View users list','2026-04-28 05:26:12'),(17,'manage_users','Manage Users','users','Edit, delete users','2026-04-28 05:26:12'),(18,'cabang.read','View Cabang','cabang','View branch list','2026-04-28 05:26:12'),(19,'manage_cabang','Manage Cabang','cabang','Create, edit, delete branches','2026-04-28 05:26:12'),(20,'view_laporan','View Laporan','laporan','View reports','2026-04-28 05:26:12'),(21,'manage_pengeluaran','Manage Pengeluaran','pengeluaran','Create, edit, delete pengeluaran','2026-04-28 05:26:12'),(22,'view_pengeluaran','View Pengeluaran','pengeluaran','View pengeluaran list','2026-04-28 05:26:12'),(23,'manage_kas_bon','Manage Kas Bon','kas_bon','Create, edit, delete kas bon','2026-04-28 05:26:12'),(24,'view_kas_bon','View Kas Bon','kas_bon','View kas bon list','2026-04-28 05:26:12'),(25,'manage_bunga','Manage Bunga','settings','Edit bunga settings','2026-04-28 05:26:12'),(26,'view_settings','View Settings','settings','View settings','2026-04-28 05:26:12'),(27,'manage_petugas','Manage Petugas','users','Create, edit, delete petugas','2026-04-28 05:26:12'),(28,'view_petugas','View Petugas','users','View petugas list','2026-04-28 05:26:12'),(29,'assign_permissions','Assign Permissions','admin','Assign permissions to users','2026-04-28 05:26:12'),(58,'nasabah.create','Nasabah create','general','Permission for nasabah.create','2026-04-29 17:41:31'),(59,'nasabah.edit','Nasabah edit','general','Permission for nasabah.edit','2026-04-29 17:41:31'),(60,'nasabah.delete','Nasabah delete','general','Permission for nasabah.delete','2026-04-29 17:41:31'),(61,'pinjaman.create','Pinjaman create','general','Permission for pinjaman.create','2026-04-29 17:41:31'),(62,'pinjaman.edit','Pinjaman edit','general','Permission for pinjaman.edit','2026-04-29 17:41:31'),(63,'pinjaman.delete','Pinjaman delete','general','Permission for pinjaman.delete','2026-04-29 17:41:31'),(64,'angsuran.edit','Angsuran edit','general','Permission for angsuran.edit','2026-04-29 17:41:31'),(65,'angsuran.delete','Angsuran delete','general','Permission for angsuran.delete','2026-04-29 17:41:31'),(66,'users.edit','Users edit','general','Permission for users.edit','2026-04-29 17:41:31'),(67,'users.delete','Users delete','general','Permission for users.delete','2026-04-29 17:41:31'),(68,'cabang.create','Cabang create','general','Permission for cabang.create','2026-04-29 17:41:31'),(69,'cabang.edit','Cabang edit','general','Permission for cabang.edit','2026-04-29 17:41:31'),(70,'cabang.delete','Cabang delete','general','Permission for cabang.delete','2026-04-29 17:41:31'),(71,'rute_harian.read','Rute_harian read','general','Permission for rute_harian.read','2026-04-29 17:41:31');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `kode_pinjaman` varchar(20) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `plafon` decimal(12,2) NOT NULL,
  `tenor` int(11) NOT NULL COMMENT 'Tenor: harian (max 100), mingguan (max 52), bulanan (max 12)',
  `frekuensi` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'bulanan',
  `bunga_per_bulan` decimal(5,2) NOT NULL,
  `total_bunga` decimal(12,2) NOT NULL,
  `total_pembayaran` decimal(12,2) NOT NULL,
  `angsuran_pokok` decimal(12,2) NOT NULL,
  `angsuran_bunga` decimal(12,2) NOT NULL,
  `angsuran_total` decimal(12,2) NOT NULL,
  `tanggal_akad` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tujuan_pinjaman` text DEFAULT NULL,
  `jaminan` text DEFAULT NULL,
  `jaminan_tipe` enum('tanpa','bpkb','shm','ajb','tabungan') DEFAULT 'tanpa',
  `jaminan_nilai` decimal(12,0) DEFAULT NULL,
  `jaminan_dokumen` varchar(255) DEFAULT NULL,
  `status` enum('pengajuan','disetujui','aktif','lunas','ditolak','macet') NOT NULL DEFAULT 'pengajuan',
  `auto_confirmed` tinyint(1) DEFAULT 0,
  `auto_confirmed_at` timestamp NULL DEFAULT NULL,
  `auto_confirmed_by` int(11) DEFAULT NULL,
  `petugas_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pinjaman` (`kode_pinjaman`),
  KEY `petugas_id` (`petugas_id`),
  KEY `idx_pinjaman_nasabah` (`nasabah_id`),
  KEY `idx_pinjaman_cabang` (`cabang_id`),
  KEY `idx_pinjaman_cabang_status` (`cabang_id`,`status`),
  KEY `idx_pinjaman_nasabah_status` (`nasabah_id`,`status`),
  KEY `auto_confirmed_by` (`auto_confirmed_by`),
  KEY `idx_pinjaman_frekuensi` (`frekuensi`),
  KEY `idx_pinjaman_frekuensi_status` (`frekuensi`,`status`),
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pinjaman_ibfk_4` FOREIGN KEY (`auto_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_pinjaman_plafon` CHECK (`plafon` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman`
--

LOCK TABLES `pinjaman` WRITE;
/*!40000 ALTER TABLE `pinjaman` DISABLE KEYS */;
INSERT INTO `pinjaman` VALUES (5,16,'PIN001',7,2000000.00,10,'harian',2.00,400000.00,2400000.00,200000.00,40000.00,240000.00,'2026-04-30','2027-02-28','Modal usaha tambahan',NULL,'tanpa',NULL,NULL,'disetujui',0,NULL,NULL,33,'2026-04-29 18:42:30','2026-04-29 18:42:30'),(6,16,'PIN002',8,5000000.00,12,'mingguan',1.50,900000.00,5900000.00,416666.67,75000.00,491666.67,'2026-04-30','2027-04-30','Pembelian stok barang',NULL,'tanpa',NULL,NULL,'disetujui',0,NULL,NULL,33,'2026-04-29 18:42:30','2026-04-29 18:42:30'),(7,16,'PIN003',9,10000000.00,24,'bulanan',1.00,2400000.00,12400000.00,416666.67,100000.00,516666.67,'2026-04-30','2028-04-30','Renovasi warung',NULL,'tanpa',NULL,NULL,'disetujui',0,NULL,NULL,33,'2026-04-29 18:42:30','2026-04-29 18:42:30');
/*!40000 ALTER TABLE `pinjaman` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_auto_update_family_risk
AFTER UPDATE ON pinjaman
FOR EACH ROW
BEGIN
    DECLARE v_nasabah_id INT;
    DECLARE v_alamat TEXT;
    DECLARE v_cabang_id INT;
    DECLARE v_family_risk_id INT;
    DECLARE v_nama_nasabah VARCHAR(100);
    
    
    IF NEW.status = 'macet' AND OLD.status != 'macet' THEN
        
        SELECT nasabah_id, cabang_id INTO v_nasabah_id, v_cabang_id
        FROM pinjaman WHERE id = NEW.id;
        
        SELECT alamat, nama INTO v_alamat, v_nama_nasabah
        FROM nasabah WHERE id = v_nasabah_id;
        
        
        INSERT INTO loan_risk_log (cabang_id, nasabah_id, pinjaman_id, jenis_risiko, tingkat_risiko, deskripsi, tindakan_diambil, tanggal_kejadian)
        VALUES (v_cabang_id, v_nasabah_id, NEW.id, 'gagal_bayar', 'tinggi', 'Pinjaman gagal bayar', 'Auto-tagged as family risk', CURDATE());
        
        
        UPDATE nasabah 
        SET skor_risiko_keluarga = skor_risiko_keluarga + 10
        WHERE id = v_nasabah_id;
        
        
        SELECT id INTO v_family_risk_id FROM family_risk WHERE alamat_keluarga LIKE CONCAT('%', SUBSTRING_INDEX(v_alamat, ' ', 3), '%') AND cabang_id = v_cabang_id LIMIT 1;
        
        IF v_family_risk_id IS NULL THEN
            
            INSERT INTO family_risk (cabang_id, nama_kepala_keluarga, alamat_keluarga, tingkat_risiko, total_pinjaman_gagal, total_nasabah_bermasalah, tanggal_ditandai, alasan)
            VALUES (v_cabang_id, v_nama_nasabah, v_alamat, 'tinggi', 1, 1, CURDATE(), 'Pinjaman gagal bayar');
        ELSE
            
            UPDATE family_risk 
            SET total_pinjaman_gagal = total_pinjaman_gagal + 1,
                total_nasabah_bermasalah = total_nasabah_bermasalah + 1,
                tingkat_risiko = CASE 
                    WHEN total_pinjaman_gagal + 1 >= 3 THEN 'sangat_tinggi'
                    WHEN total_pinjaman_gagal + 1 = 2 THEN 'tinggi'
                    ELSE 'sedang'
                END,
                updated_at = NOW()
            WHERE id = v_family_risk_id;
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ref_jaminan_tipe`
--

DROP TABLE IF EXISTS `ref_jaminan_tipe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_jaminan_tipe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipe_kode` varchar(20) NOT NULL,
  `tipe_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipe_kode` (`tipe_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jaminan_tipe`
--

LOCK TABLES `ref_jaminan_tipe` WRITE;
/*!40000 ALTER TABLE `ref_jaminan_tipe` DISABLE KEYS */;
INSERT INTO `ref_jaminan_tipe` VALUES (6,'JAM001','Tanpa Jaminan','Pinjaman tanpa jaminan','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(7,'JAM002','BPKB Kendaraan','Jaminan BPKB kendaraan bermotor','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(8,'JAM003','SHM Tanah/Rumah','Sertifikat Hak Milik tanah atau rumah','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(9,'JAM004','AJB','Akta Jual Beli','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(10,'JAM005','Tabungan','Jaminan tabungan','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(11,'JAM006','SK Kerja','Surat Keterangan Kerja','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42'),(12,'JAM007','Lainnya','Jaminan lainnya','aktif','2026-04-29 16:58:42','2026-04-29 16:58:42');
/*!40000 ALTER TABLE `ref_jaminan_tipe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_usaha`
--

DROP TABLE IF EXISTS `ref_jenis_usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_jenis_usaha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_kode` varchar(20) NOT NULL,
  `jenis_nama` varchar(50) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_kode` (`jenis_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_usaha`
--

LOCK TABLES `ref_jenis_usaha` WRITE;
/*!40000 ALTER TABLE `ref_jenis_usaha` DISABLE KEYS */;
INSERT INTO `ref_jenis_usaha` VALUES (7,'J001','Warung','Retail','Warung kelontong atau minimarket','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(8,'J002','Toko Sembako','Retail','Toko bahan pokok','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(9,'J003','Restoran','F&B','Restoran atau rumah makan','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(10,'J004','Kafe','F&B','Kafe atau kedai kopi','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(11,'J005','Pangkas Rambut','Jasa','Usaha pangkas rambut','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(12,'J006','Laundry','Jasa','Usaha laundry','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(13,'J007','Bengkel','Jasa','Bengkel motor atau mobil','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(14,'J008','Toko Elektronik','Retail','Toko elektronik','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(15,'J009','Toko Pakaian','Retail','Toko pakaian atau butik','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(16,'J010','Pedagang Kaki Lima','Retail','Pedagang kaki lima','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(17,'J011','Produksi Makanan','Produksi','Usaha produksi makanan','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41'),(18,'J012','Lainnya','Lainnya','Jenis usaha lainnya','aktif','2026-04-29 16:58:41','2026-04-29 16:58:41');
/*!40000 ALTER TABLE `ref_jenis_usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_kategori_pengeluaran`
--

DROP TABLE IF EXISTS `ref_kategori_pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_kategori_pengeluaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori_kode` varchar(20) NOT NULL,
  `kategori_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kategori_kode` (`kategori_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_kategori_pengeluaran`
--

LOCK TABLES `ref_kategori_pengeluaran` WRITE;
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` DISABLE KEYS */;
INSERT INTO `ref_kategori_pengeluaran` VALUES (7,'KAT001','Operasional','Pengeluaran operasional harian','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(8,'KAT002','Gaji','Penggajian karyawan','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(9,'KAT003','Sewa','Sewa tempat usaha','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(10,'KAT004','Listrik & Air','Tagihan listrik dan air','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(11,'KAT005','Transportasi','Pengeluaran transportasi','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(12,'KAT006','Peralatan','Pembelian peralatan','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(13,'KAT007','Marketing','Biaya marketing dan promosi','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43'),(14,'KAT008','Lainnya','Pengeluaran lainnya','aktif','2026-04-29 16:58:43','2026-04-29 16:58:43');
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_metode_pembayaran`
--

DROP TABLE IF EXISTS `ref_metode_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_metode_pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metode_kode` varchar(20) NOT NULL,
  `metode_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `metode_kode` (`metode_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_metode_pembayaran`
--

LOCK TABLES `ref_metode_pembayaran` WRITE;
/*!40000 ALTER TABLE `ref_metode_pembayaran` DISABLE KEYS */;
INSERT INTO `ref_metode_pembayaran` VALUES (4,'MET001','Tunai','Pembayaran tunai','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(5,'MET002','Transfer Bank','Transfer melalui bank','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(6,'MET003','QRIS','Pembayaran QRIS','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(7,'MET004','Debit','Kartu debit','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(8,'MET005','Kredit','Kartu kredit','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(9,'MET006','E-Wallet','Dompet elektronik','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(10,'MET007','Cek','Pembayaran dengan cek','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44'),(11,'MET008','Lainnya','Metode pembayaran lainnya','aktif','2026-04-29 16:58:44','2026-04-29 16:58:44');
/*!40000 ALTER TABLE `ref_metode_pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_roles`
--

DROP TABLE IF EXISTS `ref_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_kode` varchar(20) NOT NULL,
  `role_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_kode` (`role_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_roles`
--

LOCK TABLES `ref_roles` WRITE;
/*!40000 ALTER TABLE `ref_roles` DISABLE KEYS */;
INSERT INTO `ref_roles` VALUES (26,'superadmin','Superadmin','Pencipta aplikasi dengan akses penuh untuk pengawasan teknis dan pengelolaan sistem',NULL,1,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(27,'bos','Bos','Pemilik usaha dengan akses penuh untuk pengawasan operasional dan keuangan',NULL,2,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(28,'manager_pusat','Manager Pusat','Manager di pusat dengan kontrol operasional lintas cabang',NULL,3,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(29,'manager_cabang','Manager Cabang','Manager cabang dengan kontrol operasional cabang',NULL,4,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(30,'admin_pusat','Admin Pusat','Admin di pusat dengan akses administratif lintas cabang',NULL,5,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(31,'admin_cabang','Admin Cabang','Admin cabang dengan akses administratif cabang',NULL,6,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(32,'petugas_pusat','Petugas Pusat','Petugas di pusat dengan akses lapangan lintas cabang',NULL,7,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(33,'petugas_cabang','Petugas Cabang','Petugas cabang dengan akses lapangan cabang',NULL,8,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35'),(34,'karyawan','Karyawan','Karyawan dengan akses administratif dasar',NULL,9,'aktif','2026-04-28 05:28:35','2026-04-28 05:28:35');
/*!40000 ALTER TABLE `ref_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_status_pinjaman`
--

DROP TABLE IF EXISTS `ref_status_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_status_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_kode` varchar(20) NOT NULL,
  `status_nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `urutan_tampil` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_kode` (`status_kode`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_status_pinjaman`
--

LOCK TABLES `ref_status_pinjaman` WRITE;
/*!40000 ALTER TABLE `ref_status_pinjaman` DISABLE KEYS */;
INSERT INTO `ref_status_pinjaman` VALUES (7,'STS001','Pending','Pinjaman menunggu persetujuan',1,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(8,'STS002','Disetujui','Pinjaman disetujui dan aktif',2,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(9,'STS003','Ditolak','Pinjaman ditolak',3,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(10,'STS004','Lunas','Pinjaman telah lunas',4,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(11,'STS005','Blacklist','Pinjaman masuk blacklist',5,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45'),(12,'STS006','Batal','Pinjaman dibatalkan',6,'aktif','2026-04-29 16:58:45','2026-04-29 16:58:45');
/*!40000 ALTER TABLE `ref_status_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `permission_code` varchar(100) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_role` (`role`),
  KEY `idx_permission_code` (`permission_code`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,'superadmin','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(2,'superadmin','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(3,'superadmin','assign_permissions',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(4,'superadmin','cabang.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(5,'superadmin','dashboard.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(6,'superadmin','kas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(7,'superadmin','kas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(8,'superadmin','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(9,'superadmin','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(10,'superadmin','manage_bunga',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(11,'superadmin','manage_cabang',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(12,'superadmin','manage_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(13,'superadmin','manage_nasabah',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(14,'superadmin','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(15,'superadmin','manage_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(16,'superadmin','manage_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(17,'superadmin','manage_pinjaman',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(18,'superadmin','manage_users',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(19,'superadmin','nasabah.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(20,'superadmin','pinjaman.approve',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(21,'superadmin','pinjaman.auto_confirm',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(22,'superadmin','pinjaman.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(23,'superadmin','users.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(24,'superadmin','users.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(25,'superadmin','view_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(26,'superadmin','view_laporan',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(27,'superadmin','view_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(28,'superadmin','view_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(29,'superadmin','view_settings',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(32,'bos','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(33,'bos','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(34,'bos','assign_permissions',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(35,'bos','cabang.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(36,'bos','dashboard.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(37,'bos','kas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(38,'bos','kas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(39,'bos','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(40,'bos','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(41,'bos','manage_bunga',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(42,'bos','manage_cabang',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(43,'bos','manage_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(44,'bos','manage_nasabah',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(45,'bos','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(46,'bos','manage_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(47,'bos','manage_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(48,'bos','manage_pinjaman',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(49,'bos','manage_users',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(50,'bos','nasabah.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(51,'bos','pinjaman.approve',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(52,'bos','pinjaman.auto_confirm',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(53,'bos','pinjaman.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(54,'bos','users.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(55,'bos','users.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(56,'bos','view_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(57,'bos','view_laporan',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(58,'bos','view_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(59,'bos','view_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(60,'bos','view_settings',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(63,'petugas_cabang','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(64,'petugas_cabang','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(65,'petugas_cabang','assign_permissions',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(66,'petugas_cabang','cabang.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(67,'petugas_cabang','dashboard.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(68,'petugas_cabang','kas.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(69,'petugas_cabang','kas.update',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(70,'petugas_cabang','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(71,'petugas_cabang','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(72,'petugas_cabang','manage_bunga',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(73,'petugas_cabang','manage_cabang',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(74,'petugas_cabang','manage_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(75,'petugas_cabang','manage_nasabah',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(76,'petugas_cabang','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(77,'petugas_cabang','manage_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(78,'petugas_cabang','manage_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(79,'petugas_cabang','manage_pinjaman',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(80,'petugas_cabang','manage_users',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(81,'petugas_cabang','nasabah.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(82,'petugas_cabang','pinjaman.approve',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(83,'petugas_cabang','pinjaman.auto_confirm',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(84,'petugas_cabang','pinjaman.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(85,'petugas_cabang','users.create',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(86,'petugas_cabang','users.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(87,'petugas_cabang','view_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(88,'petugas_cabang','view_laporan',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(89,'petugas_cabang','view_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(90,'petugas_cabang','view_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(91,'petugas_cabang','view_settings',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(94,'superadmin','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(95,'superadmin','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(96,'superadmin','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(97,'superadmin','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(98,'superadmin','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(99,'superadmin','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(100,'superadmin','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(101,'superadmin','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(102,'superadmin','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(103,'superadmin','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(104,'superadmin','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(105,'superadmin','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(106,'superadmin','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(107,'superadmin','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(108,'bos','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(109,'bos','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(110,'bos','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(111,'bos','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(112,'bos','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(113,'bos','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(114,'bos','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(115,'bos','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(116,'bos','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(117,'bos','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(118,'bos','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(119,'bos','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(120,'bos','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(121,'bos','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(122,'petugas','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(123,'petugas','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(124,'petugas','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(125,'petugas','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(126,'petugas','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(127,'petugas','angsuran.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(128,'petugas','kas_petugas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(129,'petugas','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(130,'petugas','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(131,'manager_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(132,'manager_pusat','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(133,'manager_pusat','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(134,'manager_pusat','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(135,'manager_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(136,'manager_pusat','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(137,'manager_pusat','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(138,'manager_pusat','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(139,'manager_pusat','pinjaman.auto_confirm',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(140,'manager_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(141,'manager_pusat','angsuran.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(142,'manager_pusat','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(143,'manager_pusat','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(144,'manager_pusat','users.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(145,'manager_pusat','users.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(146,'manager_pusat','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(147,'manager_pusat','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(148,'manager_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(149,'manager_pusat','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(150,'manager_pusat','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(151,'manager_pusat','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(152,'manager_pusat','manage_bunga',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(153,'manager_pusat','view_settings',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(154,'manager_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(155,'manager_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(156,'manager_pusat','manage_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(157,'manager_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(158,'manager_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(159,'manager_pusat','manage_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(160,'manager_pusat','view_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(161,'manager_pusat','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(162,'manager_pusat','kas_petugas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(163,'manager_pusat','kas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(164,'manager_pusat','assign_permissions',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(165,'admin_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(166,'admin_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(167,'admin_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(168,'admin_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(169,'admin_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(170,'admin_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(171,'admin_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(172,'admin_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_bunga`
--

DROP TABLE IF EXISTS `setting_bunga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting_bunga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `jenis_pinjaman` varchar(50) NOT NULL,
  `frekuensi` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'bulanan',
  `tenor_min` int(11) NOT NULL DEFAULT 1,
  `tenor_max` int(11) NOT NULL DEFAULT 24,
  `bunga_default` decimal(5,2) NOT NULL,
  `bunga_min` decimal(5,2) NOT NULL,
  `bunga_max` decimal(5,2) NOT NULL,
  `faktor_risiko` decimal(5,2) DEFAULT 0.00,
  `jaminan_adjustment` decimal(5,2) DEFAULT 0.00,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cabang_jenis_frekuensi_unique` (`cabang_id`,`jenis_pinjaman`,`frekuensi`),
  KEY `cabang_id` (`cabang_id`),
  CONSTRAINT `setting_bunga_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_bunga`
--

LOCK TABLES `setting_bunga` WRITE;
/*!40000 ALTER TABLE `setting_bunga` DISABLE KEYS */;
/*!40000 ALTER TABLE `setting_bunga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_denda`
--

DROP TABLE IF EXISTS `setting_denda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `setting_denda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `frekuensi` enum('harian','mingguan','bulanan') NOT NULL DEFAULT 'bulanan',
  `tipe_denda` enum('persentase','nominal_tetap') NOT NULL DEFAULT 'persentase',
  `nilai_denda` decimal(8,4) NOT NULL DEFAULT 0.5000 COMMENT 'Persentase per hari (%) atau nominal tetap',
  `denda_maksimal` decimal(12,2) DEFAULT NULL COMMENT 'Batas maksimal denda (NULL = tidak ada batas)',
  `grace_period` int(11) NOT NULL DEFAULT 0 COMMENT 'Masa toleransi dalam hari',
  `bisa_waive` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Apakah denda bisa di-waive oleh manager/owner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cabang_frekuensi_unique` (`cabang_id`,`frekuensi`),
  KEY `idx_cabang_id` (`cabang_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_setting_denda_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_denda`
--

LOCK TABLES `setting_denda` WRITE;
/*!40000 ALTER TABLE `setting_denda` DISABLE KEYS */;
/*!40000 ALTER TABLE `setting_denda` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_log`
--

DROP TABLE IF EXISTS `transaksi_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_transaksi` varchar(50) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `tipe_transaksi` enum('pinjaman','angsuran','pembayaran','pengeluaran','kas_masuk','kas_keluar','kas_bon','kas_setoran','rekonsiliasi') NOT NULL,
  `jumlah` decimal(20,2) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `nasabah_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','posted','void') DEFAULT 'pending',
  `jurnal_id` int(11) DEFAULT NULL COMMENT 'Reference to journal entry',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_nomor_transaksi` (`nomor_transaksi`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  KEY `idx_tipe` (`tipe_transaksi`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_nasabah` (`nasabah_id`),
  KEY `idx_pinjaman` (`pinjaman_id`),
  KEY `jurnal_id` (`jurnal_id`),
  CONSTRAINT `transaksi_log_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_log`
--

LOCK TABLES `transaksi_log` WRITE;
/*!40000 ALTER TABLE `transaksi_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
INSERT INTO `user_permissions` VALUES (1,15,9,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(2,15,7,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(3,15,29,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(4,15,18,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(5,15,1,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(6,15,12,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(7,15,13,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(8,15,10,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(9,15,11,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(10,15,25,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(11,15,19,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(12,15,23,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(13,15,3,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(14,15,8,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(15,15,21,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(16,15,27,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(17,15,5,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(18,15,17,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(19,15,2,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(20,15,6,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(21,15,14,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(22,15,4,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(23,15,15,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(24,15,16,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(25,15,24,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(26,15,20,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(27,15,22,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(28,15,28,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42'),(29,15,26,1,15,'2026-04-29 16:45:42','2026-04-29 16:45:42');
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'karyawan',
  `cabang_id` int(11) DEFAULT NULL,
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `derived_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`derived_permissions`)),
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `limit_kasbon` decimal(12,0) DEFAULT 0,
  `gaji` decimal(12,0) DEFAULT 0,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_users_email` (`email`),
  KEY `idx_owner_bos_id` (`owner_bos_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (15,'patri','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','PATRI SIHALOHO','patri@kewer.com',NULL,'superadmin',NULL,NULL,NULL,'aktif','2026-04-28 11:42:46','2026-04-29 03:53:28',0,0,NULL,NULL),(31,'bos_simulasi','$2y$10$K0C5ZIiN4gsFg3HiAYQyjuyP0hnXlpt1YTL73VTWv7hiOI/TFPtdS','Bos Simulasi','bos@kewer.id',NULL,'bos',1,NULL,NULL,'aktif','2026-04-29 18:42:30','2026-04-29 18:46:29',0,0,NULL,NULL),(32,'manager_pusat_sim','$2y$10$QxJfPi/MTAN1rI0ylAUB1e8G53iMRWWntL60ICnkwOifcQAgT5F82','Manager Pusat Simulasi','manager.pusat@kewer.id',NULL,'manager_pusat',1,NULL,NULL,'aktif','2026-04-29 18:42:30','2026-04-29 18:46:29',1000000,5000000,NULL,NULL),(33,'petugas1_sim','$2y$10$B65T72FKkwOM3h/b3UsJYOgyeTxbNyAQTnLDOcYOjsdaMqtlVRZ1G','Petugas Lapangan 1','petugas1@kewer.id',NULL,'petugas',16,NULL,NULL,'aktif','2026-04-29 18:42:30','2026-04-29 18:42:30',500000,3000000,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_cabang_complete`
--

DROP TABLE IF EXISTS `v_cabang_complete`;
/*!50001 DROP VIEW IF EXISTS `v_cabang_complete`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_cabang_complete` AS SELECT 
 1 AS `id`,
 1 AS `kode_cabang`,
 1 AS `nama_cabang`,
 1 AS `alamat`,
 1 AS `telp`,
 1 AS `email`,
 1 AS `kota`,
 1 AS `provinsi`,
 1 AS `kode_pos`,
 1 AS `province_id`,
 1 AS `regency_id`,
 1 AS `district_id`,
 1 AS `village_id`,
 1 AS `status`,
 1 AS `province_name`,
 1 AS `regency_name`,
 1 AS `district_name`,
 1 AS `village_name`,
 1 AS `complete_address`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_jadwal_angsuran`
--

DROP TABLE IF EXISTS `v_jadwal_angsuran`;
/*!50001 DROP VIEW IF EXISTS `v_jadwal_angsuran`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_jadwal_angsuran` AS SELECT 
 1 AS `id`,
 1 AS `cabang_id`,
 1 AS `pinjaman_id`,
 1 AS `frekuensi`,
 1 AS `no_angsuran`,
 1 AS `jatuh_tempo`,
 1 AS `pokok`,
 1 AS `bunga`,
 1 AS `total_angsuran`,
 1 AS `denda`,
 1 AS `total_bayar`,
 1 AS `status`,
 1 AS `tanggal_bayar`,
 1 AS `cara_bayar`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `nasabah_id`,
 1 AS `pinjaman_nominal`,
 1 AS `pinjaman_frekuensi`,
 1 AS `pinjaman_tenor`,
 1 AS `nasabah_nama`,
 1 AS `nasabah_telepon`,
 1 AS `cabang_nama`,
 1 AS `status_display`,
 1 AS `hari_terlambat`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_karyawan_kasbon`
--

DROP TABLE IF EXISTS `v_karyawan_kasbon`;
/*!50001 DROP VIEW IF EXISTS `v_karyawan_kasbon`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_karyawan_kasbon` AS SELECT 
 1 AS `karyawan_id`,
 1 AS `nama_karyawan`,
 1 AS `cabang_id`,
 1 AS `limit_kasbon`,
 1 AS `total_kasbon`,
 1 AS `total_dipinjam`,
 1 AS `total_sisa`,
 1 AS `total_lunas`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_kasbon_summary`
--

DROP TABLE IF EXISTS `v_kasbon_summary`;
/*!50001 DROP VIEW IF EXISTS `v_kasbon_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_kasbon_summary` AS SELECT 
 1 AS `id`,
 1 AS `kode_kasbon`,
 1 AS `cabang_id`,
 1 AS `karyawan_id`,
 1 AS `nama_karyawan`,
 1 AS `tanggal_pengajuan`,
 1 AS `tanggal_pemberian`,
 1 AS `tanggal_potong`,
 1 AS `jumlah`,
 1 AS `tenor_bulan`,
 1 AS `potongan_per_bulan`,
 1 AS `sisa_bon`,
 1 AS `potongan_ke`,
 1 AS `status`,
 1 AS `tujuan`,
 1 AS `catatan`,
 1 AS `disetujui_oleh`,
 1 AS `tanggal_disetujui`,
 1 AS `jumlah_potongan`,
 1 AS `total_dipotong`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_laporan_kas_harian`
--

DROP TABLE IF EXISTS `v_laporan_kas_harian`;
/*!50001 DROP VIEW IF EXISTS `v_laporan_kas_harian`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_laporan_kas_harian` AS SELECT 
 1 AS `cabang_id`,
 1 AS `tanggal`,
 1 AS `total_saldo_awal`,
 1 AS `total_terima`,
 1 AS `total_disetor`,
 1 AS `total_saldo_akhir`,
 1 AS `jumlah_petugas`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_laporan_pengeluaran_kategori`
--

DROP TABLE IF EXISTS `v_laporan_pengeluaran_kategori`;
/*!50001 DROP VIEW IF EXISTS `v_laporan_pengeluaran_kategori`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_laporan_pengeluaran_kategori` AS SELECT 
 1 AS `cabang_id`,
 1 AS `kategori`,
 1 AS `jumlah_transaksi`,
 1 AS `total_pengeluaran`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_nasabah_complete`
--

DROP TABLE IF EXISTS `v_nasabah_complete`;
/*!50001 DROP VIEW IF EXISTS `v_nasabah_complete`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_nasabah_complete` AS SELECT 
 1 AS `id`,
 1 AS `cabang_id`,
 1 AS `kode_nasabah`,
 1 AS `nama`,
 1 AS `nama_ayah`,
 1 AS `nama_ibu`,
 1 AS `alamat`,
 1 AS `alamat_rumah`,
 1 AS `province_id`,
 1 AS `regency_id`,
 1 AS `district_id`,
 1 AS `village_id`,
 1 AS `hubungan_keluarga`,
 1 AS `ktp`,
 1 AS `telp`,
 1 AS `email`,
 1 AS `jenis_usaha`,
 1 AS `lokasi_pasar`,
 1 AS `foto_ktp`,
 1 AS `foto_selfie`,
 1 AS `referensi_nasabah_id`,
 1 AS `status`,
 1 AS `skor_risiko_keluarga`,
 1 AS `catatan_risiko`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `province_name`,
 1 AS `regency_name`,
 1 AS `district_name`,
 1 AS `village_name`,
 1 AS `complete_address`,
 1 AS `nama_cabang`,
 1 AS `kode_cabang`,
 1 AS `db_orang_user_id`,
 1 AS `db_orang_address_id`,
 1 AS `user_orang_name`,
 1 AS `user_orang_email`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_ringkasan_bunga`
--

DROP TABLE IF EXISTS `v_ringkasan_bunga`;
/*!50001 DROP VIEW IF EXISTS `v_ringkasan_bunga`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_ringkasan_bunga` AS SELECT 
 1 AS `cabang_id`,
 1 AS `jenis_pinjaman`,
 1 AS `rata_rata_bunga`,
 1 AS `bunga_terendah`,
 1 AS `bunga_tertinggi`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_risiko_keluarga`
--

DROP TABLE IF EXISTS `v_risiko_keluarga`;
/*!50001 DROP VIEW IF EXISTS `v_risiko_keluarga`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_risiko_keluarga` AS SELECT 
 1 AS `nasabah_id`,
 1 AS `nama`,
 1 AS `alamat`,
 1 AS `ktp`,
 1 AS `status_nasabah`,
 1 AS `skor_risiko_keluarga`,
 1 AS `tingkat_risiko`,
 1 AS `nama_kepala_keluarga`,
 1 AS `alamat_keluarga`,
 1 AS `total_pinjaman_gagal`,
 1 AS `total_nasabah_bermasalah`,
 1 AS `total_log_risiko`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `labarugi`
--

/*!50001 DROP VIEW IF EXISTS `labarugi`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `labarugi` AS select 'Pendapatan' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'pendapatan' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Beban' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'beban' and `neraca_saldo`.`saldo_akhir` <> 0 order by case when `kategori` = 'Pendapatan' then 1 else 2 end,`kategori`,`kode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `neraca`
--

/*!50001 DROP VIEW IF EXISTS `neraca`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `neraca` AS select 'Aset' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'aset' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Kewajiban' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'kewajiban' and `neraca_saldo`.`saldo_akhir` <> 0 union all select 'Ekuitas' AS `kategori`,`neraca_saldo`.`kode` AS `kode`,`neraca_saldo`.`nama` AS `nama`,`neraca_saldo`.`saldo_akhir` AS `saldo_akhir` from `neraca_saldo` where `neraca_saldo`.`tipe` = 'ekuitas' and `neraca_saldo`.`saldo_akhir` <> 0 order by `kategori`,`kode` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `neraca_saldo`
--

/*!50001 DROP VIEW IF EXISTS `neraca_saldo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `neraca_saldo` AS select `a`.`kode` AS `kode`,`a`.`nama` AS `nama`,`a`.`tipe` AS `tipe`,`a`.`kategori` AS `kategori`,`a`.`saldo_normal` AS `saldo_normal`,coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) AS `total_debit`,coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) AS `total_kredit`,case when `a`.`saldo_normal` = 'debit' then coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) - coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) else coalesce(sum(case when `jd`.`kredit` > 0 then `jd`.`kredit` else 0 end),0) - coalesce(sum(case when `jd`.`debit` > 0 then `jd`.`debit` else 0 end),0) end AS `saldo_akhir` from (`akun` `a` left join `jurnal_detail` `jd` on(`a`.`kode` = `jd`.`akun_kode`)) group by `a`.`kode`,`a`.`nama`,`a`.`tipe`,`a`.`kategori`,`a`.`saldo_normal` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cabang_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_cabang_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cabang_complete` AS select `c`.`id` AS `id`,`c`.`kode_cabang` AS `kode_cabang`,`c`.`nama_cabang` AS `nama_cabang`,`c`.`alamat` AS `alamat`,`c`.`telp` AS `telp`,`c`.`email` AS `email`,`c`.`kota` AS `kota`,`c`.`provinsi` AS `provinsi`,`c`.`kode_pos` AS `kode_pos`,`c`.`province_id` AS `province_id`,`c`.`regency_id` AS `regency_id`,`c`.`district_id` AS `district_id`,`c`.`village_id` AS `village_id`,`c`.`status` AS `status`,`p`.`name` AS `province_name`,`r`.`name` AS `regency_name`,`d`.`name` AS `district_name`,`v`.`name` AS `village_name`,concat(coalesce(`c`.`alamat`,''),', ',coalesce(`v`.`name`,''),', ',coalesce(`d`.`name`,''),', ',coalesce(`r`.`name`,''),', ',coalesce(`p`.`name`,''),' ',coalesce(`c`.`kode_pos`,'')) AS `complete_address`,`c`.`created_at` AS `created_at`,`c`.`updated_at` AS `updated_at` from ((((`kewer`.`cabang` `c` left join `db_alamat_simple`.`provinces` `p` on(`c`.`province_id` = `p`.`id`)) left join `db_alamat_simple`.`regencies` `r` on(`c`.`regency_id` = `r`.`id`)) left join `db_alamat_simple`.`districts` `d` on(`c`.`district_id` = `d`.`id`)) left join `db_alamat_simple`.`villages` `v` on(`c`.`village_id` = `v`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_jadwal_angsuran`
--

/*!50001 DROP VIEW IF EXISTS `v_jadwal_angsuran`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_jadwal_angsuran` AS select `a`.`id` AS `id`,`a`.`cabang_id` AS `cabang_id`,`a`.`pinjaman_id` AS `pinjaman_id`,`a`.`frekuensi` AS `frekuensi`,`a`.`no_angsuran` AS `no_angsuran`,`a`.`jatuh_tempo` AS `jatuh_tempo`,`a`.`pokok` AS `pokok`,`a`.`bunga` AS `bunga`,`a`.`total_angsuran` AS `total_angsuran`,`a`.`denda` AS `denda`,`a`.`total_bayar` AS `total_bayar`,`a`.`status` AS `status`,`a`.`tanggal_bayar` AS `tanggal_bayar`,`a`.`cara_bayar` AS `cara_bayar`,`a`.`created_at` AS `created_at`,`a`.`updated_at` AS `updated_at`,`p`.`nasabah_id` AS `nasabah_id`,`p`.`plafon` AS `pinjaman_nominal`,`p`.`frekuensi` AS `pinjaman_frekuensi`,`p`.`tenor` AS `pinjaman_tenor`,`n`.`nama` AS `nasabah_nama`,`n`.`telp` AS `nasabah_telepon`,`c`.`nama_cabang` AS `cabang_nama`,case when `a`.`status` = 'belum' and `a`.`jatuh_tempo` < curdate() then 'telat' when `a`.`status` = 'belum' and `a`.`jatuh_tempo` = curdate() then 'jatuh_tempo_hari_ini' when `a`.`status` = 'belum' and `a`.`jatuh_tempo` between curdate() and curdate() + interval 3 day then 'akan_jatuh_tempo' else `a`.`status` end AS `status_display`,to_days(curdate()) - to_days(`a`.`jatuh_tempo`) AS `hari_terlambat` from (((`angsuran` `a` join `pinjaman` `p` on(`a`.`pinjaman_id` = `p`.`id`)) join `nasabah` `n` on(`p`.`nasabah_id` = `n`.`id`)) join `cabang` `c` on(`a`.`cabang_id` = `c`.`id`)) where `p`.`status` in ('aktif','disetujui') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_karyawan_kasbon`
--

/*!50001 DROP VIEW IF EXISTS `v_karyawan_kasbon`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_karyawan_kasbon` AS select `u`.`id` AS `karyawan_id`,`u`.`nama` AS `nama_karyawan`,`u`.`cabang_id` AS `cabang_id`,`u`.`limit_kasbon` AS `limit_kasbon`,count(`kb`.`id`) AS `total_kasbon`,sum(case when `kb`.`status` in ('disetujui','diberikan','dipotong','selesai') then `kb`.`jumlah` else 0 end) AS `total_dipinjam`,sum(case when `kb`.`status` in ('dipotong','selesai') then `kb`.`sisa_bon` else 0 end) AS `total_sisa`,sum(case when `kb`.`status` = 'selesai' then `kb`.`jumlah` else 0 end) AS `total_lunas` from (`users` `u` left join `kas_bon` `kb` on(`u`.`id` = `kb`.`karyawan_id` and `kb`.`status` <> 'deleted')) where `u`.`role` = 'karyawan' or `u`.`role` = 'petugas' group by `u`.`id`,`u`.`nama`,`u`.`cabang_id`,`u`.`limit_kasbon` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_kasbon_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_kasbon_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_kasbon_summary` AS select `kb`.`id` AS `id`,`kb`.`kode_kasbon` AS `kode_kasbon`,`kb`.`cabang_id` AS `cabang_id`,`kb`.`karyawan_id` AS `karyawan_id`,`u`.`nama` AS `nama_karyawan`,`kb`.`tanggal_pengajuan` AS `tanggal_pengajuan`,`kb`.`tanggal_pemberian` AS `tanggal_pemberian`,`kb`.`tanggal_potong` AS `tanggal_potong`,`kb`.`jumlah` AS `jumlah`,`kb`.`tenor_bulan` AS `tenor_bulan`,`kb`.`potongan_per_bulan` AS `potongan_per_bulan`,`kb`.`sisa_bon` AS `sisa_bon`,`kb`.`potongan_ke` AS `potongan_ke`,`kb`.`status` AS `status`,`kb`.`tujuan` AS `tujuan`,`kb`.`catatan` AS `catatan`,`kb`.`disetujui_oleh` AS `disetujui_oleh`,`kb`.`tanggal_disetujui` AS `tanggal_disetujui`,(select count(0) from `kas_bon_potongan` `kbp` where `kbp`.`kas_bon_id` = `kb`.`id`) AS `jumlah_potongan`,(select sum(`kbp`.`jumlah_potong`) from `kas_bon_potongan` `kbp` where `kbp`.`kas_bon_id` = `kb`.`id`) AS `total_dipotong` from (`kas_bon` `kb` join `users` `u` on(`kb`.`karyawan_id` = `u`.`id`)) where `kb`.`status` <> 'deleted' order by `kb`.`tanggal_pengajuan` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_laporan_kas_harian`
--

/*!50001 DROP VIEW IF EXISTS `v_laporan_kas_harian`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_laporan_kas_harian` AS select `kas_petugas`.`cabang_id` AS `cabang_id`,`kas_petugas`.`tanggal` AS `tanggal`,sum(`kas_petugas`.`saldo_awal`) AS `total_saldo_awal`,sum(`kas_petugas`.`total_terima`) AS `total_terima`,sum(`kas_petugas`.`total_disetor`) AS `total_disetor`,sum(`kas_petugas`.`saldo_akhir`) AS `total_saldo_akhir`,count(0) AS `jumlah_petugas` from `kas_petugas` group by `kas_petugas`.`cabang_id`,`kas_petugas`.`tanggal` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_laporan_pengeluaran_kategori`
--

/*!50001 DROP VIEW IF EXISTS `v_laporan_pengeluaran_kategori`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_laporan_pengeluaran_kategori` AS select `pengeluaran`.`cabang_id` AS `cabang_id`,`pengeluaran`.`kategori` AS `kategori`,count(0) AS `jumlah_transaksi`,sum(`pengeluaran`.`jumlah`) AS `total_pengeluaran` from `pengeluaran` where `pengeluaran`.`status` = 'approved' group by `pengeluaran`.`cabang_id`,`pengeluaran`.`kategori` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_nasabah_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_nasabah_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = cp850 */;
/*!50001 SET character_set_results     = cp850 */;
/*!50001 SET collation_connection      = cp850_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_nasabah_complete` AS select `n`.`id` AS `id`,`n`.`cabang_id` AS `cabang_id`,`n`.`kode_nasabah` AS `kode_nasabah`,`n`.`nama` AS `nama`,`n`.`nama_ayah` AS `nama_ayah`,`n`.`nama_ibu` AS `nama_ibu`,`n`.`alamat` AS `alamat`,`n`.`alamat_rumah` AS `alamat_rumah`,`n`.`province_id` AS `province_id`,`n`.`regency_id` AS `regency_id`,`n`.`district_id` AS `district_id`,`n`.`village_id` AS `village_id`,`n`.`hubungan_keluarga` AS `hubungan_keluarga`,`n`.`ktp` AS `ktp`,`n`.`telp` AS `telp`,`n`.`email` AS `email`,`n`.`jenis_usaha` AS `jenis_usaha`,`n`.`lokasi_pasar` AS `lokasi_pasar`,`n`.`foto_ktp` AS `foto_ktp`,`n`.`foto_selfie` AS `foto_selfie`,`n`.`referensi_nasabah_id` AS `referensi_nasabah_id`,`n`.`status` AS `status`,`n`.`skor_risiko_keluarga` AS `skor_risiko_keluarga`,`n`.`catatan_risiko` AS `catatan_risiko`,`n`.`created_at` AS `created_at`,`n`.`updated_at` AS `updated_at`,`p`.`name` AS `province_name`,`r`.`name` AS `regency_name`,`d`.`name` AS `district_name`,`v`.`name` AS `village_name`,concat(coalesce(`n`.`alamat`,`n`.`alamat_rumah`,''),', ',coalesce(`v`.`name`,''),', ',coalesce(`d`.`name`,''),', ',coalesce(`r`.`name`,''),', ',coalesce(`p`.`name`,'')) AS `complete_address`,`c`.`nama_cabang` AS `nama_cabang`,`c`.`kode_cabang` AS `kode_cabang`,`nom`.`db_orang_user_id` AS `db_orang_user_id`,`nom`.`db_orang_address_id` AS `db_orang_address_id`,`u`.`nama` AS `user_orang_name`,`u`.`email` AS `user_orang_email` from (((((((`kewer`.`nasabah` `n` left join `kewer`.`cabang` `c` on(`n`.`cabang_id` = `c`.`id`)) left join `db_alamat_simple`.`provinces` `p` on(`n`.`province_id` = `p`.`id`)) left join `db_alamat_simple`.`regencies` `r` on(`n`.`regency_id` = `r`.`id`)) left join `db_alamat_simple`.`districts` `d` on(`n`.`district_id` = `d`.`id`)) left join `db_alamat_simple`.`villages` `v` on(`n`.`village_id` = `v`.`id`)) left join `kewer`.`nasabah_orang_mapping` `nom` on(`n`.`id` = `nom`.`nasabah_id`)) left join `db_orang`.`users` `u` on(`nom`.`db_orang_user_id` = `u`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_ringkasan_bunga`
--

/*!50001 DROP VIEW IF EXISTS `v_ringkasan_bunga`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_ringkasan_bunga` AS select `setting_bunga`.`cabang_id` AS `cabang_id`,`setting_bunga`.`jenis_pinjaman` AS `jenis_pinjaman`,avg(`setting_bunga`.`bunga_default`) AS `rata_rata_bunga`,min(`setting_bunga`.`bunga_min`) AS `bunga_terendah`,max(`setting_bunga`.`bunga_max`) AS `bunga_tertinggi` from `setting_bunga` where `setting_bunga`.`status` = 'aktif' group by `setting_bunga`.`cabang_id`,`setting_bunga`.`jenis_pinjaman` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_risiko_keluarga`
--

/*!50001 DROP VIEW IF EXISTS `v_risiko_keluarga`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_risiko_keluarga` AS select `n`.`id` AS `nasabah_id`,`n`.`nama` AS `nama`,`n`.`alamat` AS `alamat`,`n`.`ktp` AS `ktp`,`n`.`status` AS `status_nasabah`,`n`.`skor_risiko_keluarga` AS `skor_risiko_keluarga`,`fr`.`tingkat_risiko` AS `tingkat_risiko`,`fr`.`nama_kepala_keluarga` AS `nama_kepala_keluarga`,`fr`.`alamat_keluarga` AS `alamat_keluarga`,`fr`.`total_pinjaman_gagal` AS `total_pinjaman_gagal`,`fr`.`total_nasabah_bermasalah` AS `total_nasabah_bermasalah`,count(distinct `lrl`.`id`) AS `total_log_risiko` from ((`nasabah` `n` left join `family_risk` `fr` on(`n`.`alamat` like concat('%',substring_index(`fr`.`alamat_keluarga`,' ',3),'%'))) left join `loan_risk_log` `lrl` on(`n`.`id` = `lrl`.`nasabah_id`)) where `fr`.`status` = 'aktif' group by `n`.`id`,`n`.`nama`,`n`.`alamat`,`n`.`ktp`,`n`.`status`,`n`.`skor_risiko_keluarga`,`fr`.`tingkat_risiko`,`fr`.`nama_kepala_keluarga`,`fr`.`alamat_keluarga`,`fr`.`total_pinjaman_gagal`,`fr`.`total_nasabah_bermasalah` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-30  1:55:03
