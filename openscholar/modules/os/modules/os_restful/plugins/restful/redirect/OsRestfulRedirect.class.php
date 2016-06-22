<?php

class OsRestfulRedirect extends \RestfulBase implements \RestfulDataProviderInterface {

  public function publicFieldsInfo() {
    // what is this even for
  }

  public function create() {
    $redirect = new stdClass();

    // Merge default values.
    redirect_object_prepare($redirect, array(
      'source' => isset($this->request['path']) ? urldecode($this->request['path']) : '',
      'source_options' => array(),
      'redirect' => isset($this->request['target']) ? urldecode($this->request['target']) : '',
      'redirect_options' => array(),
      'language' => LANGUAGE_NONE,
    ));

    if (!empty($_GET['vsite']) && $vsite = vsite_get_vsite($_GET['vsite'])) {
      $redirect->redirect_options['purl'] = array(
        'add' => array(
          0 => array(
            'provider' => $vsite->purl_provider,
            'id' => $vsite->id,
          )
        ),
      );
    }

    // check that there there are no redirect loops
    if (url($redirect->source) == url($redirect->redirect)) {
      throw new RestfulBadRequestException(t('You are attempting to redirect the page to itself. This will result in an infinite loop.'));
    }

    redirect_hash($redirect);
    if ($existing = redirect_load_by_hash($redirect->hash)) {
      if ($redirect->rid != $existing->rid) {
        throw new RestfulBadRequestException(t('The source path %source is already being redirected. Do you want to <a href="@edit-page">edit the existing redirect</a>?', array('%source' => redirect_url($redirect->source, $redirect->source_options), '@edit-page' => url('admin/config/search/redirect/edit/'. $existing->rid))));
      }
    }

    redirect_save($redirect);
    return $this->renderRedirect($redirect);
  }

  public function index() {
    if (!empty($this->request['vsite']) && $vsite = vsite_get_vsite($this->request['vsite'])) {
      return array();
    }
  }

  public function view($id) {
    $redirect = redirect_load($id);
    return $this->renderRedirect($redirect);
  }

  public function remove($id) {
    redirect_delete($id);

    // Set the HTTP headers.
    $this->setHttpHeaders('Status', 204);
  }

  public function renderRedirect($redirect) {
    return array(
      'id' => $redirect->rid,
      'path' => $redirect->source,
      'target' => $redirect->redirect
    );
  }
}