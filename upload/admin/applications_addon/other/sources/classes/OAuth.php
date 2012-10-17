<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'oauth2-php' . DIRECTORY_SEPARATOR . 'IOAuth2Storage.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'oauth2-php' . DIRECTORY_SEPARATOR . 'IOAuth2RefreshTokens.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'oauth2-php' . DIRECTORY_SEPARATOR . 'IOAuth2GrantUser.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'oauth2-php' . DIRECTORY_SEPARATOR . 'IOAuth2GrantCode.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'oauth2-php' . DIRECTORY_SEPARATOR . 'OAuth2.php';

class OAuth implements IOAuth2Storage, IOAuth2RefreshTokens, IOAuth2GrantUser, IOAuth2GrantCode {

  /**
   * AccessToken object.
   *
   * @var object
   */
  public $AccessToken;

  /**
   * An array containing the model and fields to authenticate users against
   * 
   * Inherits theses defaults:
   * 
   * $this->OAuth->authenticate = array(
   * 	'userModel' => 'User',
   * 	'fields' => array(
   * 		'username' => 'username',
   * 		'password' => 'password'
   * 	)
   * );
   * 
   * Which can be overridden in your beforeFilter:
   * 
   * $this->OAuth->authenticate = array(
   * 	'fields' => array(
   * 		'username' => 'email'
   * 	)
   * );
   * 
   * 
   * $this->OAuth->authenticate
   * 
   * @var array 
   */
  public $authenticate;

  /**
   * Defaults for $authenticate
   * 
   * @var array 
   */
  protected $_authDefaults = array(
    'userModel' => 'User',
    'fields' => array('username' => 'username', 'password' => 'password')
  );

  /**
   * AuthCode object.
   *
   * @var object
   */
  public $AuthCode;

  /**
   * Clients object.
   *
   * @var object
   */
  public $Client;

  /**
   * Array of globally supported grant types
   * 
   * By default = array('authorization_code', 'refresh_token', 'password');
   * Other grant mechanisms are not supported in the current release
   * 
   * @var array
   */
  public $grantTypes = array('authorization_code', 'refresh_token', 'password');

  /**
   * OAuth2 Object
   * 
   * @var object
   */
  public $OAuth2;

  /**
   * RefreshToken object.
   *
   * @var object
   */
  public $RefreshToken;

  /**
   * Static storage for current user
   * 
   * @var array 
   */
  protected $_user = false;

  /* IPS */
  public $DB;

  public function __construct($dbObject) {
    $this->OAuth2 = new OAuth2($this);
    $this->DB = $dbObject;
  }

  public function __call($name, $arguments) {
    if (method_exists($this->OAuth2, $name)) {
      try {
        return call_user_func_array(array($this->OAuth2, $name), $arguments);
      } catch (Exception $e) {
        if (method_exists($e, 'sendHttpResponse')) {
          $e->sendHttpResponse();
        }
        throw $e;
      }
    }
  }

  /**
   * Fakes the OAuth2.php vendor class extension for variables
   * 
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    if (isset($this->OAuth2->{$name})) {
      try {
        return $this->OAuth2->{$name};
      } catch (Exception $e) {
        $e->sendHttpResponse();
      }
    }
  }

  /**
   * Retrieve access token
   * 
   * @see IOAuth2Storage::getAccessToken().
   *
   * @param string $oauth_token
   * @return mixed AccessToken array if valid, null if not 
   */
  public function getAccessToken($oauth_token) {
    $currentToken = $this->DB->buildAndFetch(array(
      'select' => 't.token AS oauth_token, t.expires, t.scope, t.member_id AS user_id',
      'from' => array('oauth2server_tokens' => 't'),
      'where' => 'token="' . sha1($oauth_token) . '"',
      'add_join' => array(
        array(
          'select' => 'c.client_key AS client_id',
          'from' => array('oauth2server_clients' => 'c'),
          'where' => 'c.id=t.client_id',
          'type' => 'left',
        )
      ),
      ));

    return $currentToken;
  }

  /**
   * Set access token
   * 
   * @see IOAuth2Storage::setAccessToken().
   *
   * @param string $oauth_token
   * @param string $client_id
   * @param int $user_id
   * @param string $expires
   * @param string $scope
   * @return boolean true if successfull, false if failed 
   */
  public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL) {

    $clientData = $this->getApiData($client_id);
    $currentToken = $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_tokens', 'where' => 'client_id=' . (int) $clientData['id'] . ' AND member_id=' . (int) $user_id));
    $data = array(
      'client_id' => $clientData['id'],
      'member_id' => $user_id,
      'token' => sha1($oauth_token),
      'expires' => $expires,
      'scope' => $scope
    );

    if (empty($currentToken)) {
      $res = $this->DB->insert('oauth2server_tokens', $data);
    } else {
      $res = $this->DB->update('oauth2server_tokens', $data, 'id=' . $currentToken['id']);
    }

    return $res;
  }

  public function unsetRefreshToken($refresh_token) {
    return $this->RefreshToken->delete($refresh_token);
  }

  public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL) {
    $clientData = $this->getApiData($client_id);
    $currentToken = $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_tokens_refresh', 'where' => 'client_id=' . (int) $clientData['id'] . ' AND member_id=' . (int) $user_id));

    $data = array(
      'client_id' => (int) $clientData['id'],
      'member_id' => $user_id,
      'token' => sha1($refresh_token),
      'expires' => $expires,
      'scope' => $scope
    );

    if (empty($currentToken)) {
      $res = $this->DB->insert('oauth2server_tokens_refresh', $data);
    } else {
      $res = $this->DB->update('oauth2server_tokens_refresh', $data, 'id=' . $currentToken['id']);
    }

    return $res;
  }

  public function getRefreshToken($refresh_token) {
    return null;
  }

  /**
   * Grant type: authorization_code
   * 
   * @see IOAuth2GrantCode::getAuthCode()
   * 
   * @param string $code
   * @return AuthCode if valid, null of not 
   */
  public function getAuthCode($code) {
    $authCode = $this->getAuthorizeCode($code);
    if ($authCode) {
      return $authCode;
    }
    return null;
  }

  /**
   * Grant type: authorization_code
   * 
   * @see IOAuth2GrantCode::setAuthCode().
   *
   * @param string $code
   * @param string $client_id
   * @param int $user_id
   * @param string $redirect_uri
   * @param string $expires
   * @param string $scope
   * @return boolean true if successfull, otherwise false
   */
  public function setAuthCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = NULL) {

    // Code existant pour cette API ? 
    $clientData = $this->getApiData($client_id);
    $currentCode = $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_authorizes', 'where' => 'client_id=' . (int) $clientData['id'] . ' AND member_id=' . (int) $user_id));

    $data = array(
      'code' => sha1($code),
      'client_id' => $clientData['id'],
      'member_id' => $user_id,
      'redirect_uri' => $redirect_uri,
      'expires' => $expires,
      'scope' => $scope
    );

    if (empty($currentCode)) {
      $res = $this->DB->insert('oauth2server_authorizes', $data);
    } else {
      $res = $this->DB->update('oauth2server_authorizes', $data, 'id=' . $currentCode['id']);
    }

    return $res;
  }

  public function getClientDetails($clientKey) {
    $client = $this->getApiData($clientKey);
    if (!empty($client)) {
      return $client;
    }
    return false;
  }

  public function checkRestrictedGrantType($client_id, $grant_type) {
    return in_array($grant_type, $this->grantTypes);
  }

  public function checkClientCredentials($client_key, $client_secret = NULL) {
    $clientData = $this->getApiData($client_key, $client_secret);
    if ($clientData) {
      return $clientData;
    };
    return false;
  }

  public function checkUserCredentials($client_id, $username, $password) {
    return false;
  }

  /*
   * Information API (client)
   */

  private function getApiData($key, $secret = null) {
    $where = "client_key='$key'";

    if ($secret != '')
      $where .= " AND client_secret='$secret'";

    return $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_clients', 'where' => $where));
  }

  /*
   * Code authorization
   */

  private function getAuthorizeCode($code) {
    return $this->DB->buildAndFetch(array(
        'select' => 'a.code, a.redirect_uri, a.expires, a.scope, a.member_id AS user_id',
        'from' => array('oauth2server_authorizes' => 'a'),
        'add_join' => array(
          array(
            'select' => 'c.client_key AS client_id',
            'from' => array('oauth2server_clients' => 'c'),
            'where' => 'c.id=a.client_id',
            'type' => 'left',
          )
        ),
        'where' => "code='" . sha1($code) . "'"));
  }

}