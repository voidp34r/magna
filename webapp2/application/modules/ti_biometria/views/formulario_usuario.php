<?php
echo botao_voltar();
if (set_value('ID')){?>
	<a class="btn btn-default" id="addDigitais" onclick="openModal()">
		<i class="fa fa-fw fa-hand-pointer-o"></i>
		Cadastrar digital
	</a>
    <a class="btn btn-default btn-confirm" href="ti_biometria/excluir_usuario_digital/<?= set_value('ID'); ?>">
        <i class="fa fa-fw fa-ban text-danger"></i>
        Excluir digitais
    </a><?php
}?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> usuário
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Tipo *</label>
            <div class="col-sm-4 form-control-static">
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'FUNCIONARIO', set_value('TIPO', 'FUNCIONARIO') == 'FUNCIONARIO'); ?>
                        Funcionário
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'AGREGADO', set_value('TIPO') == 'AGREGADO'); ?>
                        Agregado
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'VISITANTE', set_value('TIPO') == 'VISITANTE'); ?>
                        Visitante
                    </label>
                </div>
            </div>   
        </div>    
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF *</label>
            <div class="col-sm-4">
                <?= form_input('CPF', set_value('CPF'), 'class="mascara_cpf cpfcnpj" data-ativo="1" required autofocus'); ?>
            </div>
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">
                <?= form_input('NOME', set_value('NOME'), 'required'); ?>
            </div>
        </div>
        <div class="form-group">              
            <label class="col-sm-2 control-label">                
                Horário de acesso *
            </label>
            <div class="col-sm-4">
                <?= form_dropdown('BIOMETRIA_HORARIO_ID', $horarios, set_value('BIOMETRIA_HORARIO_ID'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">
                <abbr title="Deixe o campo em branco se não tiver data de validade.">
                    Validade
                </abbr>
            </label>
            <div class="col-sm-4">
                <?= form_input('VALIDADE', set_value('VALIDADE'), 'class="mascara_data"'); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Observações</label>
            <div class="col-sm-10">
                <?= form_textarea('OBSERVACAO', set_value('OBSERVACAO')); ?>
            </div>
        </div> 
        <div class="form-group">
            <label class="col-sm-2 control-label">Número de Digitais</label>
            <div class="col-sm-4">
                <input type="text" id="num_fingerprint" value="<?= $digitais_cadastradas; ?>" disabled>
            </div>
        </div>       

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
                <div id="digitais"></div>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<!-- Modal cadastro digital -->
<div id="digitalModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="digitalModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="headerModal">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        	<span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="digitalModal">Cadastro biométrico</h4>
      </div>
      <div class="modal-body">
        <div class="modal-body row">
			<div class="col-md-12">
			    <div style="text-align: center">
					<i id="appLogIcon" class='fa fa-spinner fa-spin fa-fw' aria-hidden='true'></i>
		    	</div>
			</div>
			<div class="col-md-12" style="text-align: center">
		    	<h4 id="appLog">
		    		Iniciando...
		    	</h4>
			</div>
		</div>
		<div class="modal-body row" hidden>
			<div class="col-md-12">
			    <div style="text-align: center">
		    		<img width="100" id="imgDedo1">
		    		<img width="100" id="imgDedo2">
		    		<img width="100" id="imgDedo3">
		    	</div>
			</div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" disabled>Salvar</button>
      </div>
    </div>
  </div>
</div>

<!-- 
 * BIOMETRIA CONTROLID, cadastro biométrico.
 * 
 * 
 * - Leitura do template à partir de um scanner biométrico futronic fs80
 * - Validação do template em equipamentos IDClass(Relógio Ponto) e IDAccess (multifuncional)
 * - Envio de informações para os equipamentos (cadastros)-->
<script src="assets/js/controlid/idClass.js"></script>
<script>
	var socket;
	var isLeitorConectado;
	var address = location.protocol === 'https:' ? "wss://localhost:81818/plugin" : "ws://localhost:8181/plugin";
	var md;
	var canvas;
	var template_number = 0;
	var totalCount = parseInt($('#num_fingerprint').val() ? $('#num_fingerprint').val() : '0');
	var user_pis_param = 0;
	var is_remove_templates = false;
	var socket_connection_closed = false;
	var equipamentos = [
		{tipo: "idclass", nome: "IDClass", session: ""}
		//{tipo: "idaccess", nome: "IDAccess", session: ""}
	];

	//Abre modal do cadastro da digital
	function openModal(){
		$("#digitalModal").on("shown.bs.modal", function(){
			cadastraDigital();
		}).modal();
	}

  	/* Cadastro da digital
  	 * - Conecta nos equipamentos controlid 
  	 * - Abre websocket do leitor biométrico 
  	 * - Trabalha com o modal da tela */
	function cadastraDigital(){
		captura = 0;
		templates = [];
		templates_to_merge = [];
		template_number = 0;

		//Oculta imagens no início, para mostrar status de conectando
		$('#imgDedo1').attr('style', "display:none");
		$('#imgDedo2').attr('style', "display:none");
		$('#imgDedo3').attr('style', "display:none");
	    $('#imgDedo1').attr('src', "assets/img/templateDigitalModelo.png");
		$('#imgDedo2').attr('src', "assets/img/templateDigitalModelo.png");
		$('#imgDedo3').attr('src', "assets/img/templateDigitalModelo.png");

		appLog({info: "Iniciando..."}, true);

		//Cria sessão com os equipamentos ControlID que irão gerar o template da digital	
		for (var x in equipamentos){
			var respOk = false;
			$.ajax({
				async: false,
				url: "ti_biometria/loginControlId/" + equipamentos[x].tipo,
				type: 'POST',
				success: function(resp){
					equipamentos[x].session = $.parseJSON(resp)["session"];
					console.log(equipamentos[x].nome + " " + equipamentos[x].session);
					respOk = true;
				},
				error: function(resp){
				    console.log(JSON.stringify(resp));
				    respOk = false;
				}
		  	});
		  	
			if (!respOk){
				appLog({error: "Não foi possível conectar no equipamento " + equipamentos[x].nome + " padrão."}, false);
				return false;
			}
		}
		
		//Abre Socket do leitor biométrico (deve estar instalado)
		socket = new WebSocket(address);
		socket.binaryType = "arraybuffer";

		socket.onerror = function(error) {
			appLog({error: error.data}, false, false);
		};
		
		socket.onopen = function(e){
			socket.send('getfingerprint');
			
			//Exibe digitais modelo, com opacidade
		    $('#imgDedo1').attr('style', "opacity: 0.1;");
		    $('#imgDedo2').attr('style', "opacity: 0.1;");
		    $('#imgDedo3').attr('style', "opacity: 0.1;");
		    
			appLog({info: "Pressione o dedo."}, "dedo");
		};

		socket.onmessage = function(event){
		    if(event.data instanceof ArrayBuffer){
				captura++;
				appLog({info: "Mantenha pressionado..."}, "dedo");
				
				if (captura >= 3){
					console.log(captura);
					
					$("#spinModal").show();
					appLog({info: "Pronto! Analisando as digitais..."}, true);
				}
			    
				var bytearray = new Uint8Array(event.data);
				var tempcanvas = document.createElement('canvas');
				var imageheight = ((bytearray[0] & 0xff) << 24) | ((bytearray[1] & 0xff) << 16) | ((bytearray[2] & 0xff) << 8) | (bytearray[3] & 0xff);
				var imagewidth = ((bytearray[4] & 0xff) << 24) | ((bytearray[5] & 0xff) << 16) | ((bytearray[6] & 0xff) << 8) | (bytearray[7] & 0xff);
				tempcanvas.height = imageheight;
				tempcanvas.width = imagewidth;
				var tempcontext = tempcanvas.getContext('2d');

				var imgdata = tempcontext.getImageData(0,0,imagewidth,imageheight);
				var imgdatalen = imgdata.data.length;

				var j = 8;
				for(var i = 0;i < imgdatalen;i += 4){
						imgdata.data[0 + i] = bytearray[j];
						imgdata.data[1 + i] = bytearray[j];
						imgdata.data[2 + i] = bytearray[j];
						j++;
						imgdata.data[3 + i] = 255;
				}

				tempcontext.putImageData(imgdata,0,0);
			
				SendBinary('https://192.168.99.49/template_extract.fcgi?session=' + equipamentos[0].session + '&width=' + imagewidth + '&height=' + imageheight,
					bytearray.subarray(8),
					function (extract_result){
						console.log("extract_result: " + JSON.stringify(extract_result));
						
						if(extract_result){
							console.log("extract_result ok");
								
							if(extract_result.error){
								appLog({error: "Erro ao extrair template. Tente novamente"}, false);
								return;
							}
							
						    templates_to_merge[captura] = extract_result.template;
							console.log("captura: " + captura);

							//Insere a imagem no canvas
						    $('#imgDedo' + captura).attr('src', tempcanvas.toDataURL());
						    $('#imgDedo' + captura).attr('style', "opacity: 1");
						    
							if(captura == 3){
								var mergeParams = {
										templates: templates_to_merge,
										remove: is_remove_templates,
										user_pis: user_pis_param,
										new_templates: templates
								};
								console.log("iniciando merge..");
									
								SendJSON('https://192.168.99.49/template_merge.fcgi?session=' + equipamentos[0].session, mergeParams, 
									function(merge_result){ 
										if(merge_result.error){
											var error;
											switch (merge_result.error) {
												case 'Template exists':
													error = 'Digital já cadastrada';
													break;
												case 'Different fingerprints':
													error = 'Digitais não correspondentes';
													break;
												default:
													error = merge_result.error;
													break;
											}
											
											appLog(
												{count: captura,
													quality: extract_result.quality,
													info: error,
													success: false
												},
												false
											);
										} else {
											totalCount++;

											$('#digitais').append('<input type="hidden" name="templates[' + template_number + ']" value="' + merge_result.template + '">');
									  		$('#num_fingerprint').val(('00' + totalCount).slice(-2));

											console.log("template_number: " + template_number +
												"\n merge_result template: " + merge_result.template);
												
											templates[template_number] = merge_result.template;
											template_number++;
											var msg  = {
												count: captura,
												quality: extract_result.quality,
												template_number: template_number,
												success: true
											}
											appLog(msg, false);
										}
									}
								);
							} else {				    		    		
								socket.send('getfingerprint');
								
								var msg  = {
									count: captura,
									quality: extract_result.quality
								}
								
								appLog(msg, false);
							}
						} else {
							appLog({error: "Erro ao extrair template. Tente novamente"}, false);
						}
					}
				);
		    } else {
				appLog({error: event.data}, false, false);
		    }
		};	
	};

	function mudar_tipo() {
		var tipo = $('[name="TIPO"]:checked').val();
		if (tipo == 'AGREGADO') {
			$('[name="CPF"]').attr('data-tipo', 'FRETEIRO');
			$('[name="CPF"]').attr('data-ativo', '1');
			$('[name="CPF"]').attr('data-bloqueio', '1');
		} else if (tipo == 'VISITANTE') {
			$('[name="CPF"]').attr('data-tipo', '');
			$('[name="CPF"]').attr('data-ativo', '');
			$('[name="CPF"]').attr('data-bloqueio', '0');
		} else {
			$('[name="CPF"]').attr('data-tipo', 'FUNCIONARIO');
			$('[name="CPF"]').attr('data-ativo', '1');
			$('[name="CPF"]').attr('data-bloqueio', '1');
		}
	}

    /* Logs do cadastramento biométrico.
     * Todas as mensagens da tela modal e informações ao usuário passam por aqui.
     * Também controla o estado da tela 
     *      status: 1 = ok
     *              2 = aguardando usuario
     *              3 = erro
     *      msg: array
     *      isProcessando: true ou false */
    function appLog(msg, status, isProcessando) {
        console.log(JSON.stringify(msg));
        var verde    = "#2ecc71";
        var vermelho = "#e74c3c";
        var azul     = "#34495e";
        
        //Icones
        var errIcon  = "<i style='color: #ffffff' class='fa fa-times-circle fa-3x' aria-hidden='true'></i>";
		var okIcon   = "<i style='color: #ffffff' class='fa fa-check-circle fa-3x' aria-hidden='true'></i>";
		var space = "&nbsp&nbsp&nbsp";

		/* Icone processando
		 * isProcessando pode ser string "dedo", que significa aguardando o usuário.
		 * Mostra mãozinha animada no lugar do spinner do processando*/
		var spinIcon;
		if (isProcessando == true)
			spinIcon = "<i style='color: #ffffff' class='center-block fa fa-spinner fa-spin fa-3x fa-fw' aria-hidden='true'></i>";
		else if (isProcessando == "dedo")
			spinIcon = "<i style='color: #ffffff' class='center-block fa fa-hand-o-up fa-3x faa-pulse animated' aria-hidden='true'></i>";
		else
			spinIcon = "";

		//Tratamento da msg
        if (msg.error !== undefined){
        	//Erro
        	$('#headerModal').attr('style', "background-color: " + vermelho);
        	$('#appLogIcon').html(errIcon + space);
            $('#appLog').html(msg.error);
        } else if (msg.info !== undefined){
            //Informação
            $('#headerModal').attr('style', "background-color: " + azul);
        	$('#appLogIcon').html(spinIcon + space);
        	$('#appLog').html(msg.info);
        } else if (msg.count == 3) {
            //Processo captura 3 digitais, então após a 3a, processo finaliza.
            if (msg.success) {
            	$('#headerModal').attr('style', "background-color: " + verde);
            	$('#appLogIcon').html(okIcon + space);
                $('#appLog').html("Qualidade: " + msg.quality);
            } else {
            	$('#headerModal').attr('style', "background-color: " + azul);
            	$('#appLogIcon').html(errIcon + space);
            	$('#appLog').html("Qualidade: " + msg.quality + "<br/>" + msg.info);
            }
        } else {
        	$('#headerModal').attr('style', "background-color: " + azul);
        	$('#appLogIcon').html(spinIcon + space);
            $('#appLog').html("Qualidade: " + msg.quality);
        }
    }

 	$(document).ready(function(){
    	$("#spinModal").hide();
        
        $('[name="CPF"]').focus(function () {
            mudar_tipo();
        });
        $('[name="TIPO"]').change(function () {
            mudar_tipo();
            $('[name="CPF"]').val('');
            $('[name="CPF"]').focus();
        });
	});
</script>