<a class="btn btn-success" href="ti_noticia/adicionar_noticia">
    <i class="fa fa-fw fa-plus"></i>
    Nova notícia
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Notícias
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
                            <th>Título</th>
                            <th>Noticia</th>
                            <th>Mobile</th>
                            <th>Webapp</th>
                            <th>Data da publicação</th>
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
                                    <?= $item->DSTITULO; ?>
                                </td>
                                <td>
                                    <?= $item->DSNOTICIA; ?>
                                </td>
                                <td>
                                    <?php 
                                        if($item->FLMOBILE == 0 || $item->FLMOBILE == 1)
                                            echo tag_ativo (true);
                                         else
                                            echo tag_ativo (false); 
                                    
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        if($item->FLMOBILE == 0 || $item->FLMOBILE == 2)
                                            echo tag_ativo (true);
                                         else
                                            echo tag_ativo (false); 
                                    
                                    ?>
                                </td>                                
                                <td>
                                    <?= data_oracle_para_web($item->DTNOTICIA); ?>
                                </td>
                                <td align="right"> 
                                    <a href="ti_noticia/excluir_noticia/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                        <i class="fa fa-trash-o"></i>
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
 