<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width: 900px">
    <div class="modal-content">

      <!-- Header-->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-bookmark"></span>&ensp;<?php echo _('Añadir plantilla')?></h4>
      </div>

      <form role="form" name="newTemplateForm" ng-model="newTemplateForm" ng-submit="saveNewTemplate($event)">

        <!-- Body -->
        <div class="modal-body">
          <div ng-include="bodyModalTags"></div>


          <div class="row">
            <div class="col-sm-12 col-lg-12">
              <table class="table table-hover">
                <tbody>
                <tr>
                  <td>
                    <div class="form-group">
                      <label style="width: 100%;"><?php echo _('Región') ?>
                        <textarea rows="9" class="form-control" ng-model="respSelect.region" style="font-weight: normal" required>{{ respSelect.region }}</textarea>
                      </label>
                    </div>
                  </td>
                  <td>
                    <div class="form-group">
                      <label style="width: 100%;"><?php echo _('Comentarios') ?>
                        <textarea rows="9" class="form-control" ng-model="respSelect.comment" style="font-weight: normal" required>{{ respSelect.comment }}</textarea>
                      </label>
                    </div>
                  </td>
                  <td>
                    <div class="form-group">
                      <label style="width: 100%;"><?php echo _('Recomendaciones') ?>
                        <textarea rows="9" class="form-control" ng-model="respSelect.advice" style="font-weight: normal" required>{{ respSelect.advice }}</textarea>
                      </label>
                    </div>
                  </td>
                  <td>
                    <div class="form-group">
                      <label style="width: 100%;"><?php echo _('Idioma') ?></label>
                      <div ng-repeat="cul in cultures">
                        <input type="radio" ng-model="respSelect.new_culture" name="culture" ng-value="cul.culture" /> {{ cul.language }}
                      </div>
                    </div>
                  </td>
                </tr>
                </tbody>
              </table>
              <div class="row">
                <div class="alert {{modalAlert.alertType}}" ng-show="modalAlert.isShown">
                  <button type="button" class="close" ng-click="closeModalAlert()" aria-hidden="true">&times;</button>
                  {{modalAlert.message}}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-warning" data-dismiss="modal"><?php echo _('Cerrar') ?></button>
          <button type="button" ng-click="saveNewTemplate($event)" value="saveOnlyTemplate" class="btn btn-primary"><?php echo _('Guardar solo plantilla'); ?></button>
          <button type="submit" ng-click="saveNewTemplate($event)" value="saveTemplateSaveReportClose" class="btn btn-primary"><?php echo _('Guardar plantilla, crear informe y cerrar') ?></button>
        </div>

      </form>
    </div>
  </div>
</div>