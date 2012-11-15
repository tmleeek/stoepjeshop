<?php

/*
	id (Oplopend ID) 
	name (Naam van het bedrijf) 
	street (Straat) 
	housenr (Huisnummer) 
	postal (Postcode) 
	city (Plaats) 
	country (Land) 
	phone (Telefoonnummer) 
	email (E-mailadres) 
	textualpos (Alternatief tekstuele plaats voor weergave) 
	longitude (Lengtegraad handmatig of gegenereerd) 
	latitude (Breedtegraad handmatig of gegenereerd) 
	banknr (Bank- of gironummer) 
	pickup (Afhalen mogelijk) 
	send (Verzending mogelijk) 
	ghostfrom (Is een sub afhaal/verzend punt van ID) 
	opensunday (Openingstijden op zondag) 
	openmonday (Openingstijden op maandag) 
	opentuesday (Openingstijden op dinsdag) 
	openwednesday (Openingstijden op woensdag) 
	openthursday (Openingstijden op donderdag) 
	openfriday (Openingstijden op vrijdag) 
	opensaturday (Openingstijden op zaterdag) 
	importid (Relatie met de bron van de import)
 */

$installer = $this;

$installer->startSetup();
try{
	$installer->run("
DROP TABLE IF EXISTS {$this->getTable('storepicker/stores')};
CREATE TABLE {$this->getTable('storepicker/stores')} (
`id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
`name` varchar( 255 ) NOT NULL default '',
`street` varchar( 255 ) NOT NULL default '',
`housenr` varchar( 25 ) NOT NULL default '',
`postal` varchar( 7 ) NOT NULL default '',
`city` varchar( 255 ) NOT NULL default '',
`country` varchar( 255 ) NOT NULL default '',
`phone` varchar( 100 ) NOT NULL default '',
`email` varchar( 255 ) NOT NULL default '',
`textualpos` text NOT NULL default '',
`longitude` varchar( 255 ) NOT NULL default '',
`latitude` varchar( 255 ) NOT NULL default '',
`banknr` varchar( 255 ) NOT NULL default '',
`pickup` boolean NOT NULL,
`send` boolean NOT NULL,
`sendcosts` decimal( 6,2 ) NOT NULL,
`ghostfrom` int( 11 ) unsigned,
`opensunday` varchar( 255 ) NOT NULL default '',
`openmonday` varchar( 255 ) NOT NULL default '',
`opentuesday` varchar( 255 ) NOT NULL default '',
`openwednesday` varchar( 255 ) NOT NULL default '',
`openthursday` varchar( 255 ) NOT NULL default '',
`openfriday` varchar( 255 ) NOT NULL default '',
`opensaturday` varchar( 255 ) NOT NULL default '',
`sendradius` double NOT NULL,
`sendsunday` varchar( 255 ) NOT NULL default '',
`sendmonday` varchar( 255 ) NOT NULL default '',
`sendtuesday` varchar( 255 ) NOT NULL default '',
`sendwednesday` varchar( 255 ) NOT NULL default '',
`sendthursday` varchar( 255 ) NOT NULL default '',
`sendfriday` varchar( 255 ) NOT NULL default '',
`sendsaturday` varchar( 255 ) NOT NULL default '',
`importid` int( 11 ) unsigned
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO {$this->getTable('storepicker/stores')} (`name`, `street` ,`housenr` ,`postal` ,`city` ,`country` ,`phone` ,`email` ,`textualpos` ,`longitude` ,`latitude`, `banknr`, `pickup`, `send`, `sendcosts`, `opensunday`, `openmonday`, `opentuesday`, `openwednesday`, `openthursday`, `openfriday`, `opensaturday`, `sendradius`, `sendsunday`, `sendmonday`, `sendtuesday`, `sendwednesday`, `sendthursday`, `sendfriday`, `sendsaturday`)
VALUES ('Drecomm' ,'Bergstraat', '25', '3811NE', 'Amersfoort', 'Nederland', '033-8870552', 'info@drecomm.nl', 'Bergstraat 25\n3811NE\nAmersfoort', '0', '0', '012345678', 0, 0, '9,95', '-', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '-', 30, '-', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00', '8:30 - 17:00');
");
}catch(Exception $e){}

$installer->endSetup(); 
