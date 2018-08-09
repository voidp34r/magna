<div class="panel panel-default">
    <div class="panel-heading">
        Checklists
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
                        <th>Data/Hora</th>
                        <th>Filial</th>
                        <th>Motorista</th>
                        <th>Observações</th>
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
                                <?= $item->DATAHORA; ?>
                            </td>
                            <td>
                                <?= $item->MOTORISTA_CDEMPRESA; ?>
                            </td>
                            <td>
                                <?= $item->MOTORISTA_NOME; ?>
                            </td>
                            <td>
                                <?= $item->OBSERVACOES; ?>
                            </td>
                            <td align="right">
                                <a href="frotas_portaria/ver_checklist/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
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