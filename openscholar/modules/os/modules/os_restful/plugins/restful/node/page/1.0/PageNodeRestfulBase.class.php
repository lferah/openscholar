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
    //require_once ('includes/common.inc ');
    //$langcode = entity_language('node', 'page');

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

      //Remove some node elements from the array
      foreach ($output as $key => $value) {
        foreach($filter_elements as $keys => $values) {
          if($key == $values) {
             unset($output[$key]);
          }
        }
      }

      // Build the array contain form fields to expose
      foreach ($output as $k => $v) {
        if($k == 'title'){
          $output[$k] = array (
            'type' => $v['#type'],
            'title' => $v['#title'],
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'right'
          );
        }
        elseif($k == 'body'){
          $output[$k] = array (
            'type' =>'textarea',
            'title' => $v['und']["#title"],
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'right'
          );
        }
        elseif($k == 'field_upload'){
          $supported_file_extention = implode(explode(" ",$v['und']['drop']['#upload_validators']['file_validate_extensions'][0]), ',');

          $output[$k] = array (
            'type' =>'managed_file',
            'title' => $v['und']["#title"],
            'weight' => $v['#weight'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'custom_directive' =>'media-browser-field',
            'custom_directive_parameters' => array ('cardinality' => 1,
              'droppable_text' => $v['und']['drop']['#droppable_area_text'],
              'max_filesize'=> $v['und']['drop']['#file_upload_max_size'],
              'upload_text' => 'Select file to Add',
              'panes' => array ('upload', 'library'),
              'types' => array ($supported_file_extention)
              ),
            'upload_location'=> $v['und']['drop']['#upload_location'],
            'upload_validators'=> array ('file_validate_extensions' => array ($supported_file_extention),
              'file_validate_size' => array (134217728)),
            'column' => 'right',
            'class' => array('csv-import-fields', 'os-importer-file-upload-wrapper'),
          );
        }
        elseif($k == 'og_group_ref'){
          $output[$k] = array (
            'type' => 'textfield',
            'title' => $v['und']["#title"],
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' => true,
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'right'
          );
        }
        elseif($k == 'og_vocabulary'){
          $output[$k] = array (
            'type' => 'textfield',
            'title' => 'Page Type',
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'right'
          );
        }
        //elseif () {

        //}
      }
      return $output;
    }
    throw new RestfulForbiddenException("Content type not found.");
  }
}
