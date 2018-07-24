<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Filial *</label>
            <?php
                if (!empty($sessao['filial']))
                {
            ?>
            <div class="col-sm-10">
                <?= form_disabled_dropdown('CDEMPRESA', $filiais, set_value('CDEMPRESA', $sessao['filial']), 'class="selectpicker"', filiaisArray(array_keys($filiais), $sessao['filial'])); ?>
            </div>
            <?php
                }
            ?>
            <?php
                if (empty($sessao['filial']))
                {
            ?>
            <div class="col-sm-10">
                <?= form_dropdown('CDEMPRESA', $filiais, set_value('CDEMPRESA', $sessao['filial']), 'class="selectpicker" '); ?>
            </div>
            <?php
                }
            ?>
        </div>
        <?php
        if ($metodo == 'adicionar_solicitacao')
        {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Proprietário *</label>
                <div class="col-sm-10">                    
                    <?= form_dropdown('AGREGADO_PROPRIETARIO_ID', $proprietarios, set_value('AGREGADO_PROPRIETARIO_ID'), 'class="selectpicker"'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    Se não encontrar o proprietário desejado, <a href="frotas_agregados/adicionar_proprietario">clique aqui para cadastrar um novo.</a>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">
                    <?= ($metodo == 'adicionar_solicitacao') ? 'Avançar' : 'Gravar'; ?>
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>