<a class="btn btn-success btn-block-mobile" href="ti_sistema/adicionar_gatilho">
    <i class="fa fa-fw fa-plus"></i>
    Novo gatilho
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Listagem de gatilhos
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
    </div>
    <div class="panel-body table-responsive">
        <?php
        if (!empty($lista)) {
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
                    foreach ($lista as $item) {
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
                                <a href="ti_sistema/editar_gatilho/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>       
                                <a href="ti_sistema/excluir_gatilho/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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