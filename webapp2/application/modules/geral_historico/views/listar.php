<div class="panel panel-default">
    <div class="panel-heading">
        Histórico
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
                        <th>IP</th>
                        <th>Tela</th>
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
                                <?= $item->IP; ?>
                            </td>
                            <td>
                                <?= link_amigavel($item->URI); ?>
                            </td>
                            <td align="right">
                                <?php
                                if (!empty($item->INPUT_GET) || !empty($item->INPUT_POST))
                                {
                                    ?>
                                    <a type="button" 
                                       class="btn btn-xs btn-default" 
                                       title="<?= $item->URI; ?>"
                                       data-toggle="popover" 
                                       data-placement="left"
                                       data-content='<pre><?= str_replace("'", '"', $item->INPUT_GET . $item->INPUT_POST); ?></pre>'>
                                        <i class="fa fa-fw fa-database"></i>
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