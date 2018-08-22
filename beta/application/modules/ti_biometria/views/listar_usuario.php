<!--a class="btn btn-success" href="ti_biometria/adicionar_usuario">
    <i class="fa fa-fw fa-plus"></i>
    Novo usuário
</a-->

<a class="btn btn-default" id="btnSync">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
    Importar Tudo para o Banco
</a>
<a class="btn btn-default" id="btnUserSync">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
     Importar usuário específico
</a>

<a class="btn btn-primary" id="btnCsv">
    <i class="fa fa-cloud-download" aria-hidden="true"></i>
     Importar CSV IdBox
</a>

<div class="panel panel-default">
    <div class="panel-heading">
        Usuários
        <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>            
        </small>
        <a class="pull-right" data-toggle="collapse" href="#filtro">Filtro</a>
    </div>
    <div class="collapse" id="filtro">
        <div class="panel-body">
            <?= form_open('ti_biometria/listar_usuario', 'method="GET"'); ?>
            <div class="row">
                <div class="col-sm-3">
                    <label>Nome</label>
                    <?= campo_filtro($filtro, 'DSNOME', 'like'); ?>
                </div>
                <div class="col-sm-3">
                    <label>CPF</label>
                    <?= campo_filtro($filtro, 'CPF', 'like'); ?>
                </div>
            </div>
            <br>
            <input type="submit" value="Filtrar" class="btn btn-sm btn-primary">
            <?= form_close(); ?>
        </div>
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
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Digitais Cadastrada</th>
                        <th></th>
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
                                <?= $item->DSNOME; ?>
                            </td>
                            <td>
                                <?= substr($item->CPF,3,11) ; ?>
                            </td>
                            <td>
                                <?= $item->QTDIGITAIS; ?>
                            </td>
                            <td>
                                
                            </td>
                            <td align="right">
                                <a href="ti_biometria/editar_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                                </a>
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

        <!-- Modal de sincronização com o IdBox -->
        <div class="modal modal-md" id="modalSync">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">    
                        <button class="close" onclick="closeModal()"  id="close">&times;</button>
                        <h4 class="modal-title">Sincronização de Usuário</h4>
                    </div>
                    <div class="modal-body">
                        <center>
                            <b>
                                <p id="pWait">Aguarde..<p>
                                <p id="pSubmitEnd">Operação finalizada!<p>
                            </b>
                        </center>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-block" id="btnConcluir" onclick="closeModal()">Concluir</button>
                    </div>   
                </div>
            </div>
        </div>
        <!--// Modal de sincronização com o IdBox -->

        <!-- Modal de Seleção de usuário -->
        <div class="modal modal-md" id="modalUser">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">    
                        <button class="close" onclick="closeModalUser()"  id="close">&times;</button>
                        <h4 class="modal-title">Sincronização de Usuário</h4>
                    </div>
                    <div class="modal-body">
                        <center>
                            <div id="select_div">
                                
                                <div class="row">
                                    <div class="col-sm-8">
                                        <input type="text" id="userName"  placeholder="Nome do Usuário">
                                    </div>
                                    <div class="col-sm-4">
                                        <button class="btn btn-success" id="findUser" onclick="filtrarUsuario()">Buscar usuário</button>
                                    </div>
                                </div>

                               <div style="margin-top: 10px; height : 350px; overflow-y: scroll;">
                                <table class="table table-hover" >
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Registro</th>
                                            <th>Nome</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="usuariosEncontrados">

                                    </tbody>
                                </table>
                                </div>
                            </div>
                            <div id="edit_div" style="margin-top: 10px; height : 350px;">

                                <div style="display: none" class="alert" id="alertDiv"> 
                                    <strong id="msgAlert"></strong>
                                </div>
 
                                <div class="col-md-4 form-group">
                                    <input type="text" id="idUser" disabled />
                                </div> 
                                <div class="col-md-12 form-group">
                                    <input type="text" id="nameEdit" disabled />
                                </div>
                                <div class="col-md-12 form-group">
                                    <input type="text" placeholder="Digite o cpf aqui" id="cpfUser"/>
                                </div>
                                <button class="btn btn-success btn-block" id="btnInitIntregracao" onclick="exportaUsuarioDb()">Intregrar</button>
                                <button class="btn btn-default btn-block" id="btnTermina" onclick="resetData()">Voltar</button>
                            </div>
                        </center>
                    </div>
                    <div class="modal-footer" id="loadUser">
                        <center>
                            <Label><b>Aguarde carregando..</b></label>
                        </center>
                    </div>   
                </div>
            </div>
        </div>
        <!-- Modal de Seleção de usuário -->

    </div>
</div>
<nav>
    <ul class="pagination">
        <?= $paginacao; ?>
    </ul>
</nav>
<script> 
        
    $('#btnTermina').hide();    
    $('#loadUser').hide();
    $('#edit_div').hide();

    $('#btnUserSync').on('click',function(){
        $('#modalUser').show();
    });

    $('#btnSync').on('click',function(){

        $('#modalSync').show();
        setFormStyle(true);
        
        /*$.ajax({
            url : "ti_biometria/integrar_idbox"
        }).done(function(){
            setFormStyle(false);
        });*/
        $.ajax({
            url : "ti_biometria/integrarIdSecure"
        }).done(function(data){
            data =JSON.parse(data);
            if(data.retorno == 'erro'){
                alert(data.msg);
            }         
           
            setFormStyle(false);
        });
        
        
    });

    $('#btnCsv').on('click',function(){

        $('#modalSync').show();
        setFormStyle(true);

        $.ajax({
            url : "ti_biometria/csvIdBox"
        }).done(function(data){
            console.log('sucesso');
            window.location = 'ti_biometria/outputCSV/1/file.csv/true';
            setFormStyle(false);
        });

    });

    function exportaUsuarioDb(){
        let idIntegration =  $('#idUser').val();
        let cpf = $("#cpfUser").mask('');
        $('#loadUser').show();
        $('#btnInitIntregracao').attr('disabled',true);

        $.ajax({
            url : `ti_biometria/verificar_cpf/${idIntegration}/${cpf}`,
        }).done(function(data){
            data = JSON.parse(data);
            $('#loadUser').hide();
            $('#btnInitIntregracao').attr('disabled',false);
            let classe = data.ok ? 'alert-success' : 'alert-danger';
            let msg = !data.msg ? 'Usuário inserido com sucesso' : data.msg;
            $('#btnTermina').show();
            $('#msgAlert').text(msg)
            $('#alertDiv').show();
            $('#alertDiv').addClass(classe);
            $('#msgAlert').val(msg);
        });
    }
    
    $('#btnTermina').click(() => {
        clearForm();
        $('#select_div').show();
        $('#edit_div').hide();
        $('#btnInitIntregracao').attr('false',true);
        $('#edit_div').hide();
        $('#usuariosEncontrados').find('tr').remove()
        $('btnTermina').hide();
        $('#userName').val('');
        $('#alertDiv').hide()
    });


    function filtrarUsuario(){
        
        setFormStyleUser(true);
        let user = $('#userName').val();
        $.ajax({
            url : `ti_biometria/integrar_idbox/${user}`,
        }).done(function(data){
            setFormStyleUser(false);
            data = JSON.parse(data);
            $('#usuariosEncontrados').find('tr').remove()
            if(data.users.length){
                data.users.forEach(element => {
                    addRow(element);
                 });                  
            }else{
                alert('Nenhum usuário encontrado!');
            }
        });
    }

    function closeModal(){
         $('#modalSync').hide();
    }

    function closeModalUser(){
         $('#modalUser').hide();
    }

    function clearForm(){
        $('#nameEdit').val('');
        $('#cpfUser').val('');
        $('#idUser').val('');
        $('#cpfUser').mask('999.999.999-99');
    }

    function populateForm(data){
        $('#nameEdit').val(data.name);
        $('#idUser').val(data.id); 
    }

    function setFormStyle(isLoad){
        if(isLoad){
            $('#pSubmitEnd').hide();
            $('#pWait').show();
            $('#btnConcluir').hide();
        }else{
            $('#pSubmitEnd').show();
            $('#pWait').hide();
            $('#btnConcluir').show();
        }
    }

    function setFormStyleUser(isLoad){
        if(isLoad){
            $('#loadUser').show();
            $('#findUser').attr('disabled',true);
        }else{
            $('#loadUser').hide();
            $('#findUser').attr('disabled',false);
        }
    }

    function integrar(user){ 
        $('#select_div').hide();
        $('#edit_div').show();
        clearForm();
        populateForm(user);
    }   

    function addRow(user){
        
        var html = "<tr>" +
                        "<td>"+user.id+"</td>" + 
                        "<td>"+user.registration+"</td>" +
                        "<td>"+user.name+"</td>" +
                        "<td><input type='checkbox' onclick='integrar("+JSON.stringify(user)+")' /></td>" +
                    "</tr>";


        $('#usuariosEncontrados').append(html);
        
    }

</script>