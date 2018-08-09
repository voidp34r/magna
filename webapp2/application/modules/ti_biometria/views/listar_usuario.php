<a class="btn btn-success" href="ti_biometria/adicionar_usuario">
    <i class="fa fa-fw fa-plus"></i>
    Novo usuário
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Usuários
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
        <a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <?= form_open('ti_biometria/listar_usuario', 'method="GET"'); ?>
            <div class="row">
                <div class="col-sm-3">
                    <label>Código</label>
                    <?= campo_filtro($filtro, 'ID', 'where'); ?>
                </div>
                <div class="col-sm-3">
                    <label>Tipo</label>
                    <?= campo_filtro($filtro, 'TIPO', 'like'); ?>
                </div>
                <div class="col-sm-3">
                    <label>CPF</label>
                    <?= campo_filtro($filtro, 'CPF', 'like'); ?>
                </div>
                <div class="col-sm-3">
                    <label>Nome</label>
                    <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                </div>
            </div>
            <br>
            <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            <?= form_close(); ?>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php
        if (!empty($lista))
        {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Tipo</th>
                        <th>CPF</th>
                        <th>Nome</th>
                        <th>Validade</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {
                        ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->TIPO; ?>
                            </td>
                            <td>
                                <?= $item->CPF; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td>
                                <?= data_oracle_para_web($item->VALIDADE); ?>
                            </td>
                            <td align="right">
                                <a href="ti_biometria/editar_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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