<?php
if ($titulo == 'pendentes')
{
    ?>
    <a class="btn btn-success btn-block-mobile" href="sac_enviosms/disparar">
        <i class="fa fa-fw fa-send"></i>
        Disparar SMS
    </a>
    <?php
}
?>
<div class="panel panel-default">
    <div class="panel-heading">
        Listagem de SMS <?= $titulo; ?>
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>
        </small>
        <a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <form>
                <div class="row">
                    <div class="col-sm-3">
                        <label>Número</label>
                        <?= campo_filtro($filtro, 'CELULAR', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Filial de coleta</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_CDEMPRESA', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Conhecimento</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_NRDOCTOFISCAL', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Remetente</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_REMETENTE', 'like'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Filial entregadora</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_DESTINO', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Destinatário</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_DESTINATARIO', 'like'); ?>
                    </div>
                </div>
                <br>
                <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            </form>
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
                        <th colspan="3"></th>
                        <th colspan="3" class="th-border-left text-center">Coleta</th>
                        <th colspan="2" class="th-border-left th-border-right text-center">Entrega</th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th rowspan="2">Cód.</th>
                        <th>Data/Hora</th>
                        <th>Número</th>
                        <th class="th-border-left">Filial</th>
                        <th>Conhecimento</th>
                        <th>Remetente</th>
                        <th class="th-border-left">Filial</th>
                        <th>Destinatário</th>
                        <th class="th-border-left">Status</th>
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
                                <?= $item->CELULAR; ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_CDEMPRESA; ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_NRDOCTOFISCAL; ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_REMETENTE; ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_DESTINO; ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_DESTINATARIO; ?>
                            </td>
                            <td>
                                <?= ($item->STATUS) ? $item->STATUS : '<span class="label label-warning">Pendente</span>'; ?>
                            </td>
                            <td align="right">                                 
                                <a type="button" class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="left" title="<?= $item->MENSAGEM; ?>">
                                    <i class="fa fa-fw fa-comment"></i>
                                </a>
                                <?php
                                if ($titulo == 'pendentes')
                                {
                                    ?>
                                    <a href="sac_enviosms/editar/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-pencil"></i>
                                    </a>
                                    <a href="sac_enviosms/excluir/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-fw fa-trash"></i>
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
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>