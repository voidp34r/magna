<a class="btn btn-success" href="frotas_agregados/adicionar_contrato">
    <i class="fa fa-fw fa-plus"></i>
    Novo contrato
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Contratos
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
                        <th>Data/Hora</th>
                        <th>Modelo</th>
                        <th>Nome</th>
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
                                <?= data_oracle_para_web($item->DATAHORA); ?>
                            </td>
                            <td>
                                <?= $item->CHAVE; ?>
                            </td>
                            <td>
                                <?= $item->NOME; ?>
                            </td>
                            <td align="right">
                                <a href="frotas_agregados/editar_contrato/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
                                <a href="frotas_agregados/gerar_contrato/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default" target="_blank">
                                    <i class="fa fa-fw fa-print"></i>
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