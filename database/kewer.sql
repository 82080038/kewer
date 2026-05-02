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
-- Table structure for table `ai_advice`
--

DROP TABLE IF EXISTS `ai_advice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_advice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) DEFAULT NULL COMMENT 'NULL = saran umum',
  `kategori` enum('pertumbuhan','risiko','efisiensi','produk','umum') NOT NULL DEFAULT 'umum',
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `prioritas` enum('rendah','sedang','tinggi','kritis') DEFAULT 'sedang',
  `data_pendukung` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'data metrics yang mendukung saran' CHECK (json_valid(`data_pendukung`)),
  `status` enum('baru','dibaca','diterapkan','diabaikan') DEFAULT 'baru',
  `dibaca_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_advice`
--

LOCK TABLES `ai_advice` WRITE;
/*!40000 ALTER TABLE `ai_advice` DISABLE KEYS */;
INSERT INTO `ai_advice` VALUES (1,1,'umum','[Koperasi Kewer Pangururan] Kondisi operasional baik','Koperasi Koperasi Kewer Pangururan berjalan dengan baik. Tidak ada masalah kritis yang terdeteksi saat ini. Terus pantau:\n\n1. Pertumbuhan nasabah\n2. Tingkat keterlambatan pembayaran\n3. Efisiensi operasional staf\n4. Diversifikasi produk pinjaman','rendah','{\"nasabah\":2,\"pinjaman\":0,\"staf\":8}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04'),(2,26,'pertumbuhan','[Koperasi Test Mandiri] Belum memiliki nasabah — mulai akuisisi segera','Koperasi Koperasi Test Mandiri belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door','kritis','{\"nasabah_total\":0}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04'),(3,27,'pertumbuhan','[Koperasi Flow Test] Belum memiliki nasabah — mulai akuisisi segera','Koperasi Koperasi Flow Test belum memiliki nasabah terdaftar. Disarankan untuk segera melakukan:\n\n1. Sosialisasi ke masyarakat sekitar\n2. Kerjasama dengan kelurahan/desa setempat\n3. Tawarkan program pinjaman ringan sebagai daya tarik awal\n4. Gunakan fitur petugas lapangan untuk kunjungan door-to-door','kritis','{\"nasabah_total\":0}','baru',NULL,'2026-05-02 15:14:04','2026-05-02 15:14:04');
/*!40000 ALTER TABLE `ai_advice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akun`
--

DROP TABLE IF EXISTS `akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40000 ALTER TABLE `akun` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `angsuran`
--

DROP TABLE IF EXISTS `angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  `petugas_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  KEY `idx_angsuran_jatuh_tempo` (`jatuh_tempo`),
  KEY `idx_angsuran_pinjaman_status` (`pinjaman_id`,`status`),
  KEY `idx_angsuran_cabang_status` (`status`),
  KEY `idx_angsuran_frekuensi` (`frekuensi`),
  KEY `idx_angsuran_jatuh_tempo_status` (`jatuh_tempo`,`status`),
  CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `chk_angsuran_jatuh_tempo` CHECK (`jatuh_tempo` >= '2000-01-01')
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `angsuran`
--

LOCK TABLES `angsuran` WRITE;
/*!40000 ALTER TABLE `angsuran` DISABLE KEYS */;
INSERT INTO `angsuran` VALUES (1,1,1,NULL,1,'2026-06-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:34:59',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(2,1,1,NULL,2,'2026-07-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:35:10',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(3,1,1,NULL,3,'2026-08-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:35:10',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(4,1,1,NULL,4,'2026-09-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:35:11',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(5,1,1,NULL,5,'2026-10-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:35:11',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL),(6,1,1,NULL,6,'2026-11-02',833333.33,100000.00,933333.33,0.00,0.00,'lunas','2026-05-02','tunai','2026-05-02 14:20:40','2026-05-02 14:35:11',NULL,0.00,0.00,NULL,NULL,NULL,0,933333.33,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES (1,1,'CREATE','cabang',1,NULL,'{\"kode_cabang\":\"HQ001\",\"nama_cabang\":\"Kantor Pusat Pangururan\",\"is_headquarters\":true}','::1','curl/8.5.0','2026-05-02 14:17:25'),(2,1,'CREATE','cabang',2,'null','{\"kode_cabang\":\"CB001\",\"nama_cabang\":\"Cabang Balige\",\"alamat\":\"Jl. SM Raja No.5, Balige\"}',NULL,NULL,'2026-05-02 14:17:42');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auto_confirm_settings`
--

DROP TABLE IF EXISTS `auto_confirm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auto_confirm_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_enabled` (`enabled`),
  CONSTRAINT `auto_confirm_settings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auto_confirm_settings_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auto_confirm_settings`
--

LOCK TABLES `auto_confirm_settings` WRITE;
/*!40000 ALTER TABLE `auto_confirm_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `auto_confirm_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_plans`
--

DROP TABLE IF EXISTS `billing_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('fixed','percentage','usage') NOT NULL DEFAULT 'fixed',
  `harga_bulanan` decimal(15,2) DEFAULT 0.00,
  `persentase_keuntungan` decimal(5,2) DEFAULT 0.00,
  `harga_per_api_call` decimal(10,4) DEFAULT 0.0000,
  `harga_per_render` decimal(10,4) DEFAULT 0.0000,
  `api_call_gratis` int(11) DEFAULT 0,
  `render_gratis` int(11) DEFAULT 0,
  `max_users` int(11) DEFAULT NULL,
  `max_cabang` int(11) DEFAULT NULL,
  `max_nasabah` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_plans`
--

LOCK TABLES `billing_plans` WRITE;
/*!40000 ALTER TABLE `billing_plans` DISABLE KEYS */;
INSERT INTO `billing_plans` VALUES (1,'STARTER','Starter','fixed',250000.00,0.00,0.0000,0.0000,0,0,5,1,100,'Paket starter untuk koperasi kecil. Max 5 user, 1 cabang, 100 nasabah.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(2,'GROWTH','Growth','fixed',750000.00,0.00,0.0000,0.0000,0,0,20,5,500,'Paket growth untuk koperasi berkembang. Max 20 user, 5 cabang, 500 nasabah.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(3,'PRO','Professional','fixed',1500000.00,0.00,0.0000,0.0000,0,0,NULL,NULL,NULL,'Paket pro untuk koperasi besar. Unlimited user & cabang.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(4,'REVENUE_SHARE','Revenue Share','percentage',0.00,2.50,0.0000,0.0000,0,0,NULL,NULL,NULL,'Bayar 2.5% dari keuntungan bulanan koperasi. Cocok untuk koperasi yang baru mulai.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48'),(5,'PAY_AS_YOU_GO','Pay As You Go','usage',0.00,0.00,10.0000,5.0000,10000,50000,NULL,NULL,NULL,'Bayar sesuai pemakaian. 10.000 API call & 50.000 render gratis/bulan.',1,'2026-05-02 15:03:48','2026-05-02 15:03:48');
/*!40000 ALTER TABLE `billing_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blacklist_log`
--

DROP TABLE IF EXISTS `blacklist_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nasabah_id` int(11) NOT NULL,
  `aksi` enum('blacklist','unblacklist') NOT NULL,
  `alasan` text DEFAULT NULL,
  `dilakukan_oleh` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nasabah_id` (`nasabah_id`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bos_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `nama_usaha` varchar(100) DEFAULT NULL,
  `alamat_usaha` text DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bos_registrations`
--

LOCK TABLES `bos_registrations` WRITE;
/*!40000 ALTER TABLE `bos_registrations` DISABLE KEYS */;
INSERT INTO `bos_registrations` VALUES (1,'patri','$2y$10$zucy/tAcsiUBX5OqgemSgud7V8Kyd5kuiryiABLy1.Rux68v5JC0.','Patri Sihaloho','patri@kewer.co.id','081234567890','Koperasi Kewer Pangururan','Jl. Sisingamangaraja No.1, Pangururan, Samosir',NULL,NULL,NULL,NULL,'approved',NULL,'2026-05-02 14:17:07',NULL,'2026-05-02 14:17:07','2026-05-02 14:17:07'),(2,'bos_test','$2y$10$O8sF3tlZnxzYmuAEFbdWRemzJP6ugwOYgfJWCNr8qt6c/CCNeYmWa','Test Bos Koperasi','test@kewer.co.id','081299990000','Koperasi Test Mandiri','Jl. Test No.1',NULL,NULL,NULL,NULL,'approved',25,'2026-05-02 14:51:52',NULL,'2026-05-02 14:51:52','2026-05-02 14:51:52'),(3,'bos_flow_test','$2y$10$aVKCp3ektX/ptO64NUiu.uHb0Ny.DAS1oqEtuxh4C8u271c8wjSpi','Flow Test Bos','flow@test.co.id','081288880001','Koperasi Flow Test','Jl. Flow No.1',NULL,NULL,NULL,NULL,'approved',25,'2026-05-02 14:54:15',NULL,'2026-05-02 14:54:15','2026-05-02 14:54:15');
/*!40000 ALTER TABLE `bos_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang`
--

DROP TABLE IF EXISTS `cabang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cabang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(10) unsigned DEFAULT NULL,
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
  `db_orang_person_id` int(11) DEFAULT NULL COMMENT 'person record for branch address',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `is_headquarters` tinyint(1) DEFAULT 0,
  `headquarters_id` int(10) unsigned DEFAULT NULL,
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_cabang` (`kode_cabang`),
  KEY `idx_owner_bos_id` (`owner_bos_id`),
  KEY `idx_created_by_user_id` (`created_by_user_id`),
  KEY `idx_cooperative_id` (`cooperative_id`),
  KEY `idx_headquarters_id` (`headquarters_id`),
  KEY `idx_db_orang_person` (`db_orang_person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang`
--

LOCK TABLES `cabang` WRITE;
/*!40000 ALTER TABLE `cabang` DISABLE KEYS */;
INSERT INTO `cabang` VALUES (1,NULL,'HQ001','Kantor Pusat Pangururan','Jl. Sisingamangaraja No.1, Pangururan','062163212345','pusat@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,16,'aktif',1,NULL,1,1,'2026-05-02 14:17:25','2026-05-02 15:34:24'),(2,NULL,'CB001','Cabang Balige','Jl. SM Raja No.5, Balige','062163254321','balige@kewer.co.id',NULL,NULL,NULL,NULL,NULL,NULL,NULL,17,'aktif',0,NULL,1,1,'2026-05-02 14:17:42','2026-05-02 15:34:24');
/*!40000 ALTER TABLE `cabang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consolidated_reports`
--

DROP TABLE IF EXISTS `consolidated_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consolidated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_cash_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `prepared_by` (`prepared_by`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delegated_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegator_id` int(11) NOT NULL COMMENT 'User who delegates',
  `delegatee_id` int(11) NOT NULL COMMENT 'User who receives delegation',
  `permission_scope` varchar(100) NOT NULL COMMENT 'Scope of delegation: all_operations, employee_crud, branch_crud, branch_employee_crud',
  `scope_limitation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON with additional limitations' CHECK (json_valid(`scope_limitation`)),
  `reason` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `delegated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `delegator_id` (`delegator_id`),
  KEY `delegatee_id` (`delegatee_id`),
  CONSTRAINT `delegated_permissions_ibfk_1` FOREIGN KEY (`delegator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delegated_permissions_ibfk_2` FOREIGN KEY (`delegatee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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
/*!40101 SET character_set_client = utf8 */;
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
  KEY `idx_denda_frekuensi` (`frekuensi`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_risk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_family_risk_risiko` (`tingkat_risiko`),
  KEY `idx_family_risk_alamat` (`alamat_keluarga`(255))
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `field_officer_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL,
  `cabang_id` int(11) DEFAULT 1,
  `activity_type` enum('survey_nasabah','input_pinjaman','kutip_angsuran','follow_up','promosi','edukasi','lainnya') NOT NULL,
  `nasabah_id` int(11) DEFAULT NULL,
  `pinjaman_id` int(11) DEFAULT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `daerah_province_id` int(10) unsigned DEFAULT NULL,
  `daerah_regency_id` int(10) unsigned DEFAULT NULL,
  `daerah_district_id` int(10) unsigned DEFAULT NULL,
  `daerah_village_id` bigint(20) unsigned DEFAULT NULL,
  `activity_date` date NOT NULL,
  `activity_time` time NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nasabah_id` (`nasabah_id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `idx_petugas_cabang` (`petugas_id`),
  KEY `idx_activity_date` (`activity_date`),
  KEY `idx_activity_type` (`activity_type`),
  KEY `idx_status` (`status`),
  KEY `idx_daerah` (`daerah_district_id`,`daerah_village_id`),
  CONSTRAINT `field_officer_activities_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nomor_jurnal` varchar(50) NOT NULL,
  `tanggal_jurnal` date NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_nomor_jurnal` (`nomor_jurnal`),
  KEY `idx_tanggal` (`tanggal_jurnal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal`
--

LOCK TABLES `jurnal` WRITE;
/*!40000 ALTER TABLE `jurnal` DISABLE KEYS */;
INSERT INTO `jurnal` VALUES (1,1,'JRNL-20260502-001-0001','2026-05-02','2026-05-02','Pencairan pinjaman PNJ001 untuk nasabah',1,'2026-05-02 14:20:40','2026-05-02 14:20:40');
/*!40000 ALTER TABLE `jurnal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_detail`
--

DROP TABLE IF EXISTS `jurnal_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_detail`
--

LOCK TABLES `jurnal_detail` WRITE;
/*!40000 ALTER TABLE `jurnal_detail` DISABLE KEYS */;
INSERT INTO `jurnal_detail` VALUES (1,1,'1-2001','Piutang Pinjaman',5000000.00,0.00,'pinjaman',1,'2026-05-02 14:20:40'),(2,1,'1-1002','Kas Cabang',0.00,5000000.00,'pinjaman',1,'2026-05-02 14:20:40');
/*!40000 ALTER TABLE `jurnal_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_bon`
--

DROP TABLE IF EXISTS `kas_bon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_bon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_kasbon_karyawan` (`karyawan_id`),
  KEY `idx_kasbon_status` (`status`),
  KEY `idx_kasbon_tanggal` (`tanggal_pengajuan`),
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
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_kas_petugas_petugas` (`petugas_id`),
  KEY `idx_kas_petugas_tanggal` (`tanggal`),
  KEY `idx_kas_petugas_petugas_tanggal` (`petugas_id`,`tanggal`),
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas_petugas_setoran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `petugas_id` int(11) NOT NULL,
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
  KEY `idx_cabang_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  CONSTRAINT `kas_petugas_setoran_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
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
-- Table structure for table `koperasi_activities`
--

DROP TABLE IF EXISTS `koperasi_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'FK users.id (bos)',
  `cabang_id` int(11) DEFAULT NULL COMMENT 'FK cabang.id',
  `user_id` int(11) DEFAULT NULL COMMENT 'user yang melakukan',
  `kategori` enum('nasabah_baru','nasabah_blacklist','pinjaman_baru','pinjaman_disetujui','pinjaman_lunas','pinjaman_gagal','pembayaran','angsuran_telat','cabang_baru','petugas_baru','petugas_keluar','kas_masuk','kas_keluar','login','setting_change','lainnya') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'metadata tambahan' CHECK (json_valid(`data_json`)),
  `ref_table` varchar(50) DEFAULT NULL COMMENT 'tabel terkait (nasabah/pinjaman/etc)',
  `ref_id` int(11) DEFAULT NULL COMMENT 'ID record terkait',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_cabang` (`cabang_id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_created` (`created_at`),
  KEY `idx_ref` (`ref_table`,`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log semua aktivitas penting koperasi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_activities`
--

LOCK TABLES `koperasi_activities` WRITE;
/*!40000 ALTER TABLE `koperasi_activities` DISABLE KEYS */;
INSERT INTO `koperasi_activities` VALUES (1,1,1,NULL,'nasabah_baru','Nasabah baru: Budi Siregar','Nasabah NSB001 (Budi Siregar) terdaftar',NULL,'nasabah',1,NULL,'2026-05-02 14:19:56'),(2,1,1,NULL,'nasabah_baru','Nasabah baru: Maria Tampubolon','Nasabah NSB002 (Maria Tampubolon) terdaftar',NULL,'nasabah',2,NULL,'2026-05-02 14:19:56'),(4,1,1,NULL,'nasabah_baru','Nasabah baru: Budi Siregar','Nasabah NSB001 (Budi Siregar) terdaftar',NULL,'nasabah',1,NULL,'2026-05-02 14:19:56'),(5,1,1,NULL,'nasabah_baru','Nasabah baru: Maria Tampubolon','Nasabah NSB002 (Maria Tampubolon) terdaftar',NULL,'nasabah',2,NULL,'2026-05-02 14:19:56'),(7,1,1,19,'pinjaman_lunas','Pinjaman Rp 5,000,000 untuk nasabah #1','Pinjaman plafon Rp 5,000,000, bunga 2.00%, tenor 6 bulan','{\"plafon\": 5000000.00, \"bunga_per_bulan\": 2.00, \"tenor\": 6}','pinjaman',1,NULL,'2026-05-02 14:20:20'),(8,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #1 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',1,NULL,'2026-05-01 17:00:00'),(9,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #2 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',2,NULL,'2026-05-01 17:00:00'),(10,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #3 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',3,NULL,'2026-05-01 17:00:00'),(11,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #4 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',4,NULL,'2026-05-01 17:00:00'),(12,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #5 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',5,NULL,'2026-05-01 17:00:00'),(13,1,1,19,'pembayaran','Pembayaran Rp 933,334','Pembayaran angsuran #6 sebesar Rp 933,334','{\"jumlah_bayar\": 933334.00, \"cara_bayar\": \"tunai\"}','pembayaran',6,NULL,'2026-05-01 17:00:00');
/*!40000 ALTER TABLE `koperasi_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_billing`
--

DROP TABLE IF EXISTS `koperasi_billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'user.id of bos',
  `billing_plan_id` int(11) NOT NULL,
  `status` enum('aktif','suspended','cancelled') DEFAULT 'aktif',
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `billing_plan_id` (`billing_plan_id`),
  KEY `idx_bos` (`bos_user_id`),
  CONSTRAINT `koperasi_billing_ibfk_1` FOREIGN KEY (`billing_plan_id`) REFERENCES `billing_plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_billing`
--

LOCK TABLES `koperasi_billing` WRITE;
/*!40000 ALTER TABLE `koperasi_billing` DISABLE KEYS */;
INSERT INTO `koperasi_billing` VALUES (1,1,1,'aktif','2026-05-02',NULL,NULL,25,'2026-05-02 15:13:20','2026-05-02 15:13:20');
/*!40000 ALTER TABLE `koperasi_billing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_invoices`
--

DROP TABLE IF EXISTS `koperasi_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koperasi_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_billing_id` int(11) NOT NULL,
  `bos_user_id` int(11) NOT NULL,
  `kode_invoice` varchar(30) NOT NULL,
  `periode_bulan` int(2) NOT NULL,
  `periode_tahun` int(4) NOT NULL,
  `biaya_fixed` decimal(15,2) DEFAULT 0.00,
  `biaya_persentase` decimal(15,2) DEFAULT 0.00,
  `keuntungan_koperasi` decimal(15,2) DEFAULT 0.00 COMMENT 'basis perhitungan persentase',
  `biaya_usage` decimal(15,2) DEFAULT 0.00,
  `total_api_calls` int(11) DEFAULT 0,
  `total_renders` int(11) DEFAULT 0,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `diskon` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','terbit','dibayar','overdue','cancelled') DEFAULT 'draft',
  `tanggal_terbit` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `metode_bayar` varchar(50) DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_invoice` (`kode_invoice`),
  UNIQUE KEY `uk_billing_periode` (`koperasi_billing_id`,`periode_tahun`,`periode_bulan`),
  KEY `idx_bos` (`bos_user_id`),
  KEY `idx_periode` (`periode_tahun`,`periode_bulan`),
  CONSTRAINT `koperasi_invoices_ibfk_1` FOREIGN KEY (`koperasi_billing_id`) REFERENCES `koperasi_billing` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_invoices`
--

LOCK TABLES `koperasi_invoices` WRITE;
/*!40000 ALTER TABLE `koperasi_invoices` DISABLE KEYS */;
INSERT INTO `koperasi_invoices` VALUES (1,1,1,'INV-202605-0001',5,2026,250000.00,0.00,0.00,0.00,0,0,250000.00,0.00,250000.00,'dibayar','2026-05-02','2026-05-16','2026-05-02','transfer',NULL,NULL,'2026-05-02 15:13:20','2026-05-02 15:13:20');
/*!40000 ALTER TABLE `koperasi_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `labarugi`
--

DROP TABLE IF EXISTS `labarugi`;
/*!50001 DROP VIEW IF EXISTS `labarugi`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `labarugi` AS SELECT
 1 AS `kategori`,
  1 AS `kode`,
  1 AS `nama`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `loan_risk_log`
--

DROP TABLE IF EXISTS `loan_risk_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loan_risk_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_loan_risk_nasabah` (`nasabah_id`),
  KEY `idx_loan_risk_jenis` (`jenis_risiko`),
  KEY `idx_loan_risk_tanggal` (`tanggal_kejadian`),
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
  `cabang_id` int(11) DEFAULT 1,
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
  `blacklist_reason` text DEFAULT NULL,
  `skor_risiko_keluarga` int(11) DEFAULT 0,
  `catatan_risiko` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_nasabah` (`kode_nasabah`),
  UNIQUE KEY `ktp` (`ktp`),
  UNIQUE KEY `idx_nasabah_email` (`email`),
  KEY `idx_nasabah_ktp` (`ktp`),
  KEY `idx_referensi_nasabah` (`referensi_nasabah_id`),
  KEY `idx_nasabah_cabang_status` (`status`),
  KEY `idx_province_id` (`province_id`),
  KEY `idx_regency_id` (`regency_id`),
  KEY `idx_district_id` (`district_id`),
  KEY `idx_village_id` (`village_id`),
  KEY `idx_db_orang_user` (`db_orang_user_id`),
  KEY `idx_db_orang_address` (`db_orang_address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nasabah`
--

LOCK TABLES `nasabah` WRITE;
/*!40000 ALTER TABLE `nasabah` DISABLE KEYS */;
INSERT INTO `nasabah` VALUES (1,1,'NSB001','Budi Siregar',NULL,NULL,'Pangururan',NULL,NULL,NULL,NULL,NULL,NULL,'1201010101010001','081234500001',NULL,'Warung','',NULL,NULL,NULL,14,NULL,'aktif',NULL,0,NULL,'2026-05-02 14:19:56','2026-05-02 15:34:24'),(2,1,'NSB002','Maria Tampubolon',NULL,NULL,'Balige',NULL,NULL,NULL,NULL,NULL,NULL,'1201010101010002','081234500002',NULL,'Toko Kelontong','',NULL,NULL,NULL,15,NULL,'aktif',NULL,0,NULL,'2026-05-02 14:19:56','2026-05-02 15:34:24');
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
-- Table structure for table `nasabah_orang_mapping`
--

DROP TABLE IF EXISTS `nasabah_orang_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Temporary table structure for view `neraca`
--

DROP TABLE IF EXISTS `neraca`;
/*!50001 DROP VIEW IF EXISTS `neraca`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `neraca` AS SELECT
 1 AS `kategori`,
  1 AS `kode`,
  1 AS `nama`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `neraca_saldo`
--

DROP TABLE IF EXISTS `neraca_saldo`;
/*!50001 DROP VIEW IF EXISTS `neraca_saldo`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `neraca_saldo` AS SELECT
 1 AS `kode`,
  1 AS `nama`,
  1 AS `tipe`,
  1 AS `kategori`,
  1 AS `saldo_normal`,
  1 AS `total_debit`,
  1 AS `total_kredit`,
  1 AS `saldo_akhir` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `angsuran_id` (`angsuran_id`),
  KEY `petugas_id` (`petugas_id`),
  KEY `idx_pembayaran_pinjaman` (`pinjaman_id`),
  KEY `idx_pembayaran_pinjaman_tanggal` (`pinjaman_id`,`tanggal_bayar`),
  CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`),
  CONSTRAINT `pembayaran_ibfk_4` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
INSERT INTO `pembayaran` VALUES (1,1,1,1,'BYR001',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:34:59','2026-05-02 14:34:59',0.00,0.00,933333.33),(2,1,1,2,'BYR002',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:35:10','2026-05-02 14:35:10',0.00,0.00,933333.33),(3,1,1,3,'BYR003',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:35:10','2026-05-02 14:35:10',0.00,0.00,933333.33),(4,1,1,4,'BYR004',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33),(5,1,1,5,'BYR005',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33),(6,1,1,6,'BYR006',933334.00,0.00,933333.33,'2026-05-02','tunai',NULL,19,'','2026-05-02 14:35:11','2026-05-02 14:35:11',0.00,0.00,933333.33);
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
  `cabang_id` int(11) DEFAULT 1,
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
  KEY `idx_pengeluaran_kategori` (`kategori`),
  KEY `idx_pengeluaran_tanggal` (`tanggal`),
  KEY `idx_pengeluaran_status` (`status`),
  KEY `idx_pengeluaran_cabang_status` (`status`),
  KEY `idx_pengeluaran_kategori_tanggal` (`kategori`,`tanggal`),
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
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.read','View Dashboard','general','View dashboard statistics','2026-04-28 05:26:12'),(2,'nasabah.read','View Nasabah','nasabah','View nasabah list','2026-04-28 05:26:12'),(3,'manage_nasabah','Manage Nasabah','nasabah','Create, edit, delete nasabah','2026-04-28 05:26:12'),(4,'pinjaman.read','View Pinjaman','pinjaman','View pinjaman list','2026-04-28 05:26:12'),(5,'manage_pinjaman','Manage Pinjaman','pinjaman','Create, edit, delete pinjaman','2026-04-28 05:26:12'),(6,'pinjaman.approve','Approve Pinjaman','pinjaman','Approve pinjaman applications','2026-04-28 05:26:12'),(7,'angsuran.read','View Angsuran','angsuran','View angsuran list','2026-04-28 05:26:12'),(8,'manage_pembayaran','Manage Pembayaran','angsuran','Record payments','2026-04-28 05:26:12'),(9,'angsuran.create','Catat Aktivitas Lapangan','aktivitas','Akses untuk mencatat aktivitas lapangan','2026-04-28 05:26:12'),(10,'kas_petugas.read','View Kas Petugas','kas','View kas petugas','2026-04-28 05:26:12'),(11,'kas_petugas.update','Update Kas Petugas','kas','Approve setoran','2026-04-28 05:26:12'),(12,'kas.read','View Cash','kas','View cash reconciliation','2026-04-28 05:26:12'),(13,'kas.update','Update Cash','kas','Approve reconciliation','2026-04-28 05:26:12'),(14,'pinjaman.auto_confirm','Auto Confirm Settings','settings','Manage auto-confirm','2026-04-28 05:26:12'),(15,'users.create','Create Users','users','Create new users','2026-04-28 05:26:12'),(16,'users.read','View Users','users','View users list','2026-04-28 05:26:12'),(17,'manage_users','Manage Users','users','Edit, delete users','2026-04-28 05:26:12'),(18,'cabang.read','View Cabang','cabang','View branch list','2026-04-28 05:26:12'),(19,'manage_cabang','Manage Cabang','cabang','Create, edit, delete branches','2026-04-28 05:26:12'),(20,'view_laporan','View Laporan','laporan','View reports','2026-04-28 05:26:12'),(21,'manage_pengeluaran','Manage Pengeluaran','pengeluaran','Create, edit, delete pengeluaran','2026-04-28 05:26:12'),(22,'view_pengeluaran','View Pengeluaran','pengeluaran','View pengeluaran list','2026-04-28 05:26:12'),(23,'manage_kas_bon','Manage Kas Bon','kas_bon','Create, edit, delete kas bon','2026-04-28 05:26:12'),(24,'view_kas_bon','View Kas Bon','kas_bon','View kas bon list','2026-04-28 05:26:12'),(25,'manage_bunga','Manage Bunga','settings','Edit bunga settings','2026-04-28 05:26:12'),(26,'view_settings','View Settings','settings','View settings','2026-04-28 05:26:12'),(27,'manage_petugas','Manage Petugas','users','Create, edit, delete petugas','2026-04-28 05:26:12'),(28,'view_petugas','View Petugas','users','View petugas list','2026-04-28 05:26:12'),(29,'assign_permissions','Assign Permissions','admin','Assign permissions to users','2026-04-28 05:26:12'),(58,'nasabah.create','Nasabah create','general','Permission for nasabah.create','2026-04-29 17:41:31'),(59,'nasabah.edit','Nasabah edit','general','Permission for nasabah.edit','2026-04-29 17:41:31'),(60,'nasabah.delete','Nasabah delete','general','Permission for nasabah.delete','2026-04-29 17:41:31'),(61,'pinjaman.create','Pinjaman create','general','Permission for pinjaman.create','2026-04-29 17:41:31'),(62,'pinjaman.edit','Pinjaman edit','general','Permission for pinjaman.edit','2026-04-29 17:41:31'),(63,'pinjaman.delete','Pinjaman delete','general','Permission for pinjaman.delete','2026-04-29 17:41:31'),(64,'angsuran.edit','Angsuran edit','general','Permission for angsuran.edit','2026-04-29 17:41:31'),(65,'angsuran.delete','Angsuran delete','general','Permission for angsuran.delete','2026-04-29 17:41:31'),(66,'users.edit','Users edit','general','Permission for users.edit','2026-04-29 17:41:31'),(67,'users.delete','Users delete','general','Permission for users.delete','2026-04-29 17:41:31'),(68,'cabang.create','Cabang create','general','Permission for cabang.create','2026-04-29 17:41:31'),(69,'cabang.edit','Cabang edit','general','Permission for cabang.edit','2026-04-29 17:41:31'),(70,'cabang.delete','Cabang delete','general','Permission for cabang.delete','2026-04-29 17:41:31'),(71,'rute_harian.read','Rute_harian read','general','Permission for rute_harian.read','2026-04-29 17:41:31'),(72,'manage_app','Kelola Aplikasi','app','Akses pengelolaan level aplikasi','2026-05-02 14:44:02'),(73,'approve_bos','Approve Bos','app','Menyetujui pendaftaran Bos koperasi baru','2026-05-02 14:44:02'),(74,'view_koperasi','Lihat Koperasi','app','Melihat daftar semua koperasi terdaftar','2026-05-02 14:44:02'),(75,'suspend_koperasi','Suspend Koperasi','app','Menangguhkan koperasi','2026-05-02 14:44:02');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `petugas_daerah_tugas`
--

DROP TABLE IF EXISTS `petugas_daerah_tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `petugas_daerah_tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `petugas_id` int(11) NOT NULL COMMENT 'FK users.id (petugas)',
  `cabang_id` int(11) DEFAULT NULL,
  `province_id` int(10) unsigned DEFAULT NULL,
  `regency_id` int(10) unsigned DEFAULT NULL,
  `district_id` int(10) unsigned DEFAULT NULL,
  `village_id` bigint(20) unsigned DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL COMMENT 'nama area tugas',
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL COMMENT 'FK users.id (manager/bos)',
  PRIMARY KEY (`id`),
  KEY `idx_petugas` (`petugas_id`),
  KEY `idx_district` (`district_id`),
  KEY `idx_village` (`village_id`),
  KEY `idx_active` (`petugas_id`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Daerah tugas petugas lapangan';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `petugas_daerah_tugas`
--

LOCK TABLES `petugas_daerah_tugas` WRITE;
/*!40000 ALTER TABLE `petugas_daerah_tugas` DISABLE KEYS */;
INSERT INTO `petugas_daerah_tugas` VALUES (1,21,1,NULL,NULL,590,NULL,'Kecamatan Pangururan',1,'2026-05-02 15:36:24',1),(2,22,2,NULL,NULL,372,NULL,'Kecamatan Balige',1,'2026-05-02 15:36:24',1);
/*!40000 ALTER TABLE `petugas_daerah_tugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
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
  `jaminan_status` enum('aktif','dilepas','terjual','hilang') DEFAULT 'aktif',
  `status` enum('pengajuan','disetujui','aktif','lunas','ditolak','macet') NOT NULL DEFAULT 'pengajuan',
  `tanggal_lunas` date DEFAULT NULL,
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
  KEY `idx_pinjaman_cabang_status` (`status`),
  KEY `idx_pinjaman_nasabah_status` (`nasabah_id`,`status`),
  KEY `auto_confirmed_by` (`auto_confirmed_by`),
  KEY `idx_pinjaman_frekuensi` (`frekuensi`),
  KEY `idx_pinjaman_frekuensi_status` (`frekuensi`,`status`),
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`nasabah_id`) REFERENCES `nasabah` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pinjaman_ibfk_4` FOREIGN KEY (`auto_confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_pinjaman_plafon` CHECK (`plafon` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman`
--

LOCK TABLES `pinjaman` WRITE;
/*!40000 ALTER TABLE `pinjaman` DISABLE KEYS */;
INSERT INTO `pinjaman` VALUES (1,1,'PNJ001',1,5000000.00,6,'bulanan',2.00,600000.00,5600000.00,833333.33,100000.00,933333.33,'2026-05-02','2026-11-02','Modal usaha warung','BPKB Motor','tanpa',NULL,NULL,'aktif','lunas','2026-05-02',0,NULL,NULL,19,'2026-05-02 14:20:20','2026-05-02 14:35:11');
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ref_roles`
--

LOCK TABLES `ref_roles` WRITE;
/*!40000 ALTER TABLE `ref_roles` DISABLE KEYS */;
INSERT INTO `ref_roles` VALUES (1,'bos','Bos','Pemilik usaha dengan akses penuh untuk pengawasan operasional dan keuangan',NULL,1,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(2,'manager_pusat','Manager Pusat','Manager di kantor pusat dengan akses manajemen operasional pusat',NULL,2,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(3,'manager_cabang','Manager Cabang','Manager cabang dengan akses manajemen operasional cabang',NULL,3,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(4,'admin_pusat','Admin Pusat','Admin di kantor pusat dengan akses administratif pusat',NULL,4,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(5,'admin_cabang','Admin Cabang','Admin cabang dengan akses administratif cabang',NULL,5,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(6,'petugas_pusat','Petugas Pusat','Petugas lapangan pusat untuk kunjungan nasabah dan penagihan',NULL,6,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(7,'petugas_cabang','Petugas Cabang','Petugas lapangan cabang untuk kunjungan nasabah dan penagihan',NULL,7,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(8,'karyawan','Karyawan','Karyawan umum dengan akses dasar',NULL,8,'aktif','2026-05-02 13:19:26','2026-05-02 13:19:26'),(9,'appOwner','App Owner','Pemilik aplikasi yang mengelola pendaftaran koperasi dan persetujuan Bos',NULL,0,'aktif','2026-05-02 14:44:02','2026-05-02 14:44:02');
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
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (32,'bos','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(33,'bos','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(35,'bos','cabang.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(36,'bos','dashboard.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(37,'bos','kas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(38,'bos','kas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(39,'bos','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(40,'bos','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(41,'bos','manage_bunga',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(42,'bos','manage_cabang',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(43,'bos','manage_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(44,'bos','manage_nasabah',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(45,'bos','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(46,'bos','manage_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(47,'bos','manage_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(48,'bos','manage_pinjaman',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(49,'bos','manage_users',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(50,'bos','nasabah.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(51,'bos','pinjaman.approve',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(52,'bos','pinjaman.auto_confirm',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(53,'bos','pinjaman.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(54,'bos','users.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(55,'bos','users.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(56,'bos','view_kas_bon',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(57,'bos','view_laporan',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(58,'bos','view_pengeluaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(59,'bos','view_petugas',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(60,'bos','view_settings',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(63,'petugas_cabang','angsuran.create',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(64,'petugas_cabang','angsuran.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(65,'petugas_cabang','assign_permissions',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(66,'petugas_cabang','cabang.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(67,'petugas_cabang','dashboard.read',1,'2026-04-28 05:26:12','2026-05-02 13:29:23'),(68,'petugas_cabang','kas.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(69,'petugas_cabang','kas.update',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(70,'petugas_cabang','kas_petugas.read',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(71,'petugas_cabang','kas_petugas.update',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(72,'petugas_cabang','manage_bunga',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(73,'petugas_cabang','manage_cabang',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(74,'petugas_cabang','manage_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(75,'petugas_cabang','manage_nasabah',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(76,'petugas_cabang','manage_pembayaran',1,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(77,'petugas_cabang','manage_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(78,'petugas_cabang','manage_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(79,'petugas_cabang','manage_pinjaman',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(80,'petugas_cabang','manage_users',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(82,'petugas_cabang','pinjaman.approve',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(83,'petugas_cabang','pinjaman.auto_confirm',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(85,'petugas_cabang','users.create',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(86,'petugas_cabang','users.read',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(87,'petugas_cabang','view_kas_bon',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(89,'petugas_cabang','view_pengeluaran',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(90,'petugas_cabang','view_petugas',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(91,'petugas_cabang','view_settings',0,'2026-04-28 05:26:12','2026-04-28 05:26:12'),(108,'bos','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(109,'bos','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(110,'bos','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(111,'bos','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(112,'bos','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(113,'bos','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(114,'bos','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(115,'bos','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(116,'bos','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(117,'bos','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(118,'bos','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(119,'bos','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(120,'bos','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(121,'bos','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(131,'manager_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(132,'manager_pusat','nasabah.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(133,'manager_pusat','nasabah.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(134,'manager_pusat','nasabah.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(135,'manager_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(136,'manager_pusat','pinjaman.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(137,'manager_pusat','pinjaman.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(138,'manager_pusat','pinjaman.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(139,'manager_pusat','pinjaman.auto_confirm',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(140,'manager_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(141,'manager_pusat','angsuran.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(142,'manager_pusat','angsuran.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(143,'manager_pusat','angsuran.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(144,'manager_pusat','users.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(145,'manager_pusat','users.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(146,'manager_pusat','users.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(147,'manager_pusat','users.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(148,'manager_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(149,'manager_pusat','cabang.create',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(150,'manager_pusat','cabang.edit',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(151,'manager_pusat','cabang.delete',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(152,'manager_pusat','manage_bunga',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(153,'manager_pusat','view_settings',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(154,'manager_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(155,'manager_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(156,'manager_pusat','manage_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(157,'manager_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(158,'manager_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(159,'manager_pusat','manage_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(160,'manager_pusat','view_petugas',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(161,'manager_pusat','rute_harian.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(162,'manager_pusat','kas_petugas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(163,'manager_pusat','kas.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(164,'manager_pusat','assign_permissions',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(165,'admin_pusat','nasabah.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(166,'admin_pusat','pinjaman.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(167,'admin_pusat','angsuran.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(168,'admin_pusat','cabang.read',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(169,'admin_pusat','view_laporan',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(170,'admin_pusat','view_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(171,'admin_pusat','view_kas_bon',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(172,'admin_pusat','manage_pengeluaran',1,'2026-04-29 17:41:31','2026-04-29 17:41:31'),(173,'admin_cabang','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(174,'admin_cabang','nasabah.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(175,'admin_cabang','nasabah.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(176,'admin_cabang','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(177,'admin_cabang','pinjaman.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(178,'admin_cabang','pinjaman.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(179,'admin_cabang','angsuran.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(180,'admin_cabang','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(181,'admin_cabang','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(182,'admin_cabang','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(183,'admin_cabang','view_pengeluaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(184,'karyawan','nasabah.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(185,'karyawan','pinjaman.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(186,'karyawan','angsuran.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(187,'karyawan','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(188,'karyawan','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(189,'karyawan','view_pengeluaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(190,'manager_cabang','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(191,'manager_cabang','nasabah.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(192,'manager_cabang','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(193,'manager_cabang','pinjaman.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(194,'manager_cabang','pinjaman.approve',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(195,'manager_cabang','angsuran.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(196,'manager_cabang','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(197,'manager_cabang','kas_petugas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(198,'manager_cabang','kas_petugas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(199,'manager_cabang','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(200,'manager_cabang','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(202,'manager_cabang','view_laporan',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(203,'admin_pusat','manage_nasabah',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(204,'admin_pusat','nasabah.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(205,'admin_pusat','manage_pinjaman',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(206,'admin_pusat','pinjaman.create',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(207,'admin_pusat','pinjaman.approve',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(208,'admin_pusat','manage_pembayaran',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(209,'admin_pusat','kas.read',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(210,'admin_pusat','kas.update',1,'2026-04-30 15:10:58','2026-04-30 15:10:58'),(213,'petugas_pusat','nasabah.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(214,'petugas_pusat','nasabah.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(215,'petugas_pusat','pinjaman.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(216,'petugas_pusat','pinjaman.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(217,'petugas_pusat','angsuran.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(218,'petugas_pusat','angsuran.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(219,'petugas_pusat','kas_petugas.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(220,'petugas_pusat','rute_harian.read',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(221,'petugas_pusat','view_laporan',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(222,'petugas_pusat','kas_petugas.create',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(223,'petugas_pusat','kas_petugas.update',1,'2026-05-02 13:23:39','2026-05-02 13:23:39'),(228,'manager_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(229,'manager_cabang','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(230,'admin_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(231,'admin_cabang','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(232,'petugas_pusat','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(234,'karyawan','dashboard.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(235,'petugas_cabang','nasabah.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(236,'petugas_cabang','nasabah.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(237,'petugas_cabang','pinjaman.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(238,'petugas_cabang','pinjaman.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(239,'petugas_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(240,'petugas_cabang','view_laporan',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(241,'manager_cabang','nasabah.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(242,'manager_cabang','nasabah.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(243,'manager_cabang','nasabah.delete',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(244,'manager_cabang','pinjaman.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(245,'manager_cabang','pinjaman.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(246,'manager_cabang','angsuran.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(247,'manager_cabang','angsuran.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(248,'manager_cabang','users.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(249,'manager_cabang','cabang.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(250,'manager_cabang','view_pengeluaran',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(251,'manager_cabang','view_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(252,'manager_cabang','manage_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(253,'manager_cabang','view_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(254,'manager_cabang','manage_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(255,'manager_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(256,'manager_cabang','view_settings',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(258,'admin_cabang','nasabah.edit',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(259,'admin_cabang','nasabah.delete',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(260,'admin_cabang','angsuran.create',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(261,'admin_cabang','cabang.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(262,'admin_cabang','view_kas_bon',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(263,'admin_cabang','view_petugas',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(265,'admin_cabang','rute_harian.read',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(267,'bos','assign_permissions',1,'2026-05-02 13:29:23','2026-05-02 13:29:23'),(268,'petugas_pusat','manage_pembayaran',1,'2026-05-02 14:27:07','2026-05-02 14:27:07'),(269,'manager_pusat','manage_pembayaran',1,'2026-05-02 14:27:07','2026-05-02 14:27:07'),(270,'appOwner','manage_app',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(271,'appOwner','approve_bos',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(272,'appOwner','view_koperasi',1,'2026-05-02 14:44:02','2026-05-02 14:44:02'),(273,'appOwner','suspend_koperasi',1,'2026-05-02 14:44:02','2026-05-02 14:44:02');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting_denda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT NULL,
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
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (20,'denda_harian_percent','0.5','Denda harian dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(21,'denda_mingguan_percent','1.0','Denda mingguan dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(22,'denda_bulanan_percent','2.0','Denda bulanan dalam persen','2026-04-30 14:39:53','2026-04-30 14:39:53'),(23,'denda_grace_period_days','3','Masa toleransi keterlambatan dalam hari','2026-04-30 14:39:53','2026-04-30 14:39:53');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_log`
--

DROP TABLE IF EXISTS `transaksi_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaksi_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cabang_id` int(11) DEFAULT 1,
  `nomor_transaksi` varchar(50) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `tipe_transaksi` enum('pinjaman','angsuran','pembayaran','pengeluaran','kas_masuk','kas_keluar','kas_bon','kas_setoran','rekonsiliasi') NOT NULL,
  `jumlah` decimal(20,2) NOT NULL,
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
  KEY `idx_nasabah` (`nasabah_id`),
  KEY `idx_pinjaman` (`pinjaman_id`),
  KEY `jurnal_id` (`jurnal_id`),
  CONSTRAINT `transaksi_log_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_log`
--

LOCK TABLES `transaksi_log` WRITE;
/*!40000 ALTER TABLE `transaksi_log` DISABLE KEYS */;
INSERT INTO `transaksi_log` VALUES (1,1,'PINJ-20260502-001-0001','2026-05-02','pinjaman',5000000.00,1,1,NULL,1,'Pencairan pinjaman PNJ001','posted',1,'2026-05-02 14:20:40','2026-05-02 14:20:40');
/*!40000 ALTER TABLE `transaksi_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_daily_summary`
--

DROP TABLE IF EXISTS `usage_daily_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_daily_summary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `total_api_calls` int(11) DEFAULT 0,
  `total_renders` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bos_tanggal` (`bos_user_id`,`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_daily_summary`
--

LOCK TABLES `usage_daily_summary` WRITE;
/*!40000 ALTER TABLE `usage_daily_summary` DISABLE KEYS */;
INSERT INTO `usage_daily_summary` VALUES (1,1,'2026-05-02',22,96,'2026-05-02 15:13:20','2026-05-02 15:37:06'),(33,26,'2026-05-02',0,5,'2026-05-02 15:14:41','2026-05-02 15:14:41');
/*!40000 ALTER TABLE `usage_daily_summary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usage_log`
--

DROP TABLE IF EXISTS `usage_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bos_user_id` int(11) NOT NULL COMMENT 'owner bos of the user',
  `user_id` int(11) NOT NULL,
  `tipe` enum('api_call','page_render') NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) DEFAULT 'GET',
  `response_code` int(11) DEFAULT 200,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bos_tanggal` (`bos_user_id`,`tanggal`),
  KEY `idx_tanggal` (`tanggal`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_log`
--

LOCK TABLES `usage_log` WRITE;
/*!40000 ALTER TABLE `usage_log` DISABLE KEYS */;
INSERT INTO `usage_log` VALUES (1,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(2,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(3,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(4,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-02','2026-05-02 15:13:20'),(5,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(6,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(7,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:39'),(8,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(9,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(10,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(11,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(12,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(13,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(14,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(15,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(16,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(17,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(18,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(19,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:39'),(20,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(21,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(22,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(23,1,21,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(24,1,21,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(25,1,21,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(26,1,21,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(27,1,21,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(28,1,23,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(29,1,23,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:40'),(30,1,23,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(31,1,23,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(32,1,23,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(33,26,26,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(34,26,26,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(35,26,26,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(36,26,26,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(37,26,26,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:14:41'),(38,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:56'),(39,1,1,'api_call','/kewer/api/bos_registration.php','POST',200,'2026-05-02','2026-05-02 15:14:56'),(40,1,1,'api_call','/kewer/api/bos_registration.php','GET',200,'2026-05-02','2026-05-02 15:14:56'),(41,1,1,'api_call','/kewer/api/bos_registration.php','GET',200,'2026-05-02','2026-05-02 15:14:56'),(42,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(43,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(44,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(45,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(46,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(47,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(48,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(49,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(50,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-02','2026-05-02 15:30:12'),(51,1,1,'api_call','/kewer/api/alamat.php','GET',200,'2026-05-02','2026-05-02 15:30:13'),(52,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(53,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(54,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(55,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(56,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(57,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(58,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(59,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(60,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(61,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(62,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(63,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(64,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(65,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(66,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(67,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(68,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(69,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(70,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:31:52'),(71,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(72,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(73,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(74,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(75,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(76,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(77,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(78,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:31:53'),(79,1,1,'api_call','/kewer/api/nasabah.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(80,1,1,'api_call','/kewer/api/pinjaman.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(81,1,1,'api_call','/kewer/api/angsuran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(82,1,1,'api_call','/kewer/api/pembayaran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(83,1,1,'api_call','/kewer/api/cabang.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(84,1,1,'api_call','/kewer/api/kas_bon.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(85,1,1,'api_call','/kewer/api/pengeluaran.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(86,1,1,'api_call','/kewer/api/accounting.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(87,1,1,'api_call','/kewer/api/family_risk.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(88,1,1,'api_call','/kewer/api/kas_petugas.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(89,1,1,'api_call','/kewer/api/setting_bunga.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(90,1,1,'api_call','/kewer/api/nasabah_blacklist.php','GET',200,'2026-05-02','2026-05-02 15:31:54'),(91,1,1,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(92,1,1,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(93,1,1,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(94,1,1,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(95,1,1,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(96,1,1,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(97,1,1,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(98,1,1,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(99,1,1,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(100,1,1,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(101,1,1,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:04'),(102,1,2,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(103,1,2,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(104,1,2,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(105,1,2,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(106,1,2,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(107,1,2,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(108,1,2,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(109,1,2,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(110,1,2,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(111,1,2,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(112,1,2,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(113,1,19,'page_render','/kewer/dashboard.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(114,1,19,'page_render','/kewer/pages/nasabah/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(115,1,19,'page_render','/kewer/pages/nasabah/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(116,1,19,'page_render','/kewer/pages/pinjaman/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(117,1,19,'page_render','/kewer/pages/angsuran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(118,1,19,'page_render','/kewer/pages/petugas/index.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(119,1,19,'page_render','/kewer/pages/petugas/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:05'),(120,1,19,'page_render','/kewer/pages/cabang/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(121,1,19,'page_render','/kewer/pages/cabang/tambah.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(122,1,19,'page_render','/kewer/pages/pembayaran/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06'),(123,1,19,'page_render','/kewer/pages/users/index.php','GET',200,'2026-05-02','2026-05-02 15:37:06');
/*!40000 ALTER TABLE `usage_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
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
  `telp` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'karyawan',
  `owner_bos_id` int(10) unsigned DEFAULT NULL,
  `cabang_id` int(11) DEFAULT NULL,
  `derived_permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`derived_permissions`)),
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `limit_kasbon` decimal(12,0) DEFAULT 0,
  `gaji` decimal(12,0) DEFAULT 0,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `db_orang_person_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_owner_bos_id` (`owner_bos_id`),
  KEY `idx_db_orang_person` (`db_orang_person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'patri','$2y$10$zucy/tAcsiUBX5OqgemSgud7V8Kyd5kuiryiABLy1.Rux68v5JC0.','Patri Sihaloho','patri@kewer.co.id','081234567890','bos',NULL,1,NULL,'aktif','2026-05-02 14:17:07','2026-05-02 15:34:24',0,0,NULL,NULL,3),(2,'mgr_pusat','$2y$10$uGH5./H5aYqFVBMWUB.p/Oh3EQhw4ejNLM6Pz9ALxZpfuHTViZ4qO','Sondang Silaban','','','manager_pusat',1,1,NULL,'aktif','2026-05-02 14:18:26','2026-05-02 15:34:24',0,0,NULL,NULL,4),(18,'mgr_balige','$2y$10$M3T60PI35ooIbe7AJTFG.eEUvuuqtgSoayMu2yvpsN6yR6b6zTHFu','Roswita Nainggolan','mgr_balige@kewer.co.id',NULL,'manager_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,5),(19,'adm_pusat','$2y$10$0NOdONMqf3C2krjFnXaDVujJj6jybgKQ.HF5mbnnuHijJQ2CdaWyi','Melvina Hutabarat','adm_pusat@kewer.co.id',NULL,'admin_pusat',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,6),(20,'adm_balige','$2y$10$kvbrWAiSVSdYfgfdcJGpK.qGYjopxzgL8HZd/vuJT3Vz1SI6i5UCC','Ruli Sirait','adm_balige@kewer.co.id',NULL,'admin_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,7),(21,'ptr_pusat','$2y$10$MLr90EoeRX1o2pD7qS4pwODQ2rX/WI9Nwm2dKfjvIga5khYdQ9tO6','Darwin Sinaga','ptr_pusat@kewer.co.id',NULL,'petugas_pusat',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,8),(22,'ptr_balige','$2y$10$RoBvhk/8HBof0rZmYYXxmuEtnpS2aXZ2/1OxohVXGIFHtKAjAOv16','Markus Situmorang','ptr_balige@kewer.co.id',NULL,'petugas_cabang',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,9),(23,'krw_pusat','$2y$10$JOIggChOhqoxkU1capAwG./eELKhgkl0HTpy4Qgx90ejPuolVFDAm','Susi Aritonang','krw_pusat@kewer.co.id',NULL,'karyawan',1,1,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,10),(24,'krw_balige','$2y$10$LhWlwITO.UVOYFmpymybTe5CxDIzRZyfgr0kyzXOBPRmgXfynNzBu','Petrus Hutagalung','krw_balige@kewer.co.id',NULL,'karyawan',1,2,NULL,'aktif','2026-05-02 14:19:20','2026-05-02 15:34:24',0,0,NULL,NULL,11),(25,'appowner','$2y$10$CtXCJToI4qyhfCTWl7yPjOxF9fLr1rJwrT6LjD1dFvcFWJoHs2GhG','App Owner','admin@kewer.app',NULL,'appOwner',NULL,NULL,NULL,'aktif','2026-05-02 14:44:02','2026-05-02 15:13:20',0,0,NULL,NULL,NULL),(26,'bos_test','$2y$10$O8sF3tlZnxzYmuAEFbdWRemzJP6ugwOYgfJWCNr8qt6c/CCNeYmWa','Test Bos Koperasi','test@kewer.co.id','081299990000','bos',NULL,NULL,NULL,'aktif','2026-05-02 14:51:52','2026-05-02 15:34:24',0,0,NULL,NULL,12),(27,'bos_flow_test','$2y$10$aVKCp3ektX/ptO64NUiu.uHb0Ny.DAS1oqEtuxh4C8u271c8wjSpi','Flow Test Bos','flow@test.co.id','081288880001','bos',NULL,NULL,NULL,'aktif','2026-05-02 14:54:15','2026-05-02 15:34:24',0,0,NULL,NULL,13);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'kewer'
--
