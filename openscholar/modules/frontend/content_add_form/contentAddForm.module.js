(function() {
  var m = angular.module('ContentAddForm', ['angularModalService', 'redirectForm', 'MediaBrowserField', 'formElement', 'os-buttonSpinner']);

    /**
   * Fetches the content settings forms from the server and makes them available to directives and controllers
   */
  m.service('contentForm', ['$http', '$q', function ($http, $q, $httpParamSerializer) {
    var data = {};

    this.SettingsReady = function(form_name) {
      var settingsForms = {};
      var settings = {};

      var queryArgs = {};
      var promises = [];

      if (Drupal.settings.spaces != undefined) {
        if (Drupal.settings.spaces.id) {
          queryArgs.vsite = Drupal.settings.spaces.id;
        }
      }

      var baseUrl = Drupal.settings.paths.api;
      var config = {
        params: queryArgs
      };

      // $http returns a promise, which has a then function, which also returns a promise
      var promise = $http.get(baseUrl+'/' + form_name +'/form', config).then(function (response) {
        // The then function here is an opportunity to modify the response
        // The return value gets picked up by the then in the controller.
        return response.data;
      });
      // Return the promise to the controller
      return promise;
    }

  }])

  /**
   * Open modals for the content settings forms
   */
  m.directive('contentAddForm', ['ModalService', 'contentForm', function (ModalService, contentForm) {
    var dialogOptions = {
      minWidth: 1187,
      minHeight: 100,
      modal: true,
      position: 'center',
      dialogClass: 'content-add-form'
    };

    function link(scope, elem, attrs) {

      elem.bind('click', function (e) {

        var str = e.currentTarget.id;
        var form_name_arr = str.split('_');
        var form_name = form_name_arr[0];
        var form_title = form_name.charAt(0).toUpperCase() + form_name.slice(1);
        scope.title = 'Create ' + form_title;

        e.preventDefault();
        e.stopPropagation();

        ModalService.showModal({
          controller: 'contentAddFormController',
          template: '<form id="{{formId}}" name="contentAddForm" ng-submit="submitForm($event)">' +
            '<div class="messages" ng-show="status.length || errors.length"><div class="dismiss" ng-click="status.length = 0; errors.length = 0;">X</div>' +
              '<div class="status" ng-show="status.length > 0"><div ng-repeat="m in status track by $index"><span ng-bind-html="m"></span></div></div>' +
              '<div class="error" ng-show="errors.length > 0"><div ng-repeat="m in errors track by $index"><span ng-bind-html="m"></span></div></div></div>' +
            '</div>' +
            '<div class="form-column-wrapper column-count-{{columnCount}}" ng-if="columnCount > 1">' +
              '<div class="form-column column-{{column_key}}" ng-repeat="(column_key, elements) in columns">' +
                '<div class="form-item" ng-repeat="(key, field) in elements | weight">' +
                  '<div form-element element="field" value="formData[key]"><span>placeholder</span></div>' +
                '</div>' +
              '</div>' +
            '</div>' +

              '<div class="form-item" ng-repeat="(key, field) in formElements | weight">' +
                '<div form-element element="field" value="formData[key]"><span>placeholder</span></div>' +
              '</div>' +

            '<div class="help-link" ng-bind-html="help_link"></div>' +
          '<div class="actions" ng-show="showSaveButton"><button type="submit" button-spinner="settings_form" spinning-text="Saving">Save</button><input type="button" value="Close" ng-click="close(false)"></div></form>',
          inputs: {
            form: scope.form
          }
        })
        .then(function (modal) {
          dialogOptions.title = scope.title;
          dialogOptions.close = function (event, ui) {
            modal.element.remove();
          }
          modal.element.dialog(dialogOptions);
          modal.close.then(function (result) {
            if (result) {
              window.location.reload();
            }
          });
        });
      });
    }

    return {
      link: link,
      scope: {
        form: '@'
      }
    };

  }]);


  /**
   * The controller for the forms themselves
   */
  m.controller('contentAddFormController', ['$scope', '$sce', 'contentForm', 'buttonSpinnerStatus', 'form', 'close', function ($s, $sce, contentForm, bss, form, close) {
    var formSettings = {};
    $s.formId = form;

    $s.formElements = {};
    $s.formSettings = {};
    $s.formData = {};

    $s.status = [];
    $s.errors = [];
    $s.columns = {};
    $s.columnCount = 0;
    $s.showSaveButton = true;

    var form_name_arr = form.split('_');
    var form_name = form_name_arr[0];
    contentForm.SettingsReady(form_name).then(function(d){
      settingsRaw = d.data;
      //console.log(settingsRaw);
      for (var k in settingsRaw) {
        if(settingsRaw[k]['#type'] == 'fieldset' || settingsRaw[k]['#type'] == 'actions' || settingsRaw[k]['#type'] == 'hidden') {
          $s.formSettings[k] = settingsRaw[k];
          console.log($s.formSettings[k]);
        }
        else if(settingsRaw[k]['type'] != 'undefined') {
          $s.formElements[k] = settingsRaw[k];
        }
      }

      /*if (typeof settingsRaw.form_id['#value'] !== 'undefined') {
        if(settingsRaw.form_id['#value'] == 'page_node_form') {
          var attributes;
          for (var k in settingsRaw) {
            if(settingsRaw[k]['#type'] != 'fieldset' && settingsRaw[k]['#type'] != 'actions' && settingsRaw[k]['#type'] != '' && settingsRaw[k]['#type'] != 'hidden') {
              console.log(settingsRaw[k]);
              for (var j in settingsRaw[k]) {
                if (j.indexOf('#') === 0 && (j != '#default_value')) {
                  //console.log(settingsRaw[k][j]);
                  var attr = j.substr(1, j.length);
                  //console.log(attr);
                  //console.log(settingsRaw[k]);
                  attributes[attr] = settingsRaw[k][j];
                  console.log(attributes[attr]);
                }
              }
            }
            //$s.formElements[k] = settingsRaw[attr];
            /*if ($s.formElements[k].type == 'submit') {
              $s.showSaveButton = false;
            }
          }
        }
      }*/


    });


  }]);

})()
