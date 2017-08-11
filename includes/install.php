<?php
function church_admin_install()
{
    /**
 *
 * Installs WP tables and options
 * 
 * @author  Andy MoyleF
 * @param    null
 * @return   
 * @version  0.2
 *
 * 
 * 
 */ 
    global $wpdb,$church_admin_version;
 $wpdb->show_errors();
 church_admin_debug("******** Install.php firing for $church_admin_version ");
 
//working with children - australia
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_CP_TBL.'"') != CA_CP_TBL)
    {
    	$sql='CREATE TABLE `'.CA_CP_TBL.'` (`people_id` INT(11),`department_id` TEXT,`employment_status` TEXT,`start_date` DATE NULL,`CRW_cat` TEXT,`exemptions` TEXT, `status` TEXT,`receipt` TEXT, `WWC_card` TEXT, `WWC_expiry` DATE NULL, `review_date` DATE NULL, `validation_date` DATE NULL, `DBS` TEXT, `DBS_date` DATE NULL, `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);    
  	}

/*********************************************************
*
* App tables
*
*********************************************************/



//app logins added 2016-08-05
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_APP_TBL.'"') != CA_APP_TBL)
    {
        $sql='CREATE TABLE  '.CA_APP_TBL.' (`UUID` TEXT NOT NULL ,`user_id` INT NOT NULL,`people_id` INT NOT NULL ,`last_login` DATETIME,`last_page` TEXT, app_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);
    }  
    //fix table error from v1.072
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_APP_TBL.' LIKE "last-login"')=='last-login')
    {
    	$sql='ALTER TABLE  `'.CA_APP_TBL.'` CHANGE  `last-login`  `last_login` DATETIME NULL DEFAULT NULL ;';
    	$wpdb->query($sql);
    
     }
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_APP_TBL.' LIKE "last_page"')!='last_page')
    {
    	$sql='ALTER TABLE  `'.CA_APP_TBL.'` ADD last_page TEXT ;';
    	
    	$wpdb->query($sql);
    
     }
    //Bible REading Plan
 
      if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_BRP_TBL.'"') != CA_BRP_TBL)
    {
        $sql='CREATE TABLE IF NOT EXISTS `'.CA_BRP_TBL.'` (  `readings` TEXT NOT NULL,`passages` TEXT NOT NULL,  `ID` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`ID`)) ENGINE=MyISAM';
        $wpdb->query($sql);
        $sql='INSERT INTO '.CA_BRP_TBL.' (`readings`, `passages`, `ID`) VALUES
(\'a:4:{i:0;s:5:"Gen 1";i:1;s:6:"Matt 1";i:2;s:6:"Ezra 1";i:3;s:6:"Acts 1";}\',"",1),
(\'a:4:{i:0;s:5:"Gen 2";i:1;s:6:"Matt 2";i:2;s:6:"Ezra 2";i:3;s:6:"Acts 2";}\',"", 2),
(\'a:4:{i:0;s:5:"Gen 3";i:1;s:6:"Matt 3";i:2;s:6:"Ezra 3";i:3;s:6:"Acts 3";}\',"", 3),
(\'a:4:{i:0;s:5:"Gen 4";i:1;s:6:"Matt 4";i:2;s:6:"Ezra 4";i:3;s:6:"Acts 4";}\',"", 4),
(\'a:4:{i:0;s:5:"Gen 5";i:1;s:6:"Matt 5";i:2;s:6:"Ezra 5";i:3;s:6:"Acts 5";}\',"", 5),
(\'a:4:{i:0;s:5:"Gen 6";i:1;s:6:"Matt 6";i:2;s:6:"Ezra 6";i:3;s:6:"Acts 6";}\',"", 6),
(\'a:4:{i:0;s:5:"Gen 7";i:1;s:6:"Matt 7";i:2;s:6:"Ezra 7";i:3;s:6:"Acts 7";}\',"", 7),
(\'a:4:{i:0;s:5:"Gen 8";i:1;s:6:"Matt 8";i:2;s:6:"Ezra 8";i:3;s:6:"Acts 8";}\',"", 8),
(\'a:4:{i:0;s:8:"Gen 9-10";i:1;s:6:"Matt 9";i:2;s:6:"Ezra 9";i:3;s:6:"Acts 9";}\',"", 9),
(\'a:4:{i:0;s:6:"Gen 11";i:1;s:7:"Matt 10";i:2;s:7:"Ezra 10";i:3;s:7:"Acts 10";}\',"", 10),
(\'a:4:{i:0;s:6:"Gen 12";i:1;s:7:"Matt 11";i:2;s:5:"Neh 1";i:3;s:7:"Acts 11";}\',"", 11),
(\'a:4:{i:0;s:6:"Gen 13";i:1;s:7:"Matt 12";i:2;s:5:"Neh 2";i:3;s:7:"Acts 12";}\',"", 12),
(\'a:4:{i:0;s:6:"Gen 14";i:1;s:7:"Matt 13";i:2;s:5:"Neh 3";i:3;s:7:"Acts 13";}\',"",13),
(\'a:4:{i:0;s:6:"Gen 15";i:1;s:7:"Matt 14";i:2;s:5:"Neh 4";i:3;s:7:"Acts 14";}\', "",14),
(\'a:4:{i:0;s:6:"Gen 16";i:1;s:7:"Matt 15";i:2;s:5:"Neh 5";i:3;s:7:"Acts 15";}\',"",15),
(\'a:4:{i:0;s:6:"Gen 17";i:1;s:7:"Matt 16";i:2;s:5:"Neh 6";i:3;s:7:"Acts 16";}\',"",16),
(\'a:4:{i:0;s:6:"Gen 18";i:1;s:7:"Matt 17";i:2;s:5:"Neh 7";i:3;s:7:"Acts 17";}\',"",17),
(\'a:4:{i:0;s:6:"Gen 19";i:1;s:7:"Matt 18";i:2;s:5:"Neh 8";i:3;s:7:"Acts 18";}\',"",18),
(\'a:4:{i:0;s:6:"Gen 20";i:1;s:7:"Matt 19";i:2;s:5:"Neh 9";i:3;s:7:"Acts 19";}\',"",19),
(\'a:4:{i:0;s:6:"Gen 21";i:1;s:7:"Matt 20";i:2;s:6:"Neh 10";i:3;s:7:"Acts 20";}\',"",20),
(\'a:4:{i:0;s:6:"Gen 22";i:1;s:7:"Matt 21";i:2;s:6:"Neh 11";i:3;s:7:"Acts 21";}\',"", 21),
(\'a:4:{i:0;s:6:"Gen 23";i:1;s:7:"Matt 22";i:2;s:6:"Neh 12";i:3;s:7:"Acts 22";}\',"",22),
(\'a:4:{i:0;s:6:"Gen 24";i:1;s:7:"Matt 23";i:2;s:6:"Neh 13";i:3;s:7:"Acts 23";}\',"", 23),
(\'a:4:{i:0;s:6:"Gen 25";i:1;s:7:"Matt 24";i:2;s:5:"Est 1";i:3;s:7:"Acts 24";}\',"", 24),
(\'a:4:{i:0;s:6:"Gen 26";i:1;s:7:"Matt 25";i:2;s:5:"Est 2";i:3;s:7:"Acts 25";}\',"", 25),
(\'a:4:{i:0;s:6:"Gen 27";i:1;s:7:"Matt 26";i:2;s:5:"Est 3";i:3;s:7:"Acts 26";}\',"", 26),
(\'a:4:{i:0;s:6:"Gen 28";i:1;s:7:"Matt 27";i:2;s:5:"Est 4";i:3;s:7:"Acts 27";}\',"", 27),
(\'a:4:{i:0;s:6:"Gen 29";i:1;s:7:"Matt 28";i:2;s:5:"Est 5";i:3;s:7:"Acts 28";}\',"", 28),
(\'a:4:{i:0;s:6:"Gen 30";i:1;s:6:"Mark 1";i:2;s:5:"Est 6";i:3;s:5:"Rom 1";}\',"", 29),
(\'a:4:{i:0;s:6:"Gen 31";i:1;s:6:"Mark 2";i:2;s:5:"Est 7";i:3;s:5:"Rom 2";}\',"", 30),
(\'a:4:{i:0;s:6:"Gen 32";i:1;s:6:"Mark 3";i:2;s:5:"Est 8";i:3;s:5:"Rom 3";}\',"", 31),
(\'a:4:{i:0;s:6:"Gen 33";i:1;s:6:"Mark 4";i:2;s:8:"Est 9-10";i:3;s:5:"Rom 4";}\',"", 32),
(\'a:4:{i:0;s:6:"Gen 34";i:1;s:6:"Mark 5";i:2;s:5:"Job 1";i:3;s:5:"Rom 5";}\',"", 33),
(\'a:4:{i:0;s:9:"Gen 35-36";i:1;s:6:"Mark 6";i:2;s:5:"Job 2";i:3;s:5:"Rom 6";}\',"", 34),
(\'a:4:{i:0;s:6:"Gen 37";i:1;s:6:"Mark 7";i:2;s:5:"Job 3";i:3;s:5:"Rom 7";}\',"", 35),
(\'a:4:{i:0;s:6:"Gen 38";i:1;s:6:"Mark 8";i:2;s:5:"Job 4";i:3;s:5:"Rom 8";}\',"", 36),
(\'a:4:{i:0;s:6:"Gen 39";i:1;s:6:"Mark 9";i:2;s:5:"Job 5";i:3;s:5:"Rom 9";}\',"", 37),
(\'a:4:{i:0;s:6:"Gen 40";i:1;s:7:"Mark 10";i:2;s:5:"Job 6";i:3;s:6:"Rom 10";}\',"", 38),
(\'a:4:{i:0;s:6:"Gen 41";i:1;s:7:"Mark 11";i:2;s:5:"Job 7";i:3;s:6:"Rom 11";}\',"", 39),
(\'a:4:{i:0;s:6:"Gen 42";i:1;s:7:"Mark 12";i:2;s:5:"Job 8";i:3;s:6:"Rom 12";}\',"", 40),
(\'a:4:{i:0;s:6:"Gen 43";i:1;s:7:"Mark 13";i:2;s:5:"Job 9";i:3;s:6:"Rom 13";}\',"", 41),
(\'a:4:{i:0;s:6:"Gen 44";i:1;s:7:"Mark 14";i:2;s:6:"Job 10";i:3;s:6:"Rom 14";}\',"", 42),
(\'a:4:{i:0;s:6:"Gen 45";i:1;s:7:"Mark 15";i:2;s:6:"Job 11";i:3;s:6:"Rom 15";}\',"", 43),
(\'a:4:{i:0;s:6:"Gen 46";i:1;s:7:"Mark 16";i:2;s:6:"Job 12";i:3;s:6:"Rom 16";}\',"", 44),
(\'a:4:{i:0;s:6:"Gen 47";i:1;s:11:"Luke 1:1-38";i:2;s:6:"Job 13";i:3;s:7:"1 Cor 1";}\',"", 45),
(\'a:4:{i:0;s:6:"Gen 48";i:1;s:12:"Luke 1:39-80";i:2;s:6:"Job 14";i:3;s:7:"1 Cor 2";}\',"", 46),
(\'a:4:{i:0;s:6:"Gen 49";i:1;s:6:"Luke 2";i:2;s:6:"Job 15";i:3;s:7:"1 Cor 3";}\',"", 47),
(\'a:4:{i:0;s:6:"Gen 50";i:1;s:6:"Luke 3";i:2;s:9:"Job 16-17";i:3;s:7:"1 Cor 4";}\',"", 48),
(\'a:4:{i:0;s:4:"Ex 1";i:1;s:6:"Luke 4";i:2;s:6:"Job 18";i:3;s:7:"1 Cor 5";}\',"", 49),
(\'a:4:{i:0;s:4:"Ex 2";i:1;s:6:"Luke 5";i:2;s:6:"Job 19";i:3;s:7:"1 Cor 6";}\',"", 50),
(\'a:4:{i:0;s:4:"Ex 3";i:1;s:6:"Luke 6";i:2;s:6:"Job 20";i:3;s:7:"1 Cor 7";}\',"", 51),
(\'a:4:{i:0;s:4:"Ex 4";i:1;s:6:"Luke 7";i:2;s:6:"Job 21";i:3;s:7:"1 Cor 8";}\',"", 52),
(\'a:4:{i:0;s:4:"Ex 5";i:1;s:6:"Luke 8";i:2;s:6:"Job 22";i:3;s:7:"1 Cor 9";}\',"", 53),
(\'a:4:{i:0;s:4:"Ex 6";i:1;s:6:"Luke 9";i:2;s:6:"Job 23";i:3;s:8:"1 Cor 10";}\',"", 54),
(\'a:4:{i:0;s:4:"Ex 7";i:1;s:7:"Luke 10";i:2;s:6:"Job 24";i:3;s:8:"1 Cor 11";}\',"", 55),
(\'a:4:{i:0;s:4:"Ex 8";i:1;s:7:"Luke 11";i:2;s:9:"Job 25-26";i:3;s:8:"1 Cor 12";}\',"", 56),
(\'a:4:{i:0;s:4:"Ex 9";i:1;s:7:"Luke 12";i:2;s:6:"Job 27";i:3;s:8:"1 Cor 13";}\',"", 57),
(\'a:4:{i:0;s:5:"Ex 10";i:1;s:7:"Luke 13";i:2;s:6:"Job 28";i:3;s:8:"1 Cor 14";}\',"", 58),
(\'a:4:{i:0;s:5:"Ex 11";i:1;s:7:"Luke 14";i:2;s:6:"Job 29";i:3;s:8:"1 Cor 15";}\',"", 59),
(\'a:4:{i:0;s:5:"Ex 12";i:1;s:7:"Luke 15";i:2;s:6:"Job 30";i:3;s:8:"1 Cor 16";}\',"", 60),
(\'a:4:{i:0;s:5:"Ex 13";i:1;s:7:"Luke 16";i:2;s:6:"Job 31";i:3;s:7:"2 Cor 1";}\',"", 61),
(\'a:4:{i:0;s:5:"Ex 14";i:1;s:7:"Luke 17";i:2;s:6:"Job 32";i:3;s:7:"2 Cor 2";}\',"", 62),
(\'a:4:{i:0;s:5:"Ex 15";i:1;s:7:"Luke 18";i:2;s:6:"Job 33";i:3;s:7:"2 Cor 3";}\',"", 63),
(\'a:4:{i:0;s:5:"Ex 16";i:1;s:7:"Luke 19";i:2;s:6:"Job 34";i:3;s:7:"2 Cor 4";}\',"", 64),
(\'a:4:{i:0;s:5:"Ex 17";i:1;s:7:"Luke 20";i:2;s:6:"Job 35";i:3;s:7:"2 Cor 5";}\',"", 65),
(\'a:4:{i:0;s:5:"Ex 18";i:1;s:7:"Luke 21";i:2;s:6:"Job 36";i:3;s:7:"2 Cor 6";}\',"", 66),
(\'a:4:{i:0;s:5:"Ex 19";i:1;s:7:"Luke 22";i:2;s:6:"Job 37";i:3;s:7:"2 Cor 7";}\',"", 67),
(\'a:4:{i:0;s:5:"Ex 20";i:1;s:7:"Luke 23";i:2;s:6:"Job 38";i:3;s:7:"2 Cor 8";}\',"", 68),
(\'a:4:{i:0;s:5:"Ex 21";i:1;s:7:"Luke 24";i:2;s:6:"Job 39";i:3;s:7:"2 Cor 9";}\',"", 69),
(\'a:4:{i:0;s:5:"Ex 22";i:1;s:6:"John 1";i:2;s:6:"Job 40";i:3;s:8:"2 Cor 10";}\',"", 70),
(\'a:4:{i:0;s:5:"Ex 23";i:1;s:6:"John 2";i:2;s:6:"Job 41";i:3;s:8:"2 Cor 11";}\',"", 71),
(\'a:4:{i:0;s:5:"Ex 24";i:1;s:6:"John 3";i:2;s:6:"Job 42";i:3;s:8:"2 Cor 12";}\',"", 72),
(\'a:4:{i:0;s:5:"Ex 25";i:1;s:6:"John 4";i:2;s:6:"Prov 1";i:3;s:8:"2 Cor 13";}\',"", 73),
(\'a:4:{i:0;s:5:"Ex 26";i:1;s:6:"John 5";i:2;s:6:"Prov 2";i:3;s:5:"Gal 1";}\',"", 74),
(\'a:4:{i:0;s:5:"Ex 27";i:1;s:6:"John 6";i:2;s:6:"Prov 3";i:3;s:5:"Gal 2";}\',"", 75),
(\'a:4:{i:0;s:5:"Ex 28";i:1;s:6:"John 7";i:2;s:6:"Prov 4";i:3;s:5:"Gal 3";}\',"", 76),
(\'a:4:{i:0;s:5:"Ex 29";i:1;s:6:"John 8";i:2;s:6:"Prov 5";i:3;s:5:"Gal 4";}\',"", 77),
(\'a:4:{i:0;s:5:"Ex 30";i:1;s:6:"John 9";i:2;s:6:"Prov 6";i:3;s:5:"Gal 5";}\',"", 78),
(\'a:4:{i:0;s:5:"Ex 31";i:1;s:7:"John 10";i:2;s:6:"Prov 7";i:3;s:5:"Gal 6";}\',"", 79),
(\'a:4:{i:0;s:5:"Ex 32";i:1;s:7:"John 11";i:2;s:6:"Prov 8";i:3;s:5:"Eph 1";}\',"", 80),
(\'a:4:{i:0;s:5:"Ex 33";i:1;s:7:"John 12";i:2;s:6:"Prov 9";i:3;s:5:"Eph 2";}\',"", 81),
(\'a:4:{i:0;s:5:"Ex 34";i:1;s:7:"John 13";i:2;s:7:"Prov 10";i:3;s:5:"Eph 3";}\',"", 82),
(\'a:4:{i:0;s:5:"Ex 35";i:1;s:7:"John 14";i:2;s:7:"Prov 11";i:3;s:5:"Eph 4";}\',"", 83),
(\'a:4:{i:0;s:5:"Ex 36";i:1;s:7:"John 15";i:2;s:7:"Prov 12";i:3;s:5:"Eph 5";}\',"", 84),
(\'a:4:{i:0;s:5:"Ex 37";i:1;s:7:"John 16";i:2;s:7:"Prov 13";i:3;s:5:"Eph 6";}\',"", 85),
(\'a:4:{i:0;s:5:"Ex 38";i:1;s:7:"John 17";i:2;s:7:"Prov 14";i:3;s:6:"Phil 1";}\',"", 86),
(\'a:4:{i:0;s:5:"Ex 39";i:1;s:7:"John 18";i:2;s:7:"Prov 15";i:3;s:6:"Phil 2";}\',"", 87),
(\'a:4:{i:0;s:5:"Ex 40";i:1;s:7:"John 19";i:2;s:7:"Prov 16";i:3;s:6:"Phil 3";}\',"", 88),
(\'a:4:{i:0;s:5:"Lev 1";i:1;s:7:"John 20";i:2;s:7:"Prov 17";i:3;s:6:"Phil 4";}\',"", 89),
(\'a:4:{i:0;s:7:"Lev 2-3";i:1;s:7:"John 21";i:2;s:7:"Prov 18";i:3;s:5:"Col 1";}\',"", 90),
(\'a:4:{i:0;s:5:"Lev 4";i:1;s:6:"Ps 1-2";i:2;s:7:"Prov 19";i:3;s:5:"Col 2";}\',"", 91),
(\'a:4:{i:0;s:5:"Lev 5";i:1;s:6:"Ps 3-4";i:2;s:7:"Prov 20";i:3;s:5:"Col 3";}\',"", 92),
(\'a:4:{i:0;s:5:"Lev 6";i:1;s:6:"Ps 5-6";i:2;s:7:"Prov 21";i:3;s:5:"Col 4";}\',"", 93),
(\'a:4:{i:0;s:5:"Lev 7";i:1;s:6:"Ps 7-8";i:2;s:7:"Prov 22";i:3;s:8:"1 Thes 1";}\',"", 94),
(\'a:4:{i:0;s:5:"Lev 8";i:1;s:4:"Ps 9";i:2;s:7:"Prov 23";i:3;s:8:"1 Thes 2";}\',"", 95),
(\'a:4:{i:0;s:5:"Lev 9";i:1;s:5:"Ps 10";i:2;s:7:"Prov 24";i:3;s:8:"1 Thes 3";}\',"", 96),
(\'a:4:{i:0;s:6:"Lev 10";i:1;s:8:"Ps 11-12";i:2;s:7:"Prov 25";i:3;s:8:"1 Thes 4";}\',"", 97),
(\'a:4:{i:0;s:9:"Lev 11-12";i:1;s:8:"Ps 13-14";i:2;s:7:"Prov 26";i:3;s:8:"1 Thes 5";}\',"", 98),
(\'a:4:{i:0;s:6:"Lev 13";i:1;s:8:"Ps 15-16";i:2;s:7:"Prov 27";i:3;s:8:"2 Thes 1";}\',"", 99),
(\'a:4:{i:0;s:6:"Lev 14";i:1;s:5:"Ps 17";i:2;s:7:"Prov 28";i:3;s:8:"2 Thes 2";}\',"", 100),
(\'a:4:{i:0;s:6:"Lev 15";i:1;s:5:"Ps 18";i:2;s:7:"Prov 29";i:3;s:8:"2 Thes 3";}\',"", 101),
(\'a:4:{i:0;s:6:"Lev 16";i:1;s:5:"Ps 19";i:2;s:7:"Prov 30";i:3;s:7:"1 Tim 1";}\',"", 102),
(\'a:4:{i:0;s:6:"Lev 17";i:1;s:8:"Ps 20-21";i:2;s:7:"Prov 31";i:3;s:7:"1 Tim 2";}\',"", 103),
(\'a:4:{i:0;s:6:"Lev 18";i:1;s:5:"Ps 22";i:2;s:6:"Eccl 1";i:3;s:7:"1 Tim 3";}\',"", 104),
(\'a:4:{i:0;s:6:"Lev 19";i:1;s:8:"Ps 23-24";i:2;s:6:"Eccl 2";i:3;s:7:"1 Tim 4";}\',"", 105),
(\'a:4:{i:0;s:6:"Lev 20";i:1;s:5:"Ps 25";i:2;s:6:"Eccl 3";i:3;s:7:"1 Tim 5";}\',"", 106),
(\'a:4:{i:0;s:6:"Lev 21";i:1;s:8:"Ps 26-27";i:2;s:6:"Eccl 4";i:3;s:7:"1 Tim 6";}\',"", 107),
(\'a:4:{i:0;s:6:"Lev 22";i:1;s:8:"Ps 28-29";i:2;s:6:"Eccl 5";i:3;s:7:"2 Tim 1";}\',"", 108),
(\'a:4:{i:0;s:6:"Lev 23";i:1;s:5:"Ps 30";i:2;s:6:"Eccl 6";i:3;s:7:"2 Tim 2";}\',"", 109),
(\'a:4:{i:0;s:6:"Lev 24";i:1;s:5:"Ps 31";i:2;s:6:"Eccl 7";i:3;s:7:"2 Tim 3";}\',"", 110),
(\'a:4:{i:0;s:6:"Lev 25";i:1;s:5:"Ps 32";i:2;s:6:"Eccl 8";i:3;s:7:"2 Tim 4";}\',"", 111),
(\'a:4:{i:0;s:6:"Lev 26";i:1;s:5:"Ps 33";i:2;s:6:"Eccl 9";i:3;s:7:"Titus 1";}\',"", 112),
(\'a:4:{i:0;s:6:"Lev 27";i:1;s:5:"Ps 34";i:2;s:7:"Eccl 10";i:3;s:7:"Titus 2";}\',"", 113),
(\'a:4:{i:0;s:5:"Num 1";i:1;s:5:"Ps 35";i:2;s:7:"Eccl 11";i:3;s:7:"Titus 3";}\',"", 114),
(\'a:4:{i:0;s:5:"Num 2";i:1;s:5:"Ps 36";i:2;s:7:"Eccl 12";i:3;s:5:"Phm 1";}\',"", 115),
(\'a:4:{i:0;s:5:"Num 3";i:1;s:5:"Ps 37";i:2;s:5:"Sng 1";i:3;s:5:"Heb 1";}\',"", 116),
(\'a:4:{i:0;s:5:"Num 4";i:1;s:5:"Ps 38";i:2;s:5:"Sng 2";i:3;s:5:"Heb 2";}\',"", 117),
(\'a:4:{i:0;s:5:"Num 5";i:1;s:5:"Ps 39";i:2;s:5:"Sng 3";i:3;s:5:"Heb 3";}\',"", 118),
(\'a:4:{i:0;s:5:"Num 6";i:1;s:8:"Ps 40-41";i:2;s:5:"Sng 4";i:3;s:5:"Heb 4";}\',"", 119),
(\'a:4:{i:0;s:5:"Num 7";i:1;s:8:"Ps 42-43";i:2;s:5:"Sng 5";i:3;s:5:"Heb 5";}\',"", 120),
(\'a:4:{i:0;s:5:"Num 8";i:1;s:5:"Ps 44";i:2;s:5:"Sng 6";i:3;s:5:"Heb 6";}\',"", 121),
(\'a:4:{i:0;s:5:"Num 9";i:1;s:5:"Ps 45";i:2;s:5:"Sng 7";i:3;s:5:"Heb 7";}\',"", 122),
(\'a:4:{i:0;s:6:"Num 10";i:1;s:8:"Ps 46-47";i:2;s:5:"Sng 8";i:3;s:5:"Heb 8";}\',"", 123),
(\'a:4:{i:0;s:6:"Num 11";i:1;s:5:"Ps 48";i:2;s:5:"Isa 1";i:3;s:5:"Heb 9";}\',"", 124),
(\'a:4:{i:0;s:9:"Num 12-13";i:1;s:5:"Ps 49";i:2;s:5:"Isa 2";i:3;s:6:"Heb 10";}\',"", 125),
(\'a:4:{i:0;s:6:"Num 14";i:1;s:5:"Ps 50";i:2;s:7:"Isa 3-4";i:3;s:6:"Heb 11";}\',"", 126),
(\'a:4:{i:0;s:6:"Num 15";i:1;s:5:"Ps 51";i:2;s:5:"Isa 5";i:3;s:6:"Heb 12";}\',"", 127),
(\'a:4:{i:0;s:6:"Num 16";i:1;s:8:"Ps 52-54";i:2;s:5:"Isa 6";i:3;s:6:"Heb 13";}\',"", 128),
(\'a:4:{i:0;s:9:"Num 17-18";i:1;s:5:"Ps 55";i:2;s:5:"Isa 7";i:3;s:5:"Jas 1";}\',"", 129),
(\'a:4:{i:0;s:6:"Num 19";i:1;s:8:"Ps 56-57";i:2;s:5:"Isa 8";i:3;s:5:"Jas 2";}\',"", 130),
(\'a:4:{i:0;s:6:"Num 20";i:1;s:8:"Ps 58-59";i:2;s:5:"Isa 9";i:3;s:5:"Jas 3";}\',"", 131),
(\'a:4:{i:0;s:6:"Num 21";i:1;s:8:"Ps 60-61";i:2;s:6:"Isa 10";i:3;s:5:"Jas 4";}\',"", 132),
(\'a:4:{i:0;s:6:"Num 22";i:1;s:8:"Ps 62-63";i:2;s:9:"Isa 11-12";i:3;s:5:"Jas 5";}\',"", 133),
(\'a:4:{i:0;s:6:"Num 23";i:1;s:8:"Ps 64-65";i:2;s:6:"Isa 13";i:3;s:7:"1 Pet 1";}\',"", 134),
(\'a:4:{i:0;s:6:"Num 24";i:1;s:8:"Ps 66-67";i:2;s:6:"Isa 14";i:3;s:7:"1 Pet 2";}\',"", 135),
(\'a:4:{i:0;s:6:"Num 25";i:1;s:5:"Ps 68";i:2;s:6:"Isa 15";i:3;s:7:"1 Pet 3";}\',"", 136),
(\'a:4:{i:0;s:6:"Num 26";i:1;s:5:"Ps 69";i:2;s:6:"Isa 16";i:3;s:7:"1 Pet 4";}\',"", 137),
(\'a:4:{i:0;s:6:"Num 27";i:1;s:8:"Ps 70-71";i:2;s:9:"Isa 17-18";i:3;s:7:"1 Pet 5";}\',"", 138),
(\'a:4:{i:0;s:6:"Num 28";i:1;s:5:"Ps 72";i:2;s:9:"Isa 19-20";i:3;s:7:"2 Pet 1";}\',"", 139),
(\'a:4:{i:0;s:6:"Num 29";i:1;s:5:"Ps 73";i:2;s:6:"Isa 21";i:3;s:7:"2 Pet 2";}\',"", 140),
(\'a:4:{i:0;s:6:"Num 30";i:1;s:5:"Ps 74";i:2;s:6:"Isa 22";i:3;s:7:"2 Pet 3";}\',"", 141),
(\'a:4:{i:0;s:6:"Num 31";i:1;s:8:"Ps 75-76";i:2;s:6:"Isa 23";i:3;s:6:"1 Jn 1";}\',"", 142),
(\'a:4:{i:0;s:6:"Num 32";i:1;s:5:"Ps 77";i:2;s:6:"Isa 24";i:3;s:6:"1 Jn 2";}\',"", 143),
(\'a:4:{i:0;s:6:"Num 33";i:1;s:10:"Ps 78:1-39";i:2;s:6:"Isa 25";i:3;s:6:"1 Jn 3";}\',"", 144),
(\'a:4:{i:0;s:6:"Num 34";i:1;s:11:"Ps 78:40-72";i:2;s:6:"Isa 26";i:3;s:6:"1 Jn 4";}\',"", 145),
(\'a:4:{i:0;s:6:"Num 35";i:1;s:5:"Ps 79";i:2;s:6:"Isa 27";i:3;s:6:"1 Jn 5";}\',"", 146),
(\'a:4:{i:0;s:6:"Num 36";i:1;s:5:"Ps 80";i:2;s:6:"Isa 28";i:3;s:6:"2 Jn 1";}\',"", 147),
(\'a:4:{i:0;s:6:"Deut 1";i:1;s:8:"Ps 81-82";i:2;s:6:"Isa 29";i:3;s:6:"3 Jn 1";}\',"", 148),
(\'a:4:{i:0;s:6:"Deut 2";i:1;s:8:"Ps 83-84";i:2;s:6:"Isa 30";i:3;s:6:"Jude 1";}\',"", 149),
(\'a:4:{i:0;s:6:"Deut 3";i:1;s:5:"Ps 85";i:2;s:6:"Isa 31";i:3;s:5:"Rev 1";}\',"", 150),
(\'a:4:{i:0;s:6:"Deut 4";i:1;s:8:"Ps 86-87";i:2;s:6:"Isa 32";i:3;s:5:"Rev 2";}\',"", 151),
(\'a:4:{i:0;s:6:"Deut 5";i:1;s:5:"Ps 88";i:2;s:6:"Isa 33";i:3;s:5:"Rev 3";}\',"", 152),
(\'a:4:{i:0;s:6:"Deut 6";i:1;s:5:"Ps 89";i:2;s:6:"Isa 34";i:3;s:5:"Rev 4";}\',"", 153),
(\'a:4:{i:0;s:6:"Deut 7";i:1;s:5:"Ps 90";i:2;s:6:"Isa 35";i:3;s:5:"Rev 5";}\',"", 154),
(\'a:4:{i:0;s:6:"Deut 8";i:1;s:5:"Ps 91";i:2;s:6:"Isa 36";i:3;s:5:"Rev 6";}\',"", 155),
(\'a:4:{i:0;s:6:"Deut 9";i:1;s:8:"Ps 92-93";i:2;s:6:"Isa 37";i:3;s:5:"Rev 7";}\',"", 156),
(\'a:4:{i:0;s:7:"Deut 10";i:1;s:5:"Ps 94";i:2;s:6:"Isa 38";i:3;s:5:"Rev 8";}\',"", 157),
(\'a:4:{i:0;s:7:"Deut 11";i:1;s:8:"Ps 95-96";i:2;s:6:"Isa 39";i:3;s:5:"Rev 9";}\',"", 158),
(\'a:4:{i:0;s:7:"Deut 12";i:1;s:8:"Ps 97-98";i:2;s:6:"Isa 40";i:3;s:6:"Rev 10";}\',"", 159),
(\'a:4:{i:0;s:10:"Deut 13-14";i:1;s:9:"Ps 99-101";i:2;s:6:"Isa 41";i:3;s:6:"Rev 11";}\',"", 160),
(\'a:4:{i:0;s:7:"Deut 15";i:1;s:6:"Ps 102";i:2;s:6:"Isa 42";i:3;s:6:"Rev 12";}\',"", 161),
(\'a:4:{i:0;s:7:"Deut 16";i:1;s:6:"Ps 103";i:2;s:6:"Isa 43";i:3;s:6:"Rev 13";}\',"", 162),
(\'a:4:{i:0;s:7:"Deut 17";i:1;s:6:"Ps 104";i:2;s:6:"Isa 44";i:3;s:6:"Rev 14";}\',"", 163),
(\'a:4:{i:0;s:7:"Deut 18";i:1;s:6:"Ps 105";i:2;s:6:"Isa 45";i:3;s:6:"Rev 15";}\',"", 164),
(\'a:4:{i:0;s:7:"Deut 19";i:1;s:6:"Ps 106";i:2;s:6:"Isa 46";i:3;s:6:"Rev 16";}\',"", 165),
(\'a:4:{i:0;s:7:"Deut 20";i:1;s:6:"Ps 107";i:2;s:6:"Isa 47";i:3;s:6:"Rev 17";}\',"", 166),
(\'a:4:{i:0;s:7:"Deut 21";i:1;s:10:"Ps 108-109";i:2;s:6:"Isa 48";i:3;s:6:"Rev 18";}\',"", 167),
(\'a:4:{i:0;s:7:"Deut 22";i:1;s:10:"Ps 110-111";i:2;s:6:"Isa 49";i:3;s:6:"Rev 19";}\',"", 168),
(\'a:4:{i:0;s:7:"Deut 23";i:1;s:10:"Ps 112-113";i:2;s:6:"Isa 50";i:3;s:6:"Rev 20";}\',"", 169),
(\'a:4:{i:0;s:7:"Deut 24";i:1;s:10:"Ps 114-115";i:2;s:6:"Isa 51";i:3;s:6:"Rev 21";}\',"", 170),
(\'a:4:{i:0;s:7:"Deut 25";i:1;s:6:"Ps 116";i:2;s:6:"Isa 52";i:3;s:6:"Rev 22";}\',"", 171),
(\'a:4:{i:0;s:7:"Deut 26";i:1;s:10:"Ps 117-118";i:2;s:6:"Isa 53";i:3;s:6:"Matt 1";}\',"", 172),
(\'a:4:{i:0;s:7:"Deut 27";i:1;s:11:"Ps 119:1-24";i:2;s:6:"Isa 54";i:3;s:6:"Matt 2";}\',"", 173),
(\'a:4:{i:0;s:7:"Deut 28";i:1;s:12:"Ps 119:25-48";i:2;s:6:"Isa 55";i:3;s:6:"Matt 3";}\',"", 174),
(\'a:4:{i:0;s:7:"Deut 29";i:1;s:12:"Ps 119:49-72";i:2;s:6:"Isa 56";i:3;s:6:"Matt 4";}\',"", 175),
(\'a:4:{i:0;s:7:"Deut 30";i:1;s:12:"Ps 119:73-96";i:2;s:6:"Isa 57";i:3;s:6:"Matt 5";}\',"", 176),
(\'a:4:{i:0;s:7:"Deut 31";i:1;s:13:"Ps 119:97-120";i:2;s:6:"Isa 58";i:3;s:6:"Matt 6";}\',"", 177),
(\'a:4:{i:0;s:7:"Deut 32";i:1;s:14:"Ps 119:121-144";i:2;s:6:"Isa 59";i:3;s:6:"Matt 7";}\',"", 178),
(\'a:4:{i:0;s:10:"Deut 33-34";i:1;s:14:"Ps 119:145-176";i:2;s:6:"Isa 60";i:3;s:6:"Matt 8";}\',"", 179),
(\'a:4:{i:0;s:6:"Josh 1";i:1;s:10:"Ps 120-122";i:2;s:6:"Isa 61";i:3;s:6:"Matt 9";}\',"", 180),
(\'a:4:{i:0;s:6:"Josh 2";i:1;s:10:"Ps 123-125";i:2;s:6:"Isa 62";i:3;s:7:"Matt 10";}\',"", 181),
(\'a:4:{i:0;s:6:"Josh 3";i:1;s:10:"Ps 126-128";i:2;s:6:"Isa 63";i:3;s:7:"Matt 11";}\',"", 182),
(\'a:4:{i:0;s:6:"Josh 4";i:1;s:10:"Ps 129-131";i:2;s:6:"Isa 64";i:3;s:7:"Matt 12";}\',"", 183),
(\'a:4:{i:0;s:6:"Josh 5";i:1;s:10:"Ps 132-134";i:2;s:6:"Isa 65";i:3;s:7:"Matt 13";}\',"", 184),
(\'a:4:{i:0;s:6:"Josh 6";i:1;s:10:"Ps 135-136";i:2;s:6:"Isa 66";i:3;s:7:"Matt 14";}\',"", 185),
(\'a:4:{i:0;s:6:"Josh 7";i:1;s:10:"Ps 137-138";i:2;s:5:"Jer 1";i:3;s:7:"Matt 15";}\',"", 186),
(\'a:4:{i:0;s:6:"Josh 8";i:1;s:6:"Ps 139";i:2;s:5:"Jer 2";i:3;s:7:"Matt 16";}\',"", 187),
(\'a:4:{i:0;s:6:"Josh 9";i:1;s:10:"Ps 140-141";i:2;s:5:"Jer 3";i:3;s:7:"Matt 17";}\',"", 188),
(\'a:4:{i:0;s:7:"Josh 10";i:1;s:10:"Ps 142-143";i:2;s:5:"Jer 4";i:3;s:7:"Matt 18";}\',"", 189),
(\'a:4:{i:0;s:7:"Josh 11";i:1;s:6:"Ps 144";i:2;s:5:"Jer 5";i:3;s:7:"Matt 19";}\',"", 190),
(\'a:4:{i:0;s:10:"Josh 12-13";i:1;s:6:"Ps 145";i:2;s:5:"Jer 6";i:3;s:7:"Matt 20";}\',"", 191),
(\'a:4:{i:0;s:10:"Josh 14-15";i:1;s:10:"Ps 146-147";i:2;s:5:"Jer 7";i:3;s:7:"Matt 21";}\',"", 192),
(\'a:4:{i:0;s:10:"Josh 16-17";i:1;s:6:"Ps 148";i:2;s:5:"Jer 8";i:3;s:7:"Matt 22";}\',"", 193),
(\'a:4:{i:0;s:10:"Josh 18-19";i:1;s:10:"Ps 149-150";i:2;s:5:"Jer 9";i:3;s:7:"Matt 23";}\',"", 194),
(\'a:4:{i:0;s:10:"Josh 20-21";i:1;s:6:"Acts 1";i:2;s:6:"Jer 10";i:3;s:7:"Matt 24";}\',"", 195),
(\'a:4:{i:0;s:7:"Josh 22";i:1;s:6:"Acts 2";i:2;s:6:"Jer 11";i:3;s:7:"Matt 25";}\',"", 196),
(\'a:4:{i:0;s:7:"Josh 23";i:1;s:6:"Acts 3";i:2;s:6:"Jer 12";i:3;s:7:"Matt 26";}\',"", 197),
(\'a:4:{i:0;s:7:"Josh 24";i:1;s:6:"Acts 4";i:2;s:6:"Jer 13";i:3;s:7:"Matt 27";}\',"", 198),
(\'a:4:{i:0;s:6:"Judg 1";i:1;s:6:"Acts 5";i:2;s:6:"Jer 14";i:3;s:7:"Matt 28";}\',"", 199),
(\'a:4:{i:0;s:6:"Judg 2";i:1;s:6:"Acts 6";i:2;s:6:"Jer 15";i:3;s:6:"Mark 1";}\',"", 200),
(\'a:4:{i:0;s:6:"Judg 3";i:1;s:6:"Acts 7";i:2;s:6:"Jer 16";i:3;s:6:"Mark 2";}\',"", 201),
(\'a:4:{i:0;s:6:"Judg 4";i:1;s:6:"Acts 8";i:2;s:6:"Jer 17";i:3;s:6:"Mark 3";}\',"", 202),
(\'a:4:{i:0;s:6:"Judg 5";i:1;s:6:"Acts 9";i:2;s:6:"Jer 18";i:3;s:6:"Mark 4";}\',"", 203),
(\'a:4:{i:0;s:6:"Judg 6";i:1;s:7:"Acts 10";i:2;s:6:"Jer 19";i:3;s:6:"Mark 5";}\',"", 204),
(\'a:4:{i:0;s:6:"Judg 7";i:1;s:7:"Acts 11";i:2;s:6:"Jer 20";i:3;s:6:"Mark 6";}\',"", 205),
(\'a:4:{i:0;s:6:"Judg 8";i:1;s:7:"Acts 12";i:2;s:6:"Jer 21";i:3;s:6:"Mark 7";}\',"", 206),
(\'a:4:{i:0;s:6:"Judg 9";i:1;s:7:"Acts 13";i:2;s:6:"Jer 22";i:3;s:6:"Mark 8";}\',"", 207),
(\'a:4:{i:0;s:7:"Judg 10";i:1;s:7:"Acts 14";i:2;s:6:"Jer 23";i:3;s:6:"Mark 9";}\',"", 208),
(\'a:4:{i:0;s:7:"Judg 11";i:1;s:7:"Acts 15";i:2;s:6:"Jer 24";i:3;s:7:"Mark 10";}\',"", 209),
(\'a:4:{i:0;s:7:"Judg 12";i:1;s:7:"Acts 16";i:2;s:6:"Jer 25";i:3;s:7:"Mark 11";}\',"", 210),
(\'a:4:{i:0;s:7:"Judg 13";i:1;s:7:"Acts 17";i:2;s:6:"Jer 26";i:3;s:7:"Mark 12";}\',"", 211),
(\'a:4:{i:0;s:7:"Judg 14";i:1;s:7:"Acts 18";i:2;s:6:"Jer 27";i:3;s:7:"Mark 13";}\',"", 212),
(\'a:4:{i:0;s:7:"Judg 15";i:1;s:7:"Acts 19";i:2;s:6:"Jer 28";i:3;s:7:"Mark 14";}\',"", 213),
(\'a:4:{i:0;s:7:"Judg 16";i:1;s:7:"Acts 20";i:2;s:6:"Jer 29";i:3;s:7:"Mark 15";}\',"", 214),
(\'a:4:{i:0;s:7:"Judg 17";i:1;s:7:"Acts 21";i:2;s:9:"Jer 30-31";i:3;s:7:"Mark 16";}\',"", 215),
(\'a:4:{i:0;s:7:"Judg 18";i:1;s:7:"Acts 22";i:2;s:6:"Jer 32";i:3;s:6:"Luke 1";}\',"", 216),
(\'a:4:{i:0;s:7:"Judg 19";i:1;s:7:"Acts 23";i:2;s:6:"Jer 33";i:3;s:6:"Luke 2";}\',"", 217),
(\'a:4:{i:0;s:7:"Judg 20";i:1;s:7:"Acts 24";i:2;s:6:"Jer 34";i:3;s:6:"Luke 3";}\',"", 218),
(\'a:4:{i:0;s:7:"Judg 21";i:1;s:7:"Acts 25";i:2;s:6:"Jer 35";i:3;s:6:"Luke 4";}\',"", 219),
(\'a:4:{i:0;s:6:"Ruth 1";i:1;s:7:"Acts 26";i:2;s:6:"Jer 36";i:3;s:6:"Luke 5";}\',"", 220),
(\'a:4:{i:0;s:6:"Ruth 2";i:1;s:7:"Acts 27";i:2;s:6:"Jer 37";i:3;s:6:"Luke 6";}\',"", 221),
(\'a:4:{i:0;s:8:"Ruth 3-4";i:1;s:7:"Acts 28";i:2;s:6:"Jer 38";i:3;s:6:"Luke 7";}\',"", 222),
(\'a:4:{i:0;s:7:"1 Sam 1";i:1;s:5:"Rom 1";i:2;s:6:"Jer 39";i:3;s:6:"Luke 8";}\',"", 223),
(\'a:4:{i:0;s:7:"1 Sam 2";i:1;s:5:"Rom 2";i:2;s:6:"Jer 40";i:3;s:6:"Luke 9";}\',"", 224),
(\'a:4:{i:0;s:7:"1 Sam 3";i:1;s:5:"Rom 3";i:2;s:6:"Jer 41";i:3;s:7:"Luke 10";}\',"", 225),
(\'a:4:{i:0;s:7:"1 Sam 4";i:1;s:5:"Rom 4";i:2;s:6:"Jer 42";i:3;s:7:"Luke 11";}\',"", 226),
(\'a:4:{i:0;s:9:"1 Sam 5-6";i:1;s:5:"Rom 5";i:2;s:6:"Jer 43";i:3;s:7:"Luke 12";}\',"", 227),
(\'a:4:{i:0;s:9:"1 Sam 7-8";i:1;s:5:"Rom 6";i:2;s:9:"Jer 44-45";i:3;s:7:"Luke 13";}\',"", 228),
(\'a:4:{i:0;s:7:"1 Sam 9";i:1;s:5:"Rom 7";i:2;s:6:"Jer 46";i:3;s:7:"Luke 14";}\',"", 229),
(\'a:4:{i:0;s:8:"1 Sam 10";i:1;s:5:"Rom 8";i:2;s:6:"Jer 47";i:3;s:7:"Luke 15";}\',"", 230),
(\'a:4:{i:0;s:8:"1 Sam 11";i:1;s:5:"Rom 9";i:2;s:6:"Jer 48";i:3;s:7:"Luke 16";}\',"", 231),
(\'a:4:{i:0;s:8:"1 Sam 12";i:1;s:6:"Rom 10";i:2;s:6:"Jer 49";i:3;s:7:"Luke 17";}\',"", 232),
(\'a:4:{i:0;s:8:"1 Sam 13";i:1;s:6:"Rom 11";i:2;s:6:"Jer 50";i:3;s:7:"Luke 18";}\',"", 233),
(\'a:4:{i:0;s:8:"1 Sam 14";i:1;s:6:"Rom 12";i:2;s:6:"Jer 51";i:3;s:7:"Luke 19";}\',"", 234),
(\'a:4:{i:0;s:8:"1 Sam 15";i:1;s:6:"Rom 13";i:2;s:6:"Jer 52";i:3;s:7:"Luke 20";}\',"", 235),
(\'a:4:{i:0;s:8:"1 Sam 16";i:1;s:6:"Rom 14";i:2;s:5:"Lam 1";i:3;s:7:"Luke 21";}\',"", 236),
(\'a:4:{i:0;s:8:"1 Sam 17";i:1;s:6:"Rom 15";i:2;s:5:"Lam 2";i:3;s:7:"Luke 22";}\',"", 237),
(\'a:4:{i:0;s:8:"1 Sam 18";i:1;s:6:"Rom 16";i:2;s:5:"Lam 3";i:3;s:7:"Luke 23";}\',"", 238),
(\'a:4:{i:0;s:8:"1 Sam 19";i:1;s:7:"1 Cor 1";i:2;s:5:"Lam 4";i:3;s:7:"Luke 24";}\',"", 239),
(\'a:4:{i:0;s:8:"1 Sam 20";i:1;s:7:"1 Cor 2";i:2;s:5:"Lam 5";i:3;s:6:"John 1";}\',"", 240),
(\'a:4:{i:0;s:11:"1 Sam 21-22";i:1;s:7:"1 Cor 3";i:2;s:6:"Ezek 1";i:3;s:6:"John 2";}\',"", 241),
(\'a:4:{i:0;s:8:"1 Sam 23";i:1;s:7:"1 Cor 4";i:2;s:6:"Ezek 2";i:3;s:6:"John 3";}\',"", 242),
(\'a:4:{i:0;s:8:"1 Sam 24";i:1;s:7:"1 Cor 5";i:2;s:6:"Ezek 3";i:3;s:6:"John 4";}\',"", 243),
(\'a:4:{i:0;s:8:"1 Sam 25";i:1;s:7:"1 Cor 6";i:2;s:6:"Ezek 4";i:3;s:6:"John 5";}\',"", 244),
(\'a:4:{i:0;s:8:"1 Sam 26";i:1;s:7:"1 Cor 7";i:2;s:6:"Ezek 5";i:3;s:6:"John 6";}\',"", 245),
(\'a:4:{i:0;s:8:"1 Sam 27";i:1;s:7:"1 Cor 8";i:2;s:6:"Ezek 6";i:3;s:6:"John 7";}\',"", 246),
(\'a:4:{i:0;s:8:"1 Sam 28";i:1;s:7:"1 Cor 9";i:2;s:6:"Ezek 7";i:3;s:6:"John 8";}\',"", 247),
(\'a:4:{i:0;s:11:"1 Sam 29-30";i:1;s:8:"1 Cor 10";i:2;s:6:"Ezek 8";i:3;s:6:"John 9";}\',"", 248),
(\'a:4:{i:0;s:8:"1 Sam 31";i:1;s:8:"1 Cor 11";i:2;s:6:"Ezek 9";i:3;s:7:"John 10";}\',"", 249),
(\'a:4:{i:0;s:7:"2 Sam 1";i:1;s:8:"1 Cor 12";i:2;s:7:"Ezek 10";i:3;s:7:"John 11";}\',"", 250),
(\'a:4:{i:0;s:7:"2 Sam 2";i:1;s:8:"1 Cor 13";i:2;s:7:"Ezek 11";i:3;s:7:"John 12";}\',"", 251),
(\'a:4:{i:0;s:7:"2 Sam 3";i:1;s:8:"1 Cor 14";i:2;s:7:"Ezek 12";i:3;s:7:"John 13";}\',"", 252),
(\'a:4:{i:0;s:9:"2 Sam 4-5";i:1;s:8:"1 Cor 15";i:2;s:7:"Ezek 13";i:3;s:7:"John 14";}\',"", 253),
(\'a:4:{i:0;s:7:"2 Sam 6";i:1;s:8:"1 Cor 16";i:2;s:7:"Ezek 14";i:3;s:7:"John 15";}\',"", 254),
(\'a:4:{i:0;s:7:"2 Sam 7";i:1;s:7:"2 Cor 1";i:2;s:7:"Ezek 15";i:3;s:7:"John 16";}\',"", 255),
(\'a:4:{i:0;s:9:"2 Sam 8-9";i:1;s:7:"2 Cor 2";i:2;s:7:"Ezek 16";i:3;s:7:"John 17";}\',"", 256),
(\'a:4:{i:0;s:8:"2 Sam 10";i:1;s:7:"2 Cor 3";i:2;s:7:"Ezek 17";i:3;s:7:"John 18";}\',"", 257),
(\'a:4:{i:0;s:8:"2 Sam 11";i:1;s:7:"2 Cor 4";i:2;s:7:"Ezek 18";i:3;s:7:"John 19";}\',"", 258),
(\'a:4:{i:0;s:8:"2 Sam 12";i:1;s:7:"2 Cor 5";i:2;s:7:"Ezek 19";i:3;s:7:"John 20";}\',"", 259),
(\'a:4:{i:0;s:8:"2 Sam 13";i:1;s:7:"2 Cor 6";i:2;s:7:"Ezek 20";i:3;s:7:"John 21";}\',"", 260),
(\'a:4:{i:0;s:8:"2 Sam 14";i:1;s:7:"2 Cor 7";i:2;s:7:"Ezek 21";i:3;s:6:"Ps 1-2";}\',"", 261),
(\'a:4:{i:0;s:8:"2 Sam 15";i:1;s:7:"2 Cor 8";i:2;s:7:"Ezek 22";i:3;s:6:"Ps 3-4";}\',"", 262),
(\'a:4:{i:0;s:8:"2 Sam 16";i:1;s:7:"2 Cor 9";i:2;s:7:"Ezek 23";i:3;s:6:"Ps 5-6";}\',"", 263),
(\'a:4:{i:0;s:8:"2 Sam 17";i:1;s:8:"2 Cor 10";i:2;s:7:"Ezek 24";i:3;s:6:"Ps 7-8";}\',"", 264),
(\'a:4:{i:0;s:8:"2 Sam 18";i:1;s:8:"2 Cor 11";i:2;s:7:"Ezek 25";i:3;s:4:"Ps 9";}\',"", 265),
(\'a:4:{i:0;s:8:"2 Sam 19";i:1;s:8:"2 Cor 12";i:2;s:7:"Ezek 26";i:3;s:5:"Ps 10";}\',"", 266),
(\'a:4:{i:0;s:8:"2 Sam 20";i:1;s:8:"2 Cor 13";i:2;s:7:"Ezek 27";i:3;s:8:"Ps 11-12";}\',"", 267),
(\'a:4:{i:0;s:8:"2 Sam 21";i:1;s:5:"Gal 1";i:2;s:7:"Ezek 28";i:3;s:8:"Ps 13-14";}\',"", 268),
(\'a:4:{i:0;s:8:"2 Sam 22";i:1;s:5:"Gal 2";i:2;s:7:"Ezek 29";i:3;s:8:"Ps 15-16";}\',"", 269),
(\'a:4:{i:0;s:8:"2 Sam 23";i:1;s:5:"Gal 3";i:2;s:7:"Ezek 30";i:3;s:5:"Ps 17";}\',"", 270),
(\'a:4:{i:0;s:8:"2 Sam 24";i:1;s:5:"Gal 4";i:2;s:7:"Ezek 31";i:3;s:5:"Ps 18";}\',"", 271),
(\'a:4:{i:0;s:7:"1 Kgs 1";i:1;s:5:"Gal 5";i:2;s:7:"Ezek 32";i:3;s:5:"Ps 19";}\',"", 272),
(\'a:4:{i:0;s:7:"1 Kgs 2";i:1;s:5:"Gal 6";i:2;s:7:"Ezek 33";i:3;s:8:"Ps 20-21";}\',"", 273),
(\'a:4:{i:0;s:7:"1 Kgs 3";i:1;s:5:"Eph 1";i:2;s:7:"Ezek 34";i:3;s:5:"Ps 22";}\',"", 274),
(\'a:4:{i:0;s:9:"1 Kgs 4-5";i:1;s:5:"Eph 2";i:2;s:7:"Ezek 35";i:3;s:8:"Ps 23-24";}\',"", 275),
(\'a:4:{i:0;s:7:"1 Kgs 6";i:1;s:5:"Eph 3";i:2;s:7:"Ezek 36";i:3;s:5:"Ps 25";}\',"", 276),
(\'a:4:{i:0;s:7:"1 Kgs 7";i:1;s:5:"Eph 4";i:2;s:7:"Ezek 37";i:3;s:8:"Ps 26-27";}\',"", 277),
(\'a:4:{i:0;s:7:"1 Kgs 8";i:1;s:5:"Eph 5";i:2;s:7:"Ezek 38";i:3;s:8:"Ps 28-29";}\',"", 278),
(\'a:4:{i:0;s:7:"1 Kgs 9";i:1;s:5:"Eph 6";i:2;s:7:"Ezek 39";i:3;s:5:"Ps 30";}\',"", 279),
(\'a:4:{i:0;s:8:"1 Kgs 10";i:1;s:6:"Phil 1";i:2;s:7:"Ezek 40";i:3;s:5:"Ps 31";}\',"", 280),
(\'a:4:{i:0;s:8:"1 Kgs 11";i:1;s:6:"Phil 2";i:2;s:7:"Ezek 41";i:3;s:5:"Ps 32";}\',"", 281),
(\'a:4:{i:0;s:8:"1 Kgs 12";i:1;s:6:"Phil 3";i:2;s:7:"Ezek 42";i:3;s:5:"Ps 33";}\',"", 282),
(\'a:4:{i:0;s:8:"1 Kgs 13";i:1;s:6:"Phil 4";i:2;s:7:"Ezek 43";i:3;s:5:"Ps 34";}\',"", 283),
(\'a:4:{i:0;s:8:"1 Kgs 14";i:1;s:5:"Col 1";i:2;s:7:"Ezek 44";i:3;s:5:"Ps 35";}\',"", 284),
(\'a:4:{i:0;s:8:"1 Kgs 15";i:1;s:5:"Col 2";i:2;s:7:"Ezek 45";i:3;s:5:"Ps 36";}\',"", 285),
(\'a:4:{i:0;s:8:"1 Kgs 16";i:1;s:5:"Col 3";i:2;s:7:"Ezek 46";i:3;s:5:"Ps 37";}\',"", 286),
(\'a:4:{i:0;s:8:"1 Kgs 17";i:1;s:5:"Col 4";i:2;s:7:"Ezek 47";i:3;s:5:"Ps 38";}\',"", 287),
(\'a:4:{i:0;s:8:"1 Kgs 18";i:1;s:8:"1 Thes 1";i:2;s:7:"Ezek 48";i:3;s:5:"Ps 39";}\',"", 288),
(\'a:4:{i:0;s:8:"1 Kgs 19";i:1;s:8:"1 Thes 2";i:2;s:5:"Dan 1";i:3;s:8:"Ps 40-41";}\',"", 289),
(\'a:4:{i:0;s:8:"1 Kgs 20";i:1;s:8:"1 Thes 3";i:2;s:5:"Dan 2";i:3;s:8:"Ps 42-43";}\',"", 290),
(\'a:4:{i:0;s:8:"1 Kgs 21";i:1;s:8:"1 Thes 4";i:2;s:5:"Dan 3";i:3;s:5:"Ps 44";}\',"", 291),
(\'a:4:{i:0;s:8:"1 Kgs 22";i:1;s:8:"1 Thes 5";i:2;s:5:"Dan 4";i:3;s:5:"Ps 45";}\',"", 292),
(\'a:4:{i:0;s:7:"2 Kgs 1";i:1;s:8:"2 Thes 1";i:2;s:5:"Dan 5";i:3;s:8:"Ps 46-47";}\',"", 293),
(\'a:4:{i:0;s:7:"2 Kgs 2";i:1;s:8:"2 Thes 2";i:2;s:5:"Dan 6";i:3;s:5:"Ps 48";}\',"", 294),
(\'a:4:{i:0;s:7:"2 Kgs 3";i:1;s:8:"2 Thes 3";i:2;s:5:"Dan 7";i:3;s:5:"Ps 49";}\',"", 295),
(\'a:4:{i:0;s:7:"2 Kgs 4";i:1;s:7:"1 Tim 1";i:2;s:5:"Dan 8";i:3;s:5:"Ps 50";}\',"", 296),
(\'a:4:{i:0;s:7:"2 Kgs 5";i:1;s:7:"1 Tim 2";i:2;s:5:"Dan 9";i:3;s:5:"Ps 51";}\',"", 297),
(\'a:4:{i:0;s:7:"2 Kgs 6";i:1;s:7:"1 Tim 3";i:2;s:6:"Dan 10";i:3;s:8:"Ps 52-54";}\',"", 298),
(\'a:4:{i:0;s:7:"2 Kgs 7";i:1;s:7:"1 Tim 4";i:2;s:6:"Dan 11";i:3;s:5:"Ps 55";}\',"", 299),
(\'a:4:{i:0;s:7:"2 Kgs 8";i:1;s:7:"1 Tim 5";i:2;s:6:"Dan 12";i:3;s:8:"Ps 56-57";}\',"", 300),
(\'a:4:{i:0;s:7:"2 Kgs 9";i:1;s:7:"1 Tim 6";i:2;s:5:"Hos 1";i:3;s:8:"Ps 58-59";}\',"", 301),
(\'a:4:{i:0;s:11:"2 Kgs 10-11";i:1;s:7:"2 Tim 1";i:2;s:5:"Hos 2";i:3;s:8:"Ps 60-61";}\',"", 302),
(\'a:4:{i:0;s:8:"2 Kgs 12";i:1;s:7:"2 Tim 2";i:2;s:7:"Hos 3-4";i:3;s:8:"Ps 62-63";}\',"", 303),
(\'a:4:{i:0;s:8:"2 Kgs 13";i:1;s:7:"2 Tim 3";i:2;s:7:"Hos 5-6";i:3;s:8:"Ps 64-65";}\',"", 304),
(\'a:4:{i:0;s:8:"2 Kgs 14";i:1;s:7:"2 Tim 4";i:2;s:5:"Hos 7";i:3;s:8:"Ps 66-67";}\',"", 305),
(\'a:4:{i:0;s:8:"2 Kgs 15";i:1;s:7:"Titus 1";i:2;s:5:"Hos 8";i:3;s:5:"Ps 68";}\',"", 306),
(\'a:4:{i:0;s:8:"2 Kgs 16";i:1;s:7:"Titus 2";i:2;s:5:"Hos 9";i:3;s:5:"Ps 69";}\',"", 307),
(\'a:4:{i:0;s:8:"2 Kgs 17";i:1;s:7:"Titus 3";i:2;s:6:"Hos 10";i:3;s:8:"Ps 70-71";}\',"", 308),
(\'a:4:{i:0;s:8:"2 Kgs 18";i:1;s:5:"Phm 1";i:2;s:6:"Hos 11";i:3;s:5:"Ps 72";}\',"", 309),
(\'a:4:{i:0;s:8:"2 Kgs 19";i:1;s:5:"Heb 1";i:2;s:6:"Hos 12";i:3;s:5:"Ps 73";}\',"", 310),
(\'a:4:{i:0;s:8:"2 Kgs 20";i:1;s:5:"Heb 2";i:2;s:6:"Hos 13";i:3;s:5:"Ps 74";}\',"", 311),
(\'a:4:{i:0;s:8:"2 Kgs 21";i:1;s:5:"Heb 3";i:2;s:6:"Hos 14";i:3;s:8:"Ps 75-76";}\',"", 312),
(\'a:4:{i:0;s:8:"2 Kgs 22";i:1;s:5:"Heb 4";i:2;s:6:"Joel 1";i:3;s:5:"Ps 77";}\',"", 313),
(\'a:4:{i:0;s:8:"2 Kgs 23";i:1;s:5:"Heb 5";i:2;s:6:"Joel 2";i:3;s:5:"Ps 78";}\',"", 314),
(\'a:4:{i:0;s:8:"2 Kgs 24";i:1;s:5:"Heb 6";i:2;s:6:"Joel 3";i:3;s:5:"Ps 79";}\',"", 315),
(\'a:4:{i:0;s:8:"2 Kgs 25";i:1;s:5:"Heb 7";i:2;s:6:"Amos 1";i:3;s:5:"Ps 80";}\',"", 316),
(\'a:4:{i:0;s:9:"1 Chr 1-2";i:1;s:5:"Heb 8";i:2;s:6:"Amos 2";i:3;s:8:"Ps 81-82";}\',"", 317),
(\'a:4:{i:0;s:9:"1 Chr 3-4";i:1;s:5:"Heb 9";i:2;s:6:"Amos 3";i:3;s:8:"Ps 83-84";}\',"", 318),
(\'a:4:{i:0;s:9:"1 Chr 5-6";i:1;s:6:"Heb 10";i:2;s:6:"Amos 4";i:3;s:5:"Ps 85";}\',"", 319),
(\'a:4:{i:0;s:9:"1 Chr 7-8";i:1;s:6:"Heb 11";i:2;s:6:"Amos 5";i:3;s:5:"Ps 86";}\',"", 320),
(\'a:4:{i:0;s:10:"1 Chr 9-10";i:1;s:6:"Heb 12";i:2;s:6:"Amos 6";i:3;s:8:"Ps 87-88";}\',"", 321),
(\'a:4:{i:0;s:11:"1 Chr 11-12";i:1;s:6:"Heb 13";i:2;s:6:"Amos 7";i:3;s:5:"Ps 89";}\',"", 322),
(\'a:4:{i:0;s:11:"1 Chr 13-14";i:1;s:5:"Jas 1";i:2;s:6:"Amos 8";i:3;s:5:"Ps 90";}\',"", 323),
(\'a:4:{i:0;s:8:"1 Chr 15";i:1;s:5:"Jas 2";i:2;s:6:"Amos 9";i:3;s:5:"Ps 91";}\',"", 324),
(\'a:4:{i:0;s:8:"1 Chr 16";i:1;s:5:"Jas 3";i:2;s:6:"Obad 1";i:3;s:8:"Ps 92-93";}\',"", 325),
(\'a:4:{i:0;s:8:"1 Chr 17";i:1;s:5:"Jas 4";i:2;s:7:"Jonah 1";i:3;s:5:"Ps 94";}\',"", 326),
(\'a:4:{i:0;s:8:"1 Chr 18";i:1;s:5:"Jas 5";i:2;s:7:"Jonah 2";i:3;s:8:"Ps 95-96";}\',"", 327),
(\'a:4:{i:0;s:11:"1 Chr 19-20";i:1;s:7:"1 Pet 1";i:2;s:7:"Jonah 3";i:3;s:8:"Ps 97-98";}\',"", 328),
(\'a:4:{i:0;s:8:"1 Chr 21";i:1;s:7:"1 Pet 2";i:2;s:7:"Jonah 4";i:3;s:9:"Ps 99-101";}\',"", 329),
(\'a:4:{i:0;s:8:"1 Chr 22";i:1;s:7:"1 Pet 3";i:2;s:5:"Mic 1";i:3;s:6:"Ps 102";}\',"", 330),
(\'a:4:{i:0;s:8:"1 Chr 23";i:1;s:7:"1 Pet 4";i:2;s:5:"Mic 2";i:3;s:6:"Ps 103";}\',"", 331),
(\'a:4:{i:0;s:11:"1 Chr 24-25";i:1;s:7:"1 Pet 5";i:2;s:5:"Mic 3";i:3;s:6:"Ps 104";}\',"", 332),
(\'a:4:{i:0;s:11:"1 Chr 26-27";i:1;s:7:"2 Pet 1";i:2;s:5:"Mic 4";i:3;s:6:"Ps 105";}\',"", 333),
(\'a:4:{i:0;s:8:"1 Chr 28";i:1;s:7:"2 Pet 2";i:2;s:5:"Mic 5";i:3;s:6:"Ps 106";}\',"", 334),
(\'a:4:{i:0;s:8:"1 Chr 29";i:1;s:7:"2 Pet 3";i:2;s:5:"Mic 6";i:3;s:6:"Ps 107";}\',"", 335),
(\'a:4:{i:0;s:7:"2 Chr 1";i:1;s:6:"1 Jn 1";i:2;s:5:"Mic 7";i:3;s:10:"Ps 108-109";}\',"", 336),
(\'a:4:{i:0;s:7:"2 Chr 2";i:1;s:6:"1 Jn 2";i:2;s:7:"Nahum 1";i:3;s:10:"Ps 110-111";}\',"", 337),
(\'a:4:{i:0;s:9:"2 Chr 3-4";i:1;s:6:"1 Jn 3";i:2;s:7:"Nahum 2";i:3;s:10:"Ps 112-113";}\',"", 338),
(\'a:4:{i:0;s:7:"2 Chr 5";i:1;s:6:"1 Jn 4";i:2;s:7:"Nahum 3";i:3;s:10:"Ps 114-115";}\',"", 339),
(\'a:4:{i:0;s:7:"2 Chr 6";i:1;s:6:"1 Jn 5";i:2;s:5:"Hab 1";i:3;s:6:"Ps 116";}\',"", 340),
(\'a:4:{i:0;s:7:"2 Chr 7";i:1;s:6:"2 Jn 1";i:2;s:5:"Hab 2";i:3;s:10:"Ps 117-118";}\',"", 341),
(\'a:4:{i:0;s:7:"2 Chr 8";i:1;s:6:"3 Jn 1";i:2;s:5:"Hab 3";i:3;s:11:"Ps 119:1-24";}\',"", 342),
(\'a:4:{i:0;s:7:"2 Chr 9";i:1;s:6:"Jude 1";i:2;s:6:"Zeph 1";i:3;s:12:"Ps 119:25-48";}\',"", 343),
(\'a:4:{i:0;s:8:"2 Chr 10";i:1;s:5:"Rev 1";i:2;s:6:"Zeph 2";i:3;s:12:"Ps 119:49-72";}\',"", 344),
(\'a:4:{i:0;s:11:"2 Chr 11-12";i:1;s:5:"Rev 2";i:2;s:6:"Zeph 3";i:3;s:12:"Ps 119:73-96";}\',"", 345),
(\'a:4:{i:0;s:8:"2 Chr 13";i:1;s:5:"Rev 3";i:2;s:5:"Hag 1";i:3;s:13:"Ps 119:97-120";}\',"", 346),
(\'a:4:{i:0;s:11:"2 Chr 14-15";i:1;s:5:"Rev 4";i:2;s:5:"Hag 2";i:3;s:14:"Ps 119:121-144";}\',"", 347),
(\'a:4:{i:0;s:8:"2 Chr 16";i:1;s:5:"Rev 5";i:2;s:6:"Zech 1";i:3;s:14:"Ps 119:145-176";}\',"", 348),
(\'a:4:{i:0;s:8:"2 Chr 17";i:1;s:5:"Rev 6";i:2;s:6:"Zech 2";i:3;s:10:"Ps 120-122";}\',"", 349),
(\'a:4:{i:0;s:8:"2 Chr 18";i:1;s:5:"Rev 7";i:2;s:6:"Zech 3";i:3;s:10:"Ps 123-125";}\',"", 350),
(\'a:4:{i:0;s:11:"2 Chr 19-20";i:1;s:5:"Rev 8";i:2;s:6:"Zech 4";i:3;s:10:"Ps 126-128";}\',"", 351),
(\'a:4:{i:0;s:8:"2 Chr 21";i:1;s:5:"Rev 9";i:2;s:6:"Zech 5";i:3;s:10:"Ps 129-131";}\',"", 352),
(\'a:4:{i:0;s:11:"2 Chr 22-23";i:1;s:6:"Rev 10";i:2;s:6:"Zech 6";i:3;s:10:"Ps 132-134";}\',"", 353),
(\'a:4:{i:0;s:8:"2 Chr 24";i:1;s:6:"Rev 11";i:2;s:6:"Zech 7";i:3;s:10:"Ps 135-136";}\',"", 354),
(\'a:4:{i:0;s:8:"2 Chr 25";i:1;s:6:"Rev 12";i:2;s:6:"Zech 8";i:3;s:10:"Ps 137-138";}\',"", 355),
(\'a:4:{i:0;s:8:"2 Chr 26";i:1;s:6:"Rev 13";i:2;s:6:"Zech 9";i:3;s:6:"Ps 139";}\',"", 356),
(\'a:4:{i:0;s:11:"2 Chr 27-28";i:1;s:6:"Rev 14";i:2;s:7:"Zech 10";i:3;s:10:"Ps 140-141";}\',"", 357),
(\'a:4:{i:0;s:8:"2 Chr 29";i:1;s:6:"Rev 15";i:2;s:7:"Zech 11";i:3;s:6:"Ps 142";}\',"", 358),
(\'a:4:{i:0;s:8:"2 Chr 30";i:1;s:6:"Rev 16";i:2;s:7:"Zech 12";i:3;s:6:"Ps 143";}\',"", 359),
(\'a:4:{i:0;s:8:"2 Chr 31";i:1;s:6:"Rev 17";i:2;s:7:"Zech 13";i:3;s:6:"Ps 144";}\',"", 360),
(\'a:4:{i:0;s:8:"2 Chr 32";i:1;s:6:"Rev 18";i:2;s:7:"Zech 14";i:3;s:6:"Ps 145";}\',"", 361),
(\'a:4:{i:0;s:8:"2 Chr 33";i:1;s:6:"Rev 19";i:2;s:5:"Mal 1";i:3;s:10:"Ps 146-147";}\',"", 362),
(\'a:4:{i:0;s:8:"2 Chr 34";i:1;s:6:"Rev 20";i:2;s:5:"Mal 2";i:3;s:6:"Ps 148";}\',"", 363),
(\'a:4:{i:0;s:8:"2 Chr 35";i:1;s:6:"Rev 21";i:2;s:5:"Mal 3";i:3;s:6:"Ps 149";}\',"", 364),
(\'a:4:{i:0;s:8:"2 Chr 36";i:1;s:6:"Rev 22";i:2;s:5:"Mal 4";i:3;s:6:"Ps 150";}\',"", 365);';
        $wpdb->query($sql);
     update_option('church_admin_brp',"Murray M'Cheyne Reading Plan");   
    } 
    
//recreate cronemail.php job file

	$path=plugin_dir_path(__FILE__).'/cronemail.php';
	$contents="<?php\r\n ";
	$loadpath = preg_replace('/wp-content.*$/','',__DIR__);
	$contents.='require_once("'.ABSPATH.'wp-load.php");'."\r\n";
	$contents.='require_once(  "'.ABSPATH.'wp-content/plugins/church-admin/index.php");'."\r\n";
	$contents.="church_admin_bulk_email();\r\n";
	$contents.="exit();\r\n?>";
	$fp = fopen($path, 'w');
	fwrite($fp, $contents."\r\n");
	fclose($fp);
//recreate cronbackup.php
	$path=plugin_dir_path(__FILE__).'/cronbackup.php';
	$contents="<?php\r\n ";
	$loadpath = preg_replace('/wp-content.*$/','',__DIR__);
	$contents.='require_once("'.ABSPATH.'wp-load.php");'."\r\n";
	$contents.='require_once(  "'.ABSPATH.'wp-content/plugins/church-admin/index.php");'."\r\n";
	$contents.="church_admin_backup();\r\n";
	$contents.="exit();\r\n?>";
	$fp = fopen($path, 'w');
	fwrite($fp, $contents."\r\n");
	fclose($fp);
	

	
$use_prefix=get_option('church_admin_use_prefix');
if(!isset($use_prefix))update_option('church_admin_use_prefix',TRUE);
$use_middle=get_option('church_admin_use_middle_name');
if(!isset($use_middle))update_option('church_admin_use_middle_name',TRUE);	
//fix for v0.943
if($church_admin_version>=0.943){
	//get current saved option for auto rota email
	$rota_day=get_option('church_admin_email_rota_day');
	if(empty($rota_day)||!ctype_digit($rota_day))
	{ 
		$check=wp_get_schedule( 'church_admin_cron_email_rota');
		if(!empty($check))
		{
			wp_clear_scheduled_hook('church_admin_cron_email_rota');
			//echo'<div class="notice notice-success inline"> Rota auto email bug cleared</div>';
		}
	}
}
	
	//update old smtp settings to new if necessary
	$smtp=get_option('church_admin_smtp');
	if(!empty($smtp))//old smtp settings
	{
		
		$check=get_option('church_admin_smtp_settings');
		if(empty($check))//not done already
		{
			update_option('church_admin_smtp_settings',$smtp);
			delete_option('church_admin_smtp');
		}
	}
	
	//check for modules
	$modules=get_option('church_admin_modules');
	if(empty($modules))
	{
		$modules=array('App'=>TRUE,'People'=>TRUE,'Sessions'=>TRUE,'Services'=>TRUE,'Podcast'=>TRUE,'Rota'=>TRUE,'Children'=>TRUE,'Classes'=>TRUE,'Calendar'=>TRUE,'Comms'=>TRUE,'Groups'=>TRUE,'Media'=>TRUE,'Facilities'=>TRUE,'Ministries'=>TRUE);
		update_option('church_admin_modules',$modules);
	}
	if(empty($modules['App'])){$modules['App']=TRUE;update_option('church_admin_modules',$modules);}
	if(empty($modules['Services'])){$modules['Services']=TRUE;update_option('church_admin_modules',$modules);}
	if(empty($modules['Sessions'])){$modules['Sessions']=TRUE;update_option('church_admin_modules',$modules);}
	//check for pagination limit
	$page=get_option('church_admin_page_limit');
	if(empty($page))update_option('church_admin_page_limit',50);
	
	
	//bulksms update 
	$eapi=get_option('church_admin_bulksms');
	if($eapi=='http://community.bulksms.co.uk')update_option('church_admin_bulksms','http://community.bulksms.co.uk/eapi');
	if($eapi=='http://bulksms.co.uk')update_option('church_admin_bulksms','http://bulksms.co.uk/eapi');
    //sermon podcast table install

    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SERM_TBL.'"') != CA_SERM_TBL)
    {
        $sql='CREATE TABLE  '.CA_SERM_TBL.' (`series_name` TEXT NOT NULL ,`series_image` TEXT NOT NULL,`series_description` TEXT NOT NULL ,`series_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);
    }
/*********************************************************
* 
* Check to see if a default series has been created 
* causes display problems if user forgets
*
* added 2017-01-10
*
**********************************************************/

    $check=$wpdb->get_row('SELECT * FROM '.CA_SERM_TBL);
    if(empty($check))
    {
    	$name=get_option('blogname');
    	if(empty($name))$name=__('Default Sermon Series','church-admin');
    	$wpdb->query('INSERT INTO '.CA_SERM_TBL.' (series_name)VALUES("'.esc_sql($name).'")');    
    }


/*********************************************************
*
* Sermon Files table
*
*********************************************************/
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_FIL_TBL.'"') != CA_FIL_TBL)
    {
        $sql='CREATE TABLE  '.CA_FIL_TBL.' (`file_name` TEXT NOT NULL ,`file_title` TEXT NOT NULL ,`file_description` TEXT NOT NULL ,`service_id` INT(11),`bible_passages` TEXT NOT NULL,`private` INT(1) NOT NULL DEFAULT "0",`length` TEXT NOT NULL, `pub_date` DATETIME, last_modified DATETIME, `series_id` INT( 11 ) NOT NULL ,`transcript` TEXT,`video_url` TEXT, `speaker` TEXT NOT NULL,`file_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;';
        $wpdb->query($sql);
    }
    
// Updates to Files table
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "file_subtitle"')!='file_subtitle')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD file_subtitle TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "external_file"')!='external_file')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD external_file TEXT';
    $wpdb->query($sql);
}	
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "transcript"')!='transcript')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD transcript TEXT';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "postID"')!='postID')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD postID INT(11)';
    $wpdb->query($sql);
}
 if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "plays"')!='plays')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD plays INT(11)';
    $wpdb->query($sql);
}   
/*********************************************************
*
* Biblw Books table
*
*********************************************************/
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_BIB_TBL.'"') != CA_BIB_TBL)
    {
	$sql='CREATE TABLE IF NOT EXISTS '.CA_BIB_TBL.' (`bible_id` int(10) NOT NULL AUTO_INCREMENT,`name` varchar(30) NOT NULL, PRIMARY KEY (`bible_id`)) ENGINE=MyISAM ;';
	$wpdb->query($sql);
	$sql="INSERT INTO ".CA_BIB_TBL." (`bible_id`, `name`) VALUES(1, 'Genesis'),(2, 'Exodus'),(3, 'Leviticus'),(4, 'Numbers'),(5, 'Deuteronomy'),(6, 'Joshua'),(7, 'Judges'),(8, 'Ruth'),(9, '1 Samuel'),(10, '2 Samuel'),(11, '1 Kings'),(12, '2 Kings'),(13, '1 Chronicles'),(14, '2 Chronicles'),(15, 'Ezra'),(16, 'Nehemiah'),(17, 'Esther'),(18, 'Job'),(19, 'Psalm'),(20, 'Proverbs'),(21, 'Ecclesiastes'),(22, 'Song of Solomon'),(23, 'Isaiah'),(24, 'Jeremiah'),(25, 'Lamentations'),(26, 'Ezekiel'),(27, 'Daniel'),(28, 'Hosea'),(29, 'Joel'),(30, 'Amos'),(31, 'Obadiah'),(32, 'Jonah'),(33, 'Micah'),(34, 'Nahum'),(35, 'Habakkuk'),(36, 'Zephaniah'),(37, 'Haggai'),(38, 'Zechariah'),(39, 'Malachi'),(40, 'Matthew'),(41, 'Mark'),(42, 'Luke'),(43, 'John'),(44, 'Acts'),(45, 'Romans'),(46, '1 Corinthians'),(47, '2 Corinthians'),(48, 'Galatians'),(49, 'Ephesians'),(50, 'Philippians'),(51, 'Colossians'),(52, '1 Thessalonians'),(53, '2 Thessalonians'),(54, '1 Timothy'),(55, '2 Timothy'),(56, 'Titus'),(57, 'Philemon'),(58, 'Hebrews'),(59, 'James'),(60, '1 Peter'),(61, '2 Peter'),(62, '1 John'),(63, '2 John'),(64, '3 John'),(65, 'Jude'),(66, 'Revelation')";
	$wpdb->query($sql);
    }
  
    
	
if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FAC_TBL.'"')!=CA_FAC_TBL)
{
	$sql="CREATE TABLE IF NOT EXISTS ". CA_FAC_TBL ."  (facility_name TEXT,facilities_order INT(11),  facilities_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`facilities_id`))" ;
        $wpdb->query($sql);
}
if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOP_TBL.'"') != CA_HOP_TBL)
{

	 $sql = 'CREATE TABLE IF NOT EXISTS '.CA_HOP_TBL.' (  `job` text NOT NULL,  `ts` datetime NOT NULL,  `hope_team_id` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`hope_team_id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
	 $wpdb->query($sql);
}
	//household table    
    
	
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_HOU_TBL.'"') != CA_HOU_TBL)
    {
        $sql = 'CREATE TABLE '.CA_HOU_TBL.' ( private INT(1) DEFAULT 0,address TEXT, lat VARCHAR(50),lng VARCHAR (50), phone VARCHAR(15),member_type_id INT(11),ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,household_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (household_id));';
        $wpdb->query($sql);
    }
    //people table    
    ;
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_PEO_TBL.'"') != CA_PEO_TBL)
    {
        $sql = 'CREATE TABLE '.CA_PEO_TBL.' (first_name VARCHAR(100),last_name VARCHAR(100), date_of_birth DATE, member_type_id INT(11),attachment_id INT(11), roles TEXT, sex INT(1),mobile VARCHAR(15), email TEXT,people_type_id INT(11),smallgroup_id INT(11),household_id INT(11),member_data TEXT, user_id INT(11),people_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (people_id));';
        $wpdb->query($sql);
    }
	
    //add attachement_id to people table for photo storage
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "attachment_id"')!='attachment_id')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD attachment_id INT(11)';
    $wpdb->query($sql);
    
     }
      //communication preferences
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "email_send"')!='email_send')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD email_send INT(1) DEFAULT 1';
    $wpdb->query($sql);
    
     }
      if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "rota_email"')!='rota_email')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD rota_email INT(1) DEFAULT 1';
    $wpdb->query($sql);
    
     }
     //add prayer_chain to people table for prayer chain
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "prayer_chain"')!='prayer_chain')
    {
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD prayer_chain INT(1) NOT NULL DEFAULT "0" AFTER `attachment_id`';
    $wpdb->query($sql);
    
     }
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "marital_status"')!='marital_status')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD marital_status TEXT AFTER `attachment_id`';
    	$wpdb->query($sql);
    
     }
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "twitter"')!='twitter')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD twitter TEXT AFTER `attachment_id`';
    	$wpdb->query($sql);
    
     }
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "facebook"')!='facebook')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD facebook TEXT AFTER `attachment_id`';
    	$wpdb->query($sql);
    
     }
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "instagram"')!='instagram')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD instagram TEXT AFTER `attachment_id`';
    	$wpdb->query($sql);
    
     }
          if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "middle_name"')!='middle_name')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD middle_name TEXT AFTER `first_name`';
    	$wpdb->query($sql);
    
     }
     //add nickname to people table for photo storage
     if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "nickname"')!='nickname')
    {
    	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD nickname TEXT AFTER middle_name';
    	$wpdb->query($sql);
    
     }
    //people_meta table    
   
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MET_TBL.'"') != CA_MET_TBL)
    {
        $sql = 'CREATE TABLE '.CA_MET_TBL.' ( meta_type VARCHAR(255) DEFAULT "ministry", people_id INT(11),ID INT(11), meta_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (meta_id));';
        $wpdb->query($sql);
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "role_id"')=='role_id')
	{
		$sql='ALTER TABLE  '.CA_MET_TBL.' CHANGE role_id ID INT(11)';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "department_id"')=='department_id')
    {//update people meta table to make more flexible looking!
		$sql='ALTER TABLE  '.CA_MET_TBL.' CHANGE department_id ID INT(11)';
		$wpdb->query($sql);
    
    }
        
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "meta_type"')!='meta_type')
{
    $sql='ALTER TABLE '.CA_MET_TBL.' ADD `meta_type` VARCHAR(255) NOT NULL DEFAULT "ministry" FIRST;';
    $wpdb->query($sql);
	
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MET_TBL.' LIKE "meta_date"')!='meta_date')
{
    $sql='ALTER TABLE '.CA_MET_TBL.' ADD `meta_date` DATE NOT NULL;';
    $wpdb->query($sql);
	
}


   //ministries table
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MIN_TBL.'"') != CA_MIN_TBL)
    {
        $sql = 'CREATE TABLE '.CA_MIN_TBL.' ( ministry TEXT,safeguarding INT(1)  NULL DEFAULT "0", parentID INT(11) NULL DEFAULT NULL,ID INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (ID));';
        $wpdb->query($sql);
        $ministries=get_option('church_admin_ministries');
        if(!empty($ministries))
        {
        	$values=array();
        	foreach($ministries AS $ID=>$ministry){$values[]='("'.esc_sql($ministry).'","'.esc_sql($ID).'")';}
        	$sql='INSERT INTO '.CA_MIN_TBL.' (ministry,ID) VALUES '.implode(",",$values);
			$wpdb->query($sql);
			delete_option('church_admin_ministries');
        }
        else
        {
        	$sql='INSERT INTO '.CA_MIN_TBL.' (ministry,ID) VALUES ("'.__('Small Group Leader','church-admin').'",1),("'.__('Elder','church-admin').'",2)';
			$wpdb->query($sql);
        }
        
    }
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MIN_TBL.' LIKE "parentID"')!='parentID')
    {//update people meta table to make more flexible looking!
		$sql='ALTER TABLE  '.CA_MIN_TBL.' ADD parentID INT(11) NULL DEFAULT "0"';
		$wpdb->query($sql);
    
    }
        if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MIN_TBL.' LIKE "safeguarding"')!='safeguarding')
    {//update people meta table to make more flexible looking!
		$sql='ALTER TABLE  '.CA_MIN_TBL.' ADD safeguarding INT(1) NULL DEFAULT "0"';
		$wpdb->query($sql);
    
    }
    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_MIN_TBL.' LIKE "childID"')=='childID')
    {
    	//update min table to use parentID instead of childID
    	$results=$wpdb->get_results('SELECT * FROM '.CA_MIN_TBL.' WHERE childID!=0');
    	if(!empty($results))
    	{
    		foreach($results AS $row)
    		{
    			$wpdb->query('UPDATE '.CA_MIN_TBL.' SET parentID="'.intval($row->ID).'" WHERE ID="'.intval($row->childID).'"');
    			$wpdb->query('UPDATE '.CA_MIN_TBL.' SET childID="0" WHERE ID="'.intval($row->ID).'"');
    		}
    	}
    	$wpdb->query('ALTER TABLE '.CA_MIN_TBL.' DROP `childID`');
	}
	//sessions table
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SES_TBL.'"') != CA_SES_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SES_TBL.' ( `what` TEXT NOT NULL ,what_id INT(11) NOT NULL, `event_type` TEXT NOT NULL,`start_time` DATETIME NOT NULL , `end_time` DATETIME NOT NULL , `notes` TEXT NOT NULL , `user_id` TEXT NOT NULL , `session_id` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`session_id`))';
        $wpdb->query($sql);
	
	}
	//sessions meta table
	if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SMET_TBL.'"') != CA_SMET_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SMET_TBL.' ( `people_id` INT(11) NOT NULL, `meta_value` TEXT NULL, `session_id` INT(11) NOT NULL , `ID` INT(11) NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`))';
        $wpdb->query($sql);
	
	}
    // sort small group ids to people_meta table
	$results=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE smallgroup_id!=""');
	if(!empty($results))
	{
		foreach($results AS $row)
		{
			$sgids=maybe_unserialize($row->smallgroup_id);
			if(is_array($sgids))
			{//handle if array form
				foreach($sgids as $key=>$value)church_admin_update_people_meta($value,$row->people_id,$meta_type='smallgroup');
				
			}
			else{church_admin_update_people_meta($row->smallgroup_id,$row->people_id,$meta_type='smallgroup');}
		}
	}		
	
  //sort out people types  
    
   
    $church_admin_people_settings=get_option('church_admin_people_settings');
    if(empty($church_admin_people_settings['member_type']))$church_admin_people_settings['member_type']=array('0'=>__('Mailing List','church-admin'),'1'=>__('Visitor','church-admin'),'2'=>__('Member','church-admin'));
    if(!empty($church_admin_people_settings['member_type']))
    {
	//install member type table
	    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_MTY_TBL.'"') != CA_MTY_TBL)
	    {
		$sql='CREATE TABLE '.CA_MTY_TBL.' (`member_type_order` INT( 11 ) NOT NULL ,`member_type` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,`member_type_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY)  CHARACTER SET utf8 COLLATE utf8_general_ci;';
		$wpdb->query($sql);
		$order=1;
		foreach($church_admin_people_settings['member_type'] AS $id=>$type)
		{
		    $check=$wpdb->get_var('SELECT member_type_id FROM '. CA_MTY_TBL. ' WHERE member_type_id="'.esc_sql($id).'"');
		    if(!$check)$wpdb->query('INSERT INTO '.CA_MTY_TBL .' (member_type_order,member_type,member_type_id) VALUES("'.$order.'","'.esc_sql($type).'","'.esc_sql($id).'")');
		    $order++;
		}
	    }
    }//end member type already in people_settings option
    $people_type=get_option('church_admin_people_type');
    if ($people_type==array(1=>'Adult',2=>'Child',3=>'Teenager'))
    {
    	//make sure translation is set up by re-writing it!
    	$people_type=array('1'=>__('Adult','church-admin'),'2'=>__('Child','church-admin'),3=>__('Teenager','church-admin'));
    }
    if(empty($people_type))$people_type=array('1'=>__('Adult','church-admin'),'2'=>__('Child','church-admin'));
	if(empty($people_type[3]))$people_type[3]=__('Teenager','church-admin');
    update_option('church_admin_people_type',$people_type);
   
    
    
    delete_option('church_admin_people_settings');

//migrate old tables
    $table_name = $wpdb->prefix."church_admin_directory";
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name && $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."church_admin_directory_old'") != $wpdb->prefix.'church_admin_directory_old' )
    {
	
	$results=$wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY last_name');
	foreach($results AS $row)
	{
	    
	    //split off household
	    $address=esc_sql(implode(", ",array('address_line1'=>stripslashes($row->address_line1),'address_line2'=>stripslashes($row->address_line2),'town'=>stripslashes($row->city),'county'=>stripslashes($row->state),'postcode'=>stripslashes($row->zipcode))));
	    $wpdb->query('INSERT INTO '.$wpdb->prefix.'church_admin_household (address,lat,lng,phone,member_type_id)VALUES("'.$address.'","52.0","0","'.esc_sql($row->homephone).'","1")');
	    $household_id=$wpdb->insert_id;
	    $member_data=esc_sql(serialize(array('member'=>mysql2date('Y-m-d',$row->ts))));
	    //deal with adults assume & is the separator
	    $adults=explode(" & ",$row->first_name);
	    //update smallgroup bits
	    $sg_leader=array();
	    $sg_id=$wpdb->get_var('SELECT id FROM '.CA_SMG_TBL.' WHERE leader="'.$row->id.'"');
		foreach($adults AS $key=>$adult)
		{
		    if(!empty($adult))
		    {
		        $sql='INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($adult)).'","'.esc_sql($row->last_name).'","1","1","1","'.esc_sql($row->email).'","'.$row->cellphone.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$member_data.'")';
		   
		        $wpdb->query($sql);
			//small group leader array  while at it!
			$people_id=$wpdb->insert_id;
			if($sg_id)
			{
			    $sg_leader[]=$people_id;
			    //give person small group leader role!
			    //church_admin_update_role('1',$people_id);
			}
		    }
		}
	    if(!empty($sg_leader)&& !empty($sg_id))$wpdb->query('UPDATE '.CA_SMG_TBL.' SET leader="'.esc_sql(serialize($sg_leader)).'" WHERE id="'.esc_sql($sg_id).'"');
	    $children=explode(", ",$row->children);
	    
	    foreach($children AS $key=>$child)
	    {
		if(!empty($child))
		{
		    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($child)).'","'.esc_sql($row->last_name).'","1","2","1","'.esc_sql($row->email).'","'.$row->mobile.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$member_data.'")';
		    
		    $wpdb->query($sql);
		}
	    }
	
	}
	
	$wpdb->query('RENAME TABLE '.$wpdb->prefix.'church_admin_directory TO '.$wpdb->prefix.'church_admin_directory_old');
    }
    //handle visitors
    
    $table_name = $wpdb->prefix."church_admin_visitors";
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name && $wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."church_admin_visitors_old'") != $wpdb->prefix.'church_admin_visitors_old')
    {
	
	$results=$wpdb->get_results('SELECT * FROM '.$table_name.' ORDER BY last_name');
	foreach($results AS $row)
	{
	    $visitor_data=esc_sql(serialize(array('visitor'=>$row->first_sunday)));
	    //split off household
	    $address=serialize(array('address_line1'=>stripslashes($row->address_line1),'address_line2'=>stripslashes($row->address_line2),'town'=>stripslashes($row->city),'county'=>stripslashes($row->state),'postcode'=>stripslashes($row->zipcode)));
	    //check if entered
	    $household_id=NULL;
	    $household_id=$wpdb->get_var('SELECT household_id FROM '.CA_HOU_TBL.' WHERE address="'.esc_sql($address).'" ');
	    if($address=='a:5:{s:13:"address_line1";s:0:"";s:13:"address_line2";s:0:"";s:4:"town";s:0:"";s:6:"county";s:0:"";s:8:"postcode";s:0:"";}'||!$household_id)
	    {
		$wpdb->query('INSERT INTO '.CA_HOU_TBL.' (address,lat,lng,phone,member_type_id)VALUES("'.esc_sql($address).'","52.0","0","'.esc_sql($row->homephone).'","0")');
		$household_id=$wpdb->insert_id;
	    }
	    //deal with adults assume & is the separator
	    $adults=explode(" & ",$row->first_name);
	    
		foreach($adults AS $key=>$adult)
		{
		    if(!empty($adult))
		    {
			$people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.esc_sql(trim($adult)).'" AND last_name="'.esc_sql($row->last_name).'" AND household_id="'.esc_sql($household_id).'"');
		        if(!$people_id)
			{
			    $sql='INSERT INTO '.CA_PEO_TBL.' (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($adult)).'","'.esc_sql($row->last_name).'","0","1","1","'.esc_sql($row->email).'","'.$row->cellphone.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$visitor_data.'")';
			    $wpdb->query($sql);
			}
			else
			{//update member data
			    $member_data=maybe_unserialize($wpdb->get_var('SELECT member_data FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"'));
			    if(!$member_data)$member_data=array();
			    $member_data['visitor']=$row->first_sunday;
			    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_data="'.esc_sql(serialize($memeber_data)).'" WHERE people_id="'.$people_id.'"');
			}//update memberdata
		    }
		}
	      $children=explode(", ",$row->children);
	    
	    foreach($children AS $key=>$child)
	    {
		if(!empty($child))
		{
		    $people_id=$wpdb->get_var('SELECT people_id FROM '.CA_PEO_TBL.' WHERE first_name="'.esc_sql(trim($adult)).'" AND last_name="'.esc_sql($row->last_name).'" AND household_id="'.esc_sql($household_id).'"');
		        if(!$people_id)
			{
			    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_people (first_name,last_name,member_type_id,people_type_id,sex,email,mobile,smallgroup_id,household_id,member_data) VALUES("'.esc_sql(trim($child)).'","'.esc_sql($row->last_name).'","1","2","1","'.esc_sql($row->email).'","'.$row->mobile.'","'.esc_sql($row->small_group).'","'.$household_id.'","'.$visitor_data.'")';
			    $wpdb->query($sql);
			}
			else
			{//update member data
			    $member_data=maybe_unserialize($wpdb->get_var('SELECT member_data FROM '.CA_PEO_TBL.' WHERE people_id="'.esc_sql($people_id).'"'));
			    if(!$member_data)$member_data=array();
			    $member_data['visitor']=$row->first_sunday;
			    $wpdb->query('UPDATE '.CA_PEO_TBL.' SET member_data="'.esc_sql(serialize($memeber_data)).'" WHERE people_id="'.$people_id.'"');
			}//update memberdata
		}
	    }
	
	}
	
	$wpdb->query('RENAME TABLE '.$wpdb->prefix.'church_admin_visitors TO '.$wpdb->prefix.'church_admin_visitors_old');
    }
    
    //make sure addresses are stored not as an array from v0.554
    $result=$wpdb->get_results('SELECT * FROM '. CA_HOU_TBL);
    if(!empty($result))
    {
		foreach($result AS $row)
		{
			$address=maybe_unserialize($row->address);
			if(!empty($address) && is_array($address))$wpdb->query('UPDATE '.CA_HOU_TBL.' SET address="'.esc_sql(implode(", ",$address)).'" WHERE household_id="'.esc_sql($row->household_id).'"');
		}
    }
//end migrate old tables
//make smallgrpup_id a serialized area in people table
if(OLD_CHURCH_ADMIN_VERSION<0.5973)
{
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' CHANGE `smallgroup_id` `smallgroup_id` TEXT NOT NULL');
	$people=$wpdb->get_results('SELECT smallgroup_id, people_id FROM '.CA_PEO_TBL);
	if(!empty($people))
	{
		foreach($people AS $person)
		{
			$sg=maybe_unserialize($person->smallgroup_id);
			if(!is_array($sg))
			{
				$s=array($sg);
				$sql='UPDATE '.CA_PEO_TBL.' SET smallgroup_id="'.esc_sql(serialize($s)).'" WHERE people_id="'.esc_sql($person->people_id).'"';
				
				$wpdb->query($sql);
			}
		}
	}
}

//v1.05
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "site_id"')!='site_id')
{
	$wpdb->query('ALTER TABLE  '.CA_PEO_TBL.' ADD site_id INT(11)');
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "active"')!='active')
{
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' ADD `active` INT(1) NOT NULL DEFAULT "1" ;');
}
//v0.955 add head of household to people table for better sorting of blended families
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "head_of_household"')!='head_of_household')
{
	$sql='ALTER TABLE  '.CA_PEO_TBL.' ADD head_of_household INT(1) NULL DEFAULT "0" AFTER `last_name`';
	$wpdb->query($sql);
	$households=$wpdb->get_results('SELECT * FROM '.CA_HOU_TBL);
	if(!empty($households))
	{
		foreach($households AS $household)
		{
			$people=$wpdb->get_results('SELECT * FROM '.CA_PEO_TBL.' WHERE household_id="'.esc_sql($household->household_id).'" ORDER BY people_order');

			if(!empty($people))
			{
				if($wpdb->num_rows==1){$wpdb->query('UPDATE '.CA_PEO_TBL.' SET head_of_household=1 WHERE household_id="'.esc_sql($household->household_id).'"');}
				else
				{
					$sql='UPDATE '.CA_PEO_TBL.' SET head_of_household=1 WHERE household_id="'.esc_sql($household->household_id).'" AND people_id="'.esc_sql($people[0]->people_id).'"';
					
					$wpdb->query($sql);
				}
			}
		}
	}
	
}else{$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' CHANGE `head_of_household` `head_of_household` INT(1) NULL DEFAULT "0";');}
    
    //install small group table
    $table_name = $wpdb->prefix."church_admin_smallgroup";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ." (leadership TEXT NOT NULL,group_name varchar(255) NOT NULL,whenwhere TEXT NOT NULL,address TEXT, lat VARCHAR(30),lng VARCHAR(30), id int(11) NOT NULL AUTO_INCREMENT,PRIMARY KEY (id));";
        $wpdb->query($sql);
	$wpdb->query("INSERT INTO ".$wpdb->prefix."church_admin_smallgroup (group_name,id)VALUES ( 'Unattached', '1');");
    }
  
   


//comments

    
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_COM_TBL.'"') != CA_COM_TBL)
    {
        $sql = 'CREATE TABLE '.CA_COM_TBL.' ( comment TEXT, comment_type TEXT,  timestamp DATETIME, ID int(11), author_id INT(11), parent_id INT (11)  NOT NULL DEFAULT "0",comment_id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (comment_id));';
        $wpdb->query($sql);
	}
//services
    
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SER_TBL.'"') != CA_SER_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SER_TBL.' ( service_name TEXT, service_day INT(1),service_time TIME, venue VARCHAR(100),address TEXT,lat VARCHAR(50),lng VARCHAR(50),first_meeting DATE,service_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (service_id));';
        $wpdb->query($sql);
	$wpdb->query('INSERT INTO '.CA_SER_TBL.' (service_name,service_day,service_time,venue,address,lat,lng,first_meeting) VALUES ("'.__('Sunday Service','church-admin').'","1","10:00","'.__('Main Venue','church-admin').'","","52.0","0.0","'.date('Y-m-d').'")');
    }
    
	//sort service addresses for ver 0.5911 onwards
	$services=$wpdb->get_results('SELECT * FROM '.CA_SER_TBL);
	if(!empty($services))
	foreach($services AS $service)
	{
		if(!empty($service->address))
		{
			$address=maybe_unserialize($service->address);
			if(is_array($address))
			{
				$address=implode(', ',array_filter($address));
				$wpdb->query('UPDATE '.CA_SER_TBL.' SET address="'.esc_sql($address).'" WHERE service_id="'.esc_sql($service->service_id).'"');
			}
		}
	}
    if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_SIT_TBL.'"') != CA_SIT_TBL)
    {
        $sql = 'CREATE TABLE '.CA_SIT_TBL.' ( venue VARCHAR(100),address TEXT,lat VARCHAR(50),lng VARCHAR(50),site_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (site_id));';
        $wpdb->query($sql);
		//upgrade service table if lready established version
		//add site id to service table
		$wpdb->query('ALTER TABLE  '.CA_SER_TBL.' ADD site_id INT(11)');
		
		if(!empty($services))
		{
			foreach($services AS $service)
			{
				$siteID=$wpdb->get_var('SELECT site_id FROM '.CA_SIT_TBL.' WHERE venue="'.esc_sql($service->venue).'" AND address= "'.esc_sql($service->address).'" ');
				if(!$siteID)
				{//only make unique new sites
						$add=maybe_unserialize($service_address);
						if(is_array($add))$add=implode(', ',$add);
						$wpdb->query('INSERT INTO '.CA_SIT_TBL.' (venue,address,lat,lng,site_id)VALUES("'.esc_sql($service->venue).'","'.esc_sql($add).'","'.esc_sql($service->lat).'","'.esc_sql($service->lng).'","'.esc_sql($service->service_id).'")');
						$siteID=$wpdb->insert_id;
				}
				//update service table row with site id
				$wpdb->query('UPDATE '.CA_SER_TBL.' SET site_id="'.esc_sql($siteID).'" WHERE service_id="'.esc_sql($service->service_id).'"');
			}
		}
		$wpdb->query('ALTER TABLE '.CA_SER_TBL.' DROP venue;');
		$wpdb->query('ALTER TABLE '.CA_SER_TBL.' DROP address;');
		$wpdb->query('ALTER TABLE '.CA_SER_TBL.' DROP lat;');
		$wpdb->query('ALTER TABLE '.CA_SER_TBL.' DROP lng');
	
	}	
	
/*********************************************************
*
* Rota Settings Table
*
*********************************************************/	
	
    //install rota settings table
     if ($wpdb->get_var('SHOW TABLES LIKE "'.CA_RST_TBL.'"') != CA_RST_TBL)
    {
		$sql='CREATE TABLE  '.CA_RST_TBL.'  (rota_task TEXT NOT NULL ,rota_order INT(11),autocomplete INT(1) NULL DEFAULT "1",ministries TEXT NOT NULL, rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ));';
		
		$wpdb->query($sql);
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "ministries"')!='ministries')
	{
	  	$sql='ALTER TABLE  '.CA_RST_TBL.' ADD ministries TEXT';
    	$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "initials"')!='initials')
	{
	  	$sql='ALTER TABLE  '.CA_RST_TBL.' ADD initials INT(1)';
    	$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "autocomplete"')!='autocomplete')
	{
		$sql='ALTER TABLE '.CA_RST_TBL.' CHANGE `autocomplete` INT(1) NULL DEFAULT "0";';
		$wpdb->query($sql);
	}

    if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "service_id"')!='service_id')
	{
		$sql='ALTER TABLE  '.CA_RST_TBL.' ADD service_id TEXT AFTER `rota_order`';
		$wpdb->query($sql);
		//add default services
		$services=$wpdb->get_results('SELECT service_id FROM '.CA_SER_TBL);
		if(!empty($services))
		{
			$ser=array();
			foreach($services AS $service)$ser[]=$service->service_id;
		}
		$wpdb->query('UPDATE '.CA_RST_TBL.' SET service_id="'.esc_sql(serialize($ser)).'"');
	}
	
/*********************************************************
*
* Old Rota table
*
*********************************************************/
/*
    //install rotas table
    $table_name = CA_ROT_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) 
    {
	$sql="CREATE TABLE  ". $table_name ."  (  rota_date DATE NOT NULL,  rota_jobs TEXT NOT NULL, service_id INT(11) NOT NULL, rota_id INT(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (rota_id));";
	//echo $sql;
	$wpdb->query($sql);
    }
	if($wpdb->get_var('show tables like "'.$wpdb->prefix.'church_admin_rota"') == $wpdb->prefix.'church_admin_rota')
	{
	    //grab current jobs
	    $jobs=array();
	    $results=$wpdb->get_results('SELECT a.*,b.rota_task FROM '.$wpdb->prefix.'church_admin_rota a,'.$wpdb->prefix.'church_admin_rota_settings b WHERE a.rota_option_id=b.rota_id');
	    if($results)
	    {
		$peeps=array();
		foreach($results AS $row)
		{
		    if(!empty($row->who)){$peeps=explode(", ",$row->who);}
		    $jobs[$row->rota_date][$row->rota_task]=$peeps;
		}
		foreach($jobs AS $date=>$people)
		{
		    $day_jobs=esc_sql(serialize($people));
		    $sql='INSERT INTO '.$wpdb->prefix.'church_admin_rotas (rota_date,rota_jobs,service_id)VALUES("'.esc_sql($date).'","'.$day_jobs.'","1")';
		    $wpdb->query($sql);
		}
	    $wpdb->query('DROP TABLE '.$wpdb->prefix.'church_admin_rota');
	    }
	}
    
  if($wpdb->get_var('SHOW COLUMNS FROM '.CA_RST_TBL.' LIKE "rota_order"')!='rota_order')
{
    $sql='ALTER TABLE  '.CA_RST_TBL.' ADD rota_order INT(11)';
    $wpdb->query($sql);
    //order current rota jobs as
    $result=$wpdb->get_results('SELECT * FROM '.CA_RST_TBL.' ORDER BY rota_id');
    $x=1;
    $order=array();
    if($result)
    {
	foreach($result AS $row)
	{
	    $order[$x]=$row->rota_task;
	    $wpdb->query('UPDATE '.CA_RST_TBL.' SET rota_order ="'.$x.'" WHERE rota_id="'.$row->rota_id.'"');
	    $x++;
	}
    }
    //adjust rota table so it is normalised
   
    $results=$wpdb->get_results('SELECT * FROM '.CA_ROT_TBL);
    if($results)
    {
	 
	foreach($results AS $row)
	{
	    $tasks=maybe_unserialize($row->rota_jobs);
	    if($tasks)
	    {
		$new_rota=array();
		foreach($tasks AS $task_name=>$person)
		{
		    $id=array_search($task_name,$order);
		    if($id) $new_rota[$id]=$person;
		}
		$sql='UPDATE '.CA_ROT_TBL.' SET rota_jobs="'.esc_sql(maybe_serialize($new_rota)).'" WHERE rota_id="'.esc_sql($row->rota_id).'"';
		
		$wpdb->query($sql);
	    }
	}
    }
    
}    
*/
/**********************************************************************
*
*
*   New rota table
*
*
***********************************************************************/
	if($wpdb->get_var('SHOW TABLES LIKE "'.CA_ROTA_TBL.'"') != CA_ROTA_TBL) 
    {
		$sql='CREATE TABLE  '.CA_ROTA_TBL.'  (rota_date DATE,rota_task_id TEXT NOT NULL ,people_id TEXT,service_id INT(11),mtg_type TEXT, rota_id INT( 11 ) NOT NULL AUTO_INCREMENT ,PRIMARY KEY (  rota_id ));';
		
		$wpdb->query($sql);
	}	
	//populate with current data
	$oldRotaResults=$wpdb->get_results('SELECT * FROM '.CA_ROTA_TBL);
	if($wpdb->get_var('show tables like "'.CA_ROT_TBL.'"') == CA_ROT_TBL && empty($oldRotaResults)) 
    {
    		$results=$wpdb->get_results('SELECT * FROM '.CA_ROT_TBL.' WHERE rota_date>=CURDATE()');
    		
			if(!empty($results))
			{
			
				foreach($results AS $row)
				{
					$rota_jobs=maybe_unserialize($row->rota_jobs);
					if(!empty($rota_jobs))
					{
						foreach($rota_jobs AS $rota_task_id=>$people)
						{
								$peeps=maybe_unserialize($people);
								foreach($peeps AS $key=>$people)
								{	
									$people_id='';		
									if(empty($people)){$people_id='';}
									elseif(ctype_digit($people)){$people_id=$people;}
									else{$people_id=church_admin_get_one_id($people);}	church_admin_update_rota_entry($rota_task_id,$row->rota_date,$people_id,'service',$row->service_id);
								}
						
						}
					}
				
				}
			}
	}
		
    
 	//bug for non directory people sorted v1.0741
 	if($wpdb->get_var('select data_type from information_schema.columns where table_name = "'.CA_ROTA_TBL.'" and column_name = "people_id"')=='int')
	{
		$sql='ALTER TABLE `'.CA_ROTA_TBL.'` CHANGE `people_id` `people_id` TEXT NULL DEFAULT NULL;';
		
		$wpdb->query($sql);
	}
    //install attendance table
    $table_name = CA_ATT_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,adults INT(11) NOT NULL,children INT(11)NOT NULL,rolling_adults INT(11) NOT NULL,rolling_children INT(11)NOT NULL,service_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query($sql);
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_ATT_TBL.' LIKE "mtg_type"')!='mtg_type')
	{
		$sql='ALTER TABLE  '.CA_ATT_TBL.' ADD `mtg_type` TEXT NOT NULL AFTER `service_id`';
		$wpdb->query($sql);
		$sql='UPDATE  '.CA_ATT_TBL.' SET mtg_type="service"';
		$wpdb->query($sql);
	}    
    
	 //install attendance table
    $table_name = CA_IND_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	$sql="CREATE TABLE   IF NOT EXISTS  ". $table_name ."  (date DATE NOT NULL ,people_id INT(11) NOT NULL,meeting_type TEXT, meeting_id INT(11), attendance_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY );";
	$wpdb->query($sql);
    }
 	 //install classes table
    $table_name = CA_CLA_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {

	
	$sql='CREATE TABLE IF NOT EXISTS `'.CA_CLA_TBL.'` (  `name` text,  `description` text,  `next_start_date` date DEFAULT NULL,  `how_many` int(11) DEFAULT NULL,  `calendar` int(1) DEFAULT "1",  `class_order` int(11) DEFAULT NULL,  `class_id` int(11) NOT NULL AUTO_INCREMENT,  PRIMARY KEY (`class_id`)); ';
	$wpdb->query($sql);
    }  
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_CLA_TBL.' LIKE "recurring"')!='recurring')
	{
		$sql='ALTER TABLE  '.CA_CLA_TBL.' ADD `recurring` TEXT NOT NULL AFTER `next_start_date`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_CLA_TBL.' LIKE "event_id"')!='event_id')
	{
		$sql='ALTER TABLE  '.CA_CLA_TBL.' ADD `event_id` INT(11) AFTER `recurring`';
		$wpdb->query($sql);
	}	
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_CLA_TBL.' LIKE "start_time"')!='start_time')
	{
		$sql='ALTER TABLE  '.CA_CLA_TBL.' ADD `start_time` time NOT NULL DEFAULT "00:00:00" AFTER `recurring`';
		$wpdb->query($sql);
	}	
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_CLA_TBL.' LIKE "end_time"')!='end_time')
	{
		$sql='ALTER TABLE  '.CA_CLA_TBL.' ADD	`end_time` time NOT NULL DEFAULT "00:00:00" AFTER `start_time`';
		$wpdb->query($sql);
	}
    //install email table
   
    if($wpdb->get_var('show tables like "'.CA_EMA_TBL.'"') != CA_EMA_TBL) 
    {
        $sql="CREATE TABLE IF NOT EXISTS ". CA_EMA_TBL ." (recipient varchar(500) NOT NULL,  from_name text NOT NULL,  from_email text NOT NULL,  copy text NOT NULL, subject varchar(500) NOT NULL, message text NOT NULL,attachment text NOT NULL,sent datetime NOT NULL,email_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (email_id));";
        $wpdb->query($sql);
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_EMA_TBL.' LIKE "schedule"')!='schedule')
	{
		$sql='ALTER TABLE  '.CA_EMA_TBL.' ADD `schedule` DATE first';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('show tables like "'.CA_EBU_TBL.'"') != CA_EBU_TBL) 
	{
		$sql='CREATE TABLE IF NOT EXISTS `'.CA_EBU_TBL.'` ( `schedule` date DEFAULT NULL, `recipients` mediumtext NOT NULL, `subject` mediumtext NOT NULL,`message` mediumtext NOT NULL, `send_date` date NOT NULL, `filename` mediumtext NOT NULL, `from_name` varchar(500) NOT NULL, `from_email` varchar(500) NOT NULL, `email_id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`email_id`))';
			$wpdb->query($sql);
	}
		if($wpdb->get_var('SHOW COLUMNS FROM '.CA_EBU_TBL.' LIKE "content"')!='content')
	{
		$sql='ALTER TABLE  '.CA_EBU_TBL.' ADD `content` TEXT AFTER message';
		$wpdb->query($sql);
	}
    //install kids work table
   
    if($wpdb->get_var('show tables like "'.CA_KID_TBL.'"') != CA_KID_TBL) 
    {
        $sql="CREATE TABLE IF NOT EXISTS ". CA_KID_TBL." (group_name TEXT NOT NULL,  youngest DATE NOT NULL,  oldest DATE NOT NULL,department_id INT(11), id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id));";
        $wpdb->query($sql);
    }

    
    //install calendar table1
    $table_name = CA_DATE_TBL;
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    { $sql='CREATE TABLE IF NOT EXISTS '.CA_DATE_TBL.' (`title` text NOT NULL,`description` text NOT NULL,`location` text NOT NULL,`year_planner` int(1) NOT NULL,`event_image` int(11) DEFAULT NULL,`end_date` date NOT NULL DEFAULT "0000-00-00",`start_date` date NOT NULL DEFAULT "0000-00-00",`start_time` time NOT NULL DEFAULT "00:00:00",`end_time` time NOT NULL DEFAULT "00:00:00", `event_id` int(11) NOT NULL DEFAULT "0",`facilities_id` int(11) DEFAULT NULL,
  `general_calendar` int(1) NOT NULL DEFAULT "1",`how_many` int(11) NOT NULL,`date_id` int(11) NOT NULL AUTO_INCREMENT, `cat_id` int(11) NOT NULL,`recurring` text NOT NULL,PRIMARY KEY (`date_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
        $wpdb->query($sql);
    }
    //upgrade CA_DATE_TBL if needed
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "facilities_id"')!='facilities_id')
	{
			$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD facilities_id INT(11) AFTER event_id';
			$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "general_calendar"')!='general_calendar')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD general_calendar INT(1) NOT NULL DEFAULT "1" AFTER `facilities_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "description"')!='description')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `description` TEXT NOT NULL AFTER `facilities_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "location"')!='location')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `location` TEXT NOT NULL AFTER `description`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "year_planner"')!='year_planner')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `year_planner` INT(1) NOT NULL AFTER `location`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "cat_id"')!='cat_id')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `cat_id` INT(11) NOT NULL AFTER `year_planner`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "how_many"')!='how_many')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `how_many` INT(11) AFTER `event_id`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "event_image"')!='event_image')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `event_image` INT (11) AFTER `year_planner`';
		$wpdb->query($sql);
	}
	
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_HOU_TBL.' LIKE "private"')!='private')
	{
		$sql='ALTER TABLE  '.CA_HOU_TBL.' ADD  `private` INT( 1 ) NULL DEFAULT NULL FIRST ';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "recurring"')!='recurring')
	{
		$sql='ALTER TABLE  '.CA_DATE_TBL.' ADD `recurring` TEXT NOT NULL AFTER `year_planner`';
		$wpdb->query($sql);
	}
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_DATE_TBL.' LIKE "title"')!='title')
	{
		$sql='ALTER TABLE '.CA_DATE_TBL.' ADD `title` TEXT NOT NULL FIRST;';
		$wpdb->query($sql);
		$events=$wpdb->get_results('SELECT * FROM '.CA_EVE_TBL);
		if(!empty($events))
		{
			foreach($events AS $event)
			{
			$sql='UPDATE '. CA_DATE_TBL.' SET cat_id="'.esc_sql($event->cat_id).'",event_id="'.esc_sql($event->event_id).'",recurring="'.esc_sql($event->recurring).'",title="'.esc_sql($event->title).'", description="'.$event->description.'", location="'.esc_sql($event->location).'", year_planner="'.esc_sql($event->year_planner).'" WHERE event_id="'.esc_sql($event->event_id).'"';
		
			$wpdb->query($sql);
			}
		}
    
	}
	
	
	
    //install calendar table2
    $table_name = $wpdb->prefix."church_admin_calendar_category";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name)
    {
        $sql="CREATE TABLE IF NOT EXISTS ". $table_name ."  (category varchar(255)  NOT NULL DEFAULT '',  fgcolor varchar(7)  NOT NULL DEFAULT '', bgcolor varchar(7)  NOT NULL DEFAULT '', cat_id int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`cat_id`))" ;
        $wpdb->query($sql);
        $wpdb->query("INSERT INTO $table_name (category,bgcolor,cat_id) VALUES('Unused','#FFFFFF','0')");
    }
    //follow up funnels
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FUN_TBL.'"')!=CA_FUN_TBL)
    {
	
	if(!defined( 'DB_CHARSET'))define( 'DB_COLLATE','utf8');
	$sql='CREATE TABLE '.CA_FUN_TBL.' (action TEXT CHARACTER SET '.DB_CHARSET.' ,
member_type_id INT( 11 )  ,department_id INT( 11 )  , funnel_order INT(11), people_type_id INT(11), funnel_id INT( 11 ) AUTO_INCREMENT PRIMARY KEY
) ENGINE = MYISAM CHARACTER SET '.DB_CHARSET.';';
	$wpdb->query($sql);
    }
        //follow up people's funnels 
    if($wpdb->get_var('SHOW TABLES LIKE "'.CA_FP_TBL.'"')!=CA_FP_TBL)
    {
	
	if(!defined( 'DB_CHARSET'))define( 'DB_COLLATE','utf8');
	$sql='CREATE TABLE '.CA_FP_TBL.' (funnel_id INT(11) ,member_type_id INT(11),people_id INT( 11 )  ,assign_id INT( 11 )  , assigned_date DATE,email DATE NOT NULL DEFAULT "0000-00-00", completion_date DATE, id INT( 11 ) AUTO_INCREMENT PRIMARY KEY
) ENGINE = MYISAM CHARACTER SET '.DB_CHARSET.';';
	$wpdb->query($sql);
    }
 

  if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "smallgroup_order"')!='smallgroup_order')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD smallgroup_order INT(11)';
    $wpdb->query($sql);
    
 }




if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "lat"')!='lat')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD lat VARCHAR(30)';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "lng"')!='lng')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD lng VARCHAR(30)';
    $wpdb->query($sql);
}    
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "address"')!='address')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD address TEXT';
    $wpdb->query($sql);
}

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_SMG_TBL.' LIKE "leader"')=='leader')
{
    $sql='ALTER TABLE  '.CA_SMG_TBL.' ADD leadership TEXT';
    $wpdb->query($sql);
   
    $results=$wpdb->get_results('SELECT leader, id FROM '.CA_SMG_TBL);
    
    if(!empty($results))
    {
    	foreach($results AS $row)
    	{
    		$leader=maybe_unserialize($row->leader);
    		
    		if(is_array($leader))
    		{
    			$wpdb->query('UPDATE '.CA_SMG_TBL.' SET leadership="'.esc_sql(serialize(array(1=>$leader))).'" WHERE id="'.esc_sql($row->id).'"');
    		}
    	}
    }
    $wpdb->query('ALTER TABLE '.CA_SMG_TBL.' DROP leader');
}

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "last_updated"')!='last_updated')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD last_updated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "kidswork_override"')!='kidswork_override')
{
    $sql='ALTER TABLE '.CA_PEO_TBL.' ADD `kidswork_override` INT(11) NOT NULL AFTER `last_updated`;';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "prefix"')!='prefix')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD prefix TEXT ';
    $wpdb->query($sql);
}
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "funnels"')!='funnels')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD funnels TEXT';
    $wpdb->query($sql);
}

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_ATT_TBL.' LIKE "service_id"')!='service_id')
{
    $sql='ALTER TABLE  '.CA_ATT_TBL.' ADD service_id INT(11) DEFAULT "1"';
    $wpdb->query($sql);
}

if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "people_order"')!='people_order')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD people_order INT(11) ';
    $wpdb->query($sql);
}

$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' CHANGE `people_order` `people_order` INT(11) NULL DEFAULT "1"');

//v0.5946 add smallgroup attendance indicator
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "smallgroup_attendance"')!='smallgroup_attendance')
{
    $sql='ALTER TABLE  '.CA_PEO_TBL.' ADD smallgroup_attendance INT(1) DEFAULT 1';
    $wpdb->query($sql);
	
}
//v0.5958 added hope team
if($wpdb->get_var('SHOW COLUMNS FROM '.CA_PEO_TBL.' LIKE "other_hope_team"')!='other_hope_team')
{
    $sql='ALTER TABLE '.CA_PEO_TBL.' ADD `other_hope_team` TEXT NOT NULL;';
    $wpdb->query($sql);
	
}




//make sure tables are UTF8  
    $sql='ALTER TABLE '. CA_ATT_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
   $sql='ALTER TABLE '.CA_PEO_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
     $sql='ALTER TABLE '.CA_HOU_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
     $sql='ALTER TABLE '.CA_MTY_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
   $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_date CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
   
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_calendar_category CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.CA_EMA_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.CA_EBU_TBL.' CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_rota_settings CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    $sql='ALTER TABLE '.$wpdb->prefix.'church_admin_smallgroup CONVERT TO CHARACTER SET '.DB_CHARSET;
    if(DB_COLLATE)$sql.=' COLLATE '.DB_COLLATE.';';
    $sql.=';';
    $wpdb->query($sql);
    
//update pdf cache
if(!get_option('church_admin_calendar_width'))update_option('church_admin_calendar_width','630');
if(!get_option('church_admin_pdf_size'))update_option('church_admin_pdf_size','A4');
if(!get_option('church_admin_label'))update_option('church_admin_label','L7163');
if(!get_option('church_admin_page_limit'))update_option('church_admin_page_limit',30);


//sort out wp-cron
$church_admin_cron=get_option('church_admin_cron');
if(empty($church_admin_cron))
{
	update_option('church_admin_cron','immediate');
}
if(!empty($church_admin_cron) && $church_admin_cron=='wp-cron')
{
    add_action('church_admin_bulk_email','church_admin_cron');
   $timestamp=time();
    wp_schedule_event($timestamp, 'hourly', 'church_admin_bulk_email');
}

    
    $file_template=get_option('ca_podcast_file_template');
    if(empty($file_template))
    {
        $file_template='<div class="ca_podcast_file"><h3><a href="[FILE_URI]">[FILE_TITLE] </a> </h3><p>By [SPEAKER_NAME] on [FILE_DATE] as part of the [SERIES_NAME] series<br/>[FILE_DESCRIPTION] </p>[FILE_NAME]</div>';
        
    }
	else
	{
		if(strpos($file_template,'<p><audio class="sermonmp3" id="[FILE_ID]" src="[FILE_NAME]" preload="none"></audio></p>'))$file_template=str_replace('<p><audio class="sermonmp3" id="[FILE_ID]" src="[FILE_NAME]" preload="none"></audio></p>','[FILE_NAME]',$file_template);
		if(!strpos($file_template,'class="sermonmp3"'))$file_template=str_replace('<audio ','<audio class="sermonmp3" id="[FILE_ID]" ',$file_template);
		if(!strpos($file_template,'[FILE_PLAYS'))$file_template=str_replace('[SPEAKER_NAME]','[SPEAKER_NAME] ([FILE_PLAYS]) ',$file_template);
	}
	update_option('ca_podcast_file_template',$file_template);
    $series_template=get_option('ca_podcast_series_template');
    if(empty($series_template))
    {
        $series_template='<h2>[SERIES_NAME]</h2>[SERIES_DESCRIPTION]';
        update_option('ca_podcast_series_template',$series_template);
    }
    $speaker_template=get_option('ca_podcast_speaker_template');
    if(empty($speaker_template))
    {
        $speaker_template='<h2>[SPEAKER_NAME]</h2>[SPEAKER_DESCRIPTION]';
        update_option('ca_podcast_speaker_template',$speaker_template);
    }
    
    if(empty($ca_podcast_settings))
    {
        $ca_podcast_settings=array(
            
            'title'=>'',  
            'copyright'=>'',
            'link'=>CA_POD_URL.'podcast.xml',
            'subtitle'=>'',
            'author'=>'',
            'summary'=>'',
            'description'=>'',
            'owner_name'=>'',
            'owner_email'=>'',
            'image'=>'',
            'category'=>'',
        );
        
    }
	if($wpdb->get_var('SHOW COLUMNS FROM '.CA_FIL_TBL.' LIKE "video_url"')!='video_url')
{
    $sql='ALTER TABLE  '.CA_FIL_TBL.' ADD video_url TEXT AFTER `transcript`';
    $wpdb->query($sql);
}
//change way speakers are stored for v0.5963
$sermons=$wpdb->get_results('SELECT * FROM '.CA_FIL_TBL);
if(!empty($sermons) && OLD_CHURCH_ADMIN_VERSION <0.5963)
{

	foreach ($sermons AS $sermon)
	{
		$speaker=church_admin_get_people($sermon->speaker);
		$sql='UPDATE '.CA_FIL_TBL.' SET speaker="'.esc_sql($speaker).'" WHERE file_id="'.esc_sql($sermon->file_id).'"';
		
		$wpdb->query($sql);
	}

}


//sermonpodcast
//update version
update_option('church_admin_version',$church_admin_version);
update_option('church_admin_prayer_login',FALSE);
//change sex part!

$gender=get_option('church_admin_gender');
if($gender==array(1=>'Male',0=>'Female'))
{
	//make sure translation is set up
	update_option('church_admin_gender',array(1=>__('Male','church-admin'),0=>__('Female','church-admin')));
}
if(empty($gender))update_option('church_admin_gender',array(1=>__('Male','church-admin'),0=>__('Female','church-admin')));
 
 
 //update ministries from departments
 $ministries=get_option('church_admin_ministries');
if(empty($ministries)) {$ministries=get_option('church_admin_departments');update_option('church_admin_ministries',$ministries);delete_option('church_admin_departments');}
 
  //db indexes
 
$check=$wpdb->get_results('SHOW INDEX FROM '.CA_PEO_TBL.' WHERE KEY_NAME = "member_type_id"'); 
if(empty($check))
{ 
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' ADD INDEX `member_type_id` (`member_type_id`)');
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' ADD INDEX `household_id` (`household_id`)');
	$wpdb->query('ALTER TABLE '.CA_PEO_TBL.' ADD INDEX `user_id` (`user_id`)');
	$wpdb->query('ALTER TABLE '.CA_HOU_TBL.' ADD INDEX `household_id` (`household_id`)');
	$wpdb->query('ALTER TABLE '.CA_COM_TBL.' ADD INDEX `author_id` (`author_id`)');
}
 
 
 
}//end of install function