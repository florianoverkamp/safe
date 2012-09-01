-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-9
-- http://www.phpmyadmin.net
-- 
-- Host: toledo.simsplace.com
-- Generation Time: Jan 03, 2009 at 08:57 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.0-8+etch13
-- 
-- Database: `safe`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `customers`
-- 

CREATE TABLE `customers` (
  `customerid` bigint(20) NOT NULL auto_increment,
  `customername` varchar(80) NOT NULL,
  `attn` varchar(80) NOT NULL,
  `street` varchar(80) NOT NULL,
  `zipcode` varchar(80) NOT NULL,
  `city` varchar(80) NOT NULL,
  `country` varchar(80) NOT NULL,
  `vatnr` varchar(20) NOT NULL,
  `language` varchar(2) NOT NULL default 'nl',
  PRIMARY KEY  (`customerid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `domains`
-- 

CREATE TABLE `domains` (
  `domainid` bigint(20) NOT NULL auto_increment,
  `domain` varchar(120) NOT NULL,
  `rsp` varchar(120) NOT NULL,
  `registrar` varchar(120) NOT NULL,
  `whoisglue` varchar(120) NOT NULL,
  `dnshosts` varchar(120) NOT NULL,
  `mxhosts` varchar(120) NOT NULL,
  `web` varchar(120) NOT NULL,
  PRIMARY KEY  (`domainid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `invoicelines`
-- 

CREATE TABLE `invoicelines` (
  `invoicelineid` bigint(20) NOT NULL auto_increment,
  `invoiceid` bigint(20) default NULL,
  `customerid` bigint(20) NOT NULL,
  `subscriptionid` bigint(20) default NULL,
  `description` varchar(80) NOT NULL,
  `invoicelinedate` date NOT NULL,
  `amount` float NOT NULL default '1',
  `charge` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`invoicelineid`),
  KEY `customerid` (`customerid`),
  KEY `customerserviceid` (`subscriptionid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=210 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `services`
-- 

CREATE TABLE `services` (
  `serviceid` bigint(20) NOT NULL auto_increment,
  `servicename` varchar(128) NOT NULL,
  `nrc` decimal(10,2) NOT NULL,
  `mrc` decimal(10,2) NOT NULL,
  `qrc` decimal(10,2) NOT NULL,
  `yrc` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`serviceid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `subscriptions`
-- 

CREATE TABLE `subscriptions` (
  `subscriptionid` bigint(20) NOT NULL auto_increment,
  `customerid` bigint(20) NOT NULL,
  `serviceid` bigint(20) NOT NULL,
  `description` varchar(80) NOT NULL,
  `amount` bigint(20) NOT NULL default '1',
  `lastinvoiced` date NOT NULL,
  PRIMARY KEY  (`subscriptionid`),
  KEY `customerid` (`customerid`),
  KEY `serviceid` (`serviceid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=76 ;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `subscriptions`
-- 
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`customerid`) REFERENCES `customers` (`customerid`),
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`serviceid`) REFERENCES `services` (`serviceid`);

