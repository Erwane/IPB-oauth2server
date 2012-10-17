<?php

/**
 * Product Title:		OAuth2 Server
 * Product Version:		1.0.0
 * Author:				Erwane Breton
 * Website:				Erwane Breton
 * Website URL:			http://erwane.phea.fr
 * Email:				contact@phea.fr
 */
$INSERT = array();

/*
 * Client de test
 */
$count = ipsRegistry::DB()->buildAndFetch(array('select' => 'count(*) as count', 'from' => 'oauth2server_clients'));

if (!$count['count']) {
  //$INSERT[] = "INSERT INTO oauth2server_clients (`title`, `scope`, `client_key`, `client_secret`, `redirect_uri`) VALUES ('TEST', 'user.email user.profile', 'test123', 'azerty123', 'http://mon.domaine.com/');";
}