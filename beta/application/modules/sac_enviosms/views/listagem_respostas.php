<div class="panel panel-default">
    <div class="panel-heading">
        Listagem de respostas
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
                        <label>Empresa</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_CDEMPRESA', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Documento Fiscal</label>
                        <?= campo_filtro($filtro, 'SOFTRAN_NRDOCTOFISCAL', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Número</label>
                        <?= campo_filtro($filtro, 'CELULAR', 'where'); ?>
                    </div>
                    <div class="col-sm-3">
                        <label>Status</label>
                        <?= campo_filtro($filtro, 'STATUS', 'where'); ?>
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
                        <th>Cód.</th>
                        <th>Respondido em</th>
                        <th>Conhecimento</th>
                        <th>Número</th>
                        <th>Resposta</th>
                        <th></th>
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
                                <?= data_oracle_para_web($item->RESPOSTA_DATAHORA); ?>
                            </td>
                            <td>
                                <?= $item->SOFTRAN_CDEMPRESA . '-' . $item->SOFTRAN_NRDOCTOFISCAL; ?>
                            </td>
                            <td>
                                <?= $item->CELULAR; ?>
                            </td>
                            <td>
                                <?= $item->RESPOSTA; ?>
                            </td>
                            <td>
                                <?= ($item->RESPONDIDO) ? '<span class="label label-primary">Respondido</span>' : ''; ?>
                            </td>
                            <td align="right">                                 
                                <a type="button" class="btn btn-xs btn-default" data-toggle="tooltip" data-placement="left" title="<?= $item->MENSAGEM; ?>">
                                    <i class="fa fa-fw fa-comment"></i>
                                </a>
                                <a href="sac_enviosms/responder/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-send"></i>
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