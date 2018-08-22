<?php
if ($metodo == 'listar_pendente') {
    ?>
    <a class="btn btn-success" href="geral_tarefa/adicionar">
        <i class="fa fa-fw fa-plus"></i>
        Nova tarefa
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
        if (!empty($lista)) {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Data/Hora</th>
                        <?php
                        if ($metodo != 'listar_criadas') {
                            ?>
                            <th>Prazo</th>
                            <th>Remetente</th>
                            <?php
                        }
                        if ($metodo == 'listar_criadas') {
                            ?>
                            <th>Destinatário</th>
                            <?php
                        }
                        ?>
                        <th>Descrição</th>
                        <?php
                        if ($metodo != 'listar_pendente') {
                            ?>
                            <th>Concluído em</th>
                            <th>Conclusão</th>
                            <?php
                        }
                        ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item) {
                        $prazo = data_oracle_para_web($item->PRAZO);
                        $dias = dias_entre_datas($prazo);
                        ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= data_oracle_para_web($item->DATAHORA_CRIACAO); ?>
                            </td>
                            <?php
                            if ($metodo != 'listar_criadas') {
                                ?>
                                <td>
                                    <?= label_dias($dias, $prazo); ?>
                                </td>
                                <td>
                                    <?= $usuarios[$item->REMETENTE_USUARIO_ID]; ?>
                                </td>
                                <?php
                            }
                            if ($metodo == 'listar_criadas') {
                                ?>
                                <td>
                                    <?= $usuarios[$item->DESTINATARIO_USUARIO_ID]; ?>
                                </td>                                
                                <?php
                            }
                            ?>
                            <td>
                                <?= nl2br($item->DESCRICAO); ?>
                            </td>
                            <?php
                            if ($metodo != 'listar_pendente') {
                                ?>
                                <td>
                                    <?= data_oracle_para_web($item->ENTREGA_DATAHORA); ?>
                                </td>
                                <td>
                                    <?= nl2br($item->ENTREGA_DESCRICAO); ?>
                                </td>
                                <?php
                            }
                            ?>
                            <td align="right">       
                                <?php
                                if ($item->REMETENTE_USUARIO_ID == $usuario_id && empty($item->ENTREGA_DATAHORA)) {
                                    ?>
                                    <a href="geral_tarefa/editar/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <?php
                                }
                                if (!empty($item->LINK)) {
                                    ?>
                                    <a href="<?= $item->LINK; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-link"></i>
                                    </a>
                                    <?php
                                } else if (empty($item->ENTREGA_DATAHORA)) {
                                    ?>
                                    <a href="geral_tarefa/concluir/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-check"></i>
                                    </a>
                                    <?php
                                }
                                ?>
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