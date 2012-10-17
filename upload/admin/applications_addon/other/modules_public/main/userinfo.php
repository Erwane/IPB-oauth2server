<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'OAuth.php';

class public_oauth2server_main_userinfo extends ipsCommand {

  public function doExecute(ipsRegistry $registry) {
    $this->OAuth = new OAuth($this->DB);


    $token = $this->OAuth->getBearerToken();



    $data = $this->DB->buildAndFetch(array(
      'select' => 'm.*',
      'from' => array('members' => 'm'),
      'where' => 't.token="' . sha1($token) . '"',
      'add_join' => array(
        array(
          'select' => 't.expires',
          'from' => array('oauth2server_tokens' => 't'),
          'where' => 'm.member_id=t.member_id',
          'type' => 'inner',
        ),
        array(
          'select' => 'c.client_key',
          'from' => array('oauth2server_clients' => 'c'),
          'where' => 'c.id=t.client_id',
          'type' => 'inner',
        )
      ),
      ));


    if (empty($data))
      return false;


    // Send response
    header("Content-Type: application/json");
    header("Cache-Control: no-store");
    echo json_encode(array(
      'id' => md5($data['client_key'] . $data['member_id']),
      'email' => $data['email'],
      'birthday' => $data['bday_year'] . '-' . $data['bday_month'] . '-' . $data['bday_day'],
      'name' => $data['name'],
    ));
  }

}