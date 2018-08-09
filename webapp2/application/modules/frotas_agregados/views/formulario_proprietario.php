<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <div class="panel-body">
        <?php
        $aberto = false;
        echo form_open_multipart('', 'class="form-horizontal"');
        if (set_value('ABERTO') == 1 || !set_value('ID'))
        {
            $aberto = true;
            ?>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="CPF_CNPJ">CPF/CNPJ *</label>
                    <?= form_input('CPF_CNPJ', set_value('CPF_CNPJ'), 'id="CPF" class="mascara_cpfcnpj cpfcnpj" required'); ?>
                </div>
                <div class="col-sm-6">
                	<label for="NOME">Nome *</label>
                    <?= form_input('NOME', set_value('NOME'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="RG">RG *</label>
                    <?= form_input('RG', set_value('RG'), 'id="RG" class="numbersOnly" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3 INSCRICAO_ESTADUAL">
                	<label for="INSCRICAO_ESTADUAL">Inscrição Estadual *</label>
                    <?= form_input('INSCRICAO_ESTADUAL', set_value('INSCRICAO_ESTADUAL'), 'class="numbersOnly" required'); ?>
                </div>
                <div class="col-sm-2">
                	<label for="CEP">CEP *</label>
                    <?= form_input('CEP', set_value('CEP'), 'class="cep mascara_cep" data-name="cep1" required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="estado_proprietario">Estado *</label>
                    <?= form_dropdown('', array('') + array_estados(), '', 'id="estado_proprietario" class="estado" data-cep="cep1_uf" data-destino="GERAL_MUNICIPIO_ID" data-municipio="' . set_value('GERAL_MUNICIPIO_ID') . '" required'); ?>
                </div>
                <div class="col-sm-4">   
                    <label for="municipio_proprietario">Município *</label>
                    <?= form_dropdown('GERAL_MUNICIPIO_ID', array(), set_select('GERAL_MUNICIPIO_ID'), 'id="municipio_proprietario" required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="BAIRRO">Bairro *</label>
                    <?= form_input('BAIRRO', set_value('BAIRRO'), 'data-cep="cep1_bairro" required'); ?>
                </div>
                <div class="col-sm-5">
                	<label for="ENDERECO">Endereço *</label>
                    <?= form_input('ENDERECO', set_value('ENDERECO'), 'data-cep="cep1_logradouro" required'); ?>
                </div>
                <div class="col-sm-4">
                	<label for="NUMERO">Nº/Complemento *</label>
                    <?= form_input('NUMERO', set_value('NUMERO'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                	<label for="TELEFONE">Telefone</label>
                    <?= form_input('TELEFONE', set_value('TELEFONE')); ?>
                </div>
                <div class="col-sm-3">
                	<label for="CELULAR">Celular *</label>
                    <?= form_input('CELULAR', set_value('CELULAR'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="EMAIL">E-mail</label>
                    <?= form_input('EMAIL', set_value('EMAIL')); ?>
                </div>
                <div class="col-sm-3">
                	<label for="ANTT">ANTT *</label>
                    <?= form_input('ANTT', set_value('ANTT'), 'required'); ?>
                </div>
            </div>
            <hr>
            <h4 class="col-sm-offset-0">Dados bancários</h4>
            <div class="form-group">
                <div class="col-sm-6">
                	<label for="FAVORECIDO">Favorecido *</label>
                    <?= form_input('FAVORECIDO', set_value('FAVORECIDO'), 'required'); ?>
                </div>
                <div class="col-sm-3">
                	<label for="FAVORECIDO_INSCRICAO">CPF/CNPJ *</label>   
                    <?= form_input('FAVORECIDO_INSCRICAO', set_value('FAVORECIDO_INSCRICAO'), 'class="mascara_cpfcnpj" required'); ?>
                </div>
				<div class="col-sm-3">
                	<label for="BANCO">Banco *</label>
                    <?= form_input('BANCO', set_value('BANCO'), 'required'); ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                	<label for="AGENCIA">Agência *</label>
                    <?= form_input('AGENCIA', set_value('AGENCIA'), 'required'); ?>
                </div>
                <div class="col-sm-4">
                	<label for="CONTA">Conta corrente *</label> 
                    <?= form_input('CONTA', set_value('CONTA'), 'required'); ?>
                </div>
            </div>
            <hr>
            <?php
        }
        foreach ($upload_campos as $upload_label => $upload_nome)
        {
            if (set_value('ABERTO_' . $upload_label) == 1 || !set_value('ID'))
            {
                $aberto = true;
                ?>
                <div class="form-group" id="upload_<?= $upload_label; ?>">
                    <label class="col-sm-5 control-label"><?= $upload_nome; ?> *</label>
                    <div class="col-sm-6 form-control-static">   
                        <?= form_upload('UPLOAD_' . $upload_label, 'required'); ?>
                    </div>
                </div>    
                <?php
            }
        }
        if ($aberto)
        {
            ?>
            <div class="form-group">
                <div class="col-sm-4">
                    <button type="submit" class="btn btn-success">Gravar</button>
                </div>
            </div>
            <?php
        }
        else
        {
            echo 'Nenhuma informação em aberto';
        }
        echo form_close();
        ?>
    </div>
    <ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none"><!-- Menu de contexto -->
	<li><a tabindex="-1">Copiar</a></li>
	<li><a tabindex="-1">Copiar sem formatação</a></li>
	</ul> <!-- Menu de contexto -->
	
</div>
<script>
	$(document).ready(function(){
		//Menu de contexto
		initContextMenu(jQuery, window);
		
		//Campos que terão menu de contexto para copiar somente números
		var camposMenuContexto = ["#CPF","#RG"];
		for (x in camposMenuContexto) {
			var element = camposMenuContexto[x];
			$(element).contextMenu({
			    menuSelector: "#contextMenu",
			    menuSelected: function (invokedOn, invokedValue, selectedMenu) {
			    	execContextMenuClick(element, invokedValue, selectedMenu);
			    	console.log("depois");
			    }
			});
		}

		//Validação do campo CPF/CNPJ (deve validar ao iniciar / atualizar pagina)
		var tpPessoa = "";
		$('input[name="CPF_CNPJ"]').blur(function () {
	        var texto = $(this).val();
			console.log(texto);
	        texto = removeMascara(texto);
	
	    	/*Tratamento do tooltip dos campos:
	    	------------------------------------
	    	O comportamento esperado é que os tooltips fiquem visíveis apenas quando os campos 
	    	estão desabilitados, mostrando uma mensagem para o usuário idenfificar o motivo
	    	de estar desabilitado.
	
	    	No .blur do campo CPF_CNPJ é verificado se é pessoa física ou jurídica,
	    	desabilitando os campos que são de pessoa jurídica.
	
	    	Se pessoa Física então desabilita campos de pessoa jurídica.
	     	sem ocultar os campos, para poder mostrar a hint ao usuário com o motivo do 
	        campo não estar habilitado. Senão mostra habilita os campos
	        ---------------------------------------*/
	        if (texto.length == 11) {
	        	tpPessoa = "PF"
	            $('#upload_CONTRATO_SOCIAL').attr('readonly', 'readonly');
	            $('#upload_DADOS_EMPRESA').hide();
	
	          	//Habilita campos de pessoa física
	            $('input[name="RG"]')
	            	.removeAttr('readonly') //habilita campo
	            	.attr("rel", null) //oculta tooltip
	            	.attr('required', 'required'); //coloca como obrigatorio
	
	            //Desabilita campos pessoa jurídica
	            $('input[name="INSCRICAO_ESTADUAL"]')
	                .val("") //apaga o valor do campo
	            	.attr('readonly', 'readonly') //desabilita campo
	            	.attr("title", "Para informar uma Inscrição Estadual o proprietário precisa ser uma pessoa jurídica") //define texto do tooltip
	            	.attr("rel", "tooltip") //mostra tooltip
	            	.removeAttr('required'); //remove obrigatoriedade
	
	            
	        } else if (texto.length == 14){
	        	tpPessoa = "PJ"
	            $('#upload_CONTRATO_SOCIAL').show();
	            $('#upload_DADOS_EMPRESA').show();
	
	            //Habilita campos de pessoa jurídica
	            $('input[name="INSCRICAO_ESTADUAL"]')
	            	.removeAttr('readonly') //habilita campo
	            	.attr("rel", null) //oculta tooltip
	            	.attr('required', 'required'); //coloca como obrigatorio
	
	            //Desabilita campos pessoa física
	            $('input[name="RG"]')
	                .val("") //apaga o valor do campo
	            	.attr('readonly', 'readonly') //desabilita campo
	            	.attr("title", "Para informar um RG o proprietário precisa ser uma pessoa física") //define texto do tooltip
	            	.attr("rel", "tooltip") //mostra tooltip
	            	.removeAttr('required'); //remove obrigatoriedade
	        } else {
	        	tpPessoa = ""
	            //Habilita tudo se não for nem CPF nem CNPJ
	            $('input[name="INSCRICAO_ESTADUAL"]')
	            	.removeAttr('readonly') //habilita campo
	            	.attr("rel", null) //oculta tooltip
	            	.attr('required', 'required'); //coloca como obrigatorio
	
	            $('input[name="RG"]')
	            	.removeAttr('readonly') //habilita campo
	            	.attr("rel", null) //oculta tooltip
	            	.attr('required', 'required'); //coloca como obrigatorio
	
	            $('#upload_CONTRATO_SOCIAL').show();
	            $('#upload_DADOS_EMPRESA').show();
	        }
	        
	    }).blur();
	});
</script>