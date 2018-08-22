<style>
    
    .tituloImagens{
        color: #2c3e50;
        margin: 1px;
        font-size : 18px;
    }
    
    .thumbnail>img{
       height : 80px;
    }

    .panel-heading a{
        color: black;
        text-decoration:none;
    }
    
    .panel-webapp{
        margin : 10px
    }
    
    .row-info{
        margin : 10px
            
    }
    
    .minimize:hover{
        color : #95a5a6
    }


    
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= $visitante->NOMEVISITA ?>
    </div>
    
    <!-- Agrupador de Paneis-->
    <div class="panel-group">     

       <!-- Panel de noticias --> 
        <div class="panel panel-default panel-webapp">

           <!-- Cabeçalho do  Panel-->
           <div class="panel-heading">
               <a data-toggle="collapse" href="#collapse2">
                   Informações do Visitante
                  <i class="fa fa-minus-circle pull-right minimize"></i>
               </a>

           </div>

           <!-- Corpo do Panel-->
           <div class="panel-body panel-body-collapse collaspe in" id="collapse2"> 
                <div class="row row-info">
                    <div class="col-md-3" > 
                        <span data-toggle="tooltip" title="Nome">
                            <i class="fa fa-user" aria-hidden="true"> </i>
                            <?= $visitante->NOMEVISITA ?>
                        </span>
                    </div> 

                    <div class="col-md-2">
                       <span data-toggle="tooltip" title="Documento">
                           <i class="fa fa-indent" aria-hidden="true" ></i>
                           <?= $visitante->NRDOCUMENTO ?>
                       </span>
                    </div> 

                    <div class="col-md-2">                               
                        <span data-toggle="tooltip" title="Tipo de Visitante">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                            <?= $visitante->TPVISITA ?>
                        </span>
                    </div> 

                    <div class="col-md-2" > 
                        <span data-toggle="tooltip" title="Data Entrada">
                            <i class="fa fa-calendar" aria-hidden="true"> </i>
                            <?= data_oracle_para_web($visitante->DTENTRADA)?> 
                        </span>
                    </div> 

                    <div class="col-md-2" > 
                        <span data-toggle="tooltip" title="Data Saida">
                            <i class="fa fa-calendar" aria-hidden="true"> </i>
                            <?= $visitante->DTSAIDA ? data_oracle_para_web($visitante->DTSAIDA) : '-'?> 
                        </span>
                    </div> 

                    <?php 
                        if($visitante->DTSAIDA == null){

                    ?>
                    <div class="col-md-1" > 
                        <span data-toggle="tooltip" title="Encerrar visita">
                        <button onclick="confirmMessage('<?= $visitante->ID ?>','<?= $visitante->NOMEVISITA  ?>')" type="button" class="btn btn-xs btn-warning">
                            <i class="fa fa-fw fa-check-circle-o fa-lg"></i>
                        </button>
                        </span>
                    </div> 

                    <?php }?>

                    

                </div>
        
                <div class="row row-info">
                     <div class="col-md-3" > 
                        <span data-toggle="tooltip" title="Empresa">
                            <i class="fa fa-briefcase" aria-hidden="true"></i>
                            <?= $visitante->DSEMPRESA ?>
                        </span>
                     </div>  

                     <div class="col-md-3">                               
                         <span data-toggle="tooltip" title="Telefone">
                            <i class="fa fa-phone" aria-hidden="true"></i>
                            <?= $visitante->TELEFONEEMPRESA ?>
                         </span>
                     </div> 


                     <div class="col-md-3">                               
                         <span data-toggle="tooltip" title="Socilitante">
                            <i class="fa fa-users" aria-hidden="true"></i>
                            <?= $visitante->SOLICITANTE ?>
                         </span>
                     </div> 

                </div>

                <div class="row row-info">

                    <div class="col-md-1">                               
                         <span data-toggle="tooltip" title="Veículo">
                            <i class="fa fa-car" aria-hidden="true"></i>
                            <?= $visitante->TPVEICULO ? $visitante->TPVEICULO : '-' ?>
                         </span>
                     </div> 

                     <div class="col-md-2">                               
                         <span data-toggle="tooltip" title="Modelo">
                         <?= $visitante->MODELO ? $visitante->MODELO : '-' ?>
                         </span>
                     </div> 

                     <div class="col-md-1">                               
                         <span data-toggle="tooltip" title="Cor">
                         <?= $visitante->COR ? $visitante->COR : '-' ?>
                         </span>
                     </div> 

                     <div class="col-md-1">                               
                         <span data-toggle="tooltip" title="Placa">
                         <?= $visitante->PLACA ? $visitante->PLACA : '-' ?>
                         </span>
                     </div> 

                </div>
               <div class="row row-info">
                   <div class="col-md-12">
                        <i class="fa fa-commenting-o" aria-hidden="true" Data-toggle="tooltip" title="Motivo"></i>
                        <?= $visitante->MOTIVOVISITA ?>
                   </div>
               </div>

            </div> 
        </div> 
        <?php if(!empty($visitante->fotos)){ ?> 
            <div class="panel panel-default panel-webapp">
                <div class="panel-heading">
                    <a data-toggle="collapse" href="" onclick=" showToggle('#visitaImg')">
                    Fotos   
                    <i class="fa fa-minus-circle pull-right minimize"></i>
                    </a>

                </div>

                <div class="panel-body panel-body-collapse collaspe in" id="visitaImg"> 
                    
                    <div class="col-md-3">
                        <a href="#" class="thumbnail disableLink" onclick="zoomImage(this)">
                            <img class="foto" src="<?= $visitante->fotos->CAMINHO?>/rosto.jpg"  
                                desc="Rosto"> 
                            <div align="center" style="margin-top : 5px">
                                    <h3 class="tituloImagens">Rosto</h3>
                            </div>   
                        </a>
                    </div>

                    <div class="col-md-3">
                        <a href="#" class="thumbnail disableLink" onclick="zoomImage(this)">
                            <img class="foto" src="<?= $visitante->fotos->CAMINHO?>/documento.jpg"  
                                desc="Documento"> 
                            <div align="center" style="margin-top : 5px">
                                    <h3 class="tituloImagens">Documento</h3>
                            </div>   
                        </a>
                    </div>
                    
                </div>    
            </div>
        <?php } ?>

    </div>                    

    <div class="modal" id='modalZoom'>
        <div class="modal-dialog">       
           <div class="modal-content">
             <div class="modal-body" style="background: rgba(0,0,0,.8);">
                 <div id="divAlign">
                    <img id='imgZoom' class="img-responsive">
                 </div>
             </div>
           </div>            
        </div>
    </div>
    
 
<script>
    
    $(document).ready(function(){
        
        $('.disableLink').click(function(e) {
            e.preventDefault();
         });
    
    })

    function zoomImage(img){

       $('#tituloModal').text($(img).find('img').attr('desc'));
       $('#imgZoom').attr('src',$(img).find('img').attr('src'));
       $('#modalZoom').modal('show');
       
       if( $('#imgZoom').width() == 270){
           $('#divAlign').attr('align','center');
       }else{
           $('#divAlign').removeAttr('align');
       }
    }
    
    function closeModal(){
        $('#tituloModal').text('');
        $('#imgZoom').attr('src','');
        $('#modalZoom').modal('hide');
    }
    
    function showToggle(id){
        $(id).toggle();
    }

    const confirmMessage = (id, name) => {
        if (confirm(`Tem certeza que deseja encerrar a visita de ${name}?`)) {
            window.location.href = `frotas_portaria/finalizar_visita/${id}`;
        }
    }
</script>    
