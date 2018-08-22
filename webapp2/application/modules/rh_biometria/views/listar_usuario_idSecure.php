
<div class="panel panel-default">
	<div class="panel-heading">
		Usuários |
		<small>
			 Total: (<?= $total; ?>)
		</small>
		<a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
	</div>
	<div class="collapse" id="filtro">
		<div class="panel-body">
			<?= form_open('rh_biometria/listar_usuario_idSecure', 'method="GET"'); ?>
			<div class="row">
				<div class="col-sm-3">
					<label>Nome</label>
					<?= campo_filtro($filtro, 'nome', 'like'); ?>
				</div>
				<div class="col-sm-3">
					<label>CPF</label>
					<?= campo_filtro($filtro, 'cpf', 'like'); ?>
				</div>
				<!-- <div class="col-sm-3">
					<label>Status</label>
						<select name="status">
							<option value=0>Ativo</option>
							<option value=1>Inativo</option>
     			</select>
				</div> -->
			</div>
			<br>
			<input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
			<?= form_close(); ?>
		</div>
	</div>
	<div class="panel-body table-responsive">
		<?php if (!empty($lista))	{	?>
			<table class="table table-hover">
				<thead>
					<tr>
						<th>Key</th>
						<th>ID</th>
						<th>Nome</th>
						<th>CPF</th>
						<th>RG</th>
						<th>Ativo</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($lista as $key=>$item){ ?>
						<tr>
							<td>
								<?= $key; ?>
							</td>
							<td>
								<?= $item->id; ?>
							</td>
							<td>
								<?= $item->name; ?>
							</td>
							<td>
								<?= $item->cpf; ?>
							</td>
							<td>
								<?= $item->rg; ?>
							</td>
							<td>
								<?= $item->inativo; ?>
							</td>
							
							<td align="right">
								<a type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#myModal" onclick="setaDadosModal(<?=$key?>)">
									<i class="fa fa-fw fa-close"></i>
								</a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php	}	?>
	</div>
	
</div>

<script>
function setaDadosModal(valor) { 
	document.getElementById('key').value = valor;
	console.log(valor);
}
</script>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Exclusão de Usuário</h4>
      </div>
      <div class="modal-body">
				<p>ID:  <?= $lista[$key]->id ?> </p>
				<p>Usuário:  <?= $lista[$key]->name ?> </p>
				<p>CPF:  <?= $lista[$key]->cpf ?> </p>
				<a id="key"> </a>
			
      </div>
      <div class="modal-footer">
				<a href="rh_biometria/excluir_usuario/<?= $lista[$key]->id; ?>" type="button" class="btn btn-danger">
					<i class="fa fa-fw fa-pencil"></i> Excluir
				</a>
        <button type="button" class="btn btn-default" data-dismiss="modal">Sair</button>
      </div>
    </div>

  </div>
</div>