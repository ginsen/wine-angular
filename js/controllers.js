function mainCtrl(Tag, Response, Report, Culture, $timeout, $scope) {

  /**
   * Init values
   */
  $scope.modalUser = { tags: [] };   /** Tags selected in modal tags */
  $scope.filtroSearch = 'all';       /** Choice filter */
  $scope.finder = [];                /** Finder input */
  $scope.countResultExactly = 0;     /** Counter templates, it increment when the template has exactly tags of finder */
  $scope.countResultAproximate = 0;  /** Counter templates, it increment when the template has some tag of finder but not exactly the same tags */
  $scope.respSelect = {};            /** Template selected */
  $scope.templateUsed = {};          /** Template used to respSelect */
  $scope.newCulture = '';
  $scope.limitRows = 3;
  $scope.defaultParams = {};
  $scope.mainAlert = { isShown: false };
  $scope.modalAlert = { isShown: false };

  function showModalAlert(alertType, message) {
    $scope.modalAlert.message = message;
    $scope.modalAlert.isShown = true;
    $scope.modalAlert.alertType = alertType;
  }

  $scope.closeMainAlert = function() {
    $scope.mainAlert.isShown = false;
  };

  $scope.closeModalAlert = function() {
    $scope.modalAlert.isShown = false;
  };

  /** html partials */
  $scope.modalTags = 'tpl/modalTags.php';
  $scope.bodyModalTags = 'tpl/bodyModalTags.html';
  $scope.newTemplate = 'tpl/modalTemplate.php';


  /**
   * @var selectize selec
   */
  var selec = angular.element('#select-tags').selectize({
    plugins: ['remove_button', 'optgroup_columns'],
    delimiter: ',',
    maxItems: null,
    valueField: 'id',
    labelField: 'name',
    searchField: 'name',
    optgroups: [],
    options: [],
    create: true,
    optgroupField: 'family_id',
    optgroupLabelField: 'family',
    optgroupValueField: 'id',
    render: {
      optgroup_header: function(data, escape) {
        return '<div class="optgroup-header"><span style="font-size: 1.6em;">' + escape(data.family) + '</span></div>';
      },
      optgroup: function(data) {
        return '<div class="optgroup fixoptgroup">' + data.html + '</div>';
      }
    }
  });


  /** consumer apiRest Tags */
  $scope.families = Tag.api.query(function(familia) {
    familia.forEach(function(fam) {
      selec[0].selectize.addOptionGroup(fam.id, {
        id: fam.id,
        family: fam.family
      });
      fam.tags.forEach(function(tag) {
        selec[0].selectize.addOption({
          id: tag.id,
          name: tag.name,
          family_id: tag.family_id
        });
      }, selec);
    }, selec);
  });


  $scope.setParams = function(params) {
    $scope.defaultParams = params;
    return true;
  };



  /**
   * When click on 'Select tags' button, it open the tags modal.
   */
  $scope.openModalTags = function() {
    $scope.modalUser.tags = [];
    $scope.modalUser.tags = selec[0].selectize.getValue();
  };


  /**
   * When close the tags modal
   */
  $scope.closeModalTags = function()
  {
    $timeout(function() {
      if($scope.modalUser.tags.length > 0) {
        selec[0].selectize.clear();
        selec[0].selectize.setValue($scope.modalUser.tags);
      }
    }, 1);
  };


  /**
   * When add or change tags or text value in searcher input.
   * @model $scope.searcher
   */
  $scope.searchTemplates = function()
  {
    $scope.countResultExactly = 0;
    $scope.countResultAproximate = 0;
    $scope.respSelect = {};

    var t = '';
    $scope.finder.forEach(function(tag) { t = t + tag + ','; }, t);

    var params = {
      tags: t.substring(0, t.length - 1),
      filtro: $scope.filtroSearch
    };

    /** consumer apiRest Response */
    $scope.responses = Response.apiGet.get(params, {}, function(response) {
      response.forEach(function(resp) {
        resp.tags = resp.tags.split('||');
        if (resp.exactly === 1) {
          $scope.countResultExactly++;
        } else {
          $scope.countResultAproximate++;
        }
      });
    });
  };


  /**
   * When click on template list
   * @param resp
   */
  $scope.setTemplate = function(resp) {
    $scope.templateUsed = angular.copy(resp);
    $scope.respSelect = angular.copy(resp);
  };


  /**
   * When click on template list button trash
   * @param resp
   */
  $scope.deleteTemplate = function(resp)
  {
    Response.apiDel.delete({id: resp.id });
    $timeout($scope.searchTemplates(), 1);
  };


  /**
   * Apply special class if the template is exactly matched with the tags of the finder
   */
  $scope.applyInfoClass = function(val) {
    return (val === 1) ? 'info h4' : '';
  };


  /**
   * when is modify the text area of form template or text area is empty
   */
  function openModalNewTemplate()
  {
    $scope.modalUser.tags = [];
    $scope.modalUser.tags = selec[0].selectize.getValue();
    $scope.cultures = Culture.api.query();
    $scope.respSelect.new_culture = $scope.defaultParams.culture;
    $scope.respSelect.extra_params = $scope.defaultParams;

    angular.element('#templateModal').modal('show');
  }


  /**
   * Save report on data base.
   * @param reportParams
   * @param refresh
   */
  function saveReport(reportParams, refresh)
  {
    Report.api.save({}, reportParams, function(res) {
      if (res.hasOwnProperty('id')) {
        if (refresh === true) {
          window.location = '/?siguiente=1';
        }
      }
    });
  }


  /**
   * Determine if it open modal new template, or it save the form values as report
   */
  function submitGenerarSiguiente()
  {
    if (angular.equals({}, $scope.templateUsed)) {
      openModalNewTemplate();
    }
    else {
      if (angular.equals($scope.templateUsed, $scope.respSelect))
      {
        var reportParam = {
          template_id: $scope.respSelect.id,
          culture: $scope.defaultParams.culture,
          region: $scope.respSelect.region,
          comment: $scope.respSelect.comment,
          advice: $scope.respSelect.advice
        };
        saveReport(reportParam, true);
      }
      else {
        openModalNewTemplate();
      }
    }
  }


  /**
   * Save template without report
   */
  function submitGuardarSinPlantilla() {
    var reportParam = {
      template_id: null,
      culture: $scope.defaultParams.culture,
      region: $scope.respSelect.region,
      comment: $scope.respSelect.comment,
      advice: $scope.respSelect.advice
    };

    saveReport(reportParam, true);
  }

  /**
   * When click on "Generar, siguiente" green button, or click on "Guardar sin plantilla" gray button
   * @param event
   */
  $scope.submitResponse = function(event)
  {
    var action = event.target.value;
    switch (action)
    {
      case "generarSiguiente" :
        submitGenerarSiguiente();
        break;

      case "guardarSinPlantilla" :
        submitGuardarSinPlantilla();
        break;
    }
  };


  function saveTemplateSaveReportClose() {
    var templateParams = {
      culture: $scope.respSelect.new_culture,
      region: $scope.respSelect.region,
      comment: $scope.respSelect.comment,
      advice: $scope.respSelect.advice,
      tags: $scope.modalUser.tags
    };

    Response.api.save({}, templateParams, function(templ) {
      if (templ.hasOwnProperty('template_id')) {
        var reportParams = {
          template_id: templ.template_id,
          culture: templ.culture,
          region: templ.region,
          comment: templ.comment,
          advice: templ.advice
        };
        saveReport(reportParams, true);
      }
    });
  }


  function saveOnlyTemplate()
  {
    var templateParams = {
      culture: $scope.respSelect.new_culture,
      region: $scope.respSelect.region,
      comment: $scope.respSelect.comment,
      advice: $scope.respSelect.advice,
      tags: $scope.modalUser.tags
    };
    Response.api.save({}, templateParams, function(templ) {
      if (templ.hasOwnProperty('template_id')) {
        showModalAlert('alert-warning', 'Se ha guardado la plantilla');
        $scope.respSelect.region = '';
        $scope.respSelect.comment = '';
        $scope.respSelect.advice = '';
        $scope.modalUser.tags = [];
      }
    });
  }



  $scope.saveNewTemplate = function(event)
  {
    var action = event.target.value;
    switch (action)
    {
      case "saveTemplateSaveReportClose" :
        saveTemplateSaveReportClose();
        break;

      case "saveOnlyTemplate" :
        saveOnlyTemplate();
        break;
    }
  };

}
mainCtrl.$inject = ['Tag', 'Response', 'Report', 'Culture', '$timeout', '$scope'];
