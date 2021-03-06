<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
        <small>
            (<?= $total; ?>) &nbsp - &nbsp <?= anchor(current_url(), 'Atualizar'); ?>
            &nbsp
            &nbsp
            <a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>
        </small>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <form action="cce_entregas0800/listarMotoristas">
                <div class="row">
                    <div class="col-sm-2">
                        <label>Nome</label>
                        <?= campo_filtro($filtro, 'DSNOME', 'like'); ?>
                    </div>
                    <div class="col-sm-2">
                        <label>CPF/CNPJ</label>
                        <?= campo_filtro($filtro, 'CPFCNPJ', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Celular Sistema</label>
                        <?= campo_filtro($filtro, 'NRCELULAR', 'like'); ?>
                    </div>
                    <div class="col-sm-2">
                    	<label>&nbsp</label>
                        <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                    </div>
                    
                </div>
               
            </form>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php if (!empty($lista)){ ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                    	<th>Nome</th>
                        <th>Celular Sistema</th>
                        <th>Celular Secundário</th>
                        <th>Tipo</th>
                        <th>CPF/CNPJ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $item){ ?>                    
                        <tr>
                            <td><?= $item->DSNOME;?></td>
                            <td><?= $item->NRCELULAR;?></td>
                            <td><?= $item->NRCELULAR2;?></td>
                            <td><?= ucfirst($item->TIPO);?></td>
                            <td><?= formata_cpf_cnpj($item->CPFCNPJ);?></td>
                            <td align="right">
                                <a href="cce_entregas0800/editarMotorista/<?= mb_strtolower($item->TIPO)."/".$item->CPFCNPJ; ?>" 
                                   type="button" 
                                   class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table> 
        <?php } ?>
    </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>

<script>
	$(document).ready(function(){		
		if ($("[name='filtro[like][DSNOME]']").val().length > 0
			|| $("[name='filtro[like][NRCELULAR]']").val().length > 0
			|| $("[name='filtro[like][CPFCNPJ]']").val().length > 0
		){
			$("#aFiltro").click();
		}
	});
</script>