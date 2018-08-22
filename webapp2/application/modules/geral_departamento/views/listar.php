<a class="btn btn-success" href="geral_departamento/adicionar">
    <i class="fa fa-fw fa-plus"></i>
    Novo departamento
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Departamentos
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
                        <th>Departamento pai</th>
                        <th>Nome</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item) {
                        ?>
                        <tr <?=(!$item->ATIVO) ? 'class="text-line"' : ''; ?>>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->GERAL_DEPARTAMENTO_ID; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td align="right">       
                                <a href="geral_departamento/editar/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>     
                                <a href="geral_departamento/excluir/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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