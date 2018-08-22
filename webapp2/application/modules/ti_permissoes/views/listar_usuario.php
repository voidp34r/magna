<a class="btn btn-success btn-block-mobile" href="ti_permissoes/adicionar_usuario">
    <i class="fa fa-fw fa-plus"></i>
    Adicionar
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Listagem de usuários
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>
            &nbsp
            <a data-toggle="collapse" href="#filtro">Filtrar</a>         
        </small>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <form>
                <div class="row">
                    <div class="col-sm-3">
                        <label>Nome</label>
                        <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Usuário</label>
                        <?= campo_filtro($filtro, 'USUARIO', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>E-mail</label>
                        <?= campo_filtro($filtro, 'EMAIL', 'like'); ?>
                    </div>
                </div>
                <br>
                <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            </form>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php
	        if (!empty($lista)){?>
	            <table class="table table-hover">
	                <thead>
	                    <tr>
	                        <th>Cód.</th>
	                        <th>Usuário</th>
	                        <th>Nome</th>
	                        <th>E-mail</th>
	                        <th></th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <?php
	                    	foreach ($lista as $item){?>
		                        <tr>
		                            <td><?= $item->ID; ?></td>
		                            <td><?= strtolower(str_replace('transmagna\\', '', $item->USUARIO)); ?></td>
		                            <td><?= upperFirsts($item->NOME); ?></td>
		                            <td><?= $item->EMAIL; ?></td>
		                            <td align="right">     
		                                <a href="ti_permissoes/editar_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
		                                    <i class="fa fa-fw fa-pencil"></i>
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
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>