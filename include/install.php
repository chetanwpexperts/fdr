<?php
/**
 * Play Chess Online installer function
 *
 * @author webexe
 */
Class Installation 
{	
	Static function activate() 
	{
		global $wpdb;
		$folders = $wpdb->prefix."folders";
		
		$tbA = "CREATE TABLE ".$folders." (
		  id INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		  number varchar(255) NOT NULL UNIQUE,
		  name text NOT NULL,
		  uid int NOT NULL,
		  parent INT NOT NULL default 0,
		  status INT NOT NULL default 1,
		  created timestamp NOT NULL DEFAULT current_timestamp(),
		  PRIMARY KEY(id))ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$wpdb->query($tbA);
	}	
	/**Drop tables from this plugin on deactivaion of plugin*/
	Static function deactivate() 
	{
		global $wpdb;		
		// tables
		$folders = $wpdb->prefix."folders";
		
		// queries
		$tba = "DROP TABLE IF EXISTS $folders";
		
		//execute the all queries
		$wpdb->query($tba);
	}
}