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

  public function getForms() {

    $output = array();
    $field_instances = field_info_instances('node', 'page');
    foreach ($field_instances as $key => $field_instance) {
      $field_info = field_info_field($field_instance['field_name']);
      if ($field_instance['type'] == 'file') {
        $custom_directive =  array('directive name', 'attributes name');
      }
      $output[] = array(
        'property' => $key,
        'discovery' => array(
          'info' => array(
            'name' => $field_instance['field_name'],
            'description' => $field_instance['description'],
          ),
          'data' => array(
            'cardinality' => $field_info['cardinality'],
          ),
          'form_element' => array(
            'label' => $field_instance['label'],
            'type' => $field_info['type'],
            'default_value' => $field_instance['default_value'],
            'required' => $field_instance['required'],
            'size' => $field_instance['widget']['settings']['size'],
            'widget' => $field_instance['widget'],
            'settings' => $field_instance['settings'],
            'custom_directive' => $custom_directive,
          ),
        ),
      );
    }

    return $output;
  }
}
