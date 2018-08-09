<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Disparar SMS        
    </div>
    <div class="panel-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Conhecimento</th>
                    <th>Número</th>
                    <th>Mensagem</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($lista as $item) {
                    ?>
                    <tr>
                        <td><?= $item->SOFTRAN_CDEMPRESA . '-' . $item->SOFTRAN_NRDOCTOFISCAL; ?></td>
                        <td><?= $item->CELULAR; ?></td>
                        <td><?= $item->MENSAGEM; ?></td>
                        <td><?= $item->STATUS; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table> 
    </div>
</div>