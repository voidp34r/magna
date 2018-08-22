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
</style>

<script src='assets/js/webcam/webcam.min.js'></script>

<?= botao_voltar(); ?>
<?= form_open('', array('id' => 'cadastroVisitante')); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $titulo; ?>
    </div>
    <!-- 
        2- SETAR AS MASKS
    -->
    <div class="panel-body">
        <div class="form-group">
            <div class="col-sm-6">
                <label for="NOMEVISITA">Nome *</label>
                <?= form_input('NOMEVISITA', set_value('NOMEVISITA'), 'required id="NOMEVISITA"'); ?>
            </div>
            <div class="col-sm-3">
                <label for="TPDOCTO">Tipo do documento *</label>  
                <?= form_dropdown('TPDOCTO', ["CPF" => "CPF","RG" => "RG"] , set_value('TPDOCTO'), 'required id="TPDOCTO"' ); ?>
            </div>
            <div class="col-sm-3">
                <label for="NRDOCUMENTO">Documento *</label>
                <?= form_input('NRDOCUMENTO', set_value('NRDOCUMENTO'), 'required class="mascara_cpf" id="NRDOCUMENTO"'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                    <?php 
                        $tpVisita = [
                            "CLIENTE" => "CLIENTE",
                            "FORNECEDOR" => "FORNECEDOR", 
                            "AGREGADO" => "AGREGADO",
                            "AJUDANTE" => "AJUDANTE",
                            "FUNCIONARIO" => "FUNCIONÁRIO"
                        ]
                    ?>
                    <label for="TPVISITA">Tipo da visita</label>  
                    <?= form_dropdown('TPVISITA', $tpVisita , set_value('TPVISITA'), 'required id="TPVISITA" '); ?>
            </div>
            <div class="col-sm-4">
                <label for="DSEMPRESA">Empresa *</label>
                <?= form_input('DSEMPRESA', set_value('DSEMPRESA'), 'required id="DSEMPRESA"'); ?>
            </div>
            <div class="col-sm-4">
                <label for="TELEFONEEMPRESA">Telefone Empresa *</label>
                <?= form_input('TELEFONEEMPRESA', set_value('TELEFONEEMPRESA'),'required id="TELEFONEEMPRESA" class="maskPhone9Digit"'); ?>
            </div>
        </div>

        <div class="col-sm-12">&nbsp;</div>

        <div class="col-sm-12">
        <label for="BTNCAM">Retirar Foto *  &nbsp;</label>
            <a type="buttom" id="BTNCAM" class="btn btn-primary" onclick="modalPictureFace(false)"><i class="fa fa-user fa-lg"></i></a> 
            <a type="buttom" id="BTNCAM" class="btn btn-primary" onclick="modalPictureDocument(false)"><i class="fa fa-file-text fa-lg"></i></a> 
        </div>

        <?= form_input('IDIMG', set_value('IDIMG'), 'id="IDIMG"'); ?>

        <div class="col-sm-12">&nbsp;</div>

        <div class="form-group">
            <div class="col-sm-12">
                <label for="MOTIVOVISITA">Motivo da visita *</label>
                <?= form_textarea('MOTIVOVISITA', set_value('MOTIVOVISITA'),'required id="MOTIVOVISITA"'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <label for="SOLICITANTE">Solicitante da Visita*</label>
                <?= form_input('SOLICITANTE', set_value('SOLICITANTE'), 'required id="SOLICITANTE"'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-6">
                <label for="FGVEICULO">Tem Veículo? </label>  
                <?= form_dropdown('FGVEICULO', ["NÃO" => "NÃO","SIM" => "SIM"] , set_value('FGVEICULO'), 'required id="FGVEICULO" '); ?>
            </div>
            <div class="col-sm-6">
                <label for="TPVEICULO">Tipo do Veículo? </label>  
                <?php 
                    $veiculos = ["Carro" => "Carro","Camionete" => "Camionete","Moto" => "Moto","Caminhão" => "Caminhão"]
                ?>
                <?= form_dropdown('TPVEICULO', $veiculos, set_value('TPVEICULO'), 'required class="veiculo-enable" id="TPVEICULO"'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <label for="MODELO">Modelo</label>
                <?= form_input('MODELO', set_value('MODELO'), 'required id="MODELO" class="veiculo-enable"'); ?>
            </div>
            <div class="col-sm-4">
                <label for="COR">Cor</label>
                <?= form_input('COR', set_value('COR'), 'required id="COR" class="veiculo-enable"'); ?>
            </div>
            <div class="col-sm-4">
                <label for="PLACA">Placa</label>
                <?= form_input('PLACA', set_value('PLACA'), 'required id="PLACA" class="veiculo-enable mascara_placa_veiculo"'); ?>
            </div>
        </div>
    </div>
    <div align="center" style="padding:20px">
        <button onclick="salvar(event)" class="btn btn-success btn-block">Salvar</button> 
    <div>
</div>

<?= form_close(); ?>
<!--Modal Face-->
<div class="modal" id="modalFace">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-user fa-lg"></i> Retire foto do rosto!</h4>
            </div>
            
            <div class="modal-body">
                <div class="row" align="center">

                    <div class="col-sm-6">
                        <div id="cameraFace" ></div>
                        <br>
                        <a class="btn btn-default" onclick="takeSnap(true)"><i class="fa fa-camera"></i></a>
                    </div>                 

                    <div class="col-sm-6">
                        <div id="previewFace"></div>
                    </div>
                    
                </div>    
                <div  class="row" align="center">
                    <input type="button" class="btn btn-danger" value="Cancelar" onclick="modalPictureFace(true)">
                    <input type="button" class="btn btn-success" onclick="$('#modalFace').modal('hide');" value="Salvar">
                </div>                            
                    
            </div>
            
        </div>
    </div>
</div>
<!--/Modal Face-->

<!--Modal Document-->
<div class="modal" id="modalDocument">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-file-text fa-lg"></i> Retire foto do documento do visitante!</h4>
            </div>
            
            <div class="modal-body">
                <div class="row" align="center">

                    <div class="col-sm-6">
                        <div id="cameraDocument" ></div>
                        <br>
                        <a class="btn btn-default" onclick="takeSnap(false)"><i class="fa fa-camera"></i></a>
                    </div>                 

                    <div class="col-sm-6">
                        <div id="previewDocument"></div>
                    </div>
                    
                </div>    
                <div  class="row" align="center">
                    <input type="button" class="btn btn-danger" value="Cancelar" onclick="modalPictureDocument(true)">
                    <input type="button" class="btn btn-success" onclick="$('#modalDocument').modal('hide');" value="Salvar">
                </div>                            
                    
            </div>
            
        </div>
    </div>
</div>
<!--/Modal Document-->


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
//Instacia dois obejtos, pois precisamos de duas imagens
    let faceCam;
    let documentCam;
    $(document).ready(function(){
        $('.veiculo-enable').attr('disabled',true);
        $('.mascara_phone').mask('(99) 9999-9999');
        $("#IDIMG").hide();
        //Seta as configuração da webcam
        Webcam.set({
            width: 420,
            height: 315,
            dest_width: 640,
            dest_height: 480,
            image_format: 'jpeg',
            jpeg_quality: 100,
            force_flash: false
        });
        faceCam = Webcam;
        documentCam = Webcam;
        faceCam.attach('#cameraFace');
        documentCam.attach('#cameraDocument');        
    });        

    $(function () {
        $("#NRDOCUMENTO").focusout(function(event){    
            showLoad('modalLoad',true);        
            $.ajax({
                url : "frotas_portaria/buscaVisitante/"+this.value
            }).done(function(data){
                showLoad('modalLoad',false);
                data = JSON.parse(data);
                if(data.ID){
                    $("#NOMEVISITA").val(data.NOMEVISITA);
                    $("#DSEMPRESA").val(data.DSEMPRESA);
                    $("#TELEFONEEMPRESA").val(data.TELEFONEEMPRESA);
                    $("#SOLICITANTE").val(data.SOLICITANTE);
                    $("#TPVISITA").val(data.TPVISITA).change();
                    $("#MOTIVOVISITA").val(data.MOTIVOVISITA);
                    $("#FGVEICULO").val(data.FGVEICULO).change();
                    $("#TPVEICULO").val(data.TPVEICULO).change();
                    $("#MODELO").val(data.MODELO);
                    $("#COR").val(data.COR);
                    $("#PLACA").val(data.PLACA);                               
                }
            });
        });
        $('#TPDOCTO').change(function(e){
            if(e.target.value == "CPF"){
                $('#NRDOCUMENTO').mask('999.999.999-99');
            }else{
                $('#NRDOCUMENTO').mask('999999999');
            }
        });
        $('#FGVEICULO').change(function(e){
            if(e.target.value == "SIM"){
                $('.veiculo-enable').removeAttr('disabled');
                $(".dropdown-toggle[data-id='TPVEICULO']").removeClass("disabled");
            }else{
                $(".dropdown-toggle[data-id='TPVEICULO']").addClass("disabled");
                $('.veiculo-enable').val('');
                $('.veiculo-enable').attr('disabled',true);
            }
        });
    });

    //Tira a foto e salva em um campo img
    function takeSnap(face) {
        if(face){
            faceCam.snap( function(data_uri) {
                document.getElementById('previewFace').innerHTML = '<img id="previewImgFace" class="img-thumbnail" src="'+data_uri+'"/>';
            } );
        }else{
            documentCam.snap( function(data_uri) {
                document.getElementById('previewDocument').innerHTML = '<img id="previewImgDocument" class="img-thumbnail" src="'+data_uri+'"/>';
            });
        }
    }

    //Abre o modal e inicia a webcam
    function modalPictureFace(isOpen){
        if(isOpen){
            $('#modalFace').modal('hide');
        }else{
            $('#modalFace').modal('show');            
            
        }
    }

    //Abre o modal e inicia a webcam
    function modalPictureDocument(isOpen){
        if(isOpen){
            $('#modalDocument').modal('hide');
        }else{
            $('#modalDocument').modal('show');            
           
        }
    }

    //Realiza o upload da imagem e salva
    function salvar(event){
        /*if($("#previewImgFace")[0] == null || $("#previewImgDocument")[0] == null){
            alert("Favor retirar todas as imagens!");
            event.preventDefault();
        }else {*/
            let form = document.getElementById("cadastroVisitante");
            if (form.checkValidity()) { 
                event.preventDefault();                 
                showLoad('modalLoad',true);   
                if($("#previewImgFace")[0] == null || $("#previewImgDocument")[0] == null){
                    $("#IDIMG").val('ND');
                    upload('ND');
                }else{                          
                    $.ajax({
                        url: 'frotas_portaria/cadastra_foto_visitante',
                        type: 'POST',
                        data: {},
                        success: data => {
                            $("#IDIMG").val(data);
                            upload(data);
                        }
                    });
                }
            }
        //}
    }

    function upload(id){
        let rosto = '';
        let documento = '';
        if($("#previewImgFace")[0] != null || $("#previewImgDocument")[0] != null){
            rosto = $("#previewImgFace")[0].src;
            documento = $("#previewImgDocument")[0].src;
            Webcam.upload( rosto, 'frotas_portaria/upload_img_visitante/'+id+'/face', function(code, text) {});
            Webcam.upload( documento, 'frotas_portaria/upload_img_visitante/'+id+'/rosto', function(code, text) {});        
        }

        $("#IDIMG").val(id);        
        $("#cadastroVisitante").submit();        
    }

    function showLoad(loadId,show){
        
        if(show){
            $('#'+loadId).modal('show'); 
        }else{
            $('#'+loadId).modal('hide');
        }
    }
</script>