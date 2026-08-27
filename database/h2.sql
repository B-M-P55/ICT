-- H2O2U Database - Complete Version
-- Import this file in phpMyAdmin

CREATE DATABASE IF NOT EXISTS `h2` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `h2`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `tbl_stock_transaction`;
DROP TABLE IF EXISTS `tbl_payment`;
DROP TABLE IF EXISTS `tbl_delivery`;
DROP TABLE IF EXISTS `tbl_order_details`;
DROP TABLE IF EXISTS `tbl_order`;
DROP TABLE IF EXISTS `tbl_vehicle`;
DROP TABLE IF EXISTS `tbl_driver`;
DROP TABLE IF EXISTS `tbl_product`;
DROP TABLE IF EXISTS `tbl_admin`;
DROP TABLE IF EXISTS `tbl_user`;
DROP TABLE IF EXISTS `tbl_location`;

SET FOREIGN_KEY_CHECKS = 1;

-- tbl_location
CREATE TABLE `tbl_location` (
  `location_ID` int(11) NOT NULL AUTO_INCREMENT,
  `address` varchar(70) NOT NULL,
  `estimated_arrival` varchar(30) NOT NULL,
  PRIMARY KEY (`location_ID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_location` (`location_ID`, `address`, `estimated_arrival`) VALUES
(1, 'Insein', '30 minutes'),
(2, 'Hlaing', '50 minutes'),
(3, 'MayangGone', '45 minutes');

-- tbl_user
CREATE TABLE `tbl_user` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `account_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`userID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

INSERT INTO `tbl_user` (`userID`, `first_name`, `last_name`, `email`, `phone_number`, `password`, `address`, `account_status`) VALUES
(1, 'Thiri', 'Maung', 'thiri@gmail.com', '09111111111', 'password123', 'Yangon', 'active'),
(2, 'Aung', 'Kyaw', 'aung@gmail.com', '09222222222', 'password123', 'Yangon', 'active'),
(3, 'May', 'Thant', 'may@gmail.com', '09333333333', 'password123', 'Yangon', 'active'),
(4, 'Su', 'Su', 'susu@gmail.com', '09444444444', 'password123', 'Yangon', 'active'),
(5, 'Kyaw', 'Zin', 'kyawzin@gmail.com', '09555555555', 'password123', 'Yangon', 'active'),
(6, 'Hnin', 'Wutt Yi', 'hnin@gmail.com', '09666666666', 'password123', 'Yangon', 'active'),
(7, 'Zayar', 'Linn', 'zayar@gmail.com', '09777777777', 'password123', 'Yangon', 'active'),
(8, 'Ei', 'Mon', 'eimon@gmail.com', '09888888888', 'password123', 'Yangon', 'active'),
(9, 'Phone', 'Myint', 'phone@gmail.com', '09999999999', 'password123', 'Yangon', 'active'),
(10, 'Nilar', 'Win', 'nilar@gmail.com', '09123456789', 'password123', 'Yangon', 'active');

-- tbl_admin
CREATE TABLE `tbl_admin` (
  `adminID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`adminID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB;

INSERT INTO `tbl_admin` (`adminID`, `name`, `phone_number`, `email`, `password`, `address`) VALUES
(1, 'H2O2U Administrator', '09123456789', 'admin@h2o2u.com', 'Admin@123', 'H2O2U Office');

-- tbl_product
CREATE TABLE `tbl_product` (
  `productID` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(120) NOT NULL,
  `size` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`productID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_product` (`productID`, `product_name`, `size`, `price`, `stock`, `is_active`, `image_path`) VALUES
(1, 'Purified Drinking Water', '20 L', 1000.00, 100, 1, 'productImage/water_one.jpg'),
(2, 'Purified Drinking Water', '10 L', 600.00, 80, 1, 'productImage/water_two.jpg'),
(3, 'Mineral Water', '1 L', 500.00, 120, 1, 'productImage/one_btl.jpg'),
(4, 'Mineral Water', '500 ml', 300.00, 200, 1, 'productImage/two-btl.jpg');

-- tbl_driver
CREATE TABLE `tbl_driver` (
  `driverID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL,
  `license_number` varchar(80) NOT NULL,
  `hired_date` date NOT NULL,
  `salary` decimal(12,2) NOT NULL,
  PRIMARY KEY (`driverID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_driver` (`driverID`, `name`, `phone_number`, `address`, `license_number`, `hired_date`, `salary`) VALUES
(1, 'Aung Kyaw', '09111222333', 'Kamayut, Yangon', 'B/12345/21', '2023-01-15', 350000.00),
(2, 'Zaw Min', '09222333444', 'Hlaing, Yangon', 'B/23456/21', '2023-03-20', 320000.00),
(3, 'Kyaw Swar', '09333444555', 'Hlaing, Yangon', 'B/34567/22', '2023-06-10', 300000.00),
(4, 'Thura Soe', '09444555666', 'Insein, Yangon', 'B/45678/22', '2023-09-01', 350000.00);

-- tbl_vehicle
CREATE TABLE `tbl_vehicle` (
  `vehicleID` int(11) NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(40) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('available','on_delivery','maintenance','inactive') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`vehicleID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_vehicle` (`vehicleID`, `plate_number`, `capacity`, `status`) VALUES
(1, 'YGN-1A/1234', 50, 'available'),
(2, 'YGN-2B/5678', 100, 'available'),
(3, 'MDY-3C/9012', 50, 'available'),
(4, 'BGO-4D/3456', 80, 'available');

-- tbl_order
CREATE TABLE `tbl_order` (
  `order_ID` int(11) NOT NULL AUTO_INCREMENT,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_order` int(11) NOT NULL DEFAULT 0,
  `total_amount` int(11) NOT NULL DEFAULT 0,
  `userID` int(11) NOT NULL,
  `location_ID` int(11) NOT NULL,
  PRIMARY KEY (`order_ID`),
  KEY `userID` (`userID`),
  KEY `location_ID` (`location_ID`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`userID`) REFERENCES `tbl_user` (`userID`),
  CONSTRAINT `fk_order_location` FOREIGN KEY (`location_ID`) REFERENCES `tbl_location` (`location_ID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_order` (`order_ID`, `order_date`, `total_order`, `total_amount`, `userID`, `location_ID`) VALUES
(1, '2026-08-19 15:40:48', 2, 3200, 1, 1),
(2, '2026-08-19 15:37:14', 1, 3000, 2, 2),
(3, '2026-08-19 15:40:48', 3, 3100, 3, 3),
(4, '2026-08-19 15:40:48', 2, 2200, 4, 1),
(5, '2026-08-19 15:40:48', 4, 3800, 5, 2);

-- tbl_order_details
CREATE TABLE `tbl_order_details` (
  `orderdetailID` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  PRIMARY KEY (`orderdetailID`),
  KEY `orderID` (`orderID`),
  KEY `productID` (`productID`),
  CONSTRAINT `fk_od_order` FOREIGN KEY (`orderID`) REFERENCES `tbl_order` (`order_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_od_product` FOREIGN KEY (`productID`) REFERENCES `tbl_product` (`productID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_order_details` (`orderdetailID`, `quantity`, `price`, `productID`, `orderID`) VALUES
(1, 2, 1000, 1, 1),
(2, 4, 300, 4, 1),
(3, 3, 1000, 1, 2),
(4, 1, 1000, 1, 3),
(5, 3, 500, 3, 3),
(6, 2, 300, 4, 3);

-- tbl_payment
CREATE TABLE `tbl_payment` (
  `paymentID` int(11) NOT NULL AUTO_INCREMENT,
  `payment_amount` int(11) NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `payment_method` enum('Kpay','Cash on Delivery') NOT NULL,
  `payment_status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `payment_photo` varchar(255) DEFAULT NULL,
  `order_ID` int(11) NOT NULL,
  PRIMARY KEY (`paymentID`),
  KEY `order_ID` (`order_ID`),
  CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_ID`) REFERENCES `tbl_order` (`order_ID`)
) ENGINE=InnoDB;

-- tbl_delivery
CREATE TABLE `tbl_delivery` (
  `deliveryID` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','shipping','delivered') NOT NULL DEFAULT 'pending',
  `tracking_number` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `driverID` int(11) NOT NULL,
  `vehicleID` int(11) NOT NULL,
  PRIMARY KEY (`deliveryID`),
  KEY `orderID` (`orderID`),
  KEY `driverID` (`driverID`),
  KEY `vehicleID` (`vehicleID`),
  CONSTRAINT `fk_del_order` FOREIGN KEY (`orderID`) REFERENCES `tbl_order` (`order_ID`),
  CONSTRAINT `fk_del_driver` FOREIGN KEY (`driverID`) REFERENCES `tbl_driver` (`driverID`),
  CONSTRAINT `fk_del_vehicle` FOREIGN KEY (`vehicleID`) REFERENCES `tbl_vehicle` (`vehicleID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_delivery` (`deliveryID`, `date`, `status`, `tracking_number`, `orderID`, `driverID`, `vehicleID`) VALUES
(1, '2026-08-19 15:58:05', 'delivered', 100101, 1, 1, 1),
(2, '2026-08-19 15:58:05', 'delivered', 100102, 2, 2, 2),
(3, '2026-08-19 15:58:05', 'shipping', 100103, 3, 3, 3),
(4, '2026-08-19 15:58:05', 'pending', 100104, 4, 4, 4),
(5, '2026-08-19 15:58:05', 'pending', 100105, 5, 1, 1);

-- tbl_stock_transaction
CREATE TABLE `tbl_stock_transaction` (
  `transactionID` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(255) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `adminID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  PRIMARY KEY (`transactionID`),
  KEY `adminID` (`adminID`),
  KEY `productID` (`productID`),
  CONSTRAINT `fk_stock_admin` FOREIGN KEY (`adminID`) REFERENCES `tbl_admin` (`adminID`),
  CONSTRAINT `fk_stock_product` FOREIGN KEY (`productID`) REFERENCES `tbl_product` (`productID`)
) ENGINE=InnoDB;

INSERT INTO `tbl_stock_transaction` (`transactionID`, `transaction_type`, `quantity`, `reason`, `reference_no`, `adminID`, `productID`) VALUES
(1, 'IN', 100, 'Opening stock', 'PRODUCT-1', 1, 1),
(2, 'IN', 80, 'Opening stock', 'PRODUCT-2', 1, 2),
(3, 'IN', 120, 'Opening stock', 'PRODUCT-3', 1, 3),
(4, 'IN', 200, 'Opening stock', 'PRODUCT-4', 1, 4);

-- Triggers
DELIMITER $$
CREATE TRIGGER `trg_after_order_details_insert` AFTER INSERT ON `tbl_order_details` FOR EACH ROW BEGIN
    UPDATE tbl_order 
    SET 
        total_order = (SELECT COUNT(DISTINCT productID) FROM tbl_order_details WHERE orderID = NEW.orderID),
        total_amount = (SELECT COALESCE(SUM(quantity * price), 0) FROM tbl_order_details WHERE orderID = NEW.orderID)
    WHERE order_ID = NEW.orderID;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `trg_after_order_details_update` AFTER UPDATE ON `tbl_order_details` FOR EACH ROW BEGIN
    UPDATE tbl_order 
    SET 
        total_order = (SELECT COUNT(DISTINCT productID) FROM tbl_order_details WHERE orderID = NEW.orderID),
        total_amount = (SELECT COALESCE(SUM(quantity * price), 0) FROM tbl_order_details WHERE orderID = NEW.orderID)
    WHERE order_ID = NEW.orderID;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `trg_after_order_details_delete` AFTER DELETE ON `tbl_order_details` FOR EACH ROW BEGIN
    UPDATE tbl_order 
    SET 
        total_order = (SELECT COUNT(DISTINCT productID) FROM tbl_order_details WHERE orderID = OLD.orderID),
        total_amount = (SELECT COALESCE(SUM(quantity * price), 0) FROM tbl_order_details WHERE orderID = OLD.orderID)
    WHERE order_ID = OLD.orderID;
END
$$
DELIMITER ;
