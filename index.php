<?php
session_cache_limiter(false);
session_start();
$culture = $_SESSION['culture'] = 'es';
?>
<!DOCTYPE html>
<html class="no-js" lang="es" ng-app="reviews">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Wine angular</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="bower_components/selectize/dist/css/selectize.css">
    <link rel="stylesheet" href="bower_components/selectize/dist/css/selectize.bootstrap3.css">
    <link rel="stylesheet" href="css/main.css">
  </head>

  <body ng-controller="mainCtrl">
  <div class="container" ng-show="setParams({
          culture: '<?php echo $culture ?>'
        })">

    <div class="row margin-btn-10">
      <div class="col-sm-10 col-lg-10">
        <button type="button" data-toggle="modal" ng-click="openModalTags()" data-target="#tagsModal" class="btn btn-primary">
          <span class="glyphicon glyphicon-tags"></span>&ensp;
          <?php echo _('Seleccionar tags')?>
        </button>
        <!-- Modal Tags-->
        <div ng-include="modalTags"></div>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-10 col-lg-10">
        <select id="select-tags" ng-model="finder" ng-change="searchTemplates()" multiple class="form-control" placeholder="<?php echo _('buscar tags o textos')?>"></select>
      </div>
      <div class="col-sm-2 col-lg-2">
        <select class="form-control" ng-change="searchTemplates()" ng-model="filtroSearch">
          <option value="all" ng-selected="true"><?php echo _('Buscar en todos')?></option>
          <option value="region"><?php echo _('Región')?></option>
          <option value="comment"><?php echo _('Comentarios')?></option>
          <option value="advice"><?php echo _('Recomendaciones')?></option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="alert {{mainAlert.alertType}}" ng-show="mainAlert.isShown">
        <button type="button" class="close" ng-click="closeAlert()" aria-hidden="true">&times;</button>
        {{mainAlert.message}}
      </div>
    </div>

    <form role="form" name="responseForm" ng-submit="submitResponse($event)">
      <div class="row">
        <div class="col-sm-12 col-lg-12">
          <table class="table table-hover">
            <thead>
              <tr>
                <th><?php echo _('Región') ?></th>
                <th><?php echo _('Comentarios') ?></th>
                <th><?php echo _('Recomendaciones') ?></th>
                <th style="width: 150px;">
                  <div class="small" style="font-weight: normal">
                    <ng-pluralize count="countResultExactly" when="{'0': '<?php echo _('Patrón no encontrado') ?>',
                                         'one': '{} <?php echo _('Result. exacto') ?>',
                                         'other': '{} <?php echo _('Result. exactos') ?>'}">
                    </ng-pluralize>
                  </div>
                  <div class="small" style="font-weight: normal">
                    <ng-pluralize count="countResultAproximate" when="{'0': '',
                                         'one': '{} <?php echo _('Result. aproximado') ?>',
                                         'other': '{} <?php echo _('Result. aproximados') ?>'}">
                    </ng-pluralize>
                  </div>

                  <div>
                    <select class="form-control input-sm" ng-model="limitRows">
                      <option value="3" ng-selected="true"><?php echo _('Ver 3')?></option>
                      <option value="10"><?php echo _('Ver 10')?></option>
                      <option value="1000"><?php echo _('ver todos')?></option>
                    </select>
                  </div>

                </th>
              </tr>
            </thead>
            <tbody>
              <tr ng-repeat="resp in responses | limitTo: limitRows" ng-class="applyInfoClass(resp.exactly)" ng-click="setTemplate(resp)">
                <td>{{ resp.region }}</td>
                <td>{{ resp.comment }}</td>
                <td>{{ resp.advice }}</td>
                <td class="text-right">
                  <p>{{ resp.used }} <?php echo _('veces') ?></p>
                  <div><button type="button" class="btn btn-danger btn-xs" confirmed-click="deleteTemplate(resp)"
                               ng-confirm-click="<?php echo _('Estás seguro de querer borrar esta plantilla') ?>"><span class="glyphicon glyphicon-trash"></span></button></div>
                </td>
              </tr>
            </tbody>
            <tfoot class="animate-hide">
            <tr>
              <td>
                <div class="form-group">
                  <label style="width: 100%;"><?php echo _('Región') ?>
                    <textarea rows="9" class="form-control" ng-model="respSelect.region" style="font-weight: normal">{{ respSelect.region }}</textarea>
                  </label>
                </div>
              </td>
              <td>
                <div class="form-group">
                  <label style="width: 100%;"><?php echo _('Comentarios') ?>
                    <textarea rows="9" class="form-control" ng-model="respSelect.comment" style="font-weight: normal">{{ respSelect.comment }}</textarea>
                  </label>
                </div>
              </td>
              <td>
                <div class="form-group">
                  <label style="width: 100%;"><?php echo _('Recomendaciones') ?>
                    <textarea rows="9" class="form-control" ng-model="respSelect.advice" style="font-weight: normal">{{ respSelect.advice }}</textarea>
                  </label>
                </div>
              </td>
              <td>
                <div class="form-group">
                  <label style="width: 100%;"><?php echo _('Tags') ?></label>
                  <div style="font-size: 5px; color: white;">{{ respSelect.tags }}</div>
                  <div class="tagTemplate" ng-repeat="tagsTmpl in respSelect.tags">{{ tagsTmpl }}</div>
                </div>
              </td>
            </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-2 col-lg-offset-8 text-right">
          <button type="button" ng-click="submitResponse($event)" value="guardarSinPlantilla" class="btn btn-default"><?php echo _('Guardar sin plantilla')?>&ensp; <span class="glyphicon glyphicon-arrow-right"></span></button>
        </div>
        <div class="col-lg-2 text-right">
          <button type="button" ng-click="submitResponse($event)" value="generarSiguiente" class="btn btn-success"><?php echo _('Crear o Siguiente')?>&ensp; <span class="glyphicon glyphicon-arrow-right"></span></button>
        </div>
      </div>
    </form>

    <!-- Modal new template -->
    <div ng-include="newTemplate"></div>
  </div>

  <! -- vendors -->
  <script src="bower_components/jquery/dist/jquery.js"></script>
  <script src="bower_components/selectize/dist/js/standalone/selectize.js"></script>
  <script src="bower_components/bootstrap/dist/js/bootstrap.js"></script>
  <script src="bower_components/angular/angular.js"></script>
  <script src="bower_components/angular-resource/angular-resource.js"></script>

  <!-- Sustituir por los anteriores al pasar a produccion -->
<!--  <script src="bower_components/jquery/dist/jquery.min.js"></script>-->
<!--  <script src="bower_components/selectize/dist/js/standalone/selectize.min.js"></script>-->
<!--  <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>-->
<!--  <script src="bower_components/angular/angular.min.js"></script>-->
<!--  <script src="bower_components/angular-resource/angular-resource.min.js"></script>-->

  <script src="bower_components/checklist-model/checklist-model.js"></script>

  <script src="js/app.js"></script>
  <script src="js/services.js"></script>
  <script src="js/directives.js"></script>
  <script src="js/controllers.js"></script>
  </body>
</html>
