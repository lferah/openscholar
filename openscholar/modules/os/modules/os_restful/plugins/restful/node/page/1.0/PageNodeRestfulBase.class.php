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

      drupal_map_assoc($filter_elements = array("nid", "vid", "uid", "created", "type", "language", "changed", "form_build_id", "form_token", "metatags", "actions_top", "field_child_site", 'actions', 'form_id'));

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

        /*$result[] = array(
        'property' => $k,
        'discovery' => array(
          'info' => array(
            'name' => $v['#title'] ? $v['#title'] : $v['und']["#title"],,
            'description' => $v['und'][0]['description'] ? $v['und'][0]['description'] : $v['#description'],
          ),
          'data' => array(
            'cardinality' => $v['#cardinality'] ? $v['#cardinality'] : $v['und']['cardinality'],
          ),
          'form_element' => ,
        ),
      )*/

        if($k == 'title'){
          $output[$k] = array (
            'type' => $v['#type'],
            'title' => $v['#title'],
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'left'
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
            'column' => 'left'
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
            'column' => 'left',
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
            'class' => array('csv-import-fields', 'os-importer-file-upload-wrapper')
          );
        }
        elseif($v['#type'] != 'fieldset'){
          $output[$k] = array (
            'type' => 'textfield',
            'title' => $v['und']["#title"],
            'weight' => $v['#weight'],
            'attributes'=> $v['#attributes'],
            'id' => $v['#id'],
            'access' =>$v['#access'],
            'attached'=> $v['und'][0]['#attached'] ? $v['und'][0]['#attached'] : '',
            'column' => 'left'
          );
        }

        elseif($v['#type'] == 'fieldset'){
          $output[$k] = array (
            'type' => 'markup',
            'markup' => '<label>' . $v['#title'] . '</label>',
            'weight' =>  $v['#weight'],
            'id' => $v['#id'],
            'access' => $v['#access'],
            'column' => 'right',
          );
          foreach ($v as $row => $val) {
            if (strpos($row, '#') !== 0) {
              if ($val['#title'] == '') {
                $title_val = $val['und'][0]['#title'];
              }
              else {
                $title_val = $val['#title'];
              }
              $output[$k][$row] = array (
                'type' => $val['#type'],
                'title' => $title_val,
                'weight' =>  $val['#weight'],
                'id' => $val['#id'],
                'access' => $v['#access'],
                'default_value' => $val['#default_value'] ? $val['#default_value'] : '',
                'description' => $val['#description'] ? $val['#default_value'] : '',
              );
            }
          }
        }
      }
      return $output;
    }
    throw new RestfulForbiddenException("Content type not found.");
  }
}
