<a class="btn btn-success" href="ti_biometria/adicionar_liberacao">
    <i class="fa fa-fw fa-unlock"></i>
    Liberar acesso
</a>
<a class="btn btn-default botao_checado" href="ti_biometria/reenviar_liberacao">
    <i class="fa fa-fw fa-refresh"></i>
    Reenviar <span class="botao_checado_selecionado"></span> selecionado(s)
</a>
<a class="btn btn-default botao_checado" href="ti_biometria/excluir_liberacao_selecionado">
    <i class="fa fa-fw fa-trash"></i>
    Excluir <span class="botao_checado_selecionado"></span> selecionado(s)
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Liberações
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
                        <th><?= form_checkbox('', '', '', 'id="alternar_checkbox"'); ?></th>
                        <th>Equipamento</th>
                        <th>Usuário</th>
                        <th>Horário</th>
                        <th>Enviado</th>
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
                                <?= (!$item->ENVIADO) ? form_checkbox('USUARIO_EQUIPAMENTO[]', $item->BIOMETRIA_EQUIPAMENTO_ID . '-' . $item->BIOMETRIA_USUARIO_ID, '', 'class="equipamento_checado"') : ''; ?>
                            </td>
                            <td>
                                <?= $equipamentos[$item->BIOMETRIA_EQUIPAMENTO_ID]; ?>
                            </td>
                            <td>
                                <?= $usuarios[$item->BIOMETRIA_USUARIO_ID]; ?>
                            </td>
                            <td>
                                <?= $horarios[$item->BIOMETRIA_HORARIO_ID]; ?>
                            </td>
                            <td>
                                <?= tag_ativo($item->ENVIADO); ?>
                            </td>
                            <td align="right">
                                <a href="ti_biometria/excluir_liberacao/<?= $item->BIOMETRIA_EQUIPAMENTO_ID; ?>/<?= $item->BIOMETRIA_USUARIO_ID; ?>" type="button" class="btn btn-xs btn-default btn-confirm">
                                    <i class="fa fa-fw fa-trash"></i>
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
<script>
    $(function () {
        $('.botao_checado').hide();
        $('#alternar_checkbox').change(function () {
            var checked = $(this).is(':checked');
            $('.equipamento_checado').each(function () {
                $(this).prop('checked', checked);
            });
        });
        $('input[type="checkbox"]').change(function () {
            var checado = 0;
            $('.equipamento_checado').each(function () {
                var checked = $(this).is(':checked');
                if (checked) {
                    $('.botao_checado').show();
                    checado++;
                }
            });
            if (checado == 0) {
                $('.botao_checado').hide();
            } else {
                $('.botao_checado_selecionado').text(checado);
            }
        });
        $('.botao_checado').click(function (e) {
            var liberacoes = '';
            $('.equipamento_checado').each(function () {
                if ($(this).is(':checked')) {
                    liberacoes += $(this).val() + '_';
                }
            });
            if (confirm('Você tem certeza?')) {
                location.href = $(this).attr('href') + '?liberacoes=' + liberacoes;
            }
            return false;
        });
    });
</script>