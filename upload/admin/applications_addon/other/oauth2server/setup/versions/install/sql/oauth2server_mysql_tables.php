<?php

/**
 * Product Title:		OAuth2 Server
 * Product Version:		1.0.0
 * Author:				Erwane Breton
 * Website:				Erwane Breton
 * Website URL:			http://erwane.phea.fr
 * Email:				contact@phea.fr
 */
$PRE = trim(ipsRegistry::dbFunctions()->getPrefix());

/* Ensure this doesn't get run if we're checking diagnostics */
if ($this->request['module'] != 'diagnostics' AND $this->request['section'] != 'diagnostics') {
  
}

/* Still here? Install as normal */
$TABLE[] = "CREATE TABLE oauth2server_clients (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `scope` varchar(255) NOT NULL DEFAULT 'user.email user.profile',
  `client_key` char(64) NOT NULL,
  `client_secret` char(128) NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);";

$TABLE[] = "CREATE TABLE oauth2server_authorizes (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `client_id` smallint(5) unsigned NOT NULL,
  `member_id` int(11) unsigned NOT NULL,
  `redirect_uri` varchar(200) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);";

$TABLE[] = "CREATE TABLE oauth2server_tokens (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
  `client_id` smallint(5) unsigned NOT NULL,
  `member_id` int(11) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);";

$TABLE[] = "CREATE TABLE oauth2server_tokens_refresh (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
  `client_id` smallint(5) unsigned NOT NULL,
  `member_id` int(11) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` int(11) NOT NULL,
  `scope` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);";
