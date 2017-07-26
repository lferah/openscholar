<?php

class PageNodeRestfulBase extends OsNodeRestfulBase {

  public static function controllersInfo() {
    return array(
      'form' => array(
        // If they don't pass a menu-id then display nothing.
        \RestfulInterface::GET => 'getForms',
      ),
    ) + parent:: controllersInfo();
  }

  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    $public_fields['path'] = array(
      'property' => 'path',
    );

    return $public_fields;
  }

  public function getForms($args) {
    $json_output = array();
    $content_type = 'page';
    module_load_include('inc', 'node', 'node.pages');
    if($form = node_add($content_type)) {
      $output = array();
      foreach ($form as $key => $value) {
        if (strpos($key, '#') !== 0) {
          $output[$key] = $value;
        }
      }

      drupal_map_assoc($filter_elements = array("nid", "vid", "uid", "created", "type", "language", "changed", "form_build_id", "form_token", "metatags", "actions_top", "field_child_site"));
      foreach ($output as $key => $value) {
        foreach($filter_elements as $keys => $values) {
          if($key == $values) {
             unset($output[$key]);
          }
        }
      }

      //$array_attributes = array ('#type','#title', '#weight', '#attributes', '#name');
      foreach ($output as $k => $v) {
         $output[$k] = array (
           'type' =>$v['#type'],
           'title' => $v['#title'],
           'weight' => $v['#weight'],
           'attributes'=> $v['#attributes'],
           'name' => $v['#name']);
      }
      return $output;

    }
    throw new RestfulForbiddenException("Content type not found.");
  }
}
