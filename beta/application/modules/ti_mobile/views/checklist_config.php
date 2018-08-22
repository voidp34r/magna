<!-- Estilo Css-->
<style>
    
    .callModal:hover{
        color : #c0392b;
        text-decoration: underline; 
        cursor: pointer;
    }
    
    .modal {
      text-align: center;
    }

    @media screen and (min-width: 768px) { 
      .modal:before {
        display: inline-block;
        vertical-align: middle;
        content: " ";
        height: 100%;
      }
    }

    .modal-dialog {
      display: inline-block;
      text-align: left;
      vertical-align: middle;
    }
    
    .scrollit{
        overflow:scroll;
        width: 100%;
        overflow-x:hidden;
    }

    .sortable { list-style-type: none; 
                margin: 0; padding: 0; 
                width: 60%; 
                
    }
    
    .sortable li { margin: 0 5px 5px 5px; 
                  padding: 5px; 
                  font-size: 1.2em; 
                  height: 1.5em; 
    }
    
    html>body .sortable li { height: 1.5em; 
                             line-height: 1.2em; 
    }
    
    .ui-state-highlight { height: 1.5em; 
                         line-height: 1.2em; 
    }
    
    
</style>
<?= botao_voltar(); ?>
  
    <!-- Lista de configurações -->
    <div class="panel panel-default">
        <div class="panel-heading">
            Configurações Checklist
        </div>
        <div class="panel-body">

            <table class="table table-hover table-hover table-striped"> 
                <thead>
                    <tr>
                        <th>Configuração</th>
                        <th>Descrição</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ordenação das Fotos</td>
                        <td>Ordenção das Fotos do Checkist</td>
                        <td align="right">
                            <a type="button" class="btn btn-xs btn-default" onclick="loadModalCategoria(true)" data-toggle="modal" title="Teste">
                                Categoria de Fotos
                                <i class="fa fa-outdent" aria-hidden="true"></i>
                            </a>

                            <a type="button" class="btn btn-xs btn-default" onclick="loadModalFoto(true)" data-toggle="modal">
                                <i class="fa fa-file-image-o" aria-hidden="true"></i>
                                Fotos
                            </a>

                        </td>
                    </tr>
                </tbody>
            </table>        
        </div>
    </div>
    <!-- //Lista de configurações -->
     
    <!-- Modal de Categorias -->
    <div class="modal fade col-sm-12" id="modalCategoria">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Categoria de Fotos</h4>
                </div>
                
                <div class="modal-body">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div id="infoCategoria" class="alert" style="display: none">
                                <button class="close" onclick="closeAlert('infoCategoria')">&times;</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="display:none" id='dividCategoria'>
                                <label>Cod</label>
                                <input type="text" placeholder="" disabled id='idCategoria'>    
                            </div>
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" placeholder="Nome" id='nomeCategoria'>    
                            </div>
                            <div class="form-group">
                                <label>
                                    <?= form_checkbox('FLINATIVO', 1, set_value('FLINATIVO'),'id="cb_categoria"'); ?>
                                    Inativo
                                </label>
                            </div>
                           
                            <div class="form-inline">
                                <button class="btn btn-success btn-block" id="btnSalvarCategoria" onclick="salvarCategoria()">
                                    Salvar
                                </button>
                            </div>

                            <div class="form-group" style="margin-top: 5px" >
                                <button style="display:none" onclick="limparFormCategoria()" class="btn btn-default btn-block" 
                                        id="btnCancelarCategoria">
                                    Cancelar
                                </button>
                            </div>
                           
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <label>Quantidade:</label>&nbsp;&nbsp;<label id="qtQueryCategoria"><b>0</b></label>
                            </div>
                            <div class="row">
                                <div class="scrollit" style="height: 150px ">
                                    <table class="table table-hover table-striped"> 
                                        <thead>
                                            <tr>
                                                <th>Cod.</th>
                                                <th>Nome</th>
                                                <th>Seq.</th>  
                                                <th>Ativo</th>
                                                <th></th>
                                            </tr>
                                        </thead>

                                        <tbody id="tableCategoria">

                                        </tbody>                                    
                                    </table>     
                                </div>                                
                            </div>    
                        </div>
                        <div class="col-md-12">
                            <div class="row" align="center">
                                <label>Sequencia</label>
                            </div>
                            <div class="row">  
                                <div class="scrollit" align="center" style="height : 200px">
                                    <ul id="sortable" class="sortable" style="margin-left : 25px"></ul>
                                </div>
                            </div>
                            <div class="row" align="center">
                                <button  style="margin-top:10px;margin-left: 28px" class="btn btn-success" onclick="atualizaSequencia(true)">
                                        Salvar Sequencia
                                </button>  
                            </div>
                        </div>
                        
                    </div>
                    
                    
                </div>
            </div>
        </div>
    </div>
    <!-- //Modal de Categorias -->
    
    <!-- Modal de Configurações das Fotos -->
    <div class="modal fade col-sm-12" id="modalFoto">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Fotos</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="infoFotos" class="alert" style="display: none">
                                <button class="close" onclick="closeAlert('infoFotos')">&times;</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group" style="display:none" id="divIdFoto">
                                <label for="idFoto">Código *</label>
                                <input id='idFoto' class="form-control" disabled type="text">                                
                            </div>
                            <div class="form-group">
                                <label for="foto">Foto *</label>
                                <input id='nomeFoto' class="form-control" type="text" placeholder="Descrição da Foto">
                            </div>
                            <div class="form-group">
                                <label>Categoria *</label>
                                <?= form_dropdown('FOTO_CATEGORIA_ID', $categoria, set_value('FOTO_CATEGORIA_ID'), 'required id="FOTO_CATEGORIA"'); ?>                               
                            </div> 
                            <div class="form-group">
                                <label>
                                    <?= form_checkbox('ATIVO', 1, set_value('ATIVO'),'id="cb_obrigatorio"'); ?>
                                    Obrigatória
                                </label>
                            </div>
                            <div class="form-inline">
                                <button class="btn btn-success" id="btnSalvarFoto" onclick="salvarFotoConfig()">Salvar</button>
                                <button class="btn btn-danger" onclick="removeFotoConfig()"  id="btnExcluirFoto" style="display:none">
                                    Excluir
                                </button>
                            </div>
                            <div class="form-group" style="margin-top: 5px">
                                <button onclick="limparForm()" style='display:none' class="btn btn-default btn-block" id="btnCancelarFoto">
                                    Cancelar
                                </button>
                            </div>
                            
                            
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <label>Quantidade:</label>&nbsp;&nbsp;<label id="qtQuery"><b>0</b></label>
                            </div>
                            <div class="row">
                                <div class="scrollit" style="height: 150px ">
                                    <table class="table table-hover table-striped"> 
                                        <thead>
                                            <tr>
                                                <th>Cod.</th>
                                                <th>Foto</th>
                                                <th>Categoria</th>
                                                <th>Obrigatório</th>
                                                <th></th>
                                            </tr>
                                        </thead>

                                        <tbody id="tableFoto">

                                        </tbody>                                    
                                    </table>     
                                </div>                                
                            </div>
                            
                        </div>
                    </div>
                </div>                    
            </div>
        </div>
    </div>
    <!-- //Modal de Configurações das Fotos -->
    
    <!-- Modal de Ordenação de Fotos  -->
    <div class="modal" id="modalOrdenaFotos">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Ordenação das Fotos</h4>
                </div>
                
                <div class="modal-body">
                <div class="row">  
                    <div class="scrollit" align="center">
                        <ul id="sortableFotosCategoria" class="sortable">
                            
                            
                        </ul>
                    </div>
                </div>
                </div>
                
                <div class="modal-footer">
                    <button class="btn btn-success" onclick="atualizaSequencia(false)">Salvar</button>
                    <button class="btn btn-danger" onclick="showLoad('modalOrdenaFotos',false)">Cancelar</button>
                </div>

            </div>                
         </div>
    </div>
    <!-- //Modal de Ordenação de Fotos  -->
    
    
    <!-- Modal de Load -->
    <div class="modal modalLoad" id="modalLoad" style="margin:0 auto" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>Carregando..</label>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" style="width:100%"></div>
                            </div>                                               
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- //Modal de Load -->
    


    
<script>
    
    $( function() {
        $( "#sortable" ).sortable({
          placeholder: "ui-state-highlight"
        });
    });
    
    $( function() {
        $( "#sortableFotosCategoria" ).sortable({
          placeholder: "ui-state-highlight"
        });
    });


    
    function salvarFotoConfig(){
       
       var objPost = {DSDESCRICAO : $('#nomeFoto').val(),
                     FOTO_CATEGORIA_ID : parseInt($('#FOTO_CATEGORIA').find(":selected").val())
                     };
                     
       if(validaCamposObrigatorio(objPost,'infoFotos')){
           
           objPost['FLOBRIGATORIO'] = $('#cb_obrigatorio').prop('checked') ? 1 : 0;
           showLoad('modalLoad',true);
           
           $.post('ti_mobile/salvar_foto_config',objPost,function(retorno){
               
               if(retorno){
                   setMessageAlert(true,'Configuração de foto salva com sucesso!','infoFotos');
                   limparForm();
                   loadModalFoto(false);
               }else{
                   setMessageAlert(false,'Ocorreu um problema ao tentar salvar!','infoFotos');
               }
            
           }).done(function(){
               showLoad('modalLoad',false);
           });

        } 
    };
    
    function editarFotoConfig(){
        
        var objPostEdit = {DSDESCRICAO : $('#nomeFoto').val(),
                           FOTO_CATEGORIA_ID : parseInt($('#FOTO_CATEGORIA').find(":selected").val())
                          };
                          
        if(validaCamposObrigatorio(objPostEdit,'infoFotos')){
            
            objPostEdit['FLOBRIGATORIO'] = $('#cb_obrigatorio').prop('checked') ? 1 : 0; 
            
            showLoad('modalLoad',true);
            $.post('ti_mobile/atualizar_foto_config/'+$('#idFoto').val(),objPostEdit,function(retorno){
                
                if(retorno){
                   setMessageAlert(true,'Configuração de foto alterada com sucesso!','infoFotos');
                   limparForm();
                   loadModalFoto(false);        
                }else{
                   setMessageAlert(true,'Ocorreu um problema ao tentar salvar!','infoFotos'); 
                }
            }).done(function(){
                showLoad('modalLoad',false);
            });
        }
    }
    
    function removeFotoConfig(){
         
         showLoad('modalLoad',true);
         
         $.post('ti_mobile/remover_foto_config/'+$('#idFoto').val(),null,function(retorno){
             
             if(retorno){
                setMessageAlert(true,'Configuração de Foto Exlcuida com sucesso!','infoFotos');
                limparForm();
                loadModalFoto(false);       
             }else{
                setMessageAlert(true,'Ocorreu um erro ao excluir a foto!','infoFotos'); 
             }
             
         }).done(function(){
            showLoad('modalLoad',false);
         })
    }

    function showLoad(loadId,show){
        
        if(show){
            $('#'+loadId).modal('show'); 
        }else{
            $('#'+loadId).modal('hide');
        }
    }
    
    function validaCamposObrigatorio(objObg,alert){
        
        var notOk = false;
        for(var i in objObg){ 
            
            if (!objObg[i]){
                $("#"+alert).addClass('alert-danger');
                $("#"+alert).find('p').remove();
                $("#"+alert).append('<p>Existem Campos Obrigatórios em branco!</p>');
                $("#"+alert).show();
                notOk = true;
            };  
        };
        return !notOk ? true : false;
        
    };
    
    function setMessageAlert(success,message,idAlert){
        
        //Remove toda a classe e seta a default
        $('#'+idAlert).removeClass();
        $('#'+idAlert).addClass('alert');
        
        if(success){
            $('#'+idAlert).addClass('alert-success');
        }else{
            $('#'+idAlert).addClass('alert-danger');
        }
        
        $("#"+idAlert).find('p').remove();
        $("#"+idAlert).append('<p>'+message+'</p>');
        $("#"+idAlert).modal('show'); 
        
    }
    
    function closeAlert(alert){
        $("#"+alert).hide();
    }
    
    function loadModalFoto(loadIni){
        
        showLoad('modalLoad',true);
        
        $.get('ti_mobile/load_foto_config',function(data){
            
            var retJson = JSON.parse(data);
            if(retJson.arr){
                setDataTable('tableFoto',retJson.arr);
                $('#qtQuery').text(retJson.arr.length);
            }; 
            
        }).done(function(){
            showLoad('modalLoad',false);
        });
        
        //Se for a primeira vez que está carregando inicia o modal
        if(loadIni)
            showLoad('modalFoto',true);
        
    }
    
    function setDataTable(tableName,data){
        //Inicializa e faz as configurações iniciais
        var html = "";
        $('#'+tableName).find('tr').remove();
        
        for(indexArr in data){
            html += '<tr>';
            
            var objFoto = data[indexArr];
            var strObj  =   JSON.stringify(objFoto);
            
                 
            html += '<td>'+objFoto.ID+'</td>'    
            html += '<td>'+objFoto.DSDESCRICAO+'</td>'    
            html += '<td>'+objFoto.CATEGORIA+'</td>'
            
            //Obrigatório ou não
            var classe  =   objFoto.FLOBRIGATORIO == 1 ? 'success' : 'danger';
            var texto    =  objFoto.FLOBRIGATORIO == 1? 'SIM' : 'NÃO';
            html += '<td><div class="label label-' +classe + '">' + texto + '</div></td>'
            
            //Botão Editar
            html += '<td align="right">';
            html += "<a onclick='editarFoto("+strObj+")' type='button' class='btn btn-xs btn-default'>";
            html += '<i class="fa fa-fw fa-pencil"></i></a>'   
            html += '</td>'
            
            html += '</tr>';
        }
        $('#'+tableName).append(html);
    }
    
    function setDataTableCategoria(data){
        
        var html = "";
        $('#tableCategoria').find('tr').remove();
        for(object in data){
            
            html+= '<tr>';
            html+= '<td>'+data[object].ID+'</td>';
            html+= '<td>'+data[object].NOME+'</td>';
            
            if(data[object].SEQUENCIA){
                html+= '<td>'+data[object].SEQUENCIA+'</td>';
            }else{
               html+= '<td><b>-</b></td>' 
            }
            
            //Obrigatório ou não
            var classe  =   data[object].FLINATIVO == 0 ? 'success' : 'danger';
            var texto    =  data[object].FLINATIVO == 0? 'SIM' : 'NÃO';
            html += '<td><div class="label label-' +classe + '">' + texto + '</div></td>'
            
            //Botão Editar
            html += '<td align="right">';
            html += "<a onclick='editarCategoria("+JSON.stringify(data[object])+")' type='button' class='btn btn-xs btn-default'>";
            html += '<i class="fa fa-fw fa-pencil"></i></a>'   
            html += '</td>'
            html+= '</tr>';
            
        }
        $('#tableCategoria').append(html);
    }
    
    function editarFoto(obj){
    
        //Mostra o Botão e o código da Foto na Input
        $('#divIdFoto').removeAttr('style');
        $('#btnExcluirFoto').removeAttr('style');
        $('#btnCancelarFoto').removeAttr('style');
        $('#btnSalvarFoto').attr("onclick","editarFotoConfig()");
        
        //Seta os Valores para os campo
        $('#idFoto').val(obj.ID);
        $('#nomeFoto').val(obj.DSDESCRICAO);
        $('#FOTO_CATEGORIA').val(obj.FOTO_CATEGORIA_ID).change(); 
        $('#cb_obrigatorio').prop('checked',parseInt(obj.FLOBRIGATORIO) ? true : false ); // ? 1 : 0;
    }
    
    function limparForm(){
        $('#btnSalvarFoto').attr("onclick","salvarFotoConfig()");
        $('#divIdFoto').css('display','none');
        $('#btnExcluirFoto').css('display','none');
        $('#btnCancelarFoto').css('display','none');
        $('#idFoto').val("");
        $('#nomeFoto').val("");
        $('#FOTO_CATEGORIA').val("1").change(); 
        $('#cb_obrigatorio').prop('checked',false);
    }
    
    function loadModalCategoria(iniCategoria){
        
        showLoad('modalLoad',true);
        
        $.get('ti_mobile/load_categoria',function(data){
            
            var retJson = JSON.parse(data);
            
            if(retJson){
                setDataTableCategoria(retJson);
                $('#qtQueryCategoria').text(retJson.length);
            }; 
            
        }).done(function(){
            showLoad('modalLoad',false);
        });
        
        getSequenciaCheckList();
        
        if(iniCategoria)
            showLoad('modalCategoria',true);
        
    }
    
    function limparFormCategoria(){
        
        $('#btnSalvarCategoria').attr("onclick","salvarCategoria()");
        $('#dividCategoria').css('display','none');
        $('#btnCancelarCategoria').css('display','none');
        $('#idCategoria').val("");
        $('#nomeCategoria').val("");
        
    }
    
    function salvarCategoria(){
       var objPost = {NOME : $('#nomeCategoria').val()};
       
      if(validaCamposObrigatorio(objPost,'infoCategoria')){
        objPost['FLINATIVO'] = $('#cb_categoria').prop('checked') ? 1 : 0;
        
        showLoad('modalLoad',true);
        
        $.post('ti_mobile/salvar_categoria',objPost,function(retorno){
            
            if(retorno){
                setMessageAlert(true,'Configuração de Categoria salva com sucesso!','infoCategoria');
                limparFormCategoria();
                loadModalCategoria(false);
            }else{
                setMessageAlert(false,'Ocorreu um problema ao tentar salvar a categoria','infoCategoria');
            }
            
        }).done(function(){
            showLoad('modalLoad',false);
        });
      } 
       
    }
    
    function editarCategoria(obj){
       
        //Mostra o Botão e o código da Foto na Input
        $('#dividCategoria').removeAttr('style');
        $('#btnCancelarCategoria').removeAttr('style');
        $('#btnSalvarCategoria').attr("onclick","editarCategoriaUpdate()");
        
        $('#idCategoria').val(obj.ID);
        $('#nomeCategoria').val(obj.NOME);
        $('#cb_categoria').prop('checked',parseInt(obj.FLINATIVO) ? true : false ); // ? 1 : 0;
    }
    
    function editarCategoriaUpdate(){
      
      var objPost = {NOME : $('#nomeCategoria').val()};
       
      if(validaCamposObrigatorio(objPost,'infoCategoria')){
        objPost['FLINATIVO'] = $('#cb_categoria').prop('checked') ? 1 : 0;
        showLoad('modalLoad',true);
        
        $.post('ti_mobile/atualizar_categoria/'+$('#idCategoria').val() ,objPost,function(retorno){
            
            if(retorno){
                setMessageAlert(true,'Configuração de foto salva com sucesso!','infoCategoria');
                limparFormCategoria();
                loadModalCategoria(false);
            }else{
                setMessageAlert(false,'Ocorreu um problema ao tentar salvar a categoria','infoCategoria');
            }
            
        }).done(function(){
            showLoad('modalLoad',false);
        });
      }     
    }
    
    function getSequenciaCheckList(){
        
        showLoad('modalLoad',true);
        
        $.get('ti_mobile/checklist_sequencia',function(data){
            
            var retJson = JSON.parse(data);
            
            if(retJson){
               setSequencia(retJson);
            }; 
            
        }).done(function(){
            showLoad('modalLoad',false);
        });   
    }
      
    function getSequenciaFotosChecklist(id){
        
        showLoad('modalLoad',true);
        
        $.get('ti_mobile/fotos_sequencia/'+id,function(data){
            
            var retJson = JSON.parse(data);
            
            if(retJson){
               setSequenciaCategoriaFotos(retJson);
               showLoad('modalOrdenaFotos',true);
            }; 
            
        }).done(function(){
            showLoad('modalLoad',false);
        });
    }
    
    function setSequenciaCategoriaFotos(dados){
        
        $('#sortableFotosCategoria').find('li').remove();
        
        for(index in dados){
            $('#sortableFotosCategoria').append('<li class="ui-state-default" seq="'+dados[index].SEQUENCIA+'" id="'+dados[index].ID+'">'+
                        '<label class="callModal">'+dados[index].DSDESCRICAO+'</label>'+
                     '</li>');
        }
        
            
    }
    
    function setSequencia(dados){
        
        
        $('#sortable').find('li').remove();
        for(index in dados){
          
          $('#sortable').append('<li class="ui-state-default" seq="'+dados[index].SEQUENCIA+'" id="'+dados[index].ID+'">'+
                                 '<label class="callModal" onclick="getSequenciaFotosChecklist('+dados[index].ID+')" >'+dados[index].NOME.toUpperCase()+'</label>'+
                                '</li>');
        }
         

    }
    
    function atualizaSequencia(categoria){
        
        var arrSeq = [];
        
        var id = categoria ? 'sortable' : 'sortableFotosCategoria';
        
        $('#'+id+' li').each(function(i)
        {
            var objSeq = {SEQUENCIA : i + 1 , ID : $(this).attr('id') };
            arrSeq.push(objSeq);
        });
        
        var objPost = {arr : arrSeq };
       
       
        showLoad('modalLoad',true);
        
        $.post('ti_mobile/atualiza_checklist_sequencia/'+categoria,objPost,function(retorno){
            
            if(retorno){
                
                if(categoria){
                   limparFormCategoria();
                }else{
                    showLoad('modalOrdenaFotos',false);
                }
                loadModalCategoria(false);
                setMessageAlert(true,'Configuração de foto salva com sucesso!','infoCategoria');
  
            }else{
                setMessageAlert(true,'Ocorreu um erro ao atualizar a ordenção!','infoCategoria');
            }
            
        }).done(function(){
            showLoad('modalLoad',true);
        });
    }


</script>
    


