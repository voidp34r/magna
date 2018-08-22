<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Liberar acesso
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Usuário *</label>
            <div class="col-sm-10">
                <?= form_dropdown('BIOMETRIA_USUARIO_ID', $usuarios, '', 'id="biometria_usuario_id"'); ?>
            </div>
        </div>            
        <div class="form-group">
            <label class="col-sm-4 col-sm-offset-2 form-control-static">
                Equipamento
            </label>
            <label class="col-sm-4 form-control-static">
                Horário
            </label>
            <label class="col-sm-2 form-control-static">
                Habilitado
            </label>
        </div>    
        <?php
        foreach ($equipamentos as $equipamento_id => $equipamento_nome)
        {
            ?>
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-2">
                    <?=  form_input('', $equipamento_nome, 'disabled'); ?>
                </div>
                <div class="col-sm-4">
                    <?= form_dropdown('HORARIOS[' . $equipamento_id . ']', $horarios, '', 'class="horarios"'); ?>
                </div>
                <div class="col-sm-2">
                    <div class="checkbox">
                        <input name="EQUIPAMENTOS[<?= $equipamento_id; ?>]" type="checkbox" data-toggle="toggle" data-on="Sim" data-off="Não">
                    </div>
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
        $('#biometria_usuario_id').change(function () {
            var id = $(this).val();
            if (id) {
                $.getJSON('ti_biometria/ver_usuario/' + id + '?format=json', function (data) {
                    $('.horarios').each(function () {
                        $(this).val(data.item.BIOMETRIA_HORARIO_ID);
                        $(this).selectpicker('refresh');
                    });
                });
            }
        });
    });
</script>