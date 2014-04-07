<div class="modal fade" id="tagsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width: 900px">
    <div class="modal-content">

      <!-- Header-->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-tags"></span>&ensp;<?php echo _('Añadir siguientes TAGS')?></h4>
      </div>

      <!-- Body -->
      <div class="modal-body" ng-include="bodyModalTags"></div>

      <!-- Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-warning" data-dismiss="modal"><?php echo _('Cancelar') ?></button>
        <button type="button" class="btn btn-primary" ng-click="closeModalTags()" data-dismiss="modal"><?php echo _('Aceptar') ?></button>
      </div>

    </div>
  </div>
</div>