<a class="btn btn-success" href="ti_usuario/cadastar_usuario">
    <i class="fa fa-fw fa-plus"></i>
    Novo Usuário
</a>
<div class="panel panel-default">
    <div class="panel-heading">
        Usuários
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
                    <th>Usuario</th>
                    <th>Nome</th>
                    <th>Filial</th>
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
                            <?= $item->USUARIO; ?>
                        </td>
                        <td>
                            <?= $item->USUARIO_NOME; ?>
                        </td>
                        <td>
                            <?= $item->CDEMPRESA; ?>
                        </td>                         
                        <td align="right"> 
                            <a href="ti_mobile/editar_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                    <i class="fa fa-fw fa-pencil"></i>
                            </a>                             
                            <a href="ti_mobile/excluir_usuario/<?= $item->ID; ?>" type="button" class="btn btn-xs btn-default">
                                <i class="fa fa-trash-o"></i>
                            </a>
                            <button onclick="habilitaModal('<?=$item->ID;?>','<?=$item->USUARIO_NOME;?>')" stype="button" class="btn btn-xs btn-default">
                                <i class="fa fa-key" aria-hidden="true"></i>
                            </button>                            
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
    
    <div class="modal" id='modalPass'>
        <div class="modal-dialog modal-sm">
            <div class="modal-content">   
                    <div class="modal-header">    
                        <button class="close" data-dismiss='modal' >&times;</button>
                        <h4 class="modal-title">Digite a nova senha</h4>
                    </div>

                    <div class="modal-body">
                        
                        <div class="alert " id='alertaDiv'>
                            <button class="close" onclick='fecharAlert()'>
                                <span>&times;</span>
                            </button>
                            <b><p id='alertInfo'></p></b>
                        </div>
                        
                        
                        <div class="form-group">
                            <label>Código do Usuário</label>
                            <input id='idUser' class="form-control" style='inline : block' required disabled>                                
                        </div>
                        
                        <div class="form-group">
                            <label>Nome do Usuário</label>
                            <input id='nameUser' class="form-control" required disabled>
                        </div>                        
                        
                        <div class="form-group">
                            <label>Nova Senha</label>
                            <input id='newPassword' type="password" class="form-control" placeholder="Digite sua nova senha" required>
                        </div>

                        <div class="form-group">
                            <label>Confirme a senha</label>
                            <input id='newPasswordConf' type="password" class="form-control" placeholder="Confirme sua nova senha" required>
                        </div>  
                       
                        <div class="form-group" id='modalLoad' style="display : none">
                            <label>Carregando..</label>
                            <div   class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" style="width:100%"></div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer"> 
                        <button id='btnSavePass' onclick="alterarSenha()" class="btn btn-success">Salvar</button>
                        <button id='btnCancelPass' class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                
            </div>
        </div>
    </div>
</div>
<script>
    
    /**
     * @description Habilita o modal com os dados passado por parametro
     * @param idUser : Código do Usuário a ser editado
     * @param nameUser : Nome do Usuário que será editado
     * */
    function habilitaModal(idUser,nameUser){
        
        $('#idUser').val(idUser);
        $('#nameUser').val(nameUser);
        $('#newPassword').val('');
        $('#newPasswordConf').val('');
        fecharAlert();
        $('#modalPass').modal('show');
        $('#modalLoad').css({'display' :'none'});
    };
    
    
    function alterarSenha(){
        
        if( $('#newPasswordConf').val() == $('#newPassword').val()){
            
            
            $('#modalLoad').removeAttr('style');
            $('#btnSavePass').prop('disabled',true);
            $('#btnCancelPass').prop('disabled',true);
            
            $.post('ti_usuario/alterar_senha',{id: parseInt($('#idUser').val()), pass : $('#newPasswordConf').val()},function(ret){
                
                var retorno = {};
                
                try{
                   retorno  = JSON.parse(ret);   
                }catch(err){
                   retorno["ok"] = false; 
                }
                 
                
                if(retorno.ok){
                   $('#alertaDiv').addClass('alert-success'); 
                   setaInfoModal('Senha alterada com sucesso.');
                }else{
                    $('#alertaDiv').addClass('alert-danger');
                    setaInfoModal('Ocorreu um problema ao alterar a senha, verifique se você tem permissão.');
                }
                
            }).done(function(){
                             $('#modalLoad').css({'display' :'none'});
                             $('#btnSavePass').prop('disabled',false);
                             $('#btnCancelPass').prop('disabled',false);
            });
            
        }else{
           $('#alertaDiv').addClass('alert-danger');
           setaInfoModal('As senhas não estão iguais.');  
        }
    };
    
    function fecharAlert(){
        $('#alertaDiv').hide();
    }
    
    function setaInfoModal(mensagem){
           $('#alertInfo').text(mensagem); 
           $('#alertaDiv').show(); 
    }
    
     
    
</script>



