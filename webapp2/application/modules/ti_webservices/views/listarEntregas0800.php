<div class="panel panel-default">
    <div class="panel-heading">
        Resumo das requisições
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
    </div>
    <div class="panel-body table-responsive"><?php
        if (!empty($lista)) {?>
            <table class="table table-hover">
                <thead>
                    <tr>
                    	<th>ID</th>
                    	<th>Método</th>
                    	<th>Descrição</th>
						<th>Início</th>
						<th>Fim</th>	
                        <th>Duração</th>
                        <th>Detalhes</th>
                        <th>Retorno</th>
                    </tr>
                </thead>
                <tbody><?php
                    foreach ($lista as $item) {?>
                        <tr>
                            <td><?= $item->ID; ?></td>
                            <td><?= $item->DSMETODO; ?></td>
                            <td><?= $item->DSDESCRICAO; ?></td>
                            <td><?= empty($item->DHINI) ? '' : getDateTimeMicro($item->DHINI)->format('d/m/Y H:i:s'); ?></td>
                            <td><?= empty($item->DHFIM) ? '' : getDateTimeMicro($item->DHFIM)->format('d/m/Y H:i:s'); ?></td>
                            <td><?= empty($item->DHFIM) ? 'Processando' : round($item->DHFIM - $item->DHINI, 4).' s'; ?></td>
                            <td align="center"><?php
                                if (!empty($item->DSREQ) || !empty($item->DSRET)){?>
                                    <a type="button" 
                                       class="btn btn-xs btn-default"
                                       onclick="openDetalhesModal(<?= $item->ID; ?>)">
                                       <i class="fa fa-fw fa-database"></i>
                                    </a><?php
                                }?>
                            </td>
                            <td align="center">
                            	<?= $item->CDRET == 1 ? '<i class="fa fa-check"></i>' : ($item->CDRET == 0 ? '<i class="fa fa-times"></i>' : '')?>
                            </td>
                        </tr><?php
                    }?>
                </tbody>
            </table><?php
        }?>
    </div>
</div>

<!-- Modal Detalhes da requisição  -->
<div id="requisInfoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="requisInfoModal" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	<span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title reqModalTitle" id="requisInfoModalTitle"></h4>
      </div>
      <div class="modal-body">
        <form action="" id="form-notes-event" name="form-notes-event" class="form-horizontal" method="post">
            <div class="form-horizontal">
                <fieldset>
                    <div class="form-group">
		                <div class="col-sm-12"> 
		                	<label for="requisicaoModalText">Requisição</label>
		                    <textarea readonly class="form-control" rows="4" id="requisicaoModalText"></textarea>
		                </div>
	                </div>
                    <div class="form-group">
		                <div class="col-sm-12"> 
		                	<label for="retornoModalText">Retorno</label>
		                    <textarea readonly class="form-control" rows="6" id="retornoModalText"></textarea>
		                </div>
	                </div>
                </fieldset>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>
<script>
	$(document).ready(function() {
		$("#requisInfoModal").on("shown.bs.modal", function(){
			console.log("open modal");
		});
	});

	var lastLogId = null;

	setInterval(function (){
		$.getJSON('ti_webservices/checkLog/', '', function (data) {
			console.log(data);

			if (lastLogId == null){
				lastLogId = data;
			} else if (data != lastLogId){
				location.reload();
			}

			return true;
        });
	}, 10000);
	
	function openDetalhesModal(id){
		$.get( "ti_webservices/getDetalhesRequisicaoJson/"+id, function(data) {
			$("#requisicaoModalText").val(data.DSREQ);
			$("#requisicaoModalText").val(data.DSREQ);
			$("#retornoModalText").val(data.DSRET);
			$(".reqModalTitle").text("Detalhes da requisição (cod. "+id+")");
			
			$("#requisInfoModal").modal();
		}, "json" );
	}
</script>










