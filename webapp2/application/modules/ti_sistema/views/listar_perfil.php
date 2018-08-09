<a class="btn btn-success btn-block-mobile" href="ti_sistema/adicionar_perfil">
    <i class="fa fa-fw fa-plus"></i>
    Novo perfil
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Listagem de perfis
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
                        <th>Ativo</th>
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
                                <?= tag_ativo($item->ATIVO); ?>
                            </td>
                            <td align="right">         
                                <a href="ti_sistema/editar_perfil/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>       
                                <a href="ti_sistema/excluir_perfil/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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