<a class="btn btn-success" href="ti_projetos/adicionarProjeto">
    <i class="fa fa-fw fa-plus"></i>
    Novo Projeto
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Projetos
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>
            &nbsp
            <a data-toggle="collapse" href="#filtro">Filtrar</a>         
        </small>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <?= form_open('ti_projetos/listar', 'method="GET"'); ?>
            
            <div class="row">
                <div class="col-sm-3">
                    <label>Código</label>
                    <?= campo_filtro($filtro, 'ID', 'where'); ?>
                </div>
                <div class="col-sm-3">
                    <label>Nome</label>
                    <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                </div>
                <div class="col-sm-3">
                    <label>Descrição</label>
                    <?= campo_filtro($filtro, 'DESCRICAO', 'like'); ?>
                </div>
            </div>
            <br>
            <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            <?= form_close(); ?>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php
        if (!empty($lista)){
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item){
                        ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td>
                                <?= $item->DESCRICAO; ?>
                            </td>
                            <td align="right">
                                <a href="ti_projetos/editarProjeto/<?= $item->ID; ?>" 
                                   type="button" 
                                   class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <a id="btnExcluir" 
                                   type="button" 
                                   class="btn btn-xs btn-default"
                                   data-toggle="modal" 
                                   data-target="#modalExcluir">
                                    <i class="fa fa-fw fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
</div>
<div>
	<div id="bingo"></div>
</div>
      
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>

<script>
	$(document).ready(function(){
		//Abre página modal para confirmar exclusão
		$("#btnExcluir").click(function(){
			$.post("_generico/modalExcluir.php",{}, 
				function(response, status){ //Callback (obrigatório)
		    		$("#bingo").html(response); //"response" recebe o que foi escrito no echo do php acima
		     		$("#form")[0].reset();
		     	}
	     	);
		});
	});
</script>