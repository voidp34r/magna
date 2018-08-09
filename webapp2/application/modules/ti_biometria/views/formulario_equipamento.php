<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> equipamento
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">
                <?= form_input('NOME', set_value('NOME'), 'required autofocus'); ?>
            </div>
            <label class="col-sm-2 control-label">Filial *</label>
            <div class="col-sm-4">
                <?= form_dropdown('CDEMPRESA', $filiais, set_value('CDEMPRESA')); ?>
            </div>
        </div>            
        <div class="form-group">
            <label class="col-sm-2 control-label">Usuário *</label>
            <div class="col-sm-4">
                <?= form_input('USUARIO', set_value('USUARIO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Senha *</label>
            <div class="col-sm-4">
                <?= form_password('SENHA', set_value('SENHA'), 'required'); ?>
            </div>
        </div>    
        <div class="form-group">
            <label class="col-sm-2 control-label">IP *</label>
            <div class="col-sm-4">
                <?= form_input('IP', set_value('IP'), 'required'); ?>
            </div>         
            <label class="col-sm-2 control-label">Tipo *</label>
            <div class="col-sm-4 form-control-static">
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'IDACCESS', set_value('TIPO', 'IDACCESS') == 'IDACCESS'); ?>
                        iDAccess
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'IDCLASS', set_value('TIPO') == 'IDCLASS'); ?>
                        iDClass
                    </label>
                </div>
            </div>    
        </div>    
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <div class="checkbox">
                    <label>
                        <?= form_checkbox('PADRAO', 1, set_value('PADRAO')); ?>
                        Equipamento padrão
                    </label>
                </div>
            </div>
        </div>    
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>