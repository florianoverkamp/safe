-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-9
-- http://www.phpmyadmin.net
-- 
-- Host: toledo.simsplace.com
-- Generation Time: Jan 25, 2009 at 06:00 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.0-8+etch13
-- 
-- Database: `safe-demo`
-- 

-- 
-- Dumping data for table `customers`
-- 

INSERT INTO `customers` (`customerid`, `customername`, `attn`, `street`, `zipcode`, `city`, `country`, `vatnr`, `language`) VALUES 
(14, 'Example BV', '', 'Dummy straat', '1111 XY', 'Nergensheen', '', '', 'nl');

-- 
-- Dumping data for table `domains`
-- 


-- 
-- Dumping data for table `invoicelines`
-- 

INSERT INTO `invoicelines` (`invoicelineid`, `invoiceid`, `customerid`, `subscriptionid`, `description`, `invoicelinedate`, `amount`, `charge`) VALUES 
(212, 1, 14, 1, 'Setup charge: SAFE as a Service hosted for Example BV', '2009-01-03', 1, 150.00),
(213, 1, 14, 1, 'SAFE as a Service hosted for Example BV january', '2009-01-03', 1, 25.00);

-- 
-- Dumping data for table `services`
-- 

INSERT INTO `services` (`serviceid`, `servicename`, `nrc`, `mrc`, `qrc`, `yrc`) VALUES 
(17, 'SAFE as a Service', 150.00, 25.00, 0.00, 0.00),
(18, 'Call charges', 0.00, 0.00, 0.00, 0.00);

-- 
-- Dumping data for table `subscriptions`
-- 

INSERT INTO `subscriptions` (`subscriptionid`, `customerid`, `serviceid`, `description`, `amount`, `lastinvoiced`) VALUES 
(1, 14, 17, 'hosted for Example BV', 1, '2009-01-03'),
(2, 14, 18, 'example_sip', 1, '2009-01-25');

