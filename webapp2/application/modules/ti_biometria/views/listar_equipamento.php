<a class="btn btn-success" href="ti_biometria/adicionar_equipamento">
    <i class="fa fa-fw fa-plus"></i>
    Novo equipamento
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Equipamentos
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
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
                        <th>Nome</th>
                        <th>Filial</th>
                        <th>IP</th>
                        <th>Padrão</th>
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
                                <img src="assets/img/<?= $item->TIPO; ?>.png" data-toggle="tooltip" title="<?= $item->TIPO; ?>">
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td>
                                <?= $item->CDEMPRESA; ?>
                            </td>
                            <td>
                                <?= $item->IP; ?>
                            </td>
                            <td>
                                <?= $item->PADRAO; ?>
                            </td>
                            <td align="right">
                                <a href="ti_biometria/editar_equipamento/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <a href="ti_biometria/excluir_equipamento/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>