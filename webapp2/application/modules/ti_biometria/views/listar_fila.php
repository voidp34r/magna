<a class="btn btn-success" href="ti_biometria/processar_fila">
    <i class="fa fa-fw fa-refresh"></i>
    Processar fila
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Fila
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
                        <th>Equipamento</th>
                        <th>Data/Hora</th>
                        <th>Função</th>
                        <th>Comando</th>
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
                                <?= $equipamentos[$item->BIOMETRIA_EQUIPAMENTO_ID]; ?>
                            </td>
                            <td>
                                <?= data_oracle_para_web($item->DATAHORA); ?>
                            </td>
                            <td>
                                <?= $item->FUNCAO; ?>
                            </td>
                            <td>
                                <?= $item->COMANDO; ?>
                            </td>
                            <td align="right">
                                <a href="ti_biometria/excluir_fila/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default btn-confirm">
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