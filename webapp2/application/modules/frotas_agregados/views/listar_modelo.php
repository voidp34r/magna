<a class="btn btn-success" href="frotas_agregados/adicionar_modelo">
    <i class="fa fa-fw fa-plus"></i>
    Novo modelo
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Modelos
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
                        <th>Chave</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item) {
                        ?>
                        <tr>
                            <td>
                                <?= $item->CHAVE; ?>
                            </td>
                            <td align="right">
                                <a href="frotas_agregados/editar_modelo/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
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