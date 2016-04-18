<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	$sql = [
		'0.7.0' => [
			'CREATE TABLE `attachments` (
				`attachmentID` int(11) NOT NULL AUTO_INCREMENT,
				`type` varchar(100) NOT NULL,
				`id` int(11) NOT NULL,
				`employeeID` int(11) NOT NULL,
				`uploadTime` int(11) NOT NULL,
				`name` varchar(200) NOT NULL,
				`extension` varchar(10) NOT NULL,
				`mime` varchar(100) NOT NULL,
				PRIMARY KEY (`attachmentID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `changes` (
				`changeID` int(11) NOT NULL AUTO_INCREMENT,
				`type` varchar(100) NOT NULL,
				`id` int(11) NOT NULL,
				`employeeID` int(11) NOT NULL,
				`changeTime` int(11) NOT NULL,
				`action` varchar(1) NOT NULL,
				`data` text,
				PRIMARY KEY (`changeID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `columns` (
				`employeeID` int(11) NOT NULL,
				`type` varchar(100) NOT NULL,
				`columnOrder` text NOT NULL,
				PRIMARY KEY (`employeeID`,`type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `customers` (
				`customerID` int(11) NOT NULL AUTO_INCREMENT,
				`firstName` varchar(200) NOT NULL,
				`lastName` varchar(200) NOT NULL,
				`email` varchar(200) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`customerID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `discounts` (
				`discountID` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`discountType` varchar(1) NOT NULL,
				`discountAmount` decimal(12,2) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`discountID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `employees` (
				`employeeID` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`username` varchar(100) NOT NULL,
				`password` varchar(60) NOT NULL,
				`firstName` varchar(200) NOT NULL,
				`lastName` varchar(200) NOT NULL,
				`payType` varchar(1) NOT NULL,
				`payAmount` decimal(12,2) NOT NULL,
				`address` varchar(200) DEFAULT NULL,
				`city` varchar(200) DEFAULT NULL,
				`state` varchar(2) DEFAULT NULL,
				`zip` varchar(10) DEFAULT NULL,
				`workEmail` varchar(200) NOT NULL,
				`locationID` int(11) NOT NULL,
				`positionID` int(11) NOT NULL,
				`managerID` int(11) NOT NULL,
				`vacationTotal` int(11) NOT NULL,
				`timeZone` varchar(200) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`employeeID`),
				UNIQUE KEY `username` (`username`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `expenseOthers` (
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `expensePayments` (
				`paymentID` int(11) NOT NULL AUTO_INCREMENT,
				`expenseID` int(11) NOT NULL,
				`date` int(11) NOT NULL,
				`paymentType` varchar(2) NOT NULL,
				`paymentAmount` decimal(12,2) NOT NULL,
				PRIMARY KEY (`paymentID`,`expenseID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `expenses` (
				`expenseID` int(11) NOT NULL AUTO_INCREMENT,
				`supplierID` int(11) DEFAULT NULL,
				`employeeID` int(11) NOT NULL,
				`date` int(11) NOT NULL,
				`amountDue` decimal(12,2) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`expenseID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `expenses_products` (
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `hierarchy` (
				`parentID` int(11) NOT NULL,
				`childID` int(11) NOT NULL,
				`depth` int(11) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `locations` (
				`locationID` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`address` text,
				`city` varchar(200) DEFAULT NULL,
				`state` varchar(2) DEFAULT NULL,
				`zip` varchar(10) DEFAULT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`locationID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `locations_products` (
				`locationID` int(11) NOT NULL,
				`productID` int(11) NOT NULL,
				`quantity` int(11) NOT NULL,
				PRIMARY KEY (`locationID`,`productID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `orderPayments` (
				`paymentID` int(11) NOT NULL AUTO_INCREMENT,
				`orderID` int(11) NOT NULL,
				`date` int(11) NOT NULL,
				`paymentType` varchar(2) NOT NULL,
				`paymentAmount` decimal(12,2) NOT NULL,
				PRIMARY KEY (`paymentID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `orders` (
				`orderID` int(11) NOT NULL AUTO_INCREMENT,
				`customerID` int(11) DEFAULT NULL,
				`employeeID` int(11) NOT NULL,
				`date` int(11) NOT NULL,
				`amountDue` decimal(12,2) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`orderID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `orders_discounts` (
				`orderDiscountID` int(11) NOT NULL AUTO_INCREMENT,
				`orderID` int(11) NOT NULL,
				`discountID` int(11) NOT NULL,
				`appliesToType` varchar(1) NOT NULL,
				`appliesToID` int(11) NOT NULL DEFAULT "0",
				`discountType` varchar(1) NOT NULL,
				`discountAmount` decimal(12,2) NOT NULL,
				PRIMARY KEY (`orderDiscountID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `orders_products` (
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `orders_services` (
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
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `positions` (
				`positionID` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`positionID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `products` (
				`productID` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`description` text,
				`defaultPrice` decimal(12,2) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`productID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `services` (
				`serviceID` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`description` text NOT NULL,
				`defaultPrice` decimal(12,2) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`serviceID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `sessions` (
				`sessionID` varchar(100) NOT NULL,
				`creationTime` int(11) NOT NULL,
				`sessionData` text NOT NULL,
				PRIMARY KEY (`sessionID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `suppliers` (
				`supplierID` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) NOT NULL,
				`active` tinyint(1) NOT NULL,
				PRIMARY KEY (`supplierID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `vacationRequests` (
				`vacationRequestID` int(11) NOT NULL AUTO_INCREMENT,
				`employeeID` int(11) NOT NULL,
				`submitTime` int(11) NOT NULL,
				`startTime` int(11) NOT NULL,
				`endTime` int(11) NOT NULL,
				`status` varchar(1) NOT NULL,
				PRIMARY KEY (`vacationRequestID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8'
		],
		'0.8.0' => [
			'ALTER TABLE `employees` ADD `changePassword` TINYINT(1) NOT NULL DEFAULT "1" AFTER `password`',
			'UPDATE employees SET changePassword = 0',
			'ALTER TABLE `employees` ADD `resetTime` INT NOT NULL AFTER `changePassword`',
			'ALTER TABLE `employees` ADD `resetToken` VARCHAR(25) NOT NULL AFTER `resetTime`'
		],
		'0.8.1' => [],
		'0.8.2' => [],
		'0.9.0' => [
			'CREATE TABLE `timesheetHours` (
				`timesheetID` INT NOT NULL,
				`date` INT NOT NULL,
				`regularHours` DECIMAL(5,2) NOT NULL,
				`overtimeHours` DECIMAL(5,2) NOT NULL,
				`holidayHours` DECIMAL(5,2) NOT NULL,
				PRIMARY KEY (`timesheetID`,`date`)
			) ENGINE = InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `timesheets` (
				`timesheetID` INT NOT NULL AUTO_INCREMENT,
				`employeeID` INT NOT NULL,
				`firstDate` INT NOT NULL,
				`lastDate` INT NOT NULL,
				`payType` VARCHAR(1) NOT NULL,
				`payAmount` DECIMAL(12,2) NOT NULL,
				`status` VARCHAR(1) NOT NULL,
				PRIMARY KEY (`timesheetID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'CREATE TABLE `paystubs` (
				`paystubID` INT NOT NULL AUTO_INCREMENT,
				`employeeID` INT NOT NULL,
				`timesheetID` INT NOT NULL,
				`date` INT NOT NULL,
				`grossPay` DECIMAL(12,2) NOT NULL,
				PRIMARY KEY (`paystubID`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8',
			'DROP TABLE vacationRequests'
		]
	];
?>