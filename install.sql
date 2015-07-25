
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
-- Current Database: `audemium_erp`
--

/*!40000 DROP DATABASE IF EXISTS `audemium_erp`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `audemium_erp` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `audemium_erp`;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `attachmentID` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `id` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `uploadTime` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `mime` varchar(100) NOT NULL,
  PRIMARY KEY (`attachmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `changes`
--

DROP TABLE IF EXISTS `changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changes` (
  `changeID` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `id` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `changeTime` int(11) NOT NULL,
  `action` varchar(1) NOT NULL,
  `data` text,
  PRIMARY KEY (`changeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `columns`
--

DROP TABLE IF EXISTS `columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `columns` (
  `employeeID` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `columnOrder` text NOT NULL,
  PRIMARY KEY (`employeeID`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `customerID` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(200) NOT NULL,
  `lastName` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`customerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discounts` (
  `discountID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `discountType` varchar(1) NOT NULL,
  `discountAmount` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`discountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `employeeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstName` varchar(200) NOT NULL,
  `lastName` varchar(200) NOT NULL,
  `payType` varchar(1) NOT NULL,
  `payAmount` decimal(12,2) NOT NULL,
  `address` varchar(200) NOT NULL,
  `city` varchar(200) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `personalEmail` varchar(200) NOT NULL,
  `locationID` int(11) NOT NULL,
  `positionID` int(11) NOT NULL,
  `managerID` int(11) NOT NULL,
  `vacationTotal` int(11) NOT NULL,
  `timeZone` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`employeeID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenseOthers`
--

DROP TABLE IF EXISTS `expenseOthers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenseOthers` (
  `expenseOtherID` int(11) NOT NULL AUTO_INCREMENT,
  `expenseID` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `date` int(11) DEFAULT NULL,
  `unitPrice` decimal(12,2) NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `lineAmount` decimal(12,2) NOT NULL,
  `recurringID` int(11) DEFAULT NULL,
  `parentRecurringID` int(11) DEFAULT NULL,
  `dayOfMonth` int(11) DEFAULT NULL,
  `startDate` int(11) DEFAULT NULL,
  `endDate` int(11) DEFAULT NULL,
  PRIMARY KEY (`expenseOtherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expensePayments`
--

DROP TABLE IF EXISTS `expensePayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expensePayments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `expenseID` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `paymentType` varchar(2) NOT NULL,
  `paymentAmount` decimal(12,2) NOT NULL,
  PRIMARY KEY (`paymentID`,`expenseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `expenseID` int(11) NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) DEFAULT NULL,
  `employeeID` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `amountDue` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`expenseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses_products`
--

DROP TABLE IF EXISTS `expenses_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses_products` (
  `expenseProductID` int(11) NOT NULL AUTO_INCREMENT,
  `expenseID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `locationID` int(11) NOT NULL,
  `date` int(11) DEFAULT NULL,
  `unitPrice` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineAmount` decimal(12,2) NOT NULL,
  `recurringID` int(11) DEFAULT NULL,
  `parentRecurringID` int(11) DEFAULT NULL,
  `dayOfMonth` int(11) DEFAULT NULL,
  `startDate` int(11) DEFAULT NULL,
  `endDate` int(11) DEFAULT NULL,
  PRIMARY KEY (`expenseProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hierarchy`
--

DROP TABLE IF EXISTS `hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hierarchy` (
  `parentID` int(11) NOT NULL,
  `childID` int(11) NOT NULL,
  `depth` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `locationID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(200) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`locationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations_products`
--

DROP TABLE IF EXISTS `locations_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations_products` (
  `locationID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`locationID`,`productID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orderPayments`
--

DROP TABLE IF EXISTS `orderPayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderPayments` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `paymentType` varchar(2) NOT NULL,
  `paymentAmount` decimal(12,2) NOT NULL,
  PRIMARY KEY (`paymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL AUTO_INCREMENT,
  `customerID` int(11) DEFAULT NULL,
  `employeeID` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `amountDue` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_discounts`
--

DROP TABLE IF EXISTS `orders_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_discounts` (
  `orderDiscountID` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL,
  `discountID` int(11) NOT NULL,
  `appliesToType` varchar(1) NOT NULL,
  `appliesToID` int(11) NOT NULL DEFAULT '0',
  `discountType` varchar(1) NOT NULL,
  `discountAmount` decimal(12,2) NOT NULL,
  PRIMARY KEY (`orderDiscountID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_products`
--

DROP TABLE IF EXISTS `orders_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_products` (
  `orderProductID` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL,
  `productID` varchar(45) NOT NULL,
  `date` int(11) DEFAULT NULL,
  `unitPrice` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `lineAmount` decimal(12,2) NOT NULL,
  `recurringID` int(11) DEFAULT NULL,
  `parentRecurringID` int(11) DEFAULT NULL,
  `dayOfMonth` int(11) DEFAULT NULL,
  `startDate` int(11) DEFAULT NULL,
  `endDate` int(11) DEFAULT NULL,
  PRIMARY KEY (`orderProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_services`
--

DROP TABLE IF EXISTS `orders_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_services` (
  `orderServiceID` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL,
  `serviceID` int(11) NOT NULL,
  `date` int(11) DEFAULT NULL,
  `unitPrice` decimal(12,2) NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `lineAmount` decimal(12,2) NOT NULL,
  `recurringID` int(11) DEFAULT NULL,
  `parentRecurringID` int(11) DEFAULT NULL,
  `dayOfMonth` int(11) DEFAULT NULL,
  `startDate` int(11) DEFAULT NULL,
  `endDate` int(11) DEFAULT NULL,
  PRIMARY KEY (`orderServiceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `positionID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`positionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `productID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `defaultPrice` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`productID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `serviceID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `defaultPrice` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`serviceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sessionID` varchar(100) NOT NULL,
  `creationTime` int(11) NOT NULL,
  `sessionData` text NOT NULL,
  PRIMARY KEY (`sessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `supplierID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`supplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vacationRequests`
--

DROP TABLE IF EXISTS `vacationRequests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vacationRequests` (
  `vacationRequestID` int(11) NOT NULL AUTO_INCREMENT,
  `employeeID` int(11) NOT NULL,
  `submitTime` int(11) NOT NULL,
  `startTime` int(11) NOT NULL,
  `endTime` int(11) NOT NULL,
  `status` varchar(1) NOT NULL,
  PRIMARY KEY (`vacationRequestID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Inserting some starter data
--

INSERT INTO `employees` VALUES (null,'fs1','$2y$10$NvL.1HHbXYrI9alSqyequOna3PDe3uoReiPpWelU5.qwMHOEf4V8W','First','Surname','S',50000.00,'1600 Pennsylvania Avenue','Washington','DC','20500','example@example.com',1,1,0,80,'',1);
INSERT INTO `changes` VALUES (null,'employee',1,1,1437787825,'A','{"firstName":"First","lastName":"Surname","payType":"S","payAmount":"50000.00","locationID":"1","positionID":"1","managerID":"","vacationTotal":"80","address":"1600 Pennsylvania Avenue","city":"Washington","personalEmail":"example@example.com","state":"DC","zip":"20500"}');
INSERT INTO `positions` VALUES (null,'Owner',1);
INSERT INTO `changes` VALUES (null,'position',1,1,1437787825,'A','{"name":"Owner"}');
INSERT INTO `locations` VALUES (null,'HQ','1600 Pennsylvania Avenue','Washington','DC','20500',1);
INSERT INTO `changes` VALUES (null,'location',1,1,1437787825,'A','{"name":"HQ","address":"1600 Pennsylvania Avenue","city":"Washington","state":"DC","zip":"20500"}');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
