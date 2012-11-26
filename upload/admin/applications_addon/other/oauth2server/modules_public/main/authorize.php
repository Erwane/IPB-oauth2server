<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'OAuth.php';

class public_oauth2server_main_authorize extends ipsCommand {

  private $OAuth = null;

  public function doExecute(ipsRegistry $registry) {

    $this->OAuth = new OAuth($this->DB);
    $this->lang->loadLanguageFile(array('public_global'), 'oauth2server');

    /* Not logged in? */
    if (!$this->memberData['member_id']) {
      $this->registry->output->silentRedirect($this->settings['base_url'] . 'app=core&module=global&section=login&referer=' . urlencode('?' . $this->settings['query_string_real']));
    }

    $clientKey = $this->request['client_id'];
    $clientData = $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_clients', 'where' => "client_key='$clientKey'"));
    $this->OAuth->setVariable('supported_scopes', $clientData['scope']);

    // Validation ou non de l'application
    if ($this->request['request_method'] === 'post') {
      //var_dump($this->request);

      $accepted = $this->request['accept'] === 'ok';
      try {
        $this->OAuth->finishClientAuthorization($accepted, $this->memberData['member_id'], $this->request);
      } catch (OAuth2RedirectException $e) {
        $e->sendHttpResponse();
      }
    }

    header('X-Frame-Options: DENY');
    // API key exists
    if (!empty($clientData)) {
      // Member already accept this API Key ?
      $acceptedClient = $this->DB->buildAndFetch(array('select' => '*', 'from' => 'oauth2server_authorizes', 'where' => 'client_id=' . (int) $clientData['id'] . ' AND member_id=' . (int) $this->memberData['member_id']));
      if (!empty($acceptedClient)) {
        // Création du code et redirection
        try {
          $this->OAuth->finishClientAuthorization(true, $this->memberData['member_id'], $this->request);
        } catch (OAuth2RedirectException $e) {
          $e->sendHttpResponse();
        }
      } else {
        // Formulaire pour approuver l'application
        try {
          $OAuthParams = $this->OAuth->getAuthorizeParams();
        } catch (Exception $e) {
          $e->sendHttpResponse();
        }

        $this->output = $this->registry->output->getTemplate('oauth2server')->authorize($clientData, $OAuthParams);

        $this->registry->output->setTitle($this->lang->words['oauth2server_authorize_title']);
        $this->registry->output->addContent($this->output);
        $this->registry->output->sendOutput();
      }
    } else {
      trigger_error("Clé inconnue",E_USER_ERROR);
    }
  }

}