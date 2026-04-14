-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: kewer
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `angsuran`
--

DROP TABLE IF EXISTS `angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `no_angsuran` int(11) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `pokok` decimal(12,2) NOT NULL,
  `bunga` decimal(12,2) NOT NULL,
  `total_angsuran` decimal(12,2) NOT NULL,
  `denda` decimal(12,2) DEFAULT 0.00,
  `total_bayar` decimal(12,2) DEFAULT 0.00,
  `status` enum('belum','lunas','telat') NOT NULL DEFAULT 'belum',
  `tanggal_bayar` date DEFAULT NULL,
  `cara_bayar` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  KEY `idx_angsuran_jatuh_tempo` (`jatuh_tempo`),
  KEY `idx_angsuran_pinjaman_status` (`pinjaman_id`,`status`),
  KEY `idx_angsuran_cabang_status` (`cabang_id`,`status`),
  CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
  CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `chk_angsuran_jatuh_tempo` CHECK (`jatuh_tempo` >= '2000-01-01')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `angsuran`
--

LOCK TABLES `angsuran` WRITE;
/*!40000 ALTER TABLE `angsuran` DISABLE KEYS */;
/*!40000 ALTER TABLE `angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_cabang` (`kode_cabang`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (1,'CBG001','Pusat','Jl. Merdeka No. 123','021-12345678',NULL,'Jakarta','DKI Jakarta',NULL,'aktif','2026-04-14 15:58:47','2026-04-14 15:58:47'),(2,'CBG002','Cabang A','Jl. Sudirman No. 456','021-87654321',NULL,'Jakarta','DKI Jakarta',NULL,'aktif','2026-04-14 15:58:47','2026-04-14 15:58:47');
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_risk`
--

DROP TABLE IF EXISTS `family_risk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `kas_bon`
--

DROP TABLE IF EXISTS `kas_bon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  CONSTRAINT `kas_bon_ibfk_3` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_kas_bon_jumlah` CHECK (`jumlah` > 0),
  CONSTRAINT `chk_kas_bon_tenor` CHECK (`tenor_bulan` > 0)
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
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `loan_risk_log`
--

DROP TABLE IF EXISTS `loan_risk_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nasabah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `kode_nasabah` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `alamat_rumah` text DEFAULT NULL,
  `hubungan_keluarga` text DEFAULT NULL,
  `ktp` varchar(16) NOT NULL,
  `telp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_usaha` varchar(50) DEFAULT NULL,
  `lokasi_pasar` varchar(100) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_selfie` varchar(255) DEFAULT NULL,
  `referensi_nasabah_id` int(11) DEFAULT NULL,
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
  CONSTRAINT `nasabah_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah`
--

LOCK TABLES `nasabah` WRITE;
/*!40000 ALTER TABLE `nasabah` DISABLE KEYS */;
/*!40000 ALTER TABLE `nasabah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nasabah_family_link`
--

DROP TABLE IF EXISTS `nasabah_family_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) NOT NULL,
  `kode_pinjaman` varchar(20) NOT NULL,
  `nasabah_id` int(11) NOT NULL,
  `plafon` decimal(12,2) NOT NULL,
  `tenor` int(11) NOT NULL,
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
  `status` enum('pengajuan','disetujui','aktif','lunas','ditolak') NOT NULL DEFAULT 'pengajuan',
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
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`),
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_pinjaman_plafon` CHECK (`plafon` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman`
--

LOCK TABLES `pinjaman` WRITE;
/*!40000 ALTER TABLE `pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinjaman` ENABLE KEYS */;
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
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jaminan_tipe`
--

LOCK TABLES `ref_jaminan_tipe` WRITE;
/*!40000 ALTER TABLE `ref_jaminan_tipe` DISABLE KEYS */;
INSERT INTO `ref_jaminan_tipe` VALUES (1,'tanpa','Tanpa Jaminan','No collateral','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'bpkb','BPKB Kendaraan','Vehicle registration','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'shm','SHM Tanah','Land certificate','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(4,'ajb','AJB','Sale deed','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(5,'tabungan','Tabungan','Savings collateral','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_jaminan_tipe` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_jenis_usaha`
--

DROP TABLE IF EXISTS `ref_jenis_usaha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_jenis_usaha`
--

LOCK TABLES `ref_jenis_usaha` WRITE;
/*!40000 ALTER TABLE `ref_jenis_usaha` DISABLE KEYS */;
INSERT INTO `ref_jenis_usaha` VALUES (1,'pedagang_sayur','Pedagang Sayur','Pertanian','Vegetable seller','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'pedagang_buah','Pedagang Buah','Pertanian','Fruit seller','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'warung_makan','Warung Makan','Kuliner','Food stall','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(4,'warung_kelontong','Warung Kelontong','Retail','Grocery store','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(5,'toko_baju','Toko Baju','Retail','Clothing store','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(6,'lainnya','Lainnya','Lainnya','Other businesses','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_jenis_usaha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_kategori_pengeluaran`
--

DROP TABLE IF EXISTS `ref_kategori_pengeluaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_kategori_pengeluaran`
--

LOCK TABLES `ref_kategori_pengeluaran` WRITE;
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` DISABLE KEYS */;
INSERT INTO `ref_kategori_pengeluaran` VALUES (1,'gaji','Gaji','Employee salaries','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'lembur','Lembur','Overtime pay','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'bonus','Bonus','Performance bonuses','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(4,'operasional','Operasional','Operational expenses','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(5,'belanja','Belanja','Purchases','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(6,'lainnya','Lainnya','Other expenses','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_kategori_pengeluaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_metode_pembayaran`
--

DROP TABLE IF EXISTS `ref_metode_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_metode_pembayaran`
--

LOCK TABLES `ref_metode_pembayaran` WRITE;
/*!40000 ALTER TABLE `ref_metode_pembayaran` DISABLE KEYS */;
INSERT INTO `ref_metode_pembayaran` VALUES (1,'tunai','Tunai','Cash payment','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'transfer','Transfer Bank','Bank transfer','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'digital','E-Wallet/Digital','Digital payment','aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_metode_pembayaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_roles`
--

DROP TABLE IF EXISTS `ref_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_roles`
--

LOCK TABLES `ref_roles` WRITE;
/*!40000 ALTER TABLE `ref_roles` DISABLE KEYS */;
INSERT INTO `ref_roles` VALUES (1,'superadmin','Super Administrator','Full access to all features',NULL,1,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'admin','Administrator','Manage branch operations',NULL,2,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'petugas','Petugas Lapangan','Field operations and collections',NULL,3,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(4,'karyawan','Karyawan','Employee access',NULL,4,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ref_status_pinjaman`
--

DROP TABLE IF EXISTS `ref_status_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_status_pinjaman`
--

LOCK TABLES `ref_status_pinjaman` WRITE;
/*!40000 ALTER TABLE `ref_status_pinjaman` DISABLE KEYS */;
INSERT INTO `ref_status_pinjaman` VALUES (1,'pengajuan','Pengajuan','Loan application submitted',1,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(2,'disetujui','Disetujui','Loan approved',2,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(3,'aktif','Aktif','Loan is active',3,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(4,'lunas','Lunas','Loan fully paid',4,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(5,'ditolak','Ditolak','Loan rejected',5,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38'),(6,'macet','Macet','Loan defaulted',6,'aktif','2026-04-14 16:53:38','2026-04-14 16:53:38');
/*!40000 ALTER TABLE `ref_status_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `setting_bunga`
--

DROP TABLE IF EXISTS `setting_bunga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_bunga` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
  `jenis_pinjaman` varchar(50) NOT NULL,
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
  KEY `cabang_id` (`cabang_id`),
  CONSTRAINT `setting_bunga_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `setting_bunga`
--

LOCK TABLES `setting_bunga` WRITE;
/*!40000 ALTER TABLE `setting_bunga` DISABLE KEYS */;
INSERT INTO `setting_bunga` VALUES (1,NULL,'harian',1,1,2.50,1.00,5.00,0.50,1.00,'aktif','2026-04-14 16:19:21','2026-04-14 16:19:21'),(2,NULL,'mingguan',1,4,2.00,1.50,3.00,0.50,0.50,'aktif','2026-04-14 16:19:21','2026-04-14 16:19:21'),(3,NULL,'bulanan',1,24,2.50,1.50,4.00,1.00,0.50,'aktif','2026-04-14 16:19:21','2026-04-14 16:19:21'),(4,NULL,'multi_guna',2,24,2.50,1.50,4.00,1.00,-0.50,'aktif','2026-04-14 16:19:21','2026-04-14 16:19:21');
/*!40000 ALTER TABLE `setting_bunga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'bunga_default','2.5','Bunga default per bulan (%)','2026-04-14 15:58:47','2026-04-14 15:58:47'),(2,'max_plafon','10000000','Maksimal plafon pinjaman','2026-04-14 15:58:47','2026-04-14 15:58:47'),(3,'max_tenor','12','Maksimal tenor (bulan)','2026-04-14 15:58:47','2026-04-14 15:58:47'),(4,'denda_keterlambatan','0.5','Denda keterlambatan per hari (%)','2026-04-14 15:58:47','2026-04-14 15:58:47'),(5,'min_plafon_tanpa_jaminan','1000000','Minimal plafon tanpa jaminan','2026-04-14 16:19:21','2026-04-14 16:19:21'),(6,'min_plafon_dengan_jaminan','5000000','Minimal plafon dengan jaminan','2026-04-14 16:19:21','2026-04-14 16:19:21'),(7,'alert_kas_petugas_selisih','100000','Alert jika selisih kas petugas melebihi nominal ini','2026-04-14 16:19:21','2026-04-14 16:19:21'),(8,'require_approval_pengeluaran','500000','Minimal nominal pengeluaran yang butuh approval','2026-04-14 16:19:21','2026-04-14 16:19:21'),(9,'auto_blacklist_family','3','Otomatis blacklist keluarga jika ada 3+ nasabah bermasalah','2026-04-14 16:25:12','2026-04-14 16:25:12'),(10,'family_risk_threshold','20','Threshold skor risiko keluarga untuk peringatan','2026-04-14 16:25:12','2026-04-14 16:25:12'),(11,'require_family_verification','500000','Minimal plafon untuk verifikasi keluarga','2026-04-14 16:25:12','2026-04-14 16:25:12'),(12,'max_kasbon_percentage','50','Maksimal kasbon dalam persentase dari gaji (default 50%)','2026-04-14 16:40:25','2026-04-14 16:40:25'),(13,'min_gaji_kasbon','1000000','Minimal gaji untuk bisa kasbon','2026-04-14 16:40:25','2026-04-14 16:40:25'),(14,'auto_approve_kasbon','0','Otomatis approve kasbon jika <= limit','2026-04-14 16:40:25','2026-04-14 16:40:25'),(15,'kasbon_require_approval','500000','Minimal kasbon yang butuh approval','2026-04-14 16:40:25','2026-04-14 16:40:25');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','admin','petugas','karyawan') NOT NULL DEFAULT 'petugas',
  `cabang_id` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `limit_kasbon` decimal(12,0) DEFAULT 0,
  `gaji` decimal(12,0) DEFAULT 0,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator','admin@kewer.com','superadmin',NULL,'aktif','2026-04-14 15:58:47','2026-04-14 15:58:47',0,0,NULL,NULL),(2,'petugas1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Petugas Satu','petugas1@kewer.com','petugas',1,'aktif','2026-04-14 15:58:47','2026-04-14 15:58:47',0,0,NULL,NULL),(3,'owner','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Owner Perusahaan','owner@kewer.com','superadmin',1,'aktif','2026-04-14 16:52:33','2026-04-14 16:52:33',10000000,0,NULL,NULL),(8,'manager1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Manager Cabang','manager1@kewer.com','admin',1,'aktif','2026-04-14 16:53:02','2026-04-14 16:53:02',15000000,8000000,'1985-05-15','2020-01-01'),(9,'karyawan1','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Budi Santoso','budi@kewer.com','karyawan',1,'aktif','2026-04-14 16:53:02','2026-04-14 16:53:02',3000000,4500000,'1990-08-20','2021-03-15'),(10,'karyawan2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Siti Aminah','siti@kewer.com','karyawan',1,'aktif','2026-04-14 16:53:02','2026-04-14 16:53:02',3000000,4200000,'1992-12-10','2021-06-01'),(11,'petugas2','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ahmad Dani','ahmad@kewer.com','petugas',1,'aktif','2026-04-14 16:53:02','2026-04-14 16:53:02',2000000,3500000,'1988-03-25','2022-01-10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_karyawan_kasbon`
--

DROP TABLE IF EXISTS `v_karyawan_kasbon`;
/*!50001 DROP VIEW IF EXISTS `v_karyawan_kasbon`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_karyawan_kasbon` AS SELECT
 1 AS `karyawan_id`,
  1 AS `nama_karyawan`,
  1 AS `cabang_id`,
  1 AS `limit_kasbon`,
  1 AS `total_kasbon`,
  1 AS `total_dipinjam`,
  1 AS `total_sisa`,
  1 AS `total_lunas` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_kasbon_summary`
--

DROP TABLE IF EXISTS `v_kasbon_summary`;
/*!50001 DROP VIEW IF EXISTS `v_kasbon_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  1 AS `total_dipotong` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_laporan_kas_harian`
--

DROP TABLE IF EXISTS `v_laporan_kas_harian`;
/*!50001 DROP VIEW IF EXISTS `v_laporan_kas_harian`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_laporan_kas_harian` AS SELECT
 1 AS `cabang_id`,
  1 AS `tanggal`,
  1 AS `total_saldo_awal`,
  1 AS `total_terima`,
  1 AS `total_disetor`,
  1 AS `total_saldo_akhir`,
  1 AS `jumlah_petugas` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_laporan_pengeluaran_kategori`
--

DROP TABLE IF EXISTS `v_laporan_pengeluaran_kategori`;
/*!50001 DROP VIEW IF EXISTS `v_laporan_pengeluaran_kategori`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_laporan_pengeluaran_kategori` AS SELECT
 1 AS `cabang_id`,
  1 AS `kategori`,
  1 AS `jumlah_transaksi`,
  1 AS `total_pengeluaran` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_ringkasan_bunga`
--

DROP TABLE IF EXISTS `v_ringkasan_bunga`;
/*!50001 DROP VIEW IF EXISTS `v_ringkasan_bunga`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_ringkasan_bunga` AS SELECT
 1 AS `cabang_id`,
  1 AS `jenis_pinjaman`,
  1 AS `rata_rata_bunga`,
  1 AS `bunga_terendah`,
  1 AS `bunga_tertinggi` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_risiko_keluarga`
--

DROP TABLE IF EXISTS `v_risiko_keluarga`;
/*!50001 DROP VIEW IF EXISTS `v_risiko_keluarga`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  1 AS `total_log_risiko` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_karyawan_kasbon`
--

/*!50001 DROP VIEW IF EXISTS `v_karyawan_kasbon`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
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

-- Dump completed on 2026-04-15  1:36:37
