<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'OAuth.php';

class public_oauth2server_main_token extends ipsCommand {

  public function doExecute(ipsRegistry $registry) {
    $this->OAuth = new OAuth($this->DB);
    try {
      $this->OAuth->grantAccessToken();
    } catch (OAuth2ServerException $e) {
      $e->sendHttpResponse();
    }
  }

}