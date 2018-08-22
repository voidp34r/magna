<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> Projeto
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
	        <div class="form-group">
	       		<div class="col-sm-4">
		    		<label for="NOME">Nome do Projeto *</label>
		    		<?= form_input('NOME', set_value('NOME'), 'required'); ?>
		    	</div>
		    	<div class="col-sm-8">
		    		<label for="DESCRICAO">Descrição</label>
		    		<?= form_input('DESCRICAO', set_value('DESCRICAO'), 'required'); ?>
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

<script>
	$(document).ready(function(){
		$("input").blur(function(){
			if($(this).val() == ""){
				$(this).parent().addClass("has-error");
		    } else {
		    	$(this).parent().removeClass("has-error");
		    }
	    });
	})
</script>