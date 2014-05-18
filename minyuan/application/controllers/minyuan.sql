-- MySQL dump 10.13  Distrib 5.6.10, for osx10.8 (x86_64)
--
-- Host: localhost    Database: minyuan
-- ------------------------------------------------------
-- Server version	5.6.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `minyuan`
--

DROP TABLE IF EXISTS `minyuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minyuan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `order_name` varchar(255) NOT NULL DEFAULT '' COMMENT '订单名称',
  `status` varchar(50) NOT NULL DEFAULT '正在制作' COMMENT '订单状态',
  `uniq` char(32) NOT NULL DEFAULT '',
  `uid` int(11) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq` (`uniq`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `minyuan`
--

LOCK TABLES `minyuan` WRITE;
/*!40000 ALTER TABLE `minyuan` DISABLE KEYS */;
INSERT INTO `minyuan` VALUES (4,'13472688824','uuuuu','你好','f926d8dbebdd0efbe1afd4b56ac1282c',1,'2014-05-17 16:14:30'),(5,'13472688824','uuuuu1','333','a2ce897cdc4fce50468afb3721380a44',1,'2014-05-17 16:20:05'),(6,'13472688824','uuuuu2','正在加工','4815bb181ccaece97dbb53a52216625d',1,'2014-05-17 16:22:38');
/*!40000 ALTER TABLE `minyuan` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-05-18 12:23:14
