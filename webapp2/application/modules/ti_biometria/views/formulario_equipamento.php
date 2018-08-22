<?= botao_voltar(); ?>
<?php if($_POST && !isset($_POST['INTEGRADO'])){
        if($_POST['TIPO'] != 'IDSECURE'){  ?>
        <!--<button class="btn btn-default" id="btnSync" onclick="addUsersDb(<?=set_value('ID')?>)">
            <i class="fa fa-cloud-download" aria-hidden="true"></i>
            Atualizar Usuários equipamento
        </button>-->
<?php }} ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> equipamento
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">
                <?= form_input('NOME', set_value('NOME'), 'required autofocus'); ?>
            </div>
            <label class="col-sm-2 control-label">Filial *</label>
            <div class="col-sm-4">
                <?= form_dropdown('CDEMPRESA', $filiais, set_value('CDEMPRESA')); ?>
            </div>
        </div>            
        <div class="form-group">
            <label class="col-sm-2 control-label">Usuário *</label>
            <div class="col-sm-4">
                <?= form_input('USUARIO', set_value('USUARIO'), 'required'); ?>
            </div>
            <label class="col-sm-2 control-label">Senha *</label>
            <div class="col-sm-4">
                <?= form_password('SENHA', set_value('SENHA'), 'required'); ?>
            </div>
        </div>    
        <div class="form-group">
            <label class="col-sm-2 control-label">IP *</label>
            <div class="col-sm-4">
                <?= form_input('IP', set_value('IP'), 'required'); ?>
            </div>         
            <label class="col-sm-2 control-label">Tipo *</label>
            <div class="col-sm-4 form-control-static">
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'IDACCESS', set_value('TIPO', 'IDACCESS') == 'IDACCESS'); ?>
                        iDAccess
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'IDCLASS', set_value('TIPO') == 'IDCLASS'); ?>
                        iDClass
                    </label>
                </div>
                <div class="radio-inline">
                    <label>
                        <?= form_radio('TIPO', 'IDSECURE', set_value('TIPO') == 'IDSECURE'); ?>
                        iDSecure
                    </label>
                </div>
            </div>    
        </div>    
        <div class="form-group" id="PORTA">
            <label class="col-sm-2 control-label">Porta </label>
            <div class="col-sm-4">
                <?= form_input('PORTA', set_value('PORTA'), 'oninput="numberOnly(this)"'); ?>
            </div>
        </div> 
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <div class="checkbox">
                    <label>
                        <?= form_checkbox('PADRAO', 1, set_value('PADRAO')); ?>
                        Equipamento padrão
                    </label>
                </div>
            </div>
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <div class="checkbox">
                    <label>
                        <?= form_checkbox('SINCRONIZAR', 1, set_value('SINCRONIZAR')); ?>
                        Sincronizar equipamento
                    </label>
                </div>
            </div>
        </div>    
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>

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
                        <button class="btn btn-default btn-block" id="btnConcluir" onclick="closeModal()">Concluir</button>
                    </div>   
                </div>
            </div>
        </div>
        <!--// Modal de sincronização com o IdBox -->  

    </div>
</div>
<script>

    $(document).ready(function(){
       
        if(!$("input[name='TIPO']")[2].checked){
            $("#PORTA").hide();
        }

        $("input[name='TIPO']").change(function(data){
            if(data.currentTarget.value == 'IDSECURE'){
                campoPorta(true);
            }else{
                campoPorta(false);
            }
        });
    
    })

    function numberOnly(input){
        input.value = input.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
    }

    var insertIntegration;
    var equipamento;
    var id ;
    function addUsersDb(id){
        id = id;

        $('#modalSync').show();
        setFormStyle(true);

        $.ajax({
            url : "ti_biometria/retornaUserIntegation/"+id,
            method : "GET",
            success : startIntegration,
        });
    }


    function campoPorta(habilita){
        if(habilita){
            $("#PORTA").show();
        }else{
            $("#PORTA").hide();
            $("#PORTA").val(null);
        }
    }

    function startIntegration(data){

        var response = JSON.parse(data);

        insertIntegration =  response.userData ;

        equipamento = response.equipData;

        logar(equipamento.IP,equipamento.USUARIO,equipamento.SENHA,
                
                function(data){

                 if(!data.error){
                    insertUsers(equipamento.IP,data.session); 
                 }        

            });

    }

    function insertUsers(ip,sessao){

        insertIntegration.forEach(function(usuario){
            usuario.pis = parseInt(usuario.pis);
            addUser(ip ,sessao,usuario);
        });

        $.ajax({
            url : "ti_biometria/updateEquipmentStatus/"+id,
            method : "GET"
        });

        setFormStyle(false);
    }   

    function setFormStyle(isLoad)
    {
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

    function closeModal(){
        $('#modalSync').hide();
    }

    function addUser(ip,session,user,func){


        var url =  "https://"+ip+"/add_users.fcgi?session="+session;
		
		$.ajax(
		{		
			url: url,
			type: 'POST',
            async : false,
            timeout : 5000,
            contentType: 'application/json',
			data: JSON.stringify({ "users": [user]}),
			success : func
		});
    }

    function logar(ip,usuario,senha,loginSuccess){

        var url =  "https://"+ip+"/login.fcgi";
		
		var content = {
						url: url,
						type: 'POST',	
						contentType: 'application/json',
						data: JSON.stringify({login:usuario ,password: senha}),
						success: loginSuccess			
                       };
                       
        $.ajax(content);               
    }

</script>