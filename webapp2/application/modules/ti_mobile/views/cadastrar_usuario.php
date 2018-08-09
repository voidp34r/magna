<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Cadastar Usuário
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <div class="col-sm-5">
                <label for="TITULO">Nome *</label> 
                <?= form_input('USUARIO_NOME', set_value('USUARIO_NOME'))?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-5">
                <label for="USUARIO">Usuário *</label> 
                <?= form_input('USUARIO', set_value('USUARIO'),'class="mascara_cpf cpfcnpj"')?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-5">
                <label for="USUARIO">Senha *</label> 
                <?= form_password('SENHA', set_value('SENHA'))?>
            </div>
        </div>        
        <label for="EMPRESA">Filial *</label> 
        <div id="EMPRESA" class="form-group">
            <div class="col-sm-10">
                <?= form_dropdown('CDEMPRESA', $filiais, set_value('CDEMPRESA', $sessao['filial']), 'class="selectpicker" '); ?>
            </div>
        </div> 
        <label for="USUARIO">Observação</label> 
        <div id="OBSERVACAO" class="form-group">
            <div class="col-sm-10">
                <?= form_textarea('DSOBSERVACAO', set_value('DSOBSERVACAO')); ?>
            </div>
        </div>    
        <div class="form-group">
	    <div class="col-sm-4">
                <button type="submit" class="btn btn-success">Gravar</button>
	    </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>    