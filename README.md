IPB-oauth2server
================

### Introduction

This application will add an OAuth2 server to your IPB forum.

It's tested with version 3.3.4 and i really don't known if it can work with other version.

*Before installing, you should know that*

* It's NOT a plug&play application, it's working but there is no CP or sustainable option
* It's not a full REST API, only OAuth work, but it's a base :)


### Installation

* copie the content of upload dir to your IPB folder
* log to the admin
* go to your "Manage Applications & Modules" page
* OAuth2 Server should appear in the right collumn, click "Install"
* wait ... and go to the hard part

### Create client (API key)

Here is the hard part because i don't have the time to build a beautiful CP page, so, lets do it manually

* go to http://www.phea.fr/outils/generateur-cle.php and generate 2 "Middle" (aka 'Moyen') with "40 caracs"
* open your IPB database (phpMyAdmin, phpPgAdmin, or whatever you want)
* go to the oauth2server_clients
* INSERT
* client_key => the api key
* client_secret => hum, the password ? right
* redirect_uri => the Oauth client, can be your domain only
* image => not used, it's for a beautiful "Authorize page", not yet implemented
* scope => "user.email user.profile" (without quotes)
$ Save

### Testing

Insert the right params to your OAuth client and it should work.
This is base on the quizlet [OAuth2-php library](https://github.com/quizlet/oauth2-php) and should respect Oauth protocol, but i'm aware about modifications and tips.

