<script src='assets/js/cadastroUser.js'></script>

<style>
  
  .imgDedo{
	  height: 120px;
  	  border-radius : 50%;
      width : 120px;  
  }
  .barApp{
      width: 60%;
      height : 15px; 
      border-radius : 12px;
      margin-top :30px;
      margin-bottom : 0
  }

  .progress-bar{
        float:none;
        background-color : #34495e;
  }

</style>

<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?> biometria
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>   
        <div class="form-group">        
            <label class="col-sm-2 control-label">Tipo de Cadastro *</label>
            <div class="col-sm-4 form-control-static">
                    <?php 
                        $tpVisita = [
                            "1" => "FUNCIONÁRIO",
                            "2" => "AGREGADO", 
                            "3" => "TERCEIRO"
                        ]
                    ?>                    
                    <?= form_dropdown('TPCADASTRO', $tpVisita, $tipo, 'required id="TPCADASTRO" '); ?>            
            </div>    
        </div> 
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF *</label>
            <div class="col-sm-4">
                <?= form_input('CPF', set_value('CPF'),'required id="cpf"'); ?>
            </div>
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-4">
                <?= form_input('NOME', set_value('NOME'), 'required id="nome"' ); ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">PIS *</label>
            <div class="col-sm-4">
                <?= form_input('PIS', set_value('PIS'),' required id="pis"'); ?>
                <input type="hidden" name="registration" id="registration">
                <input type="hidden" name="rg" id="rg">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Departamentos</label>
            <div class="col-sm-4">
                <?php 
                    $dep = [];
                    if($departamentos){
                        foreach ($departamentos as $key => $value) {
                            $dep[$value->id] = $value->name;
                            //echo "<option value='{$value->id}'>{$value->name}</option>";
                        }
                    }  
                ?> 
                <?= form_dropdown('DEPARTAMENTO', $dep, '', 'required id="DEPARTAMENTO"  multiple="multiple" multiple'); ?>                  
                <?php 
                                                                  
                ?>                   
                </select>                  
            </div>
        </div>
        <?= form_close(); ?>

		<!-- Div de Cadastro Biométrico-->
		<div>
			<h4>Cadastro Biométrico</h4>
			<hr>
            <div id="infoError" class="alert alert-danger" style="display : none">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <label><label>
            </div>                
			<label>Quantidade cadastradas : <span id="fingerOkCount">0</span></label>
			<div class="row"    align="center">
				<div style="text-align:center; display : inline-block;margin-left : 20px;padding : 20px">
					<img class="imgDedo img-responsive" src="" id="imgDedo1">
					<br/>
					<label for="">Digital 1</label>	
				</div>
				<div style="text-align:center; display : inline-block;margin-left : 20px;padding : 20px">
					<img  class="imgDedo img-responsive" src="" id="imgDedo2">
					<br/>
					<label for="">Digital 2</label>	
				</div>
				<div style="text-align:center; display : inline-block;margin-left : 20px;padding : 20px">
					<img class="imgDedo img-responsive" src="" id="imgDedo3">
					<br/>
					<label for="">Digital 3</label>	
				</div>
				<div class="progress progress-bar barApp" id="barInfo">
					<div class="" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
				<label style="margin-bottom:20px" id="infoLabel">Aguardando solicitação do Usuário</label>
			</div>
			<br>
			<div align="center">
				<button class="btn btn-default" onclick="iniciarCadastroBiometrico()" disabled id="btnSalvarDigital">Cadastrar Digital</button>
                <button class="btn btn-default" onclick="excluirDigitais()" disabled id="btnExcluirDigital">Remover Digitais</button>
            </div>	
            
        </div>
        
		<!--// Div de Cadastro Biométrico-->
	

        <div style="margin-top: 30px" >
            <button class="btn btn-success btn-block" disabled onclick="gravarDigitais()" id="finalizarCadastro">Finalizar Cadastro</button>
            <button class="btn btn-danger btn-block" style="display : none" onclick="removerDigitaisIntegracao()" id="apagarDigitais">Excluir Digitais Atuais</button>
        </div>
		
        <!-- Modal de Info -->
        <div class="modal modal-md" id="modalInfo">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">    
                        <button class="close" onclick="closeModal()" style="color: white" >&times;</button>
                        <h4 class="modal-title">Atenção!</h4>
                    </div>
                    <div class="modal-body">
                        <p>Ocorreu um erro ao ...</p>
                    </div>   
                </div>
            </div>
        </div>
        <!--// Modal de Info -->

        <!-- Modal de Load -->
        <div class="modal modal-md" id="modalLoad">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Espere um momento</h4>
                    </div>
                    <div class="modal-body">
                        <p>Aguarde..</p>
                    </div>   
                </div>
            </div>
        </div>
        <!--// Modal de Load -->

    </div>
</div>



