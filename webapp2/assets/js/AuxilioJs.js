/**
 * Aqui esta o auxilio de código para a integração com o 
 * aparelho FUTRONIC que realiza a leitura de digital.
 * 
 *  Via Leitor Futronic :
 *    1 - Conectar ao serviço e ligar leitor
 * 	  2 - Obter dedo 1 e extrair o template - template_extract.fcgi
 *    3 - Obter dedo 2 e extrair o template - 		''
 *    4 - Obter dedo 3 e extrair o template -  		''
 *    5 - Juntar os templates - template_merge
 * 
 * 	Atenção :	  
 *  	- Em todo retorno é validado se tem "retorno.error" já que a API da Control id retorna isso
 * 		- Todos os métodos são "assync : false" ou seja quando realiza a requisão "trava" a interface do usuário 
 *  
 **/

 //Conexão com o socket sendo global caso necessesite utilizar em qualquer método
 var socket;
 //Quantidade de Tentativas
 var fingersCount;

 //Model do Usuário
 var usuario = {templateToMerge : [], fingers : [] , nome : "", cpf : "" };
 //Variável que controla a sessão para o equipamento
 var session;
 
 //Socket de conexão com o leitor biometrico
 var socket;

//Array de Base 64 para realizar o merge das Digitais
var templateToExtract = [];

var equipamento;

//Verifica se está em modo de Edição
var urlStr = window.location.href;
var urlArr = urlStr.split('/');
var ID = '';

	//Verifica se a Url Tem parametro para editar
	if(ID = urlArr[5])
	{	

		//Procura o CPF do motorista
		$.ajax({
				url : "ti_biometria/retornaUsuario",
				method : "post",
				data : {ID : ID},
				success : function(ret)
							{
								if(ret)
								{
									
									var ret = JSON.parse(ret);
									
									//se tiver retorno parametriza o objeto e prenche os campo da tela
									if(ret)
									{
										usuario.nome  = ret.DSNOME;
										usuario.cpf   = ret.CPF;
										usuario.comentario = ret.DSCOMENTARIO
										usuario['edicao']  = true;

										$('#nome').val(usuario.nome);
										$('#cpf').val(usuario.cpf.substr(3));
										$('#observacao').val(usuario.comentario);

										//Como está em modo de edição desabilita os inputs
										$('#nome').attr('disabled',true);
										$('#cpf').attr('disabled',true);

										//Caso tenha digital cadastrada habilita botão de remover digitais, casó não habilita botão para excluir Digitais
										if(parseInt(ret.QTDIG))
										{
											$('#apagarDigitais').removeAttr('style');
										}else{
											$('#btnSalvarDigital').attr('disabled',false);
										}
										

									}else{
										//Caso ocorra algum problema ao tentar carregar o usuário
										setMessage('Ocorreu um erro ao tentar carregar o usuário solicitado, entre em contato com a TI.')
										clearAllProccess();
										setFormDefaultState();
									}

								}

							}
		});
	} 



    $( document ).ready(function(){

		$('#imgDedo1').hide()
		$('#imgDedo2').hide();
		$('#imgDedo3').hide();	

		//Seta as mascaras do input
		$("#cpf").mask("999.999.999-99");
		
		//"Ao sair" do input de cpf realiza a validação para ver se o mesmo consta no sistema
		$("#cpf").on('blur',function()
		{
     
			$.ajax({
					url : "ti_biometria/valida_usuarioCpf",
					method : "post",
					data : {CPF : $("#cpf").val()},
					success : function(ret)
								{	
									
									if(ret)
									{
										var ret = JSON.parse(ret);

										if(!ret.error){
											//Caso carregue com sucesso preenche o objeto em Js e habilita o campo de salvar digitais
											usuario.nome  = ret.funcionario.DSNOME;
											usuario.cpf   = ret.funcionario.NRCPF;
											$('#nome').val(ret.funcionario.DSNOME);
											$('#nome').attr('disabled',true);
											$('#btnSalvarDigital').attr('disabled',false);
										}else{
											//Caso ocorra erro o Controller retorna o tipo de erro
											setMessage(ret.errorMg);
											$('#nome').attr('disabled',false);
											$('#nome').val('');
											$('#btnSalvarDigital').attr('disabled',true);
										}

									}

								}
			});

         

        });

    })

    function iniciarCadastroBiometrico()
    {	
		//Aqui inicia o cadastro biomêtrico, buscando o equipamento padrão da filial do usuário
        $.ajax({
                url : "ti_biometria/retornaEquipamento",
                method : "GET",
                success : function(ret)
                          { 
							 equipamento = JSON.parse(ret);
                             init(equipamento);
                          },
               
              });
    }

    function closeModal(){
        $('#modalInfo').hide();
    }

    function setMessage(msg){
        $('#modalInfo').show();
        $('#modalInfo').find('p').text(msg);
    }

    
	//Função que inicia o processo
	function init(equipInit){

		equipamento = 	equipInit;
		var socket = null;
		var fingersCount = 0;

		//Realizando a conexão com o leitor biometrico
		realizaConexao();
	}					

	function realizaConexao()
	{	
		//Procura o driver do aparelho	
		var address = location.protocol === 'https:' ? "wss://localhost:8181/plugin" : "ws://localhost:8181/plugin";
		
		//Abre conexão socket com o leitor biometrico
		socket = new WebSocket(address);
		
		//Define o tipo de Recebimento de Dados	
		socket.binaryType = "arraybuffer";
		
		//onopen  - Ao abrir o socket manda a mensagem para o driver do leitor, ou seja "getfingerprint" para ler a digital
		socket.onopen = function(e)
		{ 
			socket.send('getfingerprint');
			loadControl();
			setMessageInfo('Pressione o dedo');
			waitAnyProccess();
		};
		
		//onmessage - Ao receber a mensagem como se fosse o response do aparelho, valida se o retorno é de arraybuffer
		// retorno.data é o valor da digital 
		socket.onmessage = function(retorno)
		{
			if(retorno.data instanceof ArrayBuffer)
			{   
				fingerSave(retorno.data);
			}
		};


		//onError  - Seta a mensagem de erro
		socket.onerror = function(e)
		{ 
			setMessage('Você não tem o driver do Futronic instalado ou o serviço não está startado, favor solicitar a TI');
		};
	}
 
 
	function fingerSave(byteFinger)
	{
		//Adiciona para um array de 8 bytes 	
		var bytearray = new Uint8Array(byteFinger);
		//Define Altura
		var imageheight = ((bytearray[0] & 0xff) << 24) | ((bytearray[1] & 0xff) << 16) | ((bytearray[2] & 0xff) << 8) | (bytearray[3] & 0xff);
        //Define Largura
		var imagewidth = ((bytearray[4] & 0xff) << 24) | ((bytearray[5] & 0xff) << 16) | ((bytearray[6] & 0xff) << 8) | (bytearray[7] & 0xff);

		//Cria Canvas no Html para transformar os bytes em imagem visual 
		//e define altura e largura conforme a digital
		var tempcanvas = document.createElement('canvas');
		tempcanvas.height = imageheight;
        tempcanvas.width = imagewidth;	
		
		//Define o contexto da Imagem
		var tempcontext = tempcanvas.getContext('2d');
		var imgdata     = tempcontext.getImageData(0,0,imagewidth,imageheight);
		var imgdatalen  = imgdata.data.length;
		
		//Varre o array e extrai os bytes para transformar em imagem
		var j = 8;
		for(var i = 0;i < imgdatalen;i += 4){
					imgdata.data[0 + i] = bytearray[j];
					imgdata.data[1 + i] = bytearray[j];
					imgdata.data[2 + i] = bytearray[j];
					j++;
					imgdata.data[3 + i] = 255;
		}
	 
		//Seta a Imagem para o contexto
		tempcontext.putImageData(imgdata,0,0);
 
 		var templateBase = bytearray.subarray(8); 

		//Adiciona o retorna para o "merge" dos templates
		templateToExtract.push({template : templateBase, altura : imageheight, largura :  imagewidth});

		//Seta como visivel a imagem
		$('#imgDedo' + templateToExtract.length).show();

		//Seta imamgem para o usuário visualizar
		$('#imgDedo' + templateToExtract.length).attr('src', tempcanvas.toDataURL());

		//Caso já extraiu as 3 digitias realiza o login no aparelho e começa a realizar a extraçãdo dos templates
		if(templateToExtract.length == 3){
			setMessageInfo('Aguarde enquanto validamos sua digital no sistema..');
			loginOn();
		}else{
			setMessageInfo('Continue pressionando.');
		}		
		
	}		

	
   /**
	* Aqui estão as funções ESSENCIAS para criação de um usuário no REP - Relógio da Control ID
	* 
	* Funções essas como : 1 - Login, 2 - Extração de Template, 3 - Merge dos templates extraidos, 				
	*
	**/
	function loginOn(){

		//Seta o parametros para conexão com o aparelho da filial do usuário
		var content = {
						url: montarUrl("login",false,""),
						type: 'POST',
						contentType: 'application/json',
						data: JSON.stringify({login:equipamento.USUARIO ,password: equipamento.SENHA}),
						success: loginSuccess,
						error :  loginError,	
						timeout:3000			
					   };
		
		function loginError(ret){

			//Caso ocorra erro de timeout redireciona para o equipamento padrão da filial de joinville
			if(ret.statusText == "timeout"){
				getEquipamentoPadrao();
			}else{
				//Caso o erro não for timeout, limpa o processo
				clearAllProccess();
				setMessage('Erro ao tentar realizar login no aparelho, tente novamente mais tarde.');
			}

		}

		function loginSuccess(ret){

			//Aqui recebe o hash com a sessão, se faz necessário guardar em uma váriavel para autenticar nos outros métodos
			session = ret.session;

			var errorObj = {error : false, msgError : ""};

			//Extrai os Templates das 3 digitais
			templateToExtract.forEach(function(element,indice)
			{			
				extrairTemplate(element.altura,element.largura,element.template,
						
					//Sucesso		
					function(retorno)
					{	
						//Caso a qualidade da  digital for maior que 50% salva, caso não vai solicitar para o usuário refazer o processo
						if(retorno.quality > 50)
						{
							usuario.templateToMerge.push(retorno.template);
						}else{
							errorObj.error = true;
							errorObj.msg = "Qualidade da digital muito baixa("+retorno.quality+"%), limpe o leitor e tente novamente.";
						}		
					},

					//Erro
					function(retorno)
					{	
						//Caso ocorra algum erro, também solicita para o usuário refazer o processo
						errorObj.error = true;
						errorObj.msg = "Erro ao realizar a extração da digital, tente novamente mais tarde";
					}

				);

			});

			//Caso não ocorra algum erro, realiza o merge dos templates
			if(!errorObj.error){	

				//Realiza o merge dos templates, transformando 3 em 1.
				mergeTemplate(usuario.templateToMerge,
					
					function(retorno){
						
						//Caso não tenha erros adiciona ao array de "fingers"
						if(!retorno.error)
						{
							//Limpa o array de templates para merge
							usuario.templateToMerge = [];

							//Adiciona o template final para o array de "fingers"
							usuario.fingers.push(retorno.template);

							//Seta o tamanho de digitais cadastradas
							setCountFingers(usuario.fingers.length);
							//Limpa todo o processo
							clearAllProccess();
							//Habilita o botão de excluir e de finalizar o cadadtro
							$('#btnExcluirDigital').attr('disabled',false);
							$('#finalizarCadastro').attr('disabled',false);
						}else{
							clearAllProccess();
							setMessage(retorno.error);	
						}

					}	
				)	

			}else{
				clearAllProccess();
				setMessage(errorObj.msg);
			}

		}	

		$.ajax(content);
			
	}
	
	
	function extrairTemplate(altura,largura,digital,retornoOk,retornoError)
	{
		
		var urlbase = montarUrl("template_extract",true,"&width="+largura+"&height="+altura);
		var content = {
						url: urlbase,
						processData:false,
						crossDomain: true,
						async : false,
						type:"POST",
						contentType:"application/octet-stream",
						data: digital,
						success : retornoOk,
						error : retornoError   
					  }
	
				
		$.ajax(content);
		
	}


	/*
		Após extrair o template, será feito o merge, será passado um array com os Templates
		e retorna apenas uma String com o array já realizado o merge método :template_merge
	*/
	function mergeTemplate(templatesUsr,sucesso){


		var mergeParams = {
				templates: templatesUsr,
				remove: false,
				user_pis: 0,
				new_templates: []
		};

		var urlbase = montarUrl("template_merge",true,"");
			
		var obj = {
					url: urlbase,
					type: 'POST',
					async : false,
					processData:false,
					crossDomain: true,
					contentType:"application/json",
					data: JSON.stringify(mergeParams),
					success : sucesso
				  };

		$.ajax(obj);
	}
	


	/**
	* Funções de integração com o DOM 
    */

	var defaultMsg = "Aguardando solicitação do Usuário";


	function setCountFingers(count)
	{
		$('#fingerOkCount').text(count);	
	}


	/**
	 * Limpa o processo de template para merge, de template para extração, e retorna o formulário para o estado inicial
	 */
	function clearAllProccess(){
		loadControl(true);	
		setMessageInfo(defaultMsg);
		endAnyProccess();
		clearFingersImage();
		templateToExtract = [];
		usuario.templateToMerge = [];
		$('#imgDedo1').hide()
		$('#imgDedo2').hide();
		$('#imgDedo3').hide();	
	}


	//Habilita a progress bar
	function loadControl(remove){

		if(remove){
			$('#barInfo').removeClass('progress-bar-striped');
			$('#barInfo').removeClass('active');
		}else{
			$('#barInfo').addClass('progress-bar-striped');
			$('#barInfo').addClass('active'); 
		}

	}

	//Limpa todas as imagens de dedo
	function clearFingersImage(){
		$('#imgDedo1').attr('src','');
		$('#imgDedo2').attr('src','');
		$('#imgDedo3').attr('src','');

	}


	//Zera todo formulario
	function setFormDefaultState()
	{	
		$('#btnSalvarDigital').prop('disabled',true);
		$('#btnExcluirDigital').prop('disabled',true);
		$('#finalizarCadastro').prop('disabled',true);
		$('#fingerOkCount').text('0');
		$('#cpf').val('');
		$('#nome').val('');
		$('#observacao').val('');

	}

	//Seta mensagem da biometria
	function setMessageInfo(message){
		$('#infoLabel').text(message);
	}

	//desabilita botão de salvar
	function waitAnyProccess(){
		$('#btnSalvarDigital').prop('disabled',true);
	}

	//Habilita botão de salvar
	function endAnyProccess(){
		$('#btnSalvarDigital').prop('disabled',false);
	}

	//Monta a url de requisão para o equipamento
	function montarUrl(metodo,useSession,param){

		var sessao  = useSession ? "?session="+session : "";

		return equipamento.PROTOCOLO+"://"+equipamento.IP+"/"+metodo+".fcgi"+sessao+param;
		 
	}


	//Este método busca o equipamento da filial de joinville, filial padrão
	function getEquipamentoPadrao(){

		setMessageInfo('O Equipamento da sua filial está fora, estamos realizando o cadastro pelo equipamento da matriz, Aguarde..');

		$.ajax(
		{		
			url : "ti_biometria/retornaEquipamentoPadraoFilial",
			method : "GET",		
			success : function(ret)
					  { 	
						equipamentoPadrao = JSON.parse(ret);		
						
						//Retorna o equipamento da matriz, caso já esteja setado o equipamento da matriz, informa que o sistema esta fora
						if(equipamento.IP != equipamentoPadrao.IP)
						{	
							equipamento = equipamentoPadrao;
							loginOn();		
						}else
						{
							clearAllProccess();
							setMessage("Não estamos conseguindo conectar aos equipamentos, contate a TI");
						}
									
					  }
		});
	}


	//Limpa as digitais já extraidas e com o merge realizado
	function excluirDigitais(){

        if(usuario.fingers.length){
			$('#fingerOkCount').text('0');
			$('#finalizarCadastro').attr('disabled',true);
			$('#btnExcluirDigital').attr('disabled',true);
			usuario.fingers = [];
		}else{
			setMessage("Não há digitais para remover!");
		}

    }

	function gravarDigitais()
	{

		$("#modalLoad").show();
		
		//Verifica se já foi cadastrada alguma digital
		if(usuario.fingers){

			var arrErros = [];
			var arrFila  = [];
			var arrContinua = [];
			var arrOk = [];

			$.ajax(
			{	
					
				url : "ti_biometria/retornaIdClass",
				method : "GET",		
				success : function(ret)
						{ 	
							equipamentos = JSON.parse(ret);		
							
							 //Verifica todos os equipamentos que é para ser realizado o caddastro
							if(equipamentos.length){

								equipamentos.forEach(
									
									function(equipamento)
									{			
										
										//Realiza o login no aparelho
										loginCad(equipamento,
											
											//Login Sucesso
											function(retorno)
											{	
												//Caso ok adiciona ao arr para continuar o cadastro, caso não adiciona ao array de erros
												if(!retorno.error){
													arrContinua.push({sessao : retorno.session , equip : equipamento });
												}else{	
													arrErros.push(objError(equipamento,'Erro ao tentar realizar a autenticação - Cadastro - '+retorno.error));
												}	
												
											},

											//Login Erro
											function(retorno)
											{
												//Caso o erro for timeout, adiciona ao array de fila, que podera ser processado futuramente
												if(retorno.statusText == "timeout")
												{
													arrFila.push(objFila(equipamento,'CADASTRO'));
												}else{
													arrErros.push(objError(equipamento,'Erro ao tentar realizar a autenticação - Cadastro'));
												}	
												
											}
										)
									}
								);

								setTimeout(function() {
									
									//Pega todos os equipamentos que foram realizados o login e continua para realizar o cadastro
									arrContinua.forEach(
										
										function(item)
										{                                                                                                                                                                                                                                                                                                                                                                                               
											
											//Passa como parametro o ip e a sessao do equipamento, no método ele processa o usuário setado inicialmente
											cadastrarUsuario(item.equip.IP,item.sessao,
											
												//Cadastro Sucesso
												function(retorno)
												{	
													/** Caso ocorra erro adiciona ao array de erro para log 
													    Caso ok adiciona ao array de Ok para realizar vinculação Usuario x Equipamento
													*/
													if(!retorno.error){
														arrOk.push(objOk(item.equip));
													}else{
														arrErros.push(objError(item.equip,'Erro ao tentar realizar o Cadastro ' + retorno.error));
													}
												},

												//Cadastro Error
												function(retorno)
												{
													arrErros.push(objError(item.equip,'Erro ao tentar realizar o Cadastro'));
												}
												
											)
											
										}	
									)
									
								},6000)
							}
							

							setTimeout(function() 
							{	
								
								//Neste momento salva toda a operação realizada no banco de dados
								var objInfo = ({erro : arrErros , fila : arrFila , ok : arrOk } );
								usuario.comentario = $('#observacao').val(); 
								saveUser(objInfo ,

									//Retorno Ok
									function(retorno){
										$("#modalLoad").hide();
										var returnObj =  JSON.parse(retorno);

										if(returnObj.status ==  'ok')
										{	
											if(returnObj.edit)
											{
												delete usuario.edicao;
												$('#cpf').attr('disabled',false);
											}

											//Limpa todo processoe
											clearAllProccess();
											
											//Zera o usuário
											usuario.nome = "";
											usuario.cpf = "";
											usuario.fingers = [];
											usuario.comentario =  "";
											
											//Habilita o campo nome
											$('#nome').attr('disabled',false);	
											
											//Zera Formulário
											setFormDefaultState();

											//Seta Mensagem de Sucesso
											setMessage("Usuário cadastrado com sucesso!");

											
											
										}

									}
								);
							}, 7000);
						}
			});

	
		}
	}
	
	
	function objError(equipamento,mensagem){
		return {EQUIPAMENTOID : equipamento.ID , USUARIOCPF : usuario.cpf , MENSAGEM :  mensagem};
	}

	function objFila(equipamento,op){
		return {EQUIPAMENTOID : equipamento.ID , OPERACAO : op , USUARIOCPF : usuario.cpf };
	}

	function objOk(equipamento){
		return {EQUIPAMENTOID : equipamento.ID , USUARIOCPF : usuario.cpf  }
	}

	function loginCad(equipamento,loginSuccess,loginError)
	{

		var url =  "https://"+equipamento.IP+"/login.fcgi";
		
		var content = {
						url: url,
						type: 'POST',	
						contentType: 'application/json',
						data: JSON.stringify({login:equipamento.USUARIO ,password: equipamento.SENHA}),
						success: loginSuccess,
						error :  loginError,	
						timeout:5000			
					   };

		$.ajax(content);
	}


	function cadastrarUsuario(ip,sessao,sucesso,erro){
		
		var url =  "https://"+ip+"/add_users.fcgi?session="+sessao;

		
		$.ajax(
		{		
			url: url,
			type: 'POST',
			contentType: 'application/json',
			async: false,
			data: JSON.stringify(
			{
				users:
				[
					{    
						admin:false,
						bars:'',
						code:0,
						name: usuario.nome,
						password:'',
						pis: parseInt(usuario.cpf),
						rfid:0,
						templates: usuario.fingers
					}
				]
			}),
			success : sucesso,
			error : erro
		});
	}

	function saveUser(obj,okFunction){

		$.ajax(
				{
					url : "ti_biometria/salvar_usuario",
					method : "POST",
					data : {usuario : usuario , objInfo : obj },
					success : okFunction
				}
			  );

	}




	 /**
	 * Este método realiza a exclusão das digitais do usuário em todos os equipamentos que ele estiver cadastrado, e segue os mesmo passo do cadastro
	 * 
	 * 1º - Logar  
	 * 2º - Remover do Equipamento
	 * 3º - Excluir do Banco de dados 
	 * 
	 * Caso ok exclui vinculação usuario x equipamento, caso erro salva na tabela de log, caso timeout adiciona para a fila para processar posteriormente 
	 */
	function removerDigitaisIntegracao()
	{
		$("#modalLoad").show();

		var arrContinua = [];
		var arrErros = [];
		var arrFila = [];
		var arrOk = [];

		$.ajax({url : "ti_biometria/retornaEquipamentoUsuario",
				method : "POST",
				data : {CPF : usuario.cpf},
				success : 
						
						function(retorno)
						{

							retorno = JSON.parse(retorno);

							if(retorno)
							{	
								retorno.forEach(
									
									function(equipamento)
									{
										loginCad(equipamento,
											
											//Login Sucesso
											function(retorno)
											{
												
												if(!retorno.error)
												{
													arrContinua.push({sessao : retorno.session , equip : equipamento });
												}else{	
													arrErros.push(objError(equipamento,'Erro ao tentar realizar a autenticação - Remoção - '+retorno.error));
												}	
												
											},

											//Login Erro
											function(retorno)
											{
												if(retorno.statusText == "timeout")
												{
													arrFila.push(objFila(equipamento,'EXCLUIR'));
												}else{
													arrErros.push(objError(equipamento,'Erro ao tentar realizar a autenticação - Remoção'));
												}	
												
											}
										)
									}
								)

								setTimeout(

									function()
									{
										arrContinua.forEach(
											
											function(item){                                                                                                                                                                                                                                                                                                                                                                                               
					
												removeDoEquipamento(item.equip.IP,item.sessao,

													//Cadastro Sucesso
													function(retorno)
													{	
														
														if(!retorno.error){
															arrOk.push(objOk(item.equip));
														}else{
															arrErros.push(objError(item.equip,'Erro ao tentar realizar a remoção ' + retorno.error));
														}
													},

													//Cadastro Error
													function(retorno)
													{
														arrErros.push(objError(item.equip,'Erro ao tentar realizar a remoção'));
													}
													
												)
												
											}	
										)
								},6000)

								setTimeout(function() 
								{
									var objInfo = ({erro : arrErros , fila : arrFila} );
									$("#modalLoad").hide();
									saveLogDelete(objInfo ,

										//Ao terminar
										function(retorno)
										{                                                                                                                                                                                           
 
											var returnObj =  JSON.parse(retorno);

											if(returnObj.status ==  'ok')
											{
												$('#btnSalvarDigital').attr('disabled',false);
												$('#apagarDigitais').css('display','none');
												setMessage('Digitais excluidas! Clique em "Cadastrar Digital" para cadastrar as novas digitais');	
												
												setTimeout(() => {
													window.location.href = "ti_biometria/listar_usuario";	
												}, 1500);
											}else{
												clearAllProccess();
												setFormDefaultState();
												setMessage('Ocorreu um erro ao excluir');	
											}

										}
									);

								}, 7000);


							}else{
								clearAllProccess();
								setFormDefaultState();
								setMessage('Não foram encontrados equipamentos para este usuário');
							}
						}
				})
	}

	function removeDoEquipamento(ip,sessao,sucesso,erro){
			
		
		var url =  "https://"+ip+"/remove_users.fcgi?session="+sessao;

		var cpf =  parseInt(usuario.cpf.substr(3));
	
		$.ajax(
		{		
			url: url,
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({users:[cpf]}),
			success : sucesso,
			error : erro
		});
	
	}


	function saveLogDelete(obj,okFunction)
	{

		$.ajax(
			{
				url : "ti_biometria/removerUsuario",
				method : "POST",
				data : {CPF : usuario.cpf , objInfo : obj },
				success : okFunction
			}
		);
			  
	}

