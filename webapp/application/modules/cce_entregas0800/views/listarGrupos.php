<div class="panel panel-default">
    <div class="panel-heading">
        Grupos de Clientes
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
        &nbsp
        &nbsp
        <small>
            <a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>
        </small>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <form action="cce_entregas0800/listar_grupos_empresa/<?=$page?>">
            <div class="row">
                <div class="col-sm-2">
                    <label>Código do Grupo</label>
                    <?= campo_filtro($filtro, 'CDGRUPOCLIENTE', 'where'); ?>
                </div>
                <div class="col-sm-4">
                    <label>Nome do Grupo</label>
                    <?= campo_filtro($filtro, 'DSGRUPOCLIENTE', 'like'); ?>
                </div>
                <div class="col-sm-2">
                    <label>&nbsp</label>
                    <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                </div>
            </div>
                    
        </div>
    </div

    <div class="panel-body table-responsive">
        <?php
        if (!empty($lista)) {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código do Grupo</th>
                        <th>Descrição</th>
                        <th>Estado atual</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item) {
                        ?>
                            <tr>
                                <td>
                                <?= $item->CDGRUPOCLIENTE ?> 
                                </td>
                                <td>
                                    <?= $item->DSGRUPOCLIENTE ?>
                                </td>
                                <td>
                                <?= $item->FGATIVO ?>
                                </td>
                                <td>
                                    <a href="cce_entregas0800/editar_grupo_cliente/<?= $item->CDGRUPOCLIENTE; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-folder-open"></i>
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
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</div>

