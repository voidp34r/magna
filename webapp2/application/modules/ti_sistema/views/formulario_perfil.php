<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= !empty($item) ? 'Editar' : 'Novo'; ?> perfil
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <input type="text" name="NOME" value="<?= !empty($item) ? $item->NOME : ''; ?>" class="form-control" required>
            </div>
        </div>
        <?php
        if (!empty($item)) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10 form-control-static">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="ATIVO" value="1" <?= !empty($item->ATIVO) ? 'checked' : ''; ?>>
                            Ativo  
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
        if (!empty($modulos)) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Telas *</label>
                <div class="col-sm-10 form-control-static">
                    <?php
                    foreach ($modulos as $modulo) {
                        if (!empty($telas[$modulo->ID])) {
                            $modulo_checked = FALSE;
                            foreach ($telas[$modulo->ID] as $tela) {
                                if (in_array($tela->ID, $telas_selecionadas)) {
                                    $modulo_checked = TRUE;
                                }
                            }
                            ?>
                            <div class="checkbox">
                                <label>
                                    <?php
                                    echo form_checkbox('', $modulo->ID, $modulo_checked, 'class="modulo_check"');
                                    echo $modulo->NOME;
                                    ?>
                                </label>
                            </div>
                            <div class="padding-left-20" id="modulo_telas_<?= $modulo->ID; ?>">
                                <?php
                                foreach ($telas[$modulo->ID] as $tela) {
                                    ?>
                                    <div class="checkbox">
                                        <label>
                                            <?php
                                            echo form_checkbox('TELAS[]', $tela->ID, in_array($tela->ID, $telas_selecionadas));
                                            echo $tela->NOME;
                                            ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                                ?>   
                            </div>
                            <?php
                            echo br();
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<script>
    $(function () {
        $('.modulo_check').change(function () {
            var modulo_id = $(this).val();
            var checado = $(this).is(':checked');
            var div_telas = $('#modulo_telas_' + modulo_id + ' div label').children('input');
            div_telas.each(function () {
                $(this).prop('checked', checado);
            });
        });
    });
</script>