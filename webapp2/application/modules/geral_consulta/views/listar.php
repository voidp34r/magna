<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
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
                <tbody>
                    <?php
                    foreach ($lista as $id => $nome)
                    {
                        ?>
                        <tr>
                            <td>
                                <?= $nome; ?>
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