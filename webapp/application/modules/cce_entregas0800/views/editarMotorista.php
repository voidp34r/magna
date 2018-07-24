<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> Motorista (<?= $tipo; ?>)
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
	        <div class="form-group">
	        	<div class="col-sm-4">
		    		<label for="CPFCNPJ">CPF/CNPJ</label>
		    		<?= form_input('CPFCNPJ', set_value('CPFCNPJ')); ?>
		    	</div>
	       		<div class="col-sm-8">
		    		<label for="DSNOME">Nome</label>
		    		<?= form_input('DSNOME', set_value('DSNOME')); ?>
		    	</div>
		    </div>
		    <div class="form-group">
		    	<div class="col-sm-5">
		    		<label for="CELEMPRESA">Celular Sistema</label>
		    		<div class="input-group">
		    			<div class="input-group-btn">
		    			    <?= form_dropdown('OP_NRCELULAR', $operadoras, set_value('OP_NRCELULAR'), 'required'); ?>
					</div>
					    <?= form_input('NRCELULAR', set_value('NRCELULAR'), 'class="form-control maskPhone9Digit"'); ?>
		    		</div>
		    	</div>
		    	<div class="col-sm-5">
		    		<label for="CELEMPRESA">Celular Secundário</label>
		    		<div class="input-group">
		    			<div class="input-group-btn">
		    			    <?= form_dropdown('OP_NRCELULAR2', $operadoras, set_value('OP_NRCELULAR2'), 'required'); ?>
						</div>
					    <?= form_input('NRCELULAR2', set_value('NRCELULAR2'), 'class="form-control maskPhone9Digit"'); ?>
		    		</div>
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

		//Campos somente leitura
		$("[name='CPFCNPJ']").attr("readonly", true);
		$("[name='DSNOME']").attr("readonly", true);
		
		$("input").blur(function(){
			//Se campo for obrigatório, usa class has-error (borda vermelha)
			if($(this).prop("required")){
				if($(this).val() == ""){
					$(this).parent().addClass("has-error");
			    } else {
			    	$(this).parent().removeClass("has-error");
			    }
			}
	    });
	});
</script>
