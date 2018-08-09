<?= (!empty($metodo) && $metodo == 'parecer_juridico') ? form_open('') : ''; ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Adicionar análise
    </div>
    <div class="panel-body form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">Status *</label>
            <div class="col-sm-4">   
                <?= form_dropdown('AGREGADO_STATUS_ID', $status, set_value('AGREGADO_STATUS_ID'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Observações</label>
            <div class="col-sm-10">   
                <?= form_textarea('OBSERVACAO', set_value('OBSERVACAO')); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deferirModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
			</div>
			<div class="modal-body">
				Todas as informações indeferidas serão marcadas como deferidas. <br>
				Deseja continuar?
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-primary" id="deferirBtn">Sim</button>
				<button type="button" data-dismiss="modal" class="btn" id="deferirCancelBtn">Cancelar</button>
			</div>
		</div>
	</div>
</div>

<?= (!empty($metodo) && $metodo == 'parecer_juridico') ? form_close() : ''; ?>
<script>
	$(document).ready(function(){

		//Verifica se algum objeto da classe "analise" está indeferido
		function temIndeferidos(){
			var tem = false;
            $('.analise').each(function(){
                if (!$(this).is(':checked')){
	                console.log('Indeferido: ' + $(this).attr('name'));
                	tem = true;
                }
            });
            return tem;
		}
		
		/* Ao selecionar o status Deferido, deve validar se todas as informações estão deferidas também.
		 * Quando alguma não estiver, abre modal perguntando se deseja alterar tudo para deferido. */
        $('select[name="AGREGADO_STATUS_ID"]').change(function(){
            if ($(this).val() == 1){ //deferido
	            if (temIndeferidos()){
            	    $('#deferirModal')
            	    	.modal({ backdrop: 'static', keyboard: false })
            	        .on('click', '#deferirBtn', function (e) { //Marca tudo como deferido
            	            $('.analise').each(function(){
            		        	$(this).prop('checked', true).change();
            		    	});
            	            console.log("deferirBtn");
            	    	})
            	        .on('click', '#deferirCancelBtn', function (e) { //Atualiza select
            	            $('select[name="AGREGADO_STATUS_ID"]').val(2).change();
            	    	});         		    	
	            }
        	}
        });
        
		/* Ao marcar alguma informação como indeferido, muda select do status da análise para indeferido também */
        $('.analise').change(function(){
            console.log("onchange");
            $('select[name="AGREGADO_STATUS_ID"]').val(!temIndeferidos() ? 1 : 2).change();
        });
    });
</script>