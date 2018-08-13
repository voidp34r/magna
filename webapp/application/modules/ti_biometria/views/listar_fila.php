<style>

  .barApp{
      width: 100%;
      height : 20px; 
      border-radius : 12px;
      margin-top :10px;
      margin-bottom : 0
  }

  .progress-bar{
        float:none;
        background-color : #34495e;
  }

</style>
<button class="btn btn-success" onclick="processaFila()">
    <i class="fa fa-fw fa-refresh"></i>
    Processar fila
</button>
<div class="panel panel-default">
    <div class="panel-heading">
        Fila
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
    </div>
    <div class="panel-body table-responsive">

        <?php
        if (!empty($lista))
        {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Equipamento</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($lista as $item)
                    {
                        ?>
                        <tr>
                            <td>
                                <?= $item->ID; ?>
                            </td>
                            <td>
                                <?= $item->EQUIPAMENTODESC; ?>
                            </td>
                            <td>
                               <?= $item->USUARIONOME; ?> 
                            </td>
                            <td>
                                <?= $item->OPERACAO; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table> 
            <?php
        }
        ?>
    </div>

    <div class="modal modal-md" id="modalLoad">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Processar Fila</h3>
                </div>
                <div class="modal-body">
                    <div align="center">
                        
                        <b>Aguarde, estamos realizando o processamento da fila..</b>
                        <br> 
                        <label style="display : none" id="logMsg">
                        Processando <label id="qtAtual">0</label> de <label id="qtTotal">0</label>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-md" id="modalLog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" onclick="closeModal()" >&times;</button>
                    <h3 class="modal-title">Atenção</h3>
                </div>
                <div class="modal-body">
                    <div align="center">
                        <label id="infoLog"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>
<script>

    function closeModal(){
         $('#modalLog').hide();
    }

    /*
     *  Este método realiza o mesmo processo que existe no arquivo "auxilio.js".
     * 
     *  Realiza o login, tenta processar a ação carregada do BD, exemplo : "CADASTRO","EXCLUSÃO"
     *  A diferençãa que aqui como já estamos na fila não adiciona a fila novamente os processos que não derem certo, somente ao log de erro
     *  como os processo realizados no outro método o processo aqui é todo sincrono.
     */
    function processaFila()
    {   
        $('#modalLoad').show();
        $.ajax(
                {url : "ti_biometria/processar_fila",
                 success : 
                        function(retorno)
                        {
                            var fila = JSON.parse(retorno);
                            
                            
                            if(fila.length)
                            {	
                                var arrContinua = [];
                                var arrErro = [];
                                var arrOk = [];

                                $('#qtTotal').text(fila.length);
                                $('#logMsg').removeAttr('style');
                                fila.forEach
                                (
                                    function(filaItem,numeroAtual)
                                    {			
                                        $('#qtAtual').text(numeroAtual+1);
                                        
                                        loginEquipamento(filaItem.PROTOCOLO,filaItem.IP,filaItem.LOGIN,filaItem.SENHA,
                                            
                                            //Login Sucesso
                                            function(retorno)
                                            {	
                                                if(!retorno.error){
                                                    arrContinua.push(
                                                                        {
                                                                            ip : filaItem.IP, 
                                                                            protocolo : filaItem.PROTOCOLO, 
                                                                            sessao : retorno.session, 
                                                                            op : filaItem.OPERACAO,
                                                                            nome : filaItem.NOME,
                                                                            cpf : filaItem.USUARIOCPF,
                                                                            templates :  filaItem.DIGITAIS,
                                                                            IDEQUIP : filaItem.IDEQUIP,
                                                                            ID : filaItem.IDFILA,
                                                                            tipo : filaItem.TIPO,
                                                                        }
                                                                    )
                                                }else{
                                                    	
                                                    arrErro.push({EQUIPAMENTOID : filaItem.IDEQUIP , USUARIOCPF : filaItem.USUARIOCPF , MENSAGEM : 'Erro Fila, retorno: '+retorno.error});
                                                }	
                                                
                                            },

                                            //Login Erro
                                            function(retorno)
                                            {
                                                if(retorno.statusText != "timeout")
                                                {
                                                  arrErro.push({EQUIPAMENTOID : filaItem.IDEQUIP , USUARIOCPF : filaItem.USUARIOCPF , MENSAGEM : 'Erro ao tentar realizar login Fila'});
                                                }	
                                                
                                            }
                                        )
                                    }
                                );

                                setTimeout(function() {

                                    arrContinua.forEach(
                                        
                                        function(item)
                                        {              
                                       
                                            if(item.op == "CADASTRO")
                                            {
                                                if(item.tipo == "IDACCESS"){
                                                    console.log("idaccess");
                                                    cadastrarUsuarioIdAccess(item.protocolo,item.ip,item.sessao,item.nome,                                                    
                                                        //Cadastro Sucesso
                                                        function(retorno){	
                                                            if(!retorno.error)
                                                            {
                                                                var usuarioID = retorno.ids[0];
                                                                cadastraCpfIdAccess(item.protocolo,item.ip,item.sessao,item.cpf,usuarioID,
                                                                    //Cadastro Sucesso
                                                                    function(retorno){	
                                                                        if(!retorno.error)
                                                                        {                                                                                                                                                        
                                                                            cadastraTemplateIdAccess(item.protocolo,item.ip,item.sessao,item.templates,usuarioID);  
                                                                            cadastraGrupoIdAccess(item.protocolo,item.ip,item.sessao,usuarioID,
                                                                                //Cadastro Sucesso
                                                                                function(retorno){	
                                                                                    if(!retorno.error)
                                                                                    {
                                                                                        arrOk.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , filaID : item.ID, OP : "CADASTRO" });
                                                                                    }else{
                                                                                        arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                                                    }
                                                                                },

                                                                                //Cadastro Error
                                                                                function(retorno){
                                                                                    arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF :  item.cpf , MENSAGEM :  'Erro ao tentar realizar cadastro Fila'});
                                                                                }
                                                                            
                                                                            );                                                                          
                                                                        }else{
                                                                            arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                                        }
                                                                    },

                                                                    //Cadastro Error
                                                                    function(retorno){
                                                                        arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF :  item.cpf , MENSAGEM :  'Erro ao tentar realizar cadastro Fila'});
                                                                    }
                                                                );                                                                
                                                            }else{
                                                                arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                            }
                                                        },

                                                        //Cadastro Error
                                                        function(retorno){
                                                            arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF :  item.cpf , MENSAGEM :  'Erro ao tentar realizar cadastro Fila'});
                                                        }
                                                        
                                                    );
                                                }else{
                                                    cadastrarUsuario(item.protocolo,item.ip,item.sessao,item.nome,item.cpf,item.templates,
                                                
                                                        //Cadastro Sucesso
                                                        function(retorno)
                                                        {	
                                                            if(!retorno.error)
                                                            {
                                                                arrOk.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , filaID : item.ID, OP : "CADASTRO" });
                                                            }else{
                                                                arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                            }
                                                        },

                                                        //Cadastro Error
                                                        function(retorno)
                                                        {
                                                        arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF :  item.cpf , MENSAGEM :  'Erro ao tentar realizar cadastro Fila'});
                                                        }
                                                        
                                                    ) 
                                                }
                                               							                                                
                                            }else{
                                                if(item.tipo == "IDACCESS"){
                                                    console.log("idaccess - exclusão");
                                                    buscaUserId(item.protocolo,item.ip,item.sessao,item.cpf,
                                                        function(retorno)
                                                        {	
                                                            if(!retorno.error)
                                                            {
                                                                console.log(retorno);                                                                
                                                                if(retorno.c_users[0] == undefined){
                                                                    arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Usuário não encontrado no equipamento'});
                                                                }else{
                                                                    var usuarioID = retorno.c_users[0].user_id;
                                                                    removeDoEquipamentoIdAccess(item.protocolo,item.ip,item.sessao,usuarioID,                                                

                                                                        //Exclusão Sucesso
                                                                        function(retorno)
                                                                        {	
                                                                            if(!retorno.error)
                                                                            {
                                                                                arrOk.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , filaID : item.ID, OP : "EXCLUSAO" });
                                                                            }else{
                                                                                arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                                            }
                                                                        },

                                                                        //Exclusão Error
                                                                        function(retorno)
                                                                        {
                                                                            arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila'});
                                                                        }
                                                                        
                                                                    ) 
                                                                }                                                               
                                                            }else{
                                                                arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                            }
                                                        },

                                                        //Exclusão Error
                                                        function(retorno)
                                                        {
                                                        arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila'});
                                                        }
                                                        
                                                    ) 
                                                }else{
                                                    removeDoEquipamento(item.protocolo,item.ip,item.sessao,item.cpf,                                                

                                                        //Exclusão Sucesso
                                                        function(retorno)
                                                        {	
                                                            if(!retorno.error)
                                                            {
                                                                arrOk.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , filaID : item.ID, OP : "EXCLUSAO" });
                                                            }else{
                                                                arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila, retorno: '+retorno.error});
                                                            }
                                                        },

                                                        //Exclusão Error
                                                        function(retorno)
                                                        {
                                                        arrErro.push({EQUIPAMENTOID :item.IDEQUIP , USUARIOCPF : item.cpf , MENSAGEM : 'Erro ao tentar realizar cadastro Fila'});
                                                        }
                                                        
                                                    )
                                                }
                                                
                                            }
                                            
                                        }	
                                    )
                            
                                },6000)

                                setTimeout(

                                    function()
                                    {
                                        salvarOperacaoBd(arrOk,arrErro,
                                            
                                            function()
                                            {
                                                $('#modalLoad').hide(); 
                                                $('#logMsg').css('display','none');   
                                                $('#modalLog').show();
                                                $('#infoLog').append('');
                                                var Proc = 'Quantidade de filas processadas : ' + arrOk.length;
                                                var Error = 'Quantidade de erros occoridos : ' + arrErro.length;
                                                
                                                $('#infoLog').append(Proc+'<br>'+Error+', Atualizando a página..');

                                                
                                                setTimeout(function() {
                                                    window.location.href = 'ti_biometria/listar_fila'
                                                }, 2000);
                                            }
                                        );
                                        
                                    },8000);

                            }else{
                               $('#modalLoad').hide();
                               $('#infoLog').append('');
                               $('#infoLog').append('Não existem registros para serem processados');
                               $('#modalLog').show();
                               
                            }
                            
                            
                        }
                }
            )
    }

    function salvarOperacaoBd(arrOk,arrErro,okFunc)
    {
        $.ajax(
                {
                    url : 'ti_biometria/salvarOperacaoBd',
                    method : "POST",
                    data : {erro :  arrErro , ok : arrOk},
                    async : false,
                    success : okFunc
                }
              )
    }

    function cadastrarUsuarioIdAccess(protocolo,ip,sessao,nome,sucesso,erro){
        var url =  protocolo+"://"+ip+"/create_objects.fcgi?session="+sessao;

        $.ajax
        (
            {		
                url: url,
                type: 'POST',
                async : false,
                contentType: 'application/json',
                data: JSON.stringify(
                {  
                    join:"LEFT",
                    object:"users",
                    fields:[  
                        name
                    ],
                    where:[  

                    ],
                    orde:[  
                        name
                    ],
                    values:[  
                        {  
                            name: nome,
                            registration: ''
                        }
                    ]
                }),
                success : sucesso,
                error : erro
        });
    }

    function cadastraCpfIdAccess(protocolo,ip,sessao,cpf,userId,sucesso,erro){
        var url =  protocolo+"://"+ip+"/create_objects.fcgi?session="+sessao;

        $.ajax
        (
            {		
                url: url,
                type: 'POST',
                async : false,
                contentType: 'application/json',
                data: JSON.stringify(
                {  
                    join:"LEFT",
                    object:"c_users",
                    fields:[  
                    ],
                    where:[  

                    ],
                    order:[  
                    ],
                    values:[  
                        {  
                            user_id: userId,
                            cpf:cpf
                        }
                    ]
                }),
                success : sucesso,
                error : erro
        });
    }


    function cadastraTemplateIdAccess(protocolo,ip,sessao,templates,userId,sucesso,erro){
        var url =  protocolo+"://"+ip+"/create_objects.fcgi?session="+sessao;
        templates.forEach(function(item){
            $.ajax(
                {		
                    url: url,
                    type: 'POST',
                    async : false,
                    contentType: 'application/json',
                    data: JSON.stringify(
                    {  
                        join:"LEFT",
                        object:"templates",
                        fields:[  

                        ],
                        where:[  

                        ],
                        order:[                        
                        ],
                        values:[  
                            {  
                                finger_type : 0,
                                template : item,
                                user_id : userId
                            }
                        ]
                    })
            });
        })        
    }

    
    function cadastraGrupoIdAccess(protocolo,ip,sessao,userId,sucesso,erro){
        var url =  protocolo+"://"+ip+"/create_objects.fcgi?session="+sessao;

        $.ajax
        (
            {		
                url: url,
                type: 'POST',
                async : false,
                contentType: 'application/json',
                data: JSON.stringify(
                {  
                    object:"user_groups",
                    values:[  
                        {  
                            user_id : userId,
                            group_id : 1
                        }
                    ]
                }),
                success : sucesso,
                error : erro
        });
    }

    function cadastrarUsuario(protocolo,ip,sessao,nome,cpf,templates,sucesso,erro){
            
        var url =  protocolo+"://"+ip+"/add_users.fcgi?session="+sessao;

            
        $.ajax
        (
            {		
                url: url,
                type: 'POST',
                async : false,
                contentType: 'application/json',
                data: JSON.stringify(
                {
                    users:
                    [
                        {    
                            admin:false,
                            bars:'',
                            code:0,
                            name: nome,
                            password:'',
                            pis: parseInt(cpf),
                            rfid:0,
                            templates: templates
                        }
                    ]
                }),
                success : sucesso,
                error : erro
        });
    }

     function buscaUserId(protocolo,ip,sessao,cpf,sucesso,erro){
			
		
            var url =  protocolo+"://"+ip+"/load_objects.fcgi?session="+sessao;
            
            $.ajax(
            {		
                url: url,
                async : false,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({  
                    join:"LEFT",
                    object:"c_users",
                    fields:[  
                    ],
                    where:[ 
                      {
                        object:"c_users",
                        field:"cpf",
                        value:cpf,
                        connector:") AND ("
                      }
                    ],
                    order:[  
                    ]
                }),
                success : sucesso,
                error : erro
            });
        
        }

    function removeDoEquipamento(protocolo,ip,sessao,cpf,sucesso,erro){
			
		
		var url =  protocolo+"://"+ip+"/remove_users.fcgi?session="+sessao;

		var cpf =  parseInt(cpf.substr(3));
	
		$.ajax(
		{		
			url: url,
            async : false,
			type: 'POST',
			contentType: 'application/json',
			data: JSON.stringify({users:[cpf]}),
			success : sucesso,
			error : erro
		});
	
    }
    
    function removeDoEquipamentoIdAccess(protocolo,ip,sessao,userId,sucesso,erro){
					
            var url =  protocolo+"://"+ip+"/destroy_objects.fcgi?session="+sessao;
            
            $.ajax(
            {		
                url: url,
                async : false,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                        object:users,
                        where:
                        {
                            users:{
                                id:[
                                    userId
                                ]
                            }
                        }
                    }),
                success : sucesso,
                error : erro
            });
        
        }


    function loginEquipamento(protocolo,ip,usuario,senha,loginSuccess,loginError)
    {
        var url =  protocolo+"://"+ip+"/login.fcgi";
            
        var content =   {
                            url: url,
                            async : false,
                            type: 'POST',	
                            contentType: 'application/json',
                            data: JSON.stringify({login:usuario ,password:senha}),
                            success: loginSuccess,
                            error :  loginError,	
                            timeout:5000			
                        };

        $.ajax(content);
    }



</script>