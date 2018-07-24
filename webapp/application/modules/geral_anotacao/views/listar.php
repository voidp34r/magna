<a class="btn btn-success" href="geral_anotacao/adicionar">
    <i class="fa fa-fw fa-plus"></i>
    Nova nota
</a>
<?php
if (!empty($lista))
{
    ?>
    <div class="panel panel-default">

                <?php
                foreach ($lista as $chave => $item)
                {
                    ?>        <div class="panel-body">                
            <samp>  
                    <b><?= $item->TITULO; ?></b>
                    <div class="pull-right">
                        <a href="geral_anotacao/editar/<?= $item->ID; ?>"><i class="fa fa-fw fa-pencil"></i></a>     
                        <a href="geral_anotacao/excluir/<?= $item->ID; ?>"><i class="fa fa-fw fa-trash"></i></a>
                    </div>
                    <br>
                    <?= nl2br($item->DESCRICAO); ?>
                    <br>
                    <br>            </samp>
        </div>
                    <?php
                    echo (($chave + 1) < count($lista)) ? '<hr>' : '';
                }
                ?>
    </div>

    <?php
}
?>