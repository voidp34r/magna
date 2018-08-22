<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> horário
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal" id="horario_form"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <?= form_input('NOME', set_value('NOME'), 'required autofocus'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2 form-control-static">
                <label>Início</label>
            </div>
            <div class="col-sm-2 form-control-static">
                <label>Fim</label>
            </div>
            <?php
            foreach (array_dias_semana_min() as $dia_semana)
            {
                ?>
                <div class="col-sm-1 form-control-static">
                    <label><?= $dia_semana; ?></label>
                </div>
                <?php
            }
            ?>
        </div>    
        <?php
        if (empty($horario_faixas))
        {
            ?>
            <div class="form-group">
                <div class="col-sm-2">
                    <?= form_input('FAIXA[INICIO]', '', 'class="mascara_hora input-sm faixa"'); ?>
                </div>
                <div class="col-sm-2">
                    <?= form_input('FAIXA[FIM]', '', 'class="mascara_hora input-sm faixa"'); ?>
                </div>
                <?php
                foreach (array_dias_semana_min() as $dia_semana)
                {
                    ?>
                    <div class="col-sm-1">
                        <?= form_checkbox('FAIXA[' . $dia_semana . ']', 1, TRUE, 'class="faixa"'); ?>
                    </div>
                    <?php
                }
                ?>
                <div class="col-sm-1">
                    <?= form_button('', '+', 'class="btn btn-sm btn-primary" id="adicionar_faixa"'); ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div id="faixa_form">
            <?php
            if (!empty($horario_faixas))
            {
                foreach ($horario_faixas as $horario_faixa)
                {
                    $faixa_hidden = array();
                    $faixa_arr = (array) $horario_faixa;
                    foreach ($faixa_arr as $faixa_id => $faixa_valor)
                    {
                        $faixa_hidden['FAIXA'][$faixa_id] = $faixa_valor;
                    }
                    ?>
                    <div class="form-group">
                        <div class="col-sm-2">
                            <?= segundos_para_hora($horario_faixa->INICIO); ?>
                        </div>
                        <div class="col-sm-2">
                            <?= segundos_para_hora($horario_faixa->FIM); ?>
                        </div>
                        <?php
                        foreach (array_dias_semana_min() as $dia_semana)
                        {
                            ?>
                            <div class="col-sm-1">
                                <i class="fa fa-fw fa-<?= ($horario_faixa->$dia_semana) ? 'check' : 'times'; ?>"></i>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
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
        $('#horario_form').submit(function () {
            add_faixa();
        });

        $('#adicionar_faixa').click(function () {
            add_faixa();
        });

        $('body').on('click', '.excluir_faixa', function () {
            $(this).parent().parent().remove();
        });
    });

    function add_faixa() {
        var dias_semana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'];
        var faixa_serialize = $('.faixa').serialize();
        var faixa_inicio = $('[name="FAIXA[INICIO]"]');
        var faixa_fim = $('[name="FAIXA[FIM]"]');
        var faixa_html = '';
        if (faixa_inicio.val() && faixa_fim.val()) {
            faixa_html += '<div class="form-group">';
            faixa_html += '<div class="col-sm-2">';
            faixa_html += faixa_inicio.val();
            faixa_html += '</div>';
            faixa_html += '<div class="col-sm-2">';
            faixa_html += faixa_fim.val();
            faixa_html += '</div>';
            for (var i in dias_semana) {
                faixa_html += '<div class="col-sm-1">';
                faixa_html += '<i class="fa fa-fw fa-';
                faixa_html += $('[name="FAIXA[' + dias_semana[i] + ']"]').is(':checked') ? 'check' : 'times';
                faixa_html += '"></i>';
                faixa_html += '</div>';
            }
            faixa_html += '<div class="col-sm-1">';
            faixa_html += '<input type="hidden" name="FAIXAS[]" value="' + faixa_serialize + '">';
            faixa_html += '<button class="btn btn-default btn-sm excluir_faixa">X</button>'
            faixa_html += '</div>';
            faixa_html += '</div>';
            $('#faixa_form').append(faixa_html);
            faixa_inicio.val('');
            faixa_fim.val('');
            faixa_inicio.focus();
        }
    }
</script>