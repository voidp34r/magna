/**
 * Aqui esta o auxilio de código para a integração com o 
 * aparelho FUTRONIC que realiza a leitura de digital.
 * 
 * Via Leitor Futronic :
 * 1 - Conectar ao serviço e ligar leitor
 *   2 - Obter dedo 1 e extrair o template - template_extract.fcgi
 * 3 - Obter dedo 2 e extrair o template -       ''
 * 4 - Obter dedo 3 e extrair o template -      ''
 * 5 - Juntar os templates - template_merge
 * 
 *  Atenção :    
 *     - Em todo retorno é validado se tem "retorno.error" já que a API da Control id retorna isso
 *      - Todos os métodos são "assync : false" ou seja quando realiza a requisão "trava" a interface do usuário 
 * 
 **/


//Model do Usuário
var usuario = { templateToMerge: [], templates: [], nome: "", cpf: "" };
//Variável que controla a sessão para o equipamento
var session;

//Array de Base 64 para realizar o merge das Digitais
var templateToExtract = [];

/**
 * Tipo do usuario
 * 1 - Funcionario
 * 2 - Agregado
 * 3 - Terceiro
 */
var tpUser = 1;

//Verifica se está em modo de Edição
var urlStr = window.location.href;
var urlArr = urlStr.split('/').pop();
var ID = '';
var EDIT = false;
//Verifica se a Url Tem parametro para editar
if (urlStr.split('/').length > 4) {
    //Procura o CPF do motorista    
    if (parseInt(urlArr)) {
        console.log(parseInt(urlArr));
        $.ajax({
            url: "rh_biometria/retornaUsuario",
            method: "POST",
            data: { ID: parseInt(urlArr) },
            success: function (ret) {
                ret = JSON.parse(ret);
                if (!ret.error) {
                    EDIT = true;
                    //Caso carregue com sucesso preenche o objeto em Js e habilita o campo de salvar digitais
                    usuario.nome = ret.funcionario.DSNOME;
                    usuario.cpf = ret.funcionario.NRCPF;
                    usuario.registration = ret.funcionario.CDMATRICULA;
                    usuario.rg = ret.funcionario.NRRG;
                    usuario.pis = parseInt(ret.funcionario.NRPIS);
                    $('#cpf').val(ret.funcionario.NRCPF);
                    $('#cpf').attr('disabled', true);
                    $('#nome').val(ret.funcionario.DSNOME);
                    $('#pis').val(ret.funcionario.NRPIS);
										$('#rg').val(ret.funcionario.NRRG);
										$('#cod').val(ret.funcionario.CDFUNCIONARIO);
                    $('#registration').val(ret.funcionario.CDMATRICULA);
                    let departamento = $("#DEPARTAMENTO").val();
                    if (departamento.length > 0) {
                        $('#btnExcluirUsuario').attr('disabled', false);
                    } else {
												$('#btnExcluirUsuario').attr('disabled', true);
												$('#piss').attr('disabled', true);
                    }
                } else {
                    //Caso ocorra erro o Controller retorna o tipo de erro
                    setMessage(ret.errorMg);
                    $('#nome').val('');
										$('#pis').val('');
										$('#rg').val('');
										$('#cod').val('');
										$('#registration').val('');
										$('#btnClean').attr('disabled', true)
										$('#btnSearch').attr('disabled', true);
										$('#btnExcluirUsuario').attr('disabled', true);
                }
            }
        });
    }
}

$(document).ready(function () {

    $('#nome').attr('disabled', true);
		$('#piss').attr('disabled', true);
		$('#btnSearch').attr('disabled', true);
		$('#btnClean').attr('disabled', true);

    //Seta as mascaras do input
    $("#cpf").mask("999.999.999-99");
    $("#pis").mask("999.9999.999-9");

    let tp = $("#TPCADASTRO").val();

    $("#TPCADASTRO").change(function () {
        let tp = $("#TPCADASTRO").val();
        usuario.tipo = tp;
		});
		
		usuario.tipo = 1;


		//"Ao sair" do input de cpf realiza a validação para ver se o mesmo consta no sistema
		$("#cpf").on('keypress', function () {
			$('#btnClean').attr('disabled', false);
			if (!EDIT) {
				let cpf = $("#cpf").val().replace(/[^\d]+/g,'');
					if (cpf.length > 0) {
						$('#btnClean').attr('disabled', false)
					}
					if (cpf.length > 10 ) {
						$('#btnSearch').attr('disabled', false);
					}
				}
		});
    $("#cpf").on('blur', function () {
        if (!EDIT) {
					let cpf = $("#cpf").val().replace(/[^\d]+/g,'');
					if (cpf.length < 1){
						$('#btnClean').attr('disabled', true)
					}
					if (cpf.length > 10) {
            validaUser($("#cpf").val());
					} else {
						setMessageInfo('CPF INVÁLIDO');
						setMessage('CPF INVÁLIDO');
						$('#nome').val('');
						$('#pis').val('');
						$('#rg').val('');
						$('#cod').val('');
						$('#registration').val('');
						$('#btnSearch').attr('disabled', true);
						$('#btnExcluirUsuario').attr('disabled', true);
					}
        }
    });

});

function validaUser(cpf) {
	$.ajax({
		url: "rh_biometria/valida_usuario",
    method: "post",
    async: false,
		data: { CPF: cpf },
		success: function (ret) {
			setMessageInfo('Buscando Informações do usuário');
			if (ret) {
				var ret = JSON.parse(ret);
				if (!ret.error) {
					setMessageInfo('Usuário encontrado');
					//Caso carregue com sucesso preenche o objeto em Js e habilita o campo de salvar digitais
					usuario.nome = ret.funcionario.DSNOME;
					usuario.cpf = ret.funcionario.NRCNPJCPF;
					usuario.registration = ret.funcionario.CDFRETEIRO;
					usuario.rg = ret.funcionario.NRRG;
					usuario.pis = parseInt(ret.funcionario.NRPIS);
					$('#nome').val(ret.funcionario.DSNOME);
					$('#pis').val(ret.funcionario.NRPIS);
					$('#rg').val(ret.funcionario.NRRG);
					$('#cod').val(ret.funcionario.CDFUNCIONARIO);
					$('#registration').val(ret.funcionario.CDMATRICULA);
					if (usuario.pis) {
						$('#btnExcluirUsuario').attr('disabled', false);
					} else {
						$('#btnExcluirUsuario').attr('disabled', true);
					}
			} else {
					setMessageInfo('Usuário não encontrado');
					//Caso ocorra erro o Controller retorna o tipo de erro
					setMessage(ret.errorMg);
					$('#nome').val('');
					$('#cpf').val('');
					$('#pis').val('');
					$('#rg').val('');
					$('#cod').val('');
					$('#registration').val('');
					$('#btnClean').attr('disabled', true)
				  $('#btnSearch').attr('disabled', true);
					$('#btnExcluirUsuario').attr('disabled', true);
					return (ret.errorMg);
				}
			}
		}
	});
}

function removerUser(cpf) {
	// var user = validaUser(cpf);
	let usercpf = $("#cpf").val().replace(/[^\d]+/g,'');
	let userpis = $("#pis").val().replace(/[^\d]+/g,'');
	$.ajax({
	url: "rh_biometria/remove_usuario",
    method: "post",
    async: false,
	data: { CPF: cpf, PIS: pis },
	success: function (ret) {
			if (ret) {
				var ret = JSON.parse(ret);
				alert(ret);
			}
		}
	});
  
}


function closeModal() {
    $('#modalInfo').hide();
}

function setMessage(msg) {
    $('#modalInfo').show();
    $('#modalInfo').find('p').text(msg);
}

/**
* Funções de integração com o DOM 
*/

var defaultMsg = "Aguardando solicitação do Usuário";


//Zera todo formulario
function setFormDefaultState() {
	$('#nome').val('');
	$('#cpf').val('');
	$('#pis').val('');
	$('#rg').val('');
	$('#cod').val('');
	$('#registration').val('');
	$('#btnClean').attr('disabled', true)
	$('#btnSearch').attr('disabled', true);
	$('#btnExcluirUsuario').attr('disabled', true);
	setMessageInfo(defaultMsg);
}

//Seta mensagem da biometria
function setMessageInfo(message) {
    $('#infoLabel').text(message);
}

//desabilita botão de salvar
function waitAnyProccess() {
    $('#btnExcluirUsuario').prop('disabled', true);
}

//Habilita botão de salvar
function endAnyProccess() {
    $('#btnExcluirUsuario').prop('disabled', false);
}

