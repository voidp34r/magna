function atualizar_estado(estado) {
    var destino = estado.data('destino');
    var options = estado.children('option');
    var municipio_selecionado = estado.attr('data-municipio');
    if (municipio_selecionado) {
        $.getJSON('geral_municipio/ver_json/' + municipio_selecionado, '', function (data) {
            options.filter(function () {
                return $(this).val() == data.UF;
            }).prop('selected', true);
            estado.selectpicker('refresh');
            atualizar_municipio(destino, data.UF, municipio_selecionado);
        });
    }
}

function atualizar_municipio(destino, uf, id) {
    $.getJSON('geral_municipio/listar_json/' + uf, '', function (data) {
        $('[name="' + destino + '"]').empty();
        $.each(data, function (i, valor) {
            $('[name="' + destino + '"]').append("<option value='" + i + "'>" + valor + "</option>");
        });
        if (id) {
            $('[name="' + destino + '"] option').filter(function () {
                return $(this).val() == id;
            }).prop('selected', true);
        }
        $('[name="' + destino + '"]').selectpicker('refresh');
    });
}

function atualizar_cep(cep, nome) {
    var validacep = /^[0-9]{8}$/;
    if (validacep.test(cep)) {
        $.getJSON('//viacep.com.br/ws/' + cep + '/json/?callback=?', function (dados) {
            var campo;
            for (var chave in dados) {
                campo = $('[data-cep="' + nome + '_' + chave + '"]');
                campo.val(dados[chave]);
                campo.removeAttr('readonly');
                if (chave == 'uf') {
                    campo.attr('data-municipio', dados.ibge);
                    atualizar_estado(campo);
                }
            }
        });
    }
}

function atualizar_pessoa(cpfcnpj, tipo, ativo, bloqueio) {
    var url = 'geral_consulta/ver_pessoa_json?CPFCNPJ=' + cpfcnpj;
    url += (tipo) ? '&TIPO=' + tipo : '';
    url += (ativo) ? '&ATIVO=' + ativo : '';
    $.getJSON(url, function (dados) {
        var campo;
        for (var chave in dados) {
            campo = $('[name="' + chave + '"]');
            if (chave != 'TIPO' && campo.length) {
                campo.val(dados[chave]);
                console.log("campo.val()!: " + campo.val());
                if ( bloqueio != '0' && campo.val().length > 0 ) {
                    campo.attr('readonly', 'readonly');
                } else {
                    campo.removeAttr('readonly');
                }
            }
        }
    });
}

function tratar_cpfcnpj(texto) {
    texto = texto.split('_').join('');
    texto = texto.split('.').join('');
    texto = texto.split('/').join('');
    texto = texto.split('-').join('');
    return texto;
}

function copyToClipboard(value) {
    var aux = document.createElement("input");
    aux.setAttribute("value", value);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
    
    console.log("Copiado: " + value);
}

function execContextMenuClick(element, value, opt){
    console.log("invokedOn: "    + element    + "\n" +
	    	    "invokedValue: " + value + "\n" +
	    	    "selectedMenu: " + opt);
	
	switch (opt) {
		case "Copiar":
			copyToClipboard(value);
			break;
		case "Copiar sem formatação":
			copiaValSemMascara(value);
			break;
	}
}

//Remove máscara
function removeMascara(value){
	return value.split(".").join("")
		.split("-").join("")
		.split("/").join("");
}

//Copia valor do elemento sem máscara
function copiaValSemMascara(value){
	copyToClipboard(removeMascara(value));
}

//Menu de contexto;
function initContextMenu($, window){	
	$.fn.contextMenu = function (settings) {
        return this.each(function () {
            //Menu de contexto
            $(this).on("contextmenu", function (e) {
                // Se pressionar CTRL retorna o menu nativo
                if (e.ctrlKey) return;
                
                //campo origem, valor origem..
                //alert(e.target.name);
                //alert(e.target.value)
                //alert(e.clientX + ", " + e.clientY);
                
                //Abre o menu
                var $menu = $(settings.menuSelector)
                    .data("invokedOn", e.target.name)
                    .data("invokedValue", e.target.value)
                    .show()
                    .css({
                        position: "absolute",
                        left: getMenuPosition(e.clientX, 'width', 'scrollLeft'),
                        top: getMenuPosition(e.clientY, 'height', 'scrollTop')
                    })

                    .off('click')
                    .on('click', 'a', function (e) {
                        $menu.hide();
                        
                        var $invokedOn = $menu.data("invokedOn");
                        var $invokedValue = $menu.data("invokedValue");
                        var $selectedMenu = e.target.text;
                        
                        settings.menuSelected.call(this, $invokedOn, $invokedValue, $selectedMenu);
                    });
                
                return false;
            });

            //Garantir que o menu vai fechar em todos os cliques
            $('body').click(function () {
                $(settings.menuSelector).hide();
            });
        });
        
        function getMenuPosition(mouse, direction, scrollDir) {
            var win = $(window)[direction](),
                scroll = $(window)[scrollDir](),
                menu = $(settings.menuSelector)[direction](),
                position = mouse + scroll;
            
            alert("win: " + direction + ": " + win + "\n scroll: " + scroll);
            /* Na hora de renderizar o menu de contexto, 
             * deve-se descontar o tamanho do menu lateral */
	        // opening menu would pass the side of the page
	        //if (mouse + menu > win && menu < mouse) 
	        //    position -= menu;
       
            if (direction == "width"){
            	 //Considera largura do menu lateral
            	position -= $("#sidenav").width();
            	
            	//Quando janela está no modo mobile
            	if (win <= 768){
            		
            	}
            	
            	//Tratamento para não estourar para a direita
            	var widthMenu = 200;
            	if (position > (win - widthMenu - $("#sidenav").width()))
            		position -= widthMenu;
            }
           
            return position;
        }
    };
}

$(function () {
    /* Funções nativas do Bootstrap */
    $('abbr').tooltip();
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({html: true});
    
	/*Selector para o Tooltip do bootstrap
    Obs: Verificar o que esse código de tooltip acima faz :) */
	$('body').tooltip({
	    selector: '[rel="tooltip"]'
	});
	
    /* Summernote - plugin para textarea com rich text */
    $.extend(true, $.summernote.lang, {
        'pt-BR': {
            print: {
                print: 'Imprimir'
            }
        }
    });
    $('.summernote').summernote('destroy');
    $('.summernote').summernote({
        lang: 'pt-BR',
        height: '500px',
        toolbar: [
            ['font', ['style', 'fontname', 'fontsize']],
            ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear', 'color']],
            ['paragraph ', ['ol', 'ul', 'paragraph', 'height']],
            ['misc', ['codeview']],
            ['do', ['undo', 'redo']],
            ['print', ['print']],
        ]
    });

    /* Fancybox - plugin galeria de fotos e documentos */
    $('.fancybox').fancybox();
    $('.fancybox-button').fancybox({
        prevEffect: 'none',
        nextEffect: 'none',
        closeBtn: false,
        arrows: false,
        helpers: {
            title: {type: 'inside'},
            buttons: {}
        },
        iframe: {
            preload: false
        }
    });

    /* Bootstrap-select - plugin <select> melhorado */
    $('select').selectpicker({
        iconBase: 'fa',
        tickIcon: 'fa-check',
        width: '100%',
        liveSearch: true,
        liveSearchPlaceholder: 'Pesquisar'
    });

    /* Atribuições de classes automatizada */
    $('input[type="text"]').addClass('form-control');
    $('input[type="password"]').addClass('form-control');
    $('textarea').addClass('form-control');

    /* Função para formatar campo CPF/CNPJ dependendo do tamanho do texto */
    $('.mascara_cpfcnpj').blur(function () {
        var texto = tratar_cpfcnpj($(this).val());
        if (texto.length == 11) {
            $(this).mask("999.999.999-99");
        } else if (texto.length == 14) {
            $(this).mask("99.999.999/9999-99");
        } else {
            $(this).val('');
        }
    });
    $('.mascara_cpfcnpj').focus(function () {
        $(this).unmask();
    });
    
    /* Definição de máscara para dia, mês, ano, hora e minuto */
    $.mask.definitions['d'] = "[0-3]";
    $.mask.definitions['m'] = "[0-1]";
    $.mask.definitions['y'] = "[1-2]";
    $.mask.definitions['h'] = "[0-2]";
    $.mask.definitions['i'] = "[0-5]";

    /* Máscaras genéricas */
    $('.mascara_ano').mask("9999");
    $('.mascara_cep').mask("99999-999");
    $('.mascara_cpf').mask("999.999.999-99");
    $('.mascara_cnpj').mask("99.999.999/9999-99");
    $('.mascara_placa_veiculo').mask("aaa9999");
    $('.mascara_data').mask("d9/m9/y999");
    $('.mascara_hora').mask("h9:i9");
    $('.mascara_datahora').mask("d9/m9/y999 h9:i9");
    $('.mascara_rg').mask('999999999');
    
    //Máscara auto-adaptável para telefones com 8 ou 9 dígitos.
    $('.maskPhone9Digit').mask("(99) 9999-9999?9").blur(function(event) {
        var target, phone, element;
        target = (event.currentTarget) ? event.currentTarget : event.srcElement;
        phone = target.value.replace(/\D/g, '');
        element = $(target);
        element.unmask();
        if(phone.length > 10) {
            element.mask("(99) 99999-999?9");
        } else {
            element.mask("(99) 9999-9999?9");  
        }
    });

    /* Função para atualizar o campo de municípios conforme estado selecionado */
    $('.estado').change(function () {
        var uf = $(this).val();
        var destino = $(this).data('destino');
        if (uf && destino) {
            atualizar_municipio(destino, uf, false);
        }
    });

    /* Função para percorrer todos os estados e marcar conforme seleção */
    $('.estado').each(function () {
        atualizar_estado($(this));
    });

    /* Função para buscar no site viacep.com.br o endereço conforme CEP */
    $('.cep').blur(function () {
        var nome = $(this).data('name');
        var cep = $(this).val().replace(/\D/g, '');
        if (cep != '') {
            atualizar_cep(cep, nome);
        }
    });

    // Somente números
    $('.numbersOnly').keyup(function () {  
        this.value = this.value.replace(/[^0-9\.]/g,''); 
    });
    
    /* Função para buscar os dados do cadastro de pessoa conforme CPF/CNPJ */
    $('.cpfcnpj').blur(function () {
        var tipo = $(this).attr('data-tipo');
        var ativo = $(this).attr('data-ativo');
        var bloqueio = $(this).attr('data-bloqueio');
        var pad = '00000000000000';
        var cpfcnpj = $(this).val();
        if (cpfcnpj) {
            cpfcnpj = cpfcnpj.replace(/\D/g, '');
            cpfcnpj = pad.substring(0, pad.length - cpfcnpj.length) + cpfcnpj;
            tipo = (tipo) ? tipo : null;
            ativo = (ativo) ? ativo : null;
            atualizar_pessoa(cpfcnpj, tipo, ativo, bloqueio);
        }
    });

    /* Função para percorrer todos os .form-control-static e atribuir .copy */
    $('.form-control-static').each(function () {
        var text = $(this).text();
        text = text.trim();
        text = text.toUpperCase();
        if ($(this).children().length == 0 && text != '') {
            $(this).addClass('copy');
            $(this).attr('data-text', text);
        }
    });

    /* Função para fazer a cópia do texto apenas clicando sobre */
    $('.copy').click(function () {
        copyToClipboard($(this).data('text'));
        $('.copiado').remove();
        $(this).append('<small class="copiado">(copiado)</small>');
    });

    /* Função para solicitar confirmação do usuário e retornar href caso "true" */
    $('.btn-confirm').click(function (e) {
        return confirm('Você tem certeza?');
    });

    /* Função para suavizar a piscada na troca de tela */
    $('#conteudo').fadeIn(500);
});