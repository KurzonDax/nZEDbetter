-- MySQL dump 10.13  Distrib 5.6.12, for Linux (x86_64)
--
-- Host: localhost    Database: nzedb
-- ------------------------------------------------------
-- Server version	5.6.12-rc60.4-log

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
-- Temporary table structure for view `UniqueCollections`
--

DROP TABLE IF EXISTS `UniqueCollections`;
/*!50001 DROP VIEW IF EXISTS `UniqueCollections`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `UniqueCollections` (
  `ID` tinyint NOT NULL,
  `subject` tinyint NOT NULL,
  `fromname` tinyint NOT NULL,
  `date` tinyint NOT NULL,
  `xref` tinyint NOT NULL,
  `totalFiles` tinyint NOT NULL,
  `groupID` tinyint NOT NULL,
  `collectionhash` tinyint NOT NULL,
  `dateadded` tinyint NOT NULL,
  `filecheck` tinyint NOT NULL,
  `filesize` tinyint NOT NULL,
  `releaseID` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `anidb`
--

DROP TABLE IF EXISTS `anidb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anidb` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `anidbID` int(7) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `related` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `creators` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `rating` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `categories` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `characters` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `epnos` varchar(2048) COLLATE utf8_unicode_ci NOT NULL,
  `airdates` text COLLATE utf8_unicode_ci NOT NULL,
  `episodetitles` text COLLATE utf8_unicode_ci NOT NULL,
  `unixtime` int(12) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `anidbID` (`anidbID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anidb`
--

LOCK TABLES `anidb` WRITE;
/*!40000 ALTER TABLE `anidb` DISABLE KEYS */;
/*!40000 ALTER TABLE `anidb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animetitles`
--

DROP TABLE IF EXISTS `animetitles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animetitles` (
  `anidbID` int(7) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unixtime` int(12) unsigned NOT NULL,
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animetitles`
--

LOCK TABLES `animetitles` WRITE;
/*!40000 ALTER TABLE `animetitles` DISABLE KEYS */;
/*!40000 ALTER TABLE `animetitles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `binaries`
--

DROP TABLE IF EXISTS `binaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `binaries` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `collectionID` int(11) unsigned NOT NULL DEFAULT '0',
  `filenumber` int(10) unsigned NOT NULL DEFAULT '0',
  `totalParts` int(11) unsigned NOT NULL DEFAULT '0',
  `binaryhash` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `partcheck` int(11) unsigned NOT NULL DEFAULT '0',
  `partsize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `partsInDB` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `binaryhash` (`binaryhash`),
  KEY `ix_binary_partcheck` (`partcheck`),
  KEY `filenumber` (`filenumber`),
  KEY `partsInDB` (`partsInDB`),
  KEY `ix_collection_filenum` (`collectionID`,`filenumber`)
) ENGINE=InnoDB AUTO_INCREMENT=5602827 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `binaries`
--

LOCK TABLES `binaries` WRITE;
/*!40000 ALTER TABLE `binaries` DISABLE KEYS */;
/*!40000 ALTER TABLE `binaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `binaryblacklist`
--

DROP TABLE IF EXISTS `binaryblacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `binaryblacklist` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `regex` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `msgcol` int(11) unsigned NOT NULL DEFAULT '1',
  `optype` int(11) unsigned NOT NULL DEFAULT '1',
  `status` int(11) unsigned NOT NULL DEFAULT '1',
  `description` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `binaryblacklist`
--

LOCK TABLES `binaryblacklist` WRITE;
/*!40000 ALTER TABLE `binaryblacklist` DISABLE KEYS */;
INSERT INTO `binaryblacklist` VALUES (1,'alt.binaries.*','(brazilian|chinese|croatian|danish|deutsch|dutch|estonian|flemish|finnish|french|german|greek|hebrew|icelandic|italian|latin|nordic|norwegian|polish|portuguese|japenese|japanese|russian|serbian|slovenian|spanish|spanisch|swedish|thai|turkish)[\\)]?( \\-)?[ \\-\\.]((19|20)\\d\\d|(480|720|1080)(i|p)|3d|5\\.1|dts|ac3|truehd|(bd|dvd|hd|sat|vhs|web)\\.?rip|(bd.)?(h|x).?2?64|divx|xvid|bluray|svcd|board|custom|\"|(d|h|p|s)d?v?tv|m?dvd(-|sc)?r|int(ernal)?|nzb|par2|\\b(((dc|ld|md|ml|dl|hr|se)[.])|(anime\\.)|(fs|ws)|dsr|pal|ntsc|iso|complete|cracked|ebook|extended|dirfix|festival|proper|game|limited|read.?nfo|real|rerip|repack|remastered|retail|samplefix|scan|screener|theatrical|uncut|unrated|incl|winall)\\b|doku|doc|dub|sub|\\(uncut\\))',1,1,0,'Blacklists non-english releases.'),(2,'alt.binaries.*','[ -.](bl|cz|de|es|fr|ger|heb|hu|hun|ita|ko|kor|nl|pl|se)[ -.]((19|20)\\d\\d|(480|720|1080)(i|p)|(bd|dvd.?|sat|vhs)?rip?|(bd|dl)mux|( -.)?(dub|sub)(ed|bed)?|complete|convert|(d|h|p|s)d?tv|dirfix|docu|dual|dvbs|dvdscr|eng|(h|x).?2?64|int(ernal)?|pal|proper|repack|xbox)',1,1,0,'Blacklists non-english abbreviated releases.'),(3,'alt.binaries.*','[ -.]((19|20)\\d\\d|(bd|dvd.?|sat|vhs)?rip?|custom|divx|dts)[ -.](bl|cz|de|es|fr|ger|heb|hu|ita|ko|kor|nl|pl|se)[ -.]',1,1,0,'Blacklists non-english abbreviated (reversed) releases.'),(4,'alt.binaries.*','[ -.](chinese.subbed|dksubs|fansubs?|finsub|hebdub|hebsub|korsub|norsub|nordicsubs|nl( -.)?sub(ed|bed|s)?|nlvlaams|pldub|plsub|slosinh|swesub|truefrench|vost(fr)?)[ -.]',1,1,0,'Blacklists non-english subtitled releases.'),(5,'alt.binaries.*','[ -._](4u\\.nl|nov[ a]+rip|realco|videomann|vost)[ -._]',1,1,0,'Blacklists non-english (release group specific) releases.'),(6,'alt.binaries.*','[ -.]((bd|dl)mux|doku|\\[foreign\\]|seizoen|staffel)[ -.]',1,1,0,'Blacklists non-english (lang specific) releases.'),(7,'alt.binaries.*','[ -.](imageset|pictureset|xxx)[ -.]',1,1,0,'Blacklists porn releases.'),(8,'alt.binaries.*','hdnectar|nzbcave',1,1,0,'Bad releases.'),(9,'alt.binaries.*','Passworded',1,1,0,'Removes passworded releases.');
/*!40000 ALTER TABLE `binaryblacklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookinfo`
--

DROP TABLE IF EXISTS `bookinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookinfo` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asin` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isbn` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ean` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salesrank` int(10) unsigned DEFAULT NULL,
  `publisher` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publishdate` datetime DEFAULT NULL,
  `pages` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `overview` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `genre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookinfo`
--

LOCK TABLES `bookinfo` WRITE;
/*!40000 ALTER TABLE `bookinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentID` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disablepreview` tinyint(1) NOT NULL DEFAULT '0',
  `minsize` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ix_category_status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1000,'Console',NULL,1,NULL,0,0),(2000,'Movies',NULL,1,NULL,0,0),(3000,'Audio',NULL,1,NULL,0,0),(4000,'PC',NULL,1,NULL,0,0),(5000,'TV',NULL,1,NULL,0,0),(6000,'XXX',NULL,1,NULL,0,0),(7000,'Other',NULL,1,NULL,0,0),(8000,'Books',NULL,1,NULL,0,0),(1010,'NDS',1000,1,NULL,0,0),(1020,'PSP',1000,1,NULL,0,0),(1030,'Wii',1000,1,NULL,0,0),(1040,'Xbox',1000,1,NULL,0,0),(1050,'Xbox 360',1000,1,NULL,0,0),(1060,'WiiWare/VC',1000,1,NULL,0,0),(1070,'XBOX 360 DLC',1000,1,NULL,0,0),(1080,'PS3',1000,1,NULL,0,0),(1090,'Other',1000,1,NULL,0,0),(2010,'Foreign',2000,1,NULL,0,0),(2020,'Other',2000,1,NULL,0,0),(2030,'SD',2000,1,NULL,0,0),(2040,'HD',2000,1,NULL,0,0),(2050,'3D',2000,1,NULL,0,0),(2060,'BluRay',2000,1,NULL,0,0),(2070,'DVD',2000,1,NULL,0,0),(3010,'MP3',3000,1,NULL,0,0),(3020,'Video',3000,1,NULL,0,0),(3030,'Audiobook',3000,1,NULL,0,0),(3040,'Lossless',3000,1,NULL,0,0),(3050,'Other',3000,1,NULL,0,0),(3060,'Foreign',3000,1,NULL,0,0),(4010,'0day',4000,1,NULL,0,0),(4020,'ISO',4000,1,NULL,0,0),(4030,'Mac',4000,1,NULL,0,0),(4040,'Phone-Other',4000,1,NULL,0,0),(4050,'Games',4000,1,NULL,0,0),(4060,'Phone-IOS',4000,1,NULL,0,0),(4070,'Phone-Android',4000,1,NULL,0,0),(5010,'WEB-DL',5000,1,NULL,0,0),(5020,'Foreign',5000,1,NULL,0,0),(5030,'SD',5000,1,NULL,0,0),(5040,'HD',5000,1,NULL,0,0),(5050,'Other',5000,1,NULL,0,0),(5060,'Sport',5000,1,NULL,0,0),(5070,'Anime',5000,1,NULL,0,0),(5080,'Documentary',5000,1,NULL,0,0),(6010,'DVD',6000,1,NULL,0,0),(6020,'WMV',6000,1,NULL,0,0),(6030,'XviD',6000,1,NULL,0,0),(6040,'x264',6000,1,NULL,0,0),(6050,'Other',6000,1,NULL,0,0),(6060,'Imageset',6000,1,NULL,0,0),(6070,'Packs',6000,1,NULL,0,0),(7010,'Misc',7000,1,NULL,0,0),(8010,'Ebook',8000,1,NULL,0,0),(8020,'Comics',8000,1,NULL,0,0),(8030,'Magazines',8000,1,NULL,0,0),(8040,'Technical',8000,1,NULL,0,0),(8050,'Other',8000,1,NULL,0,0),(8060,'Foreign',8000,1,NULL,0,0);
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `fromname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `xref` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `totalFiles` int(11) unsigned NOT NULL DEFAULT '0',
  `groupID` int(11) unsigned NOT NULL DEFAULT '0',
  `collectionhash` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `dateadded` datetime DEFAULT NULL,
  `filecheck` int(11) unsigned NOT NULL DEFAULT '0',
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `releaseID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `collectionhash` (`collectionhash`),
  KEY `groupID` (`groupID`),
  KEY `ix_collection_filecheck` (`filecheck`),
  KEY `ix_collection_dateadded` (`dateadded`),
  KEY `ix_collection_releaseID` (`releaseID`),
  KEY `filesize` (`filesize`),
  KEY `ix_totalFiles_filecheck` (`totalFiles`,`filecheck`),
  KEY `ix_filecheck_filesize` (`filecheck`,`filesize`),
  KEY `ix_filesize_filecheck` (`filesize`,`filecheck`)
) ENGINE=InnoDB AUTO_INCREMENT=2357651 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections`
--

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consoleinfo`
--

DROP TABLE IF EXISTS `consoleinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consoleinfo` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asin` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salesrank` int(10) unsigned DEFAULT NULL,
  `platform` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publisher` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `genreID` int(10) DEFAULT NULL,
  `esrb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `releasedate` datetime DEFAULT NULL,
  `review` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consoleinfo`
--

LOCK TABLES `consoleinfo` WRITE;
/*!40000 ALTER TABLE `consoleinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `consoleinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `metadescription` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `metakeywords` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `contenttype` int(11) NOT NULL,
  `showinmenu` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `ordinal` int(11) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `content`
--

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` VALUES (1,'Welcome to nZEDb.',NULL,'<p>Since nZEDb is a fork of newznab, the API is compatible with sickbeard, couchpotato, etc...</p>','','',3,0,1,NULL,0),(2,'example content','/great/seo/content/page/','<p>this is an example content page</p>','','',2,1,1,NULL,0),(3,'another example','/another/great/seo/content/page/','<p>this is another example content page</p>','','',2,1,1,NULL,0);
/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forumpost`
--

DROP TABLE IF EXISTS `forumpost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forumpost` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forumID` int(11) NOT NULL DEFAULT '1',
  `parentID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `replies` int(11) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `parentID` (`parentID`),
  KEY `userID` (`userID`),
  KEY `createddate` (`createddate`),
  KEY `updateddate` (`updateddate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forumpost`
--

LOCK TABLES `forumpost` WRITE;
/*!40000 ALTER TABLE `forumpost` DISABLE KEYS */;
/*!40000 ALTER TABLE `forumpost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genres` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(4) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=150 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genres`
--

LOCK TABLES `genres` WRITE;
/*!40000 ALTER TABLE `genres` DISABLE KEYS */;
INSERT INTO `genres` VALUES (1,'Blues',3000,0),(2,'Classic Rock',3000,0),(3,'Country',3000,0),(4,'Dance',3000,0),(5,'Disco',3000,0),(6,'Funk',3000,0),(7,'Grunge',3000,0),(8,'Hip-Hop',3000,0),(9,'Jazz',3000,0),(10,'Metal',3000,0),(11,'New Age',3000,0),(12,'Oldies',3000,0),(13,'Other',3000,0),(14,'Pop',3000,0),(15,'R&B',3000,0),(16,'Rap',3000,0),(17,'Reggae',3000,0),(18,'Rock',3000,0),(19,'Techno',3000,0),(20,'Industrial',3000,0),(21,'Alternative',3000,0),(22,'Ska',3000,0),(23,'Death Metal',3000,0),(24,'Pranks',3000,0),(25,'Soundtrack',3000,0),(26,'Euro-Techno',3000,0),(27,'Ambient',3000,0),(28,'Trip-Hop',3000,0),(29,'Vocal',3000,0),(30,'Jazz+Funk',3000,0),(31,'Fusion',3000,0),(32,'Trance',3000,0),(33,'Classical',3000,0),(34,'Instrumental',3000,0),(35,'Acid',3000,0),(36,'House',3000,0),(37,'Game',3000,0),(38,'Sound Clip',3000,0),(39,'Gospel',3000,0),(40,'Noise',3000,0),(41,'Alternative Rock',3000,0),(42,'Bass',3000,0),(43,'Soul',3000,0),(44,'Punk',3000,0),(45,'Space',3000,0),(46,'Meditative',3000,0),(47,'Instrumental Pop',3000,0),(48,'Instrumental Rock',3000,0),(49,'Ethnic',3000,0),(50,'Gothic',3000,0),(51,'Darkwave',3000,0),(52,'Techno-Industrial',3000,0),(53,'Electronic',3000,0),(54,'Pop-Folk',3000,0),(55,'Eurodance',3000,0),(56,'Dream',3000,0),(57,'Southern Rock',3000,0),(58,'Comedy',3000,0),(59,'Cult',3000,0),(60,'Gangsta',3000,0),(61,'Top 40',3000,0),(62,'Christian Rap',3000,0),(63,'Pop/Funk',3000,0),(64,'Jungle',3000,0),(65,'Native US',3000,0),(66,'Cabaret',3000,0),(67,'New Wave',3000,0),(68,'Psychadelic',3000,0),(69,'Rave',3000,0),(70,'Showtunes',3000,0),(71,'Trailer',3000,0),(72,'Lo-Fi',3000,0),(73,'Tribal',3000,0),(74,'Acid Punk',3000,0),(75,'Acid Jazz',3000,0),(76,'Polka',3000,0),(77,'Retro',3000,0),(78,'Musical',3000,0),(79,'Rock & Roll',3000,0),(80,'Hard Rock',3000,0),(81,'Folk',3000,0),(82,'Folk-Rock',3000,0),(83,'National Folk',3000,0),(84,'Swing',3000,0),(85,'Fast Fusion',3000,0),(86,'Bebob',3000,0),(87,'Latin',3000,0),(88,'Revival',3000,0),(89,'Celtic',3000,0),(90,'Bluegrass',3000,0),(91,'Avantgarde',3000,0),(92,'Gothic Rock',3000,0),(93,'Progressive Rock',3000,0),(94,'Psychedelic Rock',3000,0),(95,'Symphonic Rock',3000,0),(96,'Slow Rock',3000,0),(97,'Big Band',3000,0),(98,'Chorus',3000,0),(99,'Easy Listening',3000,0),(100,'Acoustic',3000,0),(101,'Humour',3000,0),(102,'Speech',3000,0),(103,'Chanson',3000,0),(104,'Opera',3000,0),(105,'Chamber Music',3000,0),(106,'Sonata',3000,0),(107,'Symphony',3000,0),(108,'Booty Bass',3000,0),(109,'Primus',3000,0),(110,'Porn Groove',3000,0),(111,'Satire',3000,0),(112,'Slow Jam',3000,0),(113,'Club',3000,0),(114,'Tango',3000,0),(115,'Samba',3000,0),(116,'Folklore',3000,0),(117,'Ballad',3000,0),(118,'Power Ballad',3000,0),(119,'Rhytmic Soul',3000,0),(120,'Freestyle',3000,0),(121,'Duet',3000,0),(122,'Punk Rock',3000,0),(123,'Drum Solo',3000,0),(124,'Acapella',3000,0),(125,'Euro-House',3000,0),(126,'Dance Hall',3000,0),(127,'Goa',3000,0),(128,'Drum & Bass',3000,0),(129,'Club-House',3000,0),(130,'Hardcore',3000,0),(131,'Terror',3000,0),(132,'Indie',3000,0),(133,'BritPop',3000,0),(134,'Negerpunk',3000,0),(135,'Polsk Punk',3000,0),(136,'Beat',3000,0),(137,'Christian Gangsta',3000,0),(138,'Heavy Metal',3000,0),(139,'Black Metal',3000,0),(140,'Crossover',3000,0),(141,'Contemporary C',3000,0),(142,'Christian Rock',3000,0),(143,'Merengue',3000,0),(144,'Salsa',3000,0),(145,'Thrash Metal',3000,0),(146,'Anime',3000,0),(147,'JPop',3000,0),(148,'SynthPop',3000,0),(149,'Electronica',3000,0);
/*!40000 ALTER TABLE `genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `backfill_target` int(4) NOT NULL DEFAULT '1',
  `first_record` bigint(20) unsigned NOT NULL DEFAULT '0',
  `first_record_postdate` datetime DEFAULT NULL,
  `last_record` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_record_postdate` datetime DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  `minfilestoformrelease` int(4) DEFAULT NULL,
  `minsizetoformrelease` bigint(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `backfill` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `name` (`name`),
  KEY `active` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=557 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'alt.binaries.0day.stuffz',1,0,NULL,0,NULL,NULL,2,0,0,0,'This group contains mostly 0day software.'),(3,'alt.binaries.ath',1,0,NULL,0,NULL,NULL,8,0,0,0,'This group contains a variety of Music. Some Foreign.'),(6,'alt.binaries.b4e',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains 0day and has some foreign.'),(9,'alt.binaries.blu-ray',0,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains blu-ray movies.'),(10,'alt.binaries.boneless',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID and X264 Movies. Some Foreign.'),(11,'alt.binaries.british.drama',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains British TV shows.'),(13,'alt.binaries.cats',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(14,'alt.binaries.cd.image.linux',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Linux distributions.'),(15,'alt.binaries.cd.image',1,0,NULL,0,NULL,NULL,4,0,0,0,'This group contains PC-ISO.'),(16,'alt.binaries.cd.lossless',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of lossless Music.'),(17,'alt.binaries.chello',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(18,'alt.binaries.classic.tv.shows',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Classic TV and Movies.'),(19,'alt.binaries.comics.dcp',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Comic Books'),(22,'alt.binaries.cores',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety including Nintendo DS. Lots of Foreign.'),(23,'alt.binaries.country.mp3',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Country Music.'),(27,'alt.binaries.documentaries',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Documentaries TV and Movies.'),(29,'alt.binaries.downunder',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(30,'alt.binaries.dvd',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains DVD Movies.'),(31,'alt.binaries.dvd.movies',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains DVDR Movies.'),(32,'alt.binaries.dvdr',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains DVD Movies.'),(33,'alt.binaries.dvd-r',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains DVD Movies.'),(34,'alt.binaries.e-book.flood',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains E-Books.'),(35,'alt.binaries.e-book.technical',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains E-Books.'),(36,'alt.binaries.e-book',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains E-Books.'),(37,'alt.binaries.ebook',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Ebook\'s.'),(38,'alt.binaries.erotica.divx',1,0,NULL,0,NULL,NULL,0,26214400,0,0,'This group contains XXX.'),(39,'alt.binaries.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,'This group contains XXX.'),(40,'alt.binaries.etc',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of items.'),(41,'alt.binaries.font',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(43,'alt.binaries.frogs',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety.'),(44,'alt.binaries.ftn',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of Music and TV.'),(45,'alt.binaries.games.nintendods',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Nintendo DS Games '),(46,'alt.binaries.games',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains PC and Console Games.'),(47,'alt.binaries.games.wii',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Nintendo WII Games, WII-Ware, and VC.'),(51,'alt.binaries.hdtv',1,0,NULL,0,NULL,NULL,2,0,0,0,'This group contains mostly HDTV 1080i rips.'),(53,'alt.binaries.hdtv.x264',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains X264 Movies and HDTV.'),(56,'alt.binaries.ijsklontje',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XXX.'),(57,'alt.binaries.inner-sanctum',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains PC and Music.'),(59,'alt.binaries.ipod.videos',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Mobile TV and Movies.'),(60,'alt.binaries.linux.iso',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Linux distributions.'),(62,'alt.binaries.mac',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains MAC/OSX Software.'),(63,'alt.binaries.mac.applications',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains MAC/OSX Software.'),(64,'alt.binaries.milo',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV, some german.'),(65,'alt.binaries.misc',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of items.'),(66,'alt.binaries.mojo',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(67,'alt.binaries.mma',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains MMA/TNA Sport TV.'),(69,'alt.binaries.moovee',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID and X264 Movies.'),(70,'alt.binaries.movies.divx',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains XVID Movies'),(71,'alt.binaries.movies.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,'This group contains XXX'),(73,'alt.binaries.movies',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains an assortment of Movies.'),(74,'alt.binaries.movies.xvid',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID Movies.'),(75,'alt.binaries.mp3.audiobooks',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Audio Books.'),(77,'alt.binaries.mp3.full_albums',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of Music.'),(78,'alt.binaries.mp3',1,0,NULL,0,NULL,NULL,11,0,0,0,'This group contains a variety of Music.'),(79,'alt.binaries.mpeg.video.music',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of Music Videos.'),(83,'alt.binaries.multimedia.cartoons',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Cartoon TV and Movies.'),(84,'alt.binaries.multimedia.classic-films',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Classic TV and Movies.'),(85,'alt.binaries.multimedia.comedy.british',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains British Comedy TV and Movies.'),(86,'alt.binaries.multimedia.disney',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Disney TV and Movies.'),(87,'alt.binaries.multimedia.documentaries',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Documentary Movies and TV.'),(88,'alt.binaries.multimedia.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,'This group contains XXX.'),(89,'alt.binaries.multimedia.erotica.amateur',1,0,NULL,0,NULL,NULL,0,26214400,0,0,'This group contains XXX.'),(90,'alt.binaries.multimedia.scifi',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains science-fiction TV and movies.'),(91,'alt.binaries.multimedia.scifi-and-fantasy',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains science-fiction and fantasy TV and movies.'),(92,'alt.binaries.multimedia.sitcoms',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Sitcom TV.'),(93,'alt.binaries.multimedia.sports',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Sports TV and Movies.'),(94,'alt.binaries.multimedia',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains TV, Movies, and Music.'),(95,'alt.binaries.multimedia.tv',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID and X264 TV.'),(96,'alt.binaries.multimedia.vintage-film',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Vintage Movies pre 1960.'),(97,'alt.binaries.multimedia.vintage-film.post-1960',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Vintage Movies post 1960.'),(98,'alt.binaries.music.flac',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of lossless Music.'),(100,'alt.binaries.nintendo.ds',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Nintendo DS Games.'),(101,'alt.binaries.nospam.cheerleaders',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains various.'),(102,'alt.binaries.pictures.comics.complete',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains comics.'),(103,'alt.binaries.pictures.comics.dcp',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Comic Books.'),(104,'alt.binaries.pictures.comics.reposts',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Comic Books.'),(105,'alt.binaries.pictures.comics.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Comic Books.'),(107,'alt.binaries.scary.exe.files',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID and X264 Movies.'),(109,'alt.binaries.sony.psp',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains PSP Games.'),(110,'alt.binaries.sound.audiobooks',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Audiobooks.'),(111,'alt.binaries.sound.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of Music.'),(112,'alt.binaries.sounds.1960s.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Music from the 1960\'s.'),(113,'alt.binaries.sounds.1970s.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Music from the 1970\'s.'),(114,'alt.binaries.sounds.audiobooks.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Audiobooks.'),(115,'alt.binaries.sounds.country.mp3',1,0,NULL,0,NULL,NULL,5,0,0,0,''),(116,'alt.binaries.sounds.flac.jazz',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains lossless Jazz Music.'),(117,'alt.binaries.sounds.jpop',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly Jpop music.'),(118,'alt.binaries.sounds.lossless.1960s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains lossless 1960\'s Music.'),(119,'alt.binaries.sounds.lossless.classical',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains lossless Classical Music.'),(120,'alt.binaries.sounds.lossless.country',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains lossless Country Music.'),(121,'alt.binaries.sounds.lossless',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains a variety of Lossless Music.'),(122,'alt.binaries.sounds.mp3.1950s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Music from the 1950\'s.'),(123,'alt.binaries.sounds.mp3.1970s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Music from the 1970\'s.'),(124,'alt.binaries.sounds.mp3.1980s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Music from the 1980\'s.'),(125,'alt.binaries.sounds.mp3.1990s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Music from the 1990\'s.'),(126,'alt.binaries.sounds.mp3.2000s',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Music from the 2000\'s.'),(127,'alt.binaries.sounds.mp3.acoustic',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Accoustic Music.'),(128,'alt.binaries.sounds.mp3.audiobooks',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Audiobooks.'),(129,'alt.binaries.sounds.mp3.bluegrass',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Bluegrass Music.'),(130,'alt.binaries.sounds.mp3.christian',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Christian Music.'),(131,'alt.binaries.sounds.mp3.classical',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Classical Music.'),(132,'alt.binaries.sounds.mp3.comedy',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Comedy Audio.'),(133,'alt.binaries.sounds.mp3.complete_cd',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains a variety of Music.'),(134,'alt.binaries.sounds.mp3.country',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly country music.'),(135,'alt.binaries.sounds.mp3.dance',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Dance Music.'),(136,'alt.binaries.sounds.mp3.disco',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Disco Music.'),(137,'alt.binaries.sounds.mp3.emo',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Emo Music.'),(138,'alt.binaries.sounds.mp3.full_albums',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains a variety of Music.'),(139,'alt.binaries.sounds.mp3.heavy-metal',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Heavy Metal Music.'),(140,'alt.binaries.sounds.mp3.jazz',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Jazz Music.'),(141,'alt.binaries.sounds.mp3.jazz.vocals',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Jazz Vocal Music.'),(142,'alt.binaries.sounds.mp3.musicals',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Musicals Music.'),(143,'alt.binaries.sounds.mp3.nospam',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains a variety of Music.'),(145,'alt.binaries.sounds.mp3.progressive-country',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Country Music.'),(146,'alt.binaries.sounds.mp3.rap-hiphop.full-albums',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Rap and Hip-Hop Music.'),(147,'alt.binaries.sounds.mp3.rap-hiphop',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Rap and Hip-Hop Music.'),(148,'alt.binaries.sounds.mp3.rock',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains Rock Music.'),(149,'alt.binaries.sounds.mp3',1,0,NULL,0,NULL,NULL,5,0,0,0,'This group contains a variety of Music.'),(152,'alt.binaries.sounds.whitburn.pop',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Pop Music.'),(154,'alt.binaries.teevee',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains X264 and XVID TV.'),(155,'alt.binaries.test',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of content.'),(156,'alt.binaries.town',0,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID TV and Movies. Mostly Foreign.'),(157,'alt.binaries.triballs',1,0,NULL,0,NULL,NULL,2,0,0,0,'This group contains various.'),(158,'alt.binaries.tun',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains various.'),(159,'alt.binaries.tvseries',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains X264 and XVID TV.'),(160,'alt.binaries.tv',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XVID TV.'),(163,'alt.binaries.ucc',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(164,'alt.binaries.ufg',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains mostly TV.'),(165,'alt.binaries.uzenet',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains XXX. Some Foreign.'),(166,'alt.binaries.warez.ibm-pc.0-day',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains PC-0Day.'),(168,'alt.binaries.warez.smartphone',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Mobile Phone Apps.'),(169,'alt.binaries.warez',1,0,NULL,0,NULL,NULL,5,1000000,0,0,'This group contains PC 0DAY, PC ISO, and PC PHONE.'),(170,'alt.binaries.warez.uk.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of Music.'),(171,'alt.binaries.wii',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains Nintendo WII Games, WII-Ware, and VC.'),(172,'alt.binaries.wmvhd',1,0,NULL,0,NULL,NULL,0,40000000,0,0,'This group contains WMVHD Movies.'),(173,'alt.binaries.worms',1,0,NULL,0,NULL,NULL,2,0,0,0,'I have no idea what this group contains besides a lot of U4ALL which isn\'t really usable.'),(174,'alt.binaries.x264',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains X264 Movies and TV.'),(175,'alt.binaries.x',1,0,NULL,0,NULL,NULL,0,0,0,0,'This group contains a variety of content. Some Foreign.'),(176,'alt.binaries.tv.simpsons',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(177,'alt.binaries.multimedia.cartoons.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(178,'alt.binaries.sounds.mp3.blues',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(179,'alt.binaries.multimedia.horror',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(180,'alt.binaries.music.classical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(181,'alt.binaries.multimedia.erotica.voyeurism',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(182,'alt.binaries.newzbin',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(183,'alt.binaries.monter-movies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(184,'alt.binaries.sony.psp.movies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(185,'alt.binaries.warez.ibm-pc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(186,'alt.binaries.e-book.rpg',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(187,'alt.binaries.mac.cd-images',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(188,'alt.binaries.usenet2day',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(189,'alt.binaries.hdtv.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(190,'alt.binaries.multimedia.erotica.asian',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(191,'alt.binaries.nospam.multimedia.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(192,'alt.binaries.nfonews',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(193,'alt.binaries.warez.ibm-pc.ms-beta',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(194,'alt.binaries.pl.bajki',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(195,'alt.binaries.conspiracy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(196,'alt.binaries.dvd.image',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(197,'de.alt.binaries.startrek',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(198,'alt.binaries.games.worms',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(199,'alt.binaries.music.jungle',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(200,'alt.binaries.ftr',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(201,'alt.binaries.sounds.lossless.blues',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(202,'alt.binaries.pl.ape',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(203,'alt.binaries.hou',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(204,'alt.binaries.dvd.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(205,'alt.binaries.games.kidstuff.highspeedalt.binaries.cd.image.highspeed',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(206,'alt.binaries.sounds.lossless.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(207,'alt.binaries.mac.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(208,'alt.binaries.warez.linux',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(209,'alt.binaries.emulators.nintendo',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(210,'alt.binaries.dvd.erotica.classics',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(211,'alt.binaries.tv.big-brother',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(212,'alt.binaries.battlestar-galactica',0,0,NULL,0,NULL,NULL,0,0,0,0,''),(213,'alt.binaries.mom',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(214,'alt.binaries.ftn.music',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(215,'alt.binaries.dvdrs',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(216,'alt.binaries.erotica.multimedia.asian',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(217,'alt.binaries.bitburger',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(218,'alt.binaries.comp',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(219,'alt.binaries.sounds.mp3.rock.full-album',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(220,'alt.binaries.nirpaia',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(221,'alt.binaries.sounds.mp3.electronic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(222,'alt.binaries.sounds.misc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(223,'alt.binaries.multimedia.utilities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(224,'alt.binaries.movies.mkv',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(225,'alt.binaries.ftd.nzb',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(226,'alt.binaries.big',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(227,'alt.binaries.sounds.mp3.metal.full-albums',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(228,'alt.binaries.sounds.radio.coasttocoast.am',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(229,'alt.binaries.dvd.english',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(230,'alt.binaries.punk',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(231,'alt.binaries.multimedia.cartoons.looneytunes',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(232,'alt.binaries.remixes.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(233,'alt.binaries.sounds.mp3.rap-hiphop.mixtapes',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(234,'alt.binaries.sounds.utilities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(235,'alt.binaries.multimedia.martial-arts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(236,'alt.binaries.warez.quebec-hackers.dvd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(237,'alt.binaries.ftn.movie',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(238,'alt.binaries.paranormal',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(239,'alt.binaries.the-terminal',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(240,'alt.binaries.tv.poker',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(241,'alt.binaries.erotica.teen.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(242,'alt.binaries.movies.thelostmovies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(243,'alt.binaries.bloaf',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(244,'alt.binaries.startrek',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(245,'alt.binaries.skewed',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(246,'alt.binaries.barbarella',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(247,'alt.binaries.vcd.xxx.private',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(248,'alt.binaries.beatles',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(249,'alt.binaries.ibm-pc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(250,'alt.binaries.vcdz',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(251,'alt.binaries.games.kidstuff.nl',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(252,'alt.binaries.drwho',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(253,'alt.binaries.multimedia.erotica.interracial',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(254,'alt.binaries.cracks.alt.binaries.misc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(255,'alt.binaries.multimedia.startrek',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(256,'alt.binaries.sounds.audiobooks',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(257,'alt.binaries.sounds.monkeysaudio',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(258,'alt.binaries.rusenet',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(259,'alt.binaries.svcd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(260,'alt.binaries.dvdr-tv',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(261,'alt.binaries.cd.genealogy.reposts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(262,'alt.binaries.dvd.ntsc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(263,'alt.binaries.warez.educational',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(264,'alt.binaries.dvd9',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(265,'alt.binaries.dvd.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(266,'alt.binaries.sounds.mp3.christmas',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(267,'alt.binaries.warez4kiddies.apps',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(268,'alt.binaries.ftn.nzb',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(269,'alt.binaries.games.dox',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(270,'alt.binaries.old.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(271,'alt.binaries.sounds.mp3.extreme-metal',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(272,'alt.binaries.sounds.mp3.secular',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(273,'alt.binaries.erotica.amateur.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(274,'bih.alt.binaries.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(275,'alt.binaries.sounds.samples',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(276,'alt.binaries.magic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(277,'alt.binaries.dvdrcore',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(278,'alt.binaries.sounds.lossless.rock',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(279,'alt.binaries.starwars',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(280,'alt.binaries.dc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(281,'alt.fax.alt.binaries.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(282,'alt.binaries.sounds.mp3.celtic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(283,'alt.binaries.sounds.mp3.gothic-industrial',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(284,'alt.binaries.sounds.music',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(285,'alt.binaries.fta',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(286,'alt.binaries.mac.osx.apps',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(287,'alt.binaries.music',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(288,'dk.binaer.film',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(289,'alt.binaries.zappafiles',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(290,'alt.binaries.multimedia.mst3k',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(291,'alt.binaries.multimedia.erotica.lesbians',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(292,'alt.binaries.vcd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(293,'alt.binaries.paxer',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(294,'alt.binaries.ftn.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(295,'alt.binaries.sounds.mp3.spoken-word',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(296,'alt.binaries.sounds.mp3.video-games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(297,'alt.binaries.warez.ibm-pc.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(298,'alt.binaries.multimedia.nude.celebrities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(299,'alt.binaries.atari',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(300,'alt.binaries.sounds',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(301,'alt.binaries.sounds.karaoke',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(302,'alt.binaries.bbs',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(303,'alt.binaries.snuh',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(304,'alt.binaries.movies.kidstuff',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(305,'alt.binaries.erotica.collections.rars',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(306,'alt.binaries.warcraft',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(307,'alt.binaries.dvd.midnightmovies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(308,'alt.binaries.cd.image.other',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(309,'alt.binaries.multimedia.comedy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(310,'alt.binaries.emulators.misc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(311,'alt.binaries.sounds.mp3.prog',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(312,'alt.binaries.mpeg.videos',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(313,'alt.binaries.mac.audio',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(314,'alt.binaries.pictures.erotica.bondage',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(315,'alt.binaries.sounds.mp3.reggae',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(316,'alt.binaries.sounds.radio.misc',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(317,'alt.binaries.erotica.fettish.alt.binaries.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(318,'alt.binaries.warez.pocketpc.movies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(319,'de.alt.binaries.sounds',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(320,'alt.binaries.movies.zeromovies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(321,'alt.binaries.dominion.silly-group',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(322,'alt.binaries.pl.mp3',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(323,'alt.binaries.dgma',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(324,'alt.binaries.games.adventures',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(325,'alt.binaries.warez.uk',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(326,'alt.binaries.cd.image.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(327,'alt.binaries.movies.martial.arts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(328,'alt.binaries.mac.apps',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(329,'alt.binaries.multimedia.rail',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(330,'alt.binaries.highspeed',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(331,'alt.binaries.warez.autocad',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(332,'alt.binaries.warez.quebec-hackers',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(333,'alt.binaries.bos',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(334,'alt.binaries.games.reposts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(335,'alt.binaries.erotica.vcd',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(336,'alt.binaries.owt-4-nowt',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(337,'alt.binaries.dvd.music',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(338,'alt.binaries.multimedia.erotica.repost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(339,'alt.binaries.multimedia.late-night-talkshows',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(340,'alt.binaries.warez.flightsim',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(341,'alt.binaries.madcow.highspeed',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(342,'alt.binaries.erotica.pornstars.80s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(343,'alt.binaries.ftd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(344,'alt.binaries.sounds.mp3.folk',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(345,'alt.binaries.pl.divx',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(346,'alt.binaries.kenpsx',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(347,'alt.binaries.ratcave',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(348,'alt.binaries.xylo',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(349,'alt.binaries.sounds.mp3.lounge',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(350,'alt.binaries.ftb',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(351,'alt.binaries.sleazemovies',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(352,'alt.binaries.superman',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(353,'alt.binaries.movies.shadowrealm',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(354,'alt.binaries.sounds.mp3.classic-rock',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(355,'alt.binaries.x2l',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(356,'alt.binaries.pl.utils',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(357,'alt.binaries.dvds',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(358,'alt.binaries.stargate-sg1',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(359,'alt.binaries.sounds.mp3.new-age',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(360,'alt.binaries.tv.shaggable.babes',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(361,'alt.binaries.emulators',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(362,'alt.binaries.cd.image.3do',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(363,'alt.binaries.movies.divx.repost',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(364,'alt.binaries.sounds.radio.oldtime',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(365,'alt.binaries.pictures.wallpaper',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(366,'alt.binaries.butthedd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(367,'alt.binaries.pcgame',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(368,'alt.binaries.emulators.mame',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(369,'alt.binaries.sounds.mp3.dancehall',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(370,'alt.binaries.pl.multimedia',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(371,'alt.binaries.sounds.mp3.soul-rhythm-and-blues',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(372,'alt.binaries.cbt',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(373,'alt.binaries.fz',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(374,'alt.binaries.sounds.mp3.soundtracks',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(375,'alt.binaries.sounds.music.classical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(376,'alt.binaries.ftn.applications',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(377,'alt.binaries.sounds.78rpm-era',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(378,'alt.binaries.cbts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(379,'alt.binaries.cd.image.highspeed',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(380,'alt.binaries.multimedia.ratdvd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(381,'alt.binaries.dvdrip',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(382,'alt.binaries.sounds.flac',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(383,'alt.binaries.sea-monkeys',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(384,'alt.binaries.sounds.mp3.world-music',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(385,'alt.binaries.warez.games',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(386,'alt.binaries.multimedia.smallville',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(387,'alt.binaries.erotica.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(388,'alt.binaries.sounds.dts',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(389,'alt.binaries.sound.mp3.complete_cd',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(390,'alt.binaries.dvd.erotica.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(391,'alt.binaries.erotic.cowgirls',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(392,'alt.binaries.erotic.amateur.pictures',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(393,'alt.binaries.erotic',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(394,'alt.binaries.dvd.erotica.classic',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(395,'alt.binaries.dvd.erotica.classics.80s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(396,'alt.binaries.dvd.erotica.classics.70s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(397,'alt.binaries.dvd.erotica.fills',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(398,'alt.binaries.erotic.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(399,'alt.binaries.dvd.erotica.repost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(400,'alt.binaries.dvd.erotica.reposts',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(401,'alt.binaries.dvd.erotica.scenes',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(402,'alt.binaries.dvd.erotica.classics.90s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(403,'alt.binaries.erotic.centerfolds',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(404,'alt.binaries.erotic.gloryhole.women',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(405,'alt.binaries.erotica.gymnast-girls',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(406,'alt.binaries.erotica.homemade',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(407,'alt.binaries.erotica.multimedia.russians',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(408,'alt.binaries.erotica.gapes',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(409,'alt.binaries.erotica.breasts.small',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(410,'alt.binaries.erotica.anal',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(411,'alt.binaries.erotica.angels',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(412,'alt.binaries.erotica.fetish',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(413,'alt.binaries.erotica.asian',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(414,'alt.binaries.erotica.hornyrob',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(415,'alt.binaries.erotica.multimedia.bondage',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(416,'alt.binaries.erotica.multimedia.creampie',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(417,'alt.binaries.erotica.centerfolds',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(418,'alt.binaries.erotica.breast',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(419,'alt.binaries.erotica.bondage',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(420,'alt.binaries.erotica.erotica',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(421,'alt.binaries.erotica.asian-female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(422,'alt.binaries.erotica.blondes',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(423,'alt.binaries.erotica.machines',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(424,'alt.binaries.erotica.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(425,'alt.binaries.erotica.bondage.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(426,'alt.binaries.erotica.cheerleaders',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(427,'alt.binaries.erotica.bukkake',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(428,'alt.binaries.erotica.pictures.redheadsalt.binaries.erotica.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(429,'alt.binaries.erotica.e-stim',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(430,'alt.binaries.erotica.multimedia.forcedsex',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(431,'alt.binaries.erotica.lesbians',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(432,'alt.binaries.erotica.groupsex',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(433,'alt.binaries.erotica.kinky.teens',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(434,'alt.binaries.erotica.bukkake.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(435,'alt.binaries.erotica.breast.small',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(436,'alt.binaries.erotica.movies.anal.bestpart',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(437,'alt.binaries.erotica.female.anal',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(438,'alt.binaries.erotica.creampie',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(439,'alt.binaries.erotica.collections.rar',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(440,'alt.binaries.erotica.breasts',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(441,'alt.binaries.erotica.moderated',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(442,'alt.binaries.erotica.breasts.natural',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(443,'alt.binaries.erotica.movies.divx',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(444,'alt.binaries.erotica.fisting',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(445,'alt.binaries.erotica.strap-on-sex',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(446,'alt.binaries.multimedia.erotica.amateur.teen.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(447,'alt.binaries.multimedia.erotica.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(448,'alt.binaries.multimedia.erotica.russians',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(449,'alt.binaries.erotica.southern-charms',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(450,'alt.binaries.multimedia.erotica.teen.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(451,'alt.binaries.erotica.spanking',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(452,'alt.binaries.multimedia.erotica.vcd',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(453,'alt.binaries.erotica.pornstars.80s.video',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(454,'alt.binaries.erotica.vcd.private',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(455,'alt.binaries.multimedia.erotica.vcd.poster.norepost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(456,'alt.binaries.erotica.sybian',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(457,'alt.binaries.erotica.pornstars',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(458,'alt.binaries.erotica.pornstar',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(459,'alt.binaries.erotica.teen.fuck',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(460,'alt.binaries.erotica.pornstars.90s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(461,'alt.binaries.erotica.teen.female.nude',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(462,'alt.binaries.erotica.post.yourself.nude',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(463,'alt.binaries.multimedia.erotica.homemade',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(464,'alt.binaries.multimedia.erotica.masturbation',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(465,'alt.binaries.multimedia.erotica.creampie',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(466,'alt.binaries.erotica.spanking.teen',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(467,'alt.binaries.erotica.vintage',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(468,'alt.binaries.multimedia.erotica.vcd.poster-norepost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(469,'alt.binaries.erotica.teen.females',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(470,'alt.binaries.erotica.teen.female.nonude',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(471,'alt.binaries.multimedia.erotic.female',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(472,'alt.binaries.multimedia.erotic.playboy',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(473,'alt.binaries.nospam.multimedia.erotica.vcd',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(474,'alt.binaries.multimedia.erotica.anal.gapes',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(475,'alt.binaries.erotica.teens',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(476,'alt.binaries.multimedia.erotica.lesbian',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(477,'alt.binaries.multimedia.erotica.german.porn',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(478,'alt.binaries.erotica.vcd.xxx',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(479,'alt.binaries.erotica.fettish.alt.binaries.erotica.pornstar',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(480,'alt.binaries.erotica.teen.famale.masterbation',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(481,'alt.binaries.multimedia.erotica.forced.wives',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(482,'alt.binaries.erotica.pornstars80s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(483,'alt.binaries.multimedia.erotica.teen',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(484,'alt.binaries.erotica.schoolgirls',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(485,'alt.binaries.multimedia.erotica.dee-desi',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(486,'alt.binaries.nospam.erotica.breast',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(487,'alt.binaries.erotica.pissing',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(488,'alt.binaries.multimedia.erotica.18-plus',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(489,'alt.binaries.erotica.vcd.poster.norepost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(490,'alt.binaries.erotica.teen.female.amateur',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(491,'alt.binaries.erotica.redheads',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(492,'alt.binaries.multimedia.erotica.homegrownvideo',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(493,'alt.binaries.multimedia.erotica.cumswallowing',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(494,'alt.binaries.multimedia.erotica.homemovies',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(495,'alt.binaries.multimedia.erotica.directors-cuts',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(496,'alt.binaries.multimedia.erotica.fisting',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(497,'alt.binaries.erotica.toys',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(498,'alt.binaries.erotica.teen',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(499,'alt.binaries.multimedia.erotica.ggg',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(500,'alt.binaries.erotica.teen.female.masturbation',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(501,'alt.binaries.erotica.pornstars90s',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(502,'alt.binaries.erotica.suicide-girls',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(503,'alt.binaries.erotica.vcd.repost',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(504,'alt.binaries.multimedia.erotica.amature',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(505,'alt.binaries.multimedia.erotic',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(506,'alt.binaries.multimedia.erotica.teens',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(507,'alt.binaries.multimedia.erotica.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(508,'alt.binaries.erotica.teen.female.orgasm',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(509,'alt.binaries.multimedia.erotica.amateur.d',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(510,'alt.binaries.erotica.young',1,0,NULL,0,NULL,NULL,0,26214400,0,0,''),(511,'alt.binaries.multmedia.nude.celebrities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(512,'alt.binaries.nudecelebrities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(513,'alt.binaries.nude.celebrities.femalenude',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(514,'alt.binaries.celebrities.nude',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(515,'alt.binaries.multimedia.celebrities.nude',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(516,'alt.binaries.nudism.celebrities',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(517,'alt.sex.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(518,'alt.magazines.pornagraphic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(519,'alt.sex.bondagealt.sex.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(520,'alt.media.magazine.dotnet',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(521,'alt.sex.magazines.pornographic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(522,'alt.magazine',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(523,'alt.magazine.playboy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(524,'alt.binaries.pictures.vintage.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(525,'alt.magazines.time',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(526,'alt.penthouse.sex.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(527,'alt.magazines.pornographic',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(528,'alt.binaries.e-book.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(529,'alt.magazines',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(530,'alt.sex.magazine',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(531,'alt.binaries.e-book.d',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(532,'alt.binaries.e-book.technical.d',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(533,'alt.binaries.e-book.tech',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(534,'alt.binaries.e-book.fantasy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(535,'alt.binaries.e-book.msreader',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(536,'alt.binaries.ebook.technical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(537,'alt.binaries.e-book.pl',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(538,'alt.binaries.ebook.flood',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(539,'alt.binaries.ebooks.fantasy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(540,'alt.binaries.e-book.genealogy.d',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(541,'alt.binaries.e-book.genealogy',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(542,'alt.binaries.e-books.technical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(543,'alt.binaries.e-books',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(544,'alt.binaries.e-book.techical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(545,'alt.binaries.e-book.christian',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(546,'alt.binaries.e-book.palm',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(547,'alt.binaries.e-book.mathmad',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(548,'alt.binaries.ebooks',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(549,'alt.binaries.e-book-technical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(550,'alt.binaries.ebooks-technical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(551,'alt.binaries.ebooks.technical',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(552,'alt.binaries.technical.ebooks',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(553,'alt.binaries.ebooks.flood',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(554,'alt.binaries.e-book-flood',1,0,NULL,0,NULL,NULL,0,0,0,0,''),(555,'alt.binaries.jerry',1,0,NULL,0,NULL,NULL,NULL,26214400,0,0,'This group mostly contains XXX'),(556,'alt.binaries.u-4all',1,0,NULL,0,NULL,NULL,NULL,26214400,0,0,'This group mostly contains XXX');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `href` varchar(2000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(2000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `newwindow` int(1) unsigned NOT NULL DEFAULT '0',
  `tooltip` varchar(2000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `role` int(11) unsigned NOT NULL,
  `ordinal` int(11) unsigned NOT NULL,
  `menueval` varchar(2000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'search','Advanced Search',0,'Search for releases.',1,10,''),(2,'browsegroup','Groups List',0,'Browse by Group.',1,25,''),(3,'movies','Movies',0,'Browse Movies.',1,40,''),(4,'upcoming','Theatres',0,'Movies currently in theatres.',1,45,''),(5,'series','TV Series',0,'Browse TV Series.',1,50,''),(6,'predb','PreDB',0,'Browse PreDB.',1,51,''),(7,'calendar','TV Calendar',0,'View what\'s on TV.',1,53,''),(8,'anime','Anime',0,'Browse Anime',1,55,''),(9,'music','Music',0,'Browse Music.',1,60,''),(10,'console','Console',0,'Browse Games.',1,65,''),(11,'books','Books',0,'Browse Books.',1,67,''),(12,'admin','Admin',0,'Admin',2,70,''),(13,'cart','My Cart',0,'Your Nzb cart.',1,75,''),(14,'myshows','My Shows',0,'Your TV shows.',1,77,''),(15,'mymovies','My Movies',0,'Your Movie Wishlist.',1,78,''),(16,'apihelp','API',0,'Information on the API.',1,79,''),(17,'rss','RSS',0,'RSS Feeds.',1,80,''),(18,'queue','Sab Queue',0,'View Your Sabnzbd Queue.',1,81,'{if $sabapikeytype!=2}-1{/if}'),(19,'forum','Forum',0,'Browse Forum.',1,85,''),(20,'login','Login',0,'Login.',0,100,''),(21,'register','Register',0,'Register.',0,110,'');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movieinfo`
--

DROP TABLE IF EXISTS `movieinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movieinfo` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `imdbID` mediumint(7) unsigned zerofill NOT NULL,
  `tmdbID` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tagline` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `rating` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `plot` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `year` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `genre` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `director` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `actors` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `backdrop` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `imdbID` (`imdbID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movieinfo`
--

LOCK TABLES `movieinfo` WRITE;
/*!40000 ALTER TABLE `movieinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `movieinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `musicinfo`
--

DROP TABLE IF EXISTS `musicinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `musicinfo` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asin` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salesrank` int(10) unsigned DEFAULT NULL,
  `artist` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publisher` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `releasedate` datetime DEFAULT NULL,
  `review` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `genreID` int(10) DEFAULT NULL,
  `tracks` varchar(3000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cover` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `musicinfo`
--

LOCK TABLES `musicinfo` WRITE;
/*!40000 ALTER TABLE `musicinfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `musicinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nzbs`
--

DROP TABLE IF EXISTS `nzbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nzbs` (
  `message_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `article-number` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `collectionhash` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `filesize` bigint(20) unsigned NOT NULL DEFAULT '0',
  `partnumber` int(10) unsigned NOT NULL DEFAULT '0',
  `totalparts` int(10) unsigned NOT NULL DEFAULT '0',
  `postdate` datetime DEFAULT NULL,
  `dateadded` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nzbs`
--

LOCK TABLES `nzbs` WRITE;
/*!40000 ALTER TABLE `nzbs` DISABLE KEYS */;
/*!40000 ALTER TABLE `nzbs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `partrepair`
--

DROP TABLE IF EXISTS `partrepair`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partrepair` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `numberID` bigint(20) unsigned NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `attempts` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_partrepair_numberID_groupID` (`numberID`,`groupID`)
) ENGINE=InnoDB AUTO_INCREMENT=7055073 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `partrepair`
--

LOCK TABLES `partrepair` WRITE;
/*!40000 ALTER TABLE `partrepair` DISABLE KEYS */;
/*!40000 ALTER TABLE `partrepair` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `binaryID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `messageID` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `number` bigint(20) unsigned NOT NULL DEFAULT '0',
  `partnumber` int(10) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `parthash` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `collectionID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `binarySize` bigint(20) NOT NULL DEFAULT '0',
  `PartsInDB` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`,`collectionID`,`binaryID`),
  UNIQUE KEY `parthash` (`parthash`,`collectionID`,`binaryID`),
  KEY `ix_binID_partnum` (`binaryID`,`partnumber`),
  KEY `ix_colID_size` (`collectionID`,`size`)
) ENGINE=InnoDB AUTO_INCREMENT=347448479 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
/*!50100 PARTITION BY LIST (MOD(collectionID,50))
SUBPARTITION BY HASH (binaryID)
SUBPARTITIONS 2
(PARTITION p0 VALUES IN (0) ENGINE = InnoDB,
 PARTITION p1 VALUES IN (1) ENGINE = InnoDB,
 PARTITION p2 VALUES IN (2) ENGINE = InnoDB,
 PARTITION p3 VALUES IN (3) ENGINE = InnoDB,
 PARTITION p4 VALUES IN (4) ENGINE = InnoDB,
 PARTITION p5 VALUES IN (5) ENGINE = InnoDB,
 PARTITION p6 VALUES IN (6) ENGINE = InnoDB,
 PARTITION p7 VALUES IN (7) ENGINE = InnoDB,
 PARTITION p8 VALUES IN (8) ENGINE = InnoDB,
 PARTITION p9 VALUES IN (9) ENGINE = InnoDB,
 PARTITION p10 VALUES IN (10) ENGINE = InnoDB,
 PARTITION p11 VALUES IN (11) ENGINE = InnoDB,
 PARTITION p12 VALUES IN (12) ENGINE = InnoDB,
 PARTITION p13 VALUES IN (13) ENGINE = InnoDB,
 PARTITION p14 VALUES IN (14) ENGINE = InnoDB,
 PARTITION p15 VALUES IN (15) ENGINE = InnoDB,
 PARTITION p16 VALUES IN (16) ENGINE = InnoDB,
 PARTITION p17 VALUES IN (17) ENGINE = InnoDB,
 PARTITION p18 VALUES IN (18) ENGINE = InnoDB,
 PARTITION p19 VALUES IN (19) ENGINE = InnoDB,
 PARTITION p20 VALUES IN (20) ENGINE = InnoDB,
 PARTITION p21 VALUES IN (21) ENGINE = InnoDB,
 PARTITION p22 VALUES IN (22) ENGINE = InnoDB,
 PARTITION p23 VALUES IN (23) ENGINE = InnoDB,
 PARTITION p24 VALUES IN (24) ENGINE = InnoDB,
 PARTITION p25 VALUES IN (25) ENGINE = InnoDB,
 PARTITION p26 VALUES IN (26) ENGINE = InnoDB,
 PARTITION p27 VALUES IN (27) ENGINE = InnoDB,
 PARTITION p28 VALUES IN (28) ENGINE = InnoDB,
 PARTITION p29 VALUES IN (29) ENGINE = InnoDB,
 PARTITION p30 VALUES IN (30) ENGINE = InnoDB,
 PARTITION p31 VALUES IN (31) ENGINE = InnoDB,
 PARTITION p32 VALUES IN (32) ENGINE = InnoDB,
 PARTITION p33 VALUES IN (33) ENGINE = InnoDB,
 PARTITION p34 VALUES IN (34) ENGINE = InnoDB,
 PARTITION p35 VALUES IN (35) ENGINE = InnoDB,
 PARTITION p36 VALUES IN (36) ENGINE = InnoDB,
 PARTITION p37 VALUES IN (37) ENGINE = InnoDB,
 PARTITION p38 VALUES IN (38) ENGINE = InnoDB,
 PARTITION p39 VALUES IN (39) ENGINE = InnoDB,
 PARTITION p40 VALUES IN (40) ENGINE = InnoDB,
 PARTITION p41 VALUES IN (41) ENGINE = InnoDB,
 PARTITION p42 VALUES IN (42) ENGINE = InnoDB,
 PARTITION p43 VALUES IN (43) ENGINE = InnoDB,
 PARTITION p44 VALUES IN (44) ENGINE = InnoDB,
 PARTITION p45 VALUES IN (45) ENGINE = InnoDB,
 PARTITION p46 VALUES IN (46) ENGINE = InnoDB,
 PARTITION p47 VALUES IN (47) ENGINE = InnoDB,
 PARTITION p48 VALUES IN (48) ENGINE = InnoDB,
 PARTITION p49 VALUES IN (49) ENGINE = InnoDB) */;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts`
--

LOCK TABLES `parts` WRITE;
/*!40000 ALTER TABLE `parts` DISABLE KEYS */;
/*!40000 ALTER TABLE `parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `predb`
--

DROP TABLE IF EXISTS `predb`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predb` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nfo` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `predate` datetime DEFAULT NULL,
  `adddate` datetime DEFAULT NULL,
  `source` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `md5` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `releaseID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_predb_title` (`title`),
  KEY `ix_predb_predate` (`predate`),
  KEY `ix_predb_adddate` (`adddate`),
  KEY `ix_predb_source` (`source`),
  KEY `ix_predb_md5` (`md5`),
  KEY `ix_predb_releaseID` (`releaseID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predb`
--

LOCK TABLES `predb` WRITE;
/*!40000 ALTER TABLE `predb` DISABLE KEYS */;
/*!40000 ALTER TABLE `predb` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releaseaudio`
--

DROP TABLE IF EXISTS `releaseaudio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releaseaudio` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `releaseID` int(11) unsigned NOT NULL,
  `audioID` int(2) unsigned NOT NULL,
  `audioformat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiomode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiobitratemode` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiobitrate` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiochannels` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiosamplerate` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiolibrary` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiolanguage` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `audiotitle` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `releaseID` (`releaseID`,`audioID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releaseaudio`
--

LOCK TABLES `releaseaudio` WRITE;
/*!40000 ALTER TABLE `releaseaudio` DISABLE KEYS */;
/*!40000 ALTER TABLE `releaseaudio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releasecomment`
--

DROP TABLE IF EXISTS `releasecomment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releasecomment` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `releaseID` int(11) unsigned NOT NULL,
  `text` varchar(2000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `userID` int(11) unsigned NOT NULL,
  `createddate` datetime DEFAULT NULL,
  `host` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_releasecomment_releaseID` (`releaseID`),
  KEY `ix_releasecomment_userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releasecomment`
--

LOCK TABLES `releasecomment` WRITE;
/*!40000 ALTER TABLE `releasecomment` DISABLE KEYS */;
/*!40000 ALTER TABLE `releasecomment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releaseextrafull`
--

DROP TABLE IF EXISTS `releaseextrafull`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releaseextrafull` (
  `releaseID` int(11) unsigned NOT NULL,
  `mediainfo` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`releaseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releaseextrafull`
--

LOCK TABLES `releaseextrafull` WRITE;
/*!40000 ALTER TABLE `releaseextrafull` DISABLE KEYS */;
/*!40000 ALTER TABLE `releaseextrafull` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releasefiles`
--

DROP TABLE IF EXISTS `releasefiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releasefiles` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `releaseID` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `createddate` datetime DEFAULT NULL,
  `passworded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ix_releasefiles_releaseID` (`releaseID`),
  KEY `ix_releasefiles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releasefiles`
--

LOCK TABLES `releasefiles` WRITE;
/*!40000 ALTER TABLE `releasefiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `releasefiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releasenfo`
--

DROP TABLE IF EXISTS `releasenfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releasenfo` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `releaseID` int(11) unsigned NOT NULL,
  `nfo` blob,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_releasenfo_releaseID` (`releaseID`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releasenfo`
--

LOCK TABLES `releasenfo` WRITE;
/*!40000 ALTER TABLE `releasenfo` DISABLE KEYS */;
/*!40000 ALTER TABLE `releasenfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releases`
--

DROP TABLE IF EXISTS `releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releases` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `searchname` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `totalpart` int(11) DEFAULT '0',
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `postdate` datetime DEFAULT NULL,
  `adddate` datetime DEFAULT NULL,
  `updatetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `guid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fromname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completion` float NOT NULL DEFAULT '0',
  `categoryID` int(11) DEFAULT '0',
  `rageID` int(11) DEFAULT NULL,
  `seriesfull` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `season` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `episode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tvtitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tvairdate` datetime DEFAULT NULL,
  `imdbID` mediumint(7) unsigned zerofill DEFAULT NULL,
  `musicinfoID` int(11) DEFAULT NULL,
  `consoleinfoID` int(11) DEFAULT NULL,
  `bookinfoID` int(11) DEFAULT NULL,
  `anidbID` int(11) DEFAULT NULL,
  `grabs` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `passwordstatus` tinyint(4) NOT NULL DEFAULT '0',
  `rarinnerfilecount` int(11) NOT NULL DEFAULT '0',
  `haspreview` tinyint(4) NOT NULL DEFAULT '0',
  `nzbstatus` tinyint(4) NOT NULL DEFAULT '0',
  `nfostatus` tinyint(4) NOT NULL DEFAULT '0',
  `relnamestatus` tinyint(4) NOT NULL DEFAULT '0',
  `jpgstatus` tinyint(1) NOT NULL DEFAULT '0',
  `videostatus` tinyint(1) NOT NULL DEFAULT '0',
  `audiostatus` tinyint(1) NOT NULL DEFAULT '0',
  `dehashstatus` tinyint(1) NOT NULL DEFAULT '0',
  `relstatus` tinyint(4) NOT NULL DEFAULT '0',
  `reqidstatus` tinyint(1) NOT NULL DEFAULT '0',
  `nzb_guid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nzb_imported` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`ID`),
  KEY `ix_releases_adddate` (`adddate`),
  KEY `ix_releases_postdate` (`postdate`),
  KEY `ix_releases_categoryID` (`categoryID`),
  KEY `ix_releases_rageID` (`rageID`),
  KEY `ix_releases_imdbID` (`imdbID`),
  KEY `ix_releases_guid` (`guid`),
  KEY `ix_releases_nzbstatus` (`nzbstatus`),
  KEY `ix_release_name` (`name`),
  KEY `ix_releases_relnamestatus` (`relnamestatus`),
  KEY `ix_releases_passwordstatus` (`passwordstatus`),
  KEY `ix_releases_dehashstatus` (`dehashstatus`),
  KEY `ix_releases_reqidstatus` (`reqidstatus`) USING HASH,
  KEY `ix_releases_nfostatus` (`nfostatus`) USING HASH,
  KEY `ix_releases_musicinfoID` (`musicinfoID`),
  KEY `ix_releases_consoleinfoID` (`consoleinfoID`),
  KEY `ix_releases_bookinfoID` (`bookinfoID`),
  KEY `ix_releases_haspreview` (`haspreview`) USING HASH,
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `searchname` (`searchname`)
) ENGINE=InnoDB AUTO_INCREMENT=232501 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releases`
--

LOCK TABLES `releases` WRITE;
/*!40000 ALTER TABLE `releases` DISABLE KEYS */;
/*!40000 ALTER TABLE `releases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releasesubs`
--

DROP TABLE IF EXISTS `releasesubs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releasesubs` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `releaseID` int(11) unsigned NOT NULL,
  `subsID` int(2) unsigned NOT NULL,
  `subslanguage` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `releaseID` (`releaseID`,`subsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releasesubs`
--

LOCK TABLES `releasesubs` WRITE;
/*!40000 ALTER TABLE `releasesubs` DISABLE KEYS */;
/*!40000 ALTER TABLE `releasesubs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releasevideo`
--

DROP TABLE IF EXISTS `releasevideo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releasevideo` (
  `releaseID` int(11) unsigned NOT NULL,
  `containerformat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `overallbitrate` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `videoduration` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `videoformat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `videocodec` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `videowidth` int(10) DEFAULT NULL,
  `videoheight` int(10) DEFAULT NULL,
  `videoaspect` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `videoframerate` float(7,4) DEFAULT NULL,
  `videolibrary` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`releaseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releasevideo`
--

LOCK TABLES `releasevideo` WRITE;
/*!40000 ALTER TABLE `releasevideo` DISABLE KEYS */;
/*!40000 ALTER TABLE `releasevideo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `searchnameRegex`
--

DROP TABLE IF EXISTS `searchnameRegex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `searchnameRegex` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `regexString` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `caseSensitive` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `backReferenceNum` smallint(8) unsigned NOT NULL DEFAULT '0',
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Regex strings to use to clean subjects up for release searchname field';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `searchnameRegex`
--

LOCK TABLES `searchnameRegex` WRITE;
/*!40000 ALTER TABLE `searchnameRegex` DISABLE KEYS */;
INSERT INTO `searchnameRegex` VALUES (1,'katanxya','^katanxya \"(katanxya\\d+)',0,1,'0126-00-08 06:37:30','0000-00-00 00:00:00'),(2,'[01/52] - \"H1F3E_20130715_005.par2\" - 4.59 GB yEnc','^\\[\\d+\\/\\d+\\] - \"([a-zA-Z0-9\\-_]+\\..+?)\"( - \\d).+?( yEnc)$',0,1,'0126-00-08 06:37:30','0000-00-00 00:00:00'),(3,'alt.binaries.town','(<TOWN><www\\.town\\.ag > <download all our files with>>>  www\\.ssl-news\\.info <<< >)(.+)\"(.+)\"',0,3,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(4,'thnx to original poster','(^thnx to original poster .+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(5,'teenage [xx/xx]','(^teenage - \\[\\d{1,3}\\/\\d{1,3}.+)\"(.+)',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(6,'Troll HD','(^\\[ TrollHD \\] - \\[ \\d{1,3}\\/\\d{1,3}.+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(7,'Magnum Opus','(^Magnum Opus \\[\\d{1,3}\\/\\d{1,3}.+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(8,'impossible','(^impossible.+ \\[\\d{1,3}\\/\\d{1,3}.+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(9,'(????)','(^\\(\\?\\?\\?\\?\\) \\[\\d{1,3}\\/\\d{1,3}.+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(10,'aangemeld bij usenet collector','(^\\(aangemeld bij usenet collector\\) \\[\\d{1,3}\\/\\d{1,3}.+)\"(.+)\"',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(11,'Film -','(^Film - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(12,'P2H - ','(^P2H - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:34','0000-00-00 00:00:00'),(13,'Sneaker posts','(^Sneaker posts F\\d \\d{4} Spain Event Pack Part\\d\\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(14,'panter - [xxx/xxx]','(^panter - \\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(15,'NMR - BOOK','(^NMR - BOOK \\d\\d - \\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(16,'http://dream-of-usenet.org','(^http\\:\\/\\/dream-of-usenet\\.org empfehlen newsconnection\\.eu - \\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(17,'ghost of usenet.org','(^<.+><ghost-of-usenet\\.org><Dreamload\\.com>\\(\\d{1,4}\\/\\d{1,4}\\)) \"(.+)\"',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(18,'posting_4_Usenet2day','(^posting_4_Usenet2day\\.cc \\[powered By LibraNews\\.eu\\]\\[\\d{1,4}\\/\\d{1,4}\\]) - \"(.+)\"',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(19,'Molly spot','(^Molly spot - - \\[\\d{1,4}\\/\\d{1,4}\\]) - \"(.+)\"',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(20,'jo post','(^jo post )\"(.+)\"',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(21,'nzbee','(^nzbee - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(22,'(xxx/xxx) - Knight','(^\\(\\d{1,3}\\/\\d{1,3}\\) - Knight - )\"(.+)\"',0,2,'0126-00-08 06:37:35','5246-00-08 06:45:08'),(23,'Blackbunny post op theFatBoys.org','(^\\$Blackbunny post op theFatBoys\\.org  \\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\"',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(24,'AdSlager','(^\\(AdSlager\\) \\[\\d{1,3}\\/\\d{1,3}\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(25,'UR-powered by SecretUsenet.com','(^\\[\\d{1,4}\\/\\d{1,4}\\] - )\"(.+)\".+(UR-powered by SecretUsenet\\.com).+yEnc',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(26,'usenetHQ | Disney TV','(usenetHQ Upload \\| Disney TV \\| )\"(.+)\"',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(27,'8 or more letters/numbers in quotes','\\\"([A-Z0-9]{8,})\\\" yEnc',0,1,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(28,'[278997]-[FULL]-[#a.b.erotica]-[ chi-the.walking.dead.xxx ]-[06/51] - \"chi-the.walking.dead.xxx-s.mp4\" yEnc','^\\[\\d+\\]-\\[.+?\\]-\\[.+?\\]-\\[ (.+?) \\]-\\[\\d+\\/\\d+\\] - \"(.+?)\" yEnc$',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(29,'>ghost-of-usenet.org>Udo Lindenberg & Alla Borissowna Pugatschowa - Songs Instead Of Letters [01/11] - \"ul_abp.nfo\" yEnc','^>ghost-of-usenet\\.org>(.+?) \\[\\d+\\/\\d+\\] - \".+?\" yEnc$',0,1,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(30,'<<< <ghost-of-usenet.org> <\"ABBYY.FineReader.v11.0.102.583.Corporate.Edition.MULTiLANGUAGE-PillePalle.7z.007\"> >www.SSL-News.info< - - 397,31 MB yEnc','.+?<ghost-of-usenet\\.org>( <[a-zA-Z]+>)? <\"(.+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\")> >www\\..+? yEnc$',0,2,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(31,'<ghost-of-usenet.org>Das.Glueck.dieser.Erde.S01E04.German.WS.DVDRiP.XViD-AMBASSADOR<>www.SSL-News.info< \"ar-dgde-s01e04-xvid-sample.avi\" yEnc','^<ghost-of-usenet\\.org>(.+?)<>www\\..+? \".+?\" yEnc$',0,1,'0126-00-08 06:37:38','5876-02-08 06:45:08'),(32,'NihilCumsteR [1/8] - \"Conysgirls.cumpilation.xxx.NihilCumsteR.par2\" yEnc','^NihilCumsteR.+?\"(.+?)NihilCumsteR\\.',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(33,'>ghost-of-usenet.org>Monte.Cristo.GERMAN.2002.AC3.DVDRiP.XviD.iNTERNAL-HACO<HAVE FUN> \"haco-montecristo-xvid-a.par2\" yEnc','^>ghost-of-usenet\\.org>(.+?)<.+?> \".+?\" yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(34,'<ghost-of-usenet.org>XCOM.Enemy.Unknown.Deutsch.Patch.TokZic [0/9] - \"XCOM Deutsch.nzb\" ein CrazyUpp yEnc','^<ghost-of-usenet\\.org>(.+?) \\[\\d+\\/\\d+\\] - \".+?\" .+? yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(35,'<ghost-of-usenet.org>XCOM.Enemy.Unknown.Deutsch.Patch.TokZic [0/9] - \"XCOM Deutsch.nzb\" ein CrazyUpp yEnc','^<ghost-of-usenet\\.org>(.+?) \\[\\d+\\/\\d+\\] - \".+?\" .+? yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(36,'ea17079f47de702eead5114038355a70 [1/9] - \"00-da_morty_-_boondock_sampler_02-(tbr002)-web-2013-srg.m3u\" yEnc','^([a-fA-F0-9]+) \\[\\d+\\/\\d+\\] - \".+?(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") yEnc$',0,2,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(37,'[usenet4ever.info] und [SecretUsenet.com] - 96e323468c5a8a7b948c06ec84511839-u4e - \"96e323468c5a8a7b948c06ec84511839-u4e.par2\" yEnc','^\\[usenet4ever\\.info\\] und \\[SecretUsenet\\.com\\] - (.+?)-u4e - \".+?\" yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(38,'brothers-of-usenet.info/.net <<<Partner von SSL-News.info>>> - [01/26] - \"Be.Cool.German.AC3.HDRip.x264-FuN.par2\" yEnc','\\.net <<<Partner von SSL-News\\.info>>> - \\[\\d+\\/\\d+\\] - \"(.+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(39,'[42788]-[#altbin@EFNet]-[Full]- \"margin-themasterb-xvid.par2\" yEnc','^\\[\\d+\\]-\\[.+?\\]-\\[.+?\\]- \"(.+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(40,'[052713]-[#eos@EFNet]-[All_Shall_Perish-Montreal_QUE_0628-2007-EOS]-[09/14] \"06-all_shall_perish-deconstruction-eos.mp3\" yEnc','^\\[(\\d+)\\]-\\[.+?\\]-\\[(.+?)\\]-\\[\\d+\\/\\d+\\] \".+?\" yEnc$',0,1,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(41,'High School DxD New 01 (480p|.avi|xvid|mp3) ~bY Hatsuyuki [01/18] - \"[Hatsuyuki]_High_School_DxD_New_01_[848x480][76B2BB8C].avi.001\" yEnc','.+? \\((360|480|720|1080)p\\|.+? ~bY .+? \\[\\d+\\/\\d+\\] - \"(.+?\\[[A-F0-9]+\\].+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") yEnc$',0,2,'0126-00-08 06:37:41','0677-05-08 06:45:08'),(42,'(01/37) \"Entourage S08E08.part01.rar\" - 349,20 MB - yEnc','^\\(\\d+\\/\\d+\\) \"(.+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") - \\d.+? - (\\d.+? -)? yEnc$',0,1,'0126-00-08 06:37:42','3198-00-08 06:45:08'),(43,'ah63jka93jf0jh26ahjas558 - [01/22] - \"ah63jka93jf0jh26ahjas558.par2\" yEnc','^([a-z0-9]+) - \\[\\d+\\/\\d+\\] - \"[a-z0-9]+\\..+?\" yEnc$',0,1,'0126-00-08 06:37:42','3198-00-08 06:45:08'),(44,'Borgen.2x02.A.Bruxelles.Non.Ti.Sentono.Urlare.ITA.BDMux.x264-NovaRip [02/22] - \"borgen.2x02.ita.bdmux.x264-novarip.par2\" yEnc','^([a-zA-Z0-9.\\-]+) \\[\\d+\\/\\d+\\] - \".+?\" yEnc$',0,1,'0126-00-08 06:37:42','3198-00-08 06:45:08'),(45,'(bf1) [03/31] - \"The.Block.AU.Sky.High.S07E56.WS.PDTV.XviD.BF1.part01.sfv\" yEnc','^\\(bf1\\) \\[\\d+\\/\\d+\\] - \"(.+?)(\\.part\\d+)?(\\.(par2|(vol.+?))\"|\\.[a-z0-9]{3}\"|\") yEnc$',0,2,'0126-00-08 06:37:42','3198-00-08 06:45:08'),(46,'[.in]','(\\[\\.in\\] .+ \\[\\d+\\/\\d+\\] - )\"(.+)\" yEnc',0,2,'0126-00-08 06:37:42','3198-00-08 06:45:08');
/*!40000 ALTER TABLE `searchnameRegex` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site`
--

DROP TABLE IF EXISTS `site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(19000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updateddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site`
--

LOCK TABLES `site` WRITE;
/*!40000 ALTER TABLE `site` DISABLE KEYS */;
INSERT INTO `site` VALUES (1,'code','nZEDb','2013-08-06 19:42:55'),(2,'title','nZEDBetter','2013-08-27 19:31:23'),(3,'strapline','A great usenet `indexer','2013-08-06 19:42:55'),(4,'metatitle','An indexer','2013-08-06 19:42:55'),(5,'metadescription','A usenet indexing website','2013-08-06 19:42:55'),(6,'metakeywords','usenet,nzbs,cms,community','2013-08-06 19:42:55'),(7,'footer','Usenet binary indexer.','2013-08-06 19:42:55'),(8,'email','','2013-08-27 19:31:23'),(9,'google_adsense_search','','2013-08-06 19:42:55'),(10,'google_analytics_acc','','2013-08-06 19:42:55'),(11,'google_adsense_acc','','2013-08-06 19:42:55'),(12,'siteseed','fd8fe39f64ad5943dc1beb456d93c73b','2013-08-06 19:42:55'),(13,'tandc','<p>All information within this database is indexed by an automated process, without any human intervention. It is obtained from global Usenet newsgroups over which this site has no control. We cannot prevent that you might find obscene or objectionable material by using this service. If you do come across obscene, incorrect or objectionable results, let us know by using the contact form.</p>','2013-08-06 19:42:55'),(14,'registerstatus','0','2013-08-27 16:32:36'),(15,'style','Default','2013-08-27 16:24:57'),(16,'home_link','/','2013-08-06 19:42:55'),(17,'dereferrer_link','http://derefer.me/?','2013-08-06 20:01:01'),(18,'nzbpath','/var/www/nZEDb/nzbfiles/','2013-08-06 19:46:01'),(19,'lookuptvrage','1','2013-08-06 19:42:55'),(20,'lookupimdb','1','2013-08-06 19:42:55'),(21,'lookupnfo','1','2013-08-06 19:42:55'),(22,'lookupmusic','1','2013-08-06 19:42:55'),(23,'lookupgames','1','2013-08-06 19:42:55'),(24,'lookupbooks','1','2013-08-06 19:42:55'),(25,'lookupanidb','0','2013-08-06 19:42:55'),(26,'maxaddprocessed','20','2013-08-06 20:01:01'),(27,'maxnfoprocessed','100','2013-08-06 19:42:55'),(28,'maxrageprocessed','75','2013-08-06 19:42:55'),(29,'maximdbprocessed','100','2013-08-06 19:42:55'),(30,'maxanidbprocessed','100','2013-08-06 19:42:55'),(31,'maxmusicprocessed','150','2013-08-06 19:42:55'),(32,'maxgamesprocessed','150','2013-08-06 19:42:55'),(33,'maxbooksprocessed','300','2013-08-06 19:42:55'),(34,'maxnzbsprocessed','1000','2013-08-13 04:54:33'),(35,'maxpartrepair','300000','2013-08-14 22:28:21'),(36,'binarythreads','15','2013-08-17 22:04:26'),(37,'backfillthreads','10','2013-08-15 22:09:48'),(38,'postthreads','20','2013-08-06 20:01:01'),(39,'releasethreads','1','2013-08-06 19:42:55'),(40,'nzbthreads','5','2013-08-06 20:01:01'),(41,'amazonpubkey','','2013-08-27 19:31:23'),(42,'amazonprivkey','','2013-08-27 19:31:23'),(43,'amazonassociatetag','','2013-08-27 19:31:23'),(44,'tmdbkey','','2013-08-27 19:31:23'),(45,'rottentomatokey','','2013-08-27 19:31:23'),(46,'trakttvkey','','2013-08-27 19:31:23'),(47,'compressedheaders','1','2013-08-06 20:01:01'),(48,'partrepair','3','2013-08-17 23:16:02'),(49,'maxmssgs','10000','2013-08-15 21:17:24'),(50,'newgroupscanmethod','1','2013-08-06 20:01:01'),(51,'newgroupdaystoscan','3','2013-08-21 19:49:01'),(52,'newgroupmsgstoscan','2000000','2013-08-19 03:13:43'),(53,'sabintegrationtype','0','2013-08-06 20:01:01'),(54,'saburl','','2013-08-06 19:42:55'),(55,'sabapikey','','2013-08-06 19:42:55'),(56,'sabapikeytype','1','2013-08-06 19:42:55'),(57,'sabpriority','0','2013-08-06 19:42:55'),(58,'storeuserips','0','2013-08-06 19:42:55'),(59,'minfilestoformrelease','1','2013-08-06 19:42:55'),(60,'minsizetoformrelease','0','2013-08-06 19:42:55'),(61,'maxsizetoformrelease','107374182400','2013-08-22 01:51:17'),(62,'maxsizetopostprocess','100','2013-08-06 19:42:55'),(63,'releaseretentiondays','1700','2013-08-06 20:01:01'),(64,'checkpasswordedrar','1','2013-08-06 20:01:01'),(65,'showpasswordedrelease','0','2013-08-06 19:42:55'),(66,'deletepasswordedrelease','1','2013-08-06 20:01:01'),(67,'releasecompletion','95','2013-08-06 20:01:01'),(68,'unrarpath','/usr/bin/unrar','2013-08-06 20:01:01'),(69,'mediainfopath','/usr/bin/mediainfo','2013-08-06 20:01:01'),(70,'ffmpegpath','/home/randy/bin/ffmpeg','2013-08-06 20:01:01'),(71,'tmpunrarpath','/var/www/nZEDb/nzbfiles/tmpunrar','2013-08-06 19:46:01'),(72,'adheader','','2013-08-06 19:42:55'),(73,'adbrowse','','2013-08-06 19:42:55'),(74,'addetail','','2013-08-06 19:42:55'),(75,'grabstatus','1','2013-08-06 19:42:55'),(76,'nzbsplitlevel','1','2013-08-06 19:42:55'),(77,'categorizeforeign','1','2013-08-06 19:42:55'),(78,'menuposition','2','2013-08-06 19:42:55'),(79,'crossposttime','4','2013-08-06 20:01:01'),(80,'maxpartsprocessed','3','2013-08-06 19:42:55'),(81,'catlanguage','0','2013-08-06 19:42:55'),(82,'amazonsleep','2000','2013-08-06 20:01:01'),(83,'passchkattempts','1','2013-08-06 19:42:55'),(84,'catwebdl','1','2013-08-06 20:01:01'),(85,'safebackfilldate','2012-06-24','2013-08-06 19:42:55'),(86,'processjpg','1','2013-08-06 20:01:01'),(87,'hashcheck','1','2013-08-06 19:42:55'),(88,'debuginfo','0','2013-08-06 19:42:55'),(89,'processvideos','1','2013-08-06 20:01:01'),(90,'imdburl','0','2013-08-06 19:42:55'),(91,'imdblanguage','en','2013-08-06 19:42:55'),(92,'partretentionhours','96','2013-08-27 19:31:23'),(93,'postdelay','300','2013-08-06 19:42:55'),(94,'processaudiosample','1','2013-08-06 20:01:01'),(95,'predbversion','1','2013-08-06 19:42:55'),(96,'deletepossiblerelease','1','2013-08-06 20:01:01'),(97,'miscotherretentionhours','0','2013-08-06 19:42:55'),(98,'grabnzbs','1','2013-08-09 22:50:42'),(99,'alternate_nntp','1','2013-08-06 20:01:01'),(100,'postthreadsamazon','2','2013-08-06 20:01:01'),(101,'postthreadsnon','5','2013-08-06 20:01:01'),(102,'currentppticket','0','2013-08-06 19:42:55'),(103,'nextppticket','0','2013-08-06 19:42:55'),(104,'segmentstodownload','10','2013-08-06 20:01:01'),(105,'ffmpeg_duration','10','2013-08-06 20:01:01'),(106,'ffmpeg_image_time','8','2013-08-11 11:19:05'),(107,'request_url','http://predb_irc.nzedb.com/predb_irc.php?reqid=[REQUEST_ID]&group=[GROUP_NM]','2013-08-06 19:42:55'),(108,'lookup_reqids','1','2013-08-06 19:42:55'),(109,'grabnzbthreads','15','2013-08-22 03:44:08'),(110,'sqlpatch','99','2013-08-06 19:42:55'),(111,'switchToPosts','1','2013-08-17 23:27:14'),(112,'newGroupMaxMsgs','2000000','2013-08-19 02:46:12');
/*!40000 ALTER TABLE `site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tmux`
--

DROP TABLE IF EXISTS `tmux`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmux` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(19000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updateddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tmux`
--

LOCK TABLES `tmux` WRITE;
/*!40000 ALTER TABLE `tmux` DISABLE KEYS */;
INSERT INTO `tmux` VALUES (1,'DEFRAG_CACHE','900','2013-08-06 19:42:55'),(2,'MONITOR_DELAY','30','2013-08-19 03:32:14'),(3,'TMUX_SESSION','nZEDb','2013-08-08 23:11:35'),(4,'NICENESS','10','2013-08-06 20:03:45'),(5,'BINARIES','TRUE','2013-08-22 03:40:01'),(6,'BACKFILL','0','2013-08-17 08:35:30'),(7,'IMPORT','0','2013-08-10 18:14:24'),(8,'NZBS','/mnt/external2tb/Downloads/','2013-08-09 17:42:16'),(9,'RUNNING','TRUE','2013-08-23 02:40:35'),(10,'SEQUENTIAL','FALSE','2013-08-06 19:42:55'),(11,'NFOS','FALSE','2013-08-06 19:42:55'),(12,'POST','3','2013-08-22 10:45:58'),(13,'RELEASES','TRUE','2013-08-22 07:00:31'),(14,'RELEASES_THREADED','FALSE','2013-08-06 19:42:55'),(15,'FIX_NAMES','TRUE','2013-08-22 10:45:58'),(16,'SEQ_TIMER','30','2013-08-06 19:42:55'),(17,'BINS_TIMER','30','2013-08-06 19:42:55'),(18,'BACK_TIMER','30','2013-08-06 19:42:55'),(19,'IMPORT_TIMER','30','2013-08-06 19:42:55'),(20,'REL_TIMER','30','2013-08-06 19:42:55'),(21,'FIX_TIMER','30','2013-08-06 19:42:55'),(22,'POST_TIMER','30','2013-08-06 19:42:55'),(23,'IMPORT_BULK','FALSE','2013-08-06 19:42:55'),(24,'BACKFILL_QTY','200000','2013-08-06 20:03:45'),(25,'COLLECTIONS_KILL','0','2013-08-06 19:42:55'),(26,'POSTPROCESS_KILL','0','2013-08-06 19:42:55'),(27,'CRAP_TIMER','30','2013-08-06 19:42:55'),(28,'FIX_CRAP','All','2013-08-22 10:45:58'),(29,'TV_TIMER','43200','2013-08-06 19:42:55'),(30,'UPDATE_TV','TRUE','2013-08-06 20:03:45'),(31,'HTOP','TRUE','2013-08-06 20:03:45'),(32,'NMON','FALSE','2013-08-06 19:42:55'),(33,'BWMNG','FALSE','2013-08-06 19:42:55'),(34,'MYTOP','FALSE','2013-08-06 19:42:55'),(35,'CONSOLE','TRUE','2013-08-06 20:03:45'),(36,'VNSTAT','FALSE','2013-08-06 19:42:55'),(37,'VNSTAT_ARGS','','2013-08-06 20:03:45'),(38,'TCPTRACK','FALSE','2013-08-06 19:42:55'),(39,'TCPTRACK_ARGS','-i eth0 port 443','2013-08-06 19:42:55'),(40,'BACKFILL_GROUPS','10','2013-08-07 23:28:57'),(41,'POST_KILL_TIMER','300','2013-08-06 19:42:55'),(42,'OPTIMIZE','FALSE','2013-08-06 19:42:55'),(43,'OPTIMIZE_TIMER','86400','2013-08-06 19:42:55'),(44,'MONITOR_PATH','/var/www/nZEDb/nzbfiles/tmpunrar','2013-08-07 23:25:19'),(45,'WRITE_LOGS','FALSE','2013-08-06 19:42:55'),(46,'SORTER','FALSE','2013-08-06 19:42:55'),(47,'SORTER_TIMER','30','2013-08-06 19:42:55'),(48,'POWERLINE','TRUE','2013-08-06 20:03:45'),(49,'PATCHDB','FALSE','2013-08-06 19:42:55'),(50,'PATCHDB_TIMER','21600','2013-08-06 19:42:55'),(51,'PROGRESSIVE','TRUE','2013-08-06 20:03:45'),(52,'DEHASH','3','2013-08-06 20:03:45'),(53,'DEHASH_TIMER','30','2013-08-06 19:42:55'),(54,'BACKFILL_ORDER','1','2013-08-06 20:03:45'),(55,'BACKFILL_DAYS','2','2013-08-06 20:03:45'),(56,'POST_AMAZON','TRUE','2013-08-06 20:03:45'),(57,'POST_NON','TRUE','2013-08-06 20:03:45'),(58,'POST_TIMER_AMAZON','30','2013-08-06 19:42:55'),(59,'POST_TIMER_NON','30','2013-08-06 19:42:55'),(60,'COLORS_START','1','2013-08-06 19:42:55'),(61,'COLORS_END','250','2013-08-06 19:42:55'),(62,'COLORS_EXC','4, 8, 11, 15, 16, 17, 18, 19, 46, 47, 48, 49, 50, 51, 53, 59, 60','2013-08-16 02:59:55'),(63,'MONITOR_PATH_A','','2013-08-06 20:03:45'),(64,'MONITOR_PATH_B','','2013-08-06 20:03:45'),(65,'RUN_PURGE_THREAD','FALSE','2013-08-22 18:22:43'),(66,'PURGE_MAX_COLS','100','2013-08-22 19:46:07'),(67,'PURGE_SLEEP','10','2013-08-23 02:40:35'),(68,'NEXT_FULL_PURGE',NULL,'2013-08-27 01:32:18'),(69,'FULL_PURGE_FREQ','24','2013-08-20 02:35:05'),(70,'FURIOUS_PURGE','TRUE','2013-08-27 01:44:06');
/*!40000 ALTER TABLE `tmux` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tvrage`
--

DROP TABLE IF EXISTS `tvrage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tvrage` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rageID` int(11) NOT NULL,
  `tvdbID` int(11) NOT NULL DEFAULT '0',
  `releasetitle` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(10000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `genre` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imgdata` longblob,
  `createddate` datetime DEFAULT NULL,
  `prevdate` datetime DEFAULT NULL,
  `previnfo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nextdate` datetime DEFAULT NULL,
  `nextinfo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_tvrage_rageID` (`rageID`)
) ENGINE=MyISAM AUTO_INCREMENT=10025 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tvrage`
--

LOCK TABLES `tvrage` WRITE;
/*!40000 ALTER TABLE `tvrage` DISABLE KEYS */;
/*!40000 ALTER TABLE `tvrage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tvrageepisodes`
--

DROP TABLE IF EXISTS `tvrageepisodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tvrageepisodes` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rageID` int(11) unsigned NOT NULL,
  `showtitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `airdate` datetime NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fullep` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `eptitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `rageID` (`rageID`,`fullep`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tvrageepisodes`
--

LOCK TABLES `tvrageepisodes` WRITE;
/*!40000 ALTER TABLE `tvrageepisodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tvrageepisodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upcoming`
--

DROP TABLE IF EXISTS `upcoming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upcoming` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `source` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `typeID` int(10) NOT NULL,
  `info` text COLLATE utf8_unicode_ci,
  `updateddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `source` (`source`,`typeID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upcoming`
--

LOCK TABLES `upcoming` WRITE;
/*!40000 ALTER TABLE `upcoming` DISABLE KEYS */;
/*!40000 ALTER TABLE `upcoming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usercart`
--

DROP TABLE IF EXISTS `usercart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usercart` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `releaseID` int(11) NOT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_usercart_userrelease` (`userID`,`releaseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usercart`
--

LOCK TABLES `usercart` WRITE;
/*!40000 ALTER TABLE `usercart` DISABLE KEYS */;
/*!40000 ALTER TABLE `usercart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userdownloads`
--

DROP TABLE IF EXISTS `userdownloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userdownloads` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(16) NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `userID` (`userID`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userdownloads`
--

LOCK TABLES `userdownloads` WRITE;
/*!40000 ALTER TABLE `userdownloads` DISABLE KEYS */;
/*!40000 ALTER TABLE `userdownloads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userexcat`
--

DROP TABLE IF EXISTS `userexcat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userexcat` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ix_userexcat_usercat` (`userID`,`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userexcat`
--

LOCK TABLES `userexcat` WRITE;
/*!40000 ALTER TABLE `userexcat` DISABLE KEYS */;
/*!40000 ALTER TABLE `userexcat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userinvite`
--

DROP TABLE IF EXISTS `userinvite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userinvite` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `guid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `userID` int(11) unsigned NOT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userinvite`
--

LOCK TABLES `userinvite` WRITE;
/*!40000 ALTER TABLE `userinvite` DISABLE KEYS */;
/*!40000 ALTER TABLE `userinvite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usermovies`
--

DROP TABLE IF EXISTS `usermovies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usermovies` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(16) NOT NULL,
  `imdbID` mediumint(7) unsigned zerofill DEFAULT NULL,
  `categoryID` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_usermovies_userID` (`userID`,`imdbID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usermovies`
--

LOCK TABLES `usermovies` WRITE;
/*!40000 ALTER TABLE `usermovies` DISABLE KEYS */;
/*!40000 ALTER TABLE `usermovies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userrequests`
--

DROP TABLE IF EXISTS `userrequests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userrequests` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(16) NOT NULL,
  `request` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `userID` (`userID`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userrequests`
--

LOCK TABLES `userrequests` WRITE;
/*!40000 ALTER TABLE `userrequests` DISABLE KEYS */;
/*!40000 ALTER TABLE `userrequests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userroles`
--

DROP TABLE IF EXISTS `userroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userroles` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `apirequests` int(10) unsigned NOT NULL,
  `downloadrequests` int(10) unsigned NOT NULL,
  `defaultinvites` int(10) unsigned NOT NULL,
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `canpreview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userroles`
--

LOCK TABLES `userroles` WRITE;
/*!40000 ALTER TABLE `userroles` DISABLE KEYS */;
INSERT INTO `userroles` VALUES (0,'Guest',0,0,0,0,0),(1,'User',10,10,1,1,0),(2,'Admin',1000,1000,1000,0,1),(3,'Disabled',0,0,0,0,0),(4,'Moderator',1000,1000,1000,0,1),(5,'Friend',100,100,5,0,1);
/*!40000 ALTER TABLE `userroles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` int(11) NOT NULL DEFAULT '1',
  `host` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grabs` int(11) NOT NULL DEFAULT '0',
  `rsstoken` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `createddate` datetime NOT NULL,
  `resetguid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `apiaccess` datetime DEFAULT NULL,
  `invites` int(11) NOT NULL DEFAULT '0',
  `invitedby` int(11) DEFAULT NULL,
  `movieview` int(11) NOT NULL DEFAULT '1',
  `musicview` int(11) NOT NULL DEFAULT '1',
  `consoleview` int(11) NOT NULL DEFAULT '1',
  `bookview` int(11) NOT NULL DEFAULT '1',
  `saburl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sabapikey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sabapikeytype` tinyint(1) DEFAULT NULL,
  `sabpriority` tinyint(1) DEFAULT NULL,
  `userseed` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'randy','jt@thewilliscrew.org','8f8960204031d5ec9a6ccd5614ee665e2e124bf4KQ6h',2,'',0,'1c79b40384618bf0e4e004d62ab4067c','2013-08-26 22:29:45','7f1223e7518e4799866e943af955af73','2013-08-27 14:19:40',NULL,1,NULL,1,1,1,1,NULL,NULL,NULL,NULL,'f764fb313a28a197edd462494dac3500'),(2,'testuser','test@test.com','8d55ec3b07e63b01d289b0fc970eef6ea2aba7e5jghH',2,'',0,'f537c293be7edbe49ba7368726e95b78','2013-08-27 11:33:13',NULL,'2013-08-27 11:41:53',NULL,1,NULL,1,1,1,1,NULL,NULL,NULL,NULL,'e74dfee1c374579ec49052296c2fff2b');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userseries`
--

DROP TABLE IF EXISTS `userseries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userseries` (
  `ID` int(16) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(16) NOT NULL,
  `rageID` int(16) NOT NULL,
  `categoryID` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createddate` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ix_userseries_userID` (`userID`,`rageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userseries`
--

LOCK TABLES `userseries` WRITE;
/*!40000 ALTER TABLE `userseries` DISABLE KEYS */;
/*!40000 ALTER TABLE `userseries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `UniqueCollections`
--

/*!50001 DROP TABLE IF EXISTS `UniqueCollections`*/;
/*!50001 DROP VIEW IF EXISTS `UniqueCollections`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `UniqueCollections` AS select `collections`.`ID` AS `ID`,`collections`.`subject` AS `subject`,`collections`.`fromname` AS `fromname`,`collections`.`date` AS `date`,`collections`.`xref` AS `xref`,`collections`.`totalFiles` AS `totalFiles`,`collections`.`groupID` AS `groupID`,`collections`.`collectionhash` AS `collectionhash`,`collections`.`dateadded` AS `dateadded`,`collections`.`filecheck` AS `filecheck`,`collections`.`filesize` AS `filesize`,`collections`.`releaseID` AS `releaseID` from `collections` group by `collections`.`collectionhash` order by `collections`.`dateadded` desc */;
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

-- Dump completed on 2013-08-27 14:32:56