<?php
if ($metodo == 'listar_solicitacao')
{
    ?>
    <a class="btn btn-success" href="frotas_agregados/adicionar_solicitacao">
        <i class="fa fa-fw fa-plus"></i>
        Novo agregado
    </a>
    <?php
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
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
                        <th>Data</th>
                        <th>Filial</th>
                        <th>Proprietário</th>
                        <th>Frotas</th>
                        <th>Jurídico</th>
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
                                <?= data_oracle_para_web($item->DATAHORA); ?>
                            </td>
                            <td>
                                <?= !empty($filiais[$item->CDEMPRESA]) ? $filiais[$item->CDEMPRESA] : $item->CDEMPRESA; ?>
                            </td>
                            <td>
                                <?= !empty($proprietarios[$item->AGREGADO_PROPRIETARIO_ID]) ? $proprietarios[$item->AGREGADO_PROPRIETARIO_ID] : $item->AGREGADO_PROPRIETARIO_ID; ?>
                            </td>
                            <td>
                                <?= ($item->AGREGADO_STATUS_ID) ? $status[$item->AGREGADO_STATUS_ID] : 'Aguardando'; ?>
                            </td>
                            <td>
                                <?= ($item->AGREGADO_STATUS_ID_JURIDICO) ? $status[$item->AGREGADO_STATUS_ID_JURIDICO] : 'Aguardando'; ?>
                            </td>
                            <td align="right">
                                <a href="frotas_agregados/ver_solicitacao/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>