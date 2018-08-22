<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Departamento pai</label>
            <div class="col-sm-10">
                <?= form_dropdown('GERAL_DEPARTAMENTO_ID', $departamentos, set_value('GERAL_DEPARTAMENTO_ID'), 'required'); ?>                
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <?= form_input('NOME', set_value('NOME'), 'required'); ?>
            </div>
        </div>
        <?php
        if (set_value('NOME')) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10 form-control-static">
                    <div class="checkbox">
                        <label></label>    
                        <?= form_checkbox('ATIVO', 1, set_value('ATIVO') ); ?>
                        
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