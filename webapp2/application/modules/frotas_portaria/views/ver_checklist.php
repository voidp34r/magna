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
        Checklist cod.<?= $checklist->ID ?> 
    </div>
    
    <!-- Agrupador de Paneis-->
    <div class="panel-group">     


       <!-- Panel de noticias --> 
        <div class="panel panel-default panel-webapp">

           <!-- Cabeçalho do  Panel-->
           <div class="panel-heading">
               <a data-toggle="collapse" href="#collapse2">
                   Informações do Checklist
                  <i class="fa fa-minus-circle pull-right minimize"></i>
               </a>

           </div>

           <!-- Corpo do Panel-->
           <div class="panel-body panel-body-collapse collaspe in" id="collapse2"> 
            
                <div class="row row-info">
                    <!-- Código do Checklist-->
                    <div class="col-md-3" > 
                        <span data-toggle="tooltip" title="Código do checklist">
                            <i class="fa fa-indent" aria-hidden="true"></i>
                            <?= $checklist->ID ?>
                        </span>
                    </div> 

                    <!-- Placa-->
                    <div class="col-md-3">
                       <span data-toggle="tooltip" title="Placa do veículo">
                           <i class="fa fa-car" aria-hidden="true" ></i>
                           <?= $checklist->NRPLACA?>
                       </span>
                    </div> 

                    <!-- Km Informado-->
                    <div class="col-md-3">                               
                        <span data-toggle="tooltip" title="Data e Hora do checklist">
                            <i class="fa fa-calendar" aria-hidden="true"></i>
                            <?= data_oracle_para_web($checklist->DTINCLUSAO); ?>
                        </span>
                    </div> 

                    <!-- Informação se o Hodometro foi atualizado-->
                    <div class="col-md-3">
                        <i class="label <?= $checklist->TPACAO == 2 ? 'label-success' : 'label-danger'; ?>" data-toggle="tooltip" title="Ação Tomada">
                            <i class="fa <?= $checklist->TPACAO == 2 ? 'fa-check' : 'fa-exclamation-circle'; ?>" aria-hidden="true"></i>
                            <?= $checklist->TPACAO == 2 ? 'Hodômetro Atualizado' : 'Envio de Email'; ?>
                        </i>
                    </div>
                </div>
              
                <div class="row row-info">
                    <!-- Km Anterior-->
                     <div class="col-md-3" > 
                        <span data-toggle="tooltip" title="Km Anterior">
                            <i class="fa fa-calendar-plus-o" aria-hidden="true"></i>
                            <?= $checklist->KMANTERIOR ? $checklist->KMANTERIOR : '-' ?>
                        </span>
                     </div>  

                     <!-- Km Informado-->
                     <div class="col-md-3">                               
                         <span data-toggle="tooltip" title="Km Atual">
                            <i class="fa fa-calendar-minus-o" aria-hidden="true"></i>
                            <?= $checklist->KMATUAL ? $checklist->KMATUAL : '-' ?>
                         </span>
                     </div> 


                     <!-- Km Informado-->
                     <div class="col-md-3">                               
                         <span data-toggle="tooltip" title="Usuário que realizou o checklist">
                             <i class="fa fa-user" aria-hidden="true"></i>
                             <?php echo "$checklist->USUARIO - $checklist->NOME"?>
                         </span>
                     </div> 
                </div>
               <div class="row row-info">
                   <div class="col-md-12">
                        <i class="fa fa-commenting-o" aria-hidden="true" Data-toggle="tooltip" title="Comentário"></i>
                        <?php echo $checklist->DSOBSERVACAO ? $checklist->DSOBSERVACAO : '-'  ?>
                   </div>
               </div>
                    

           </div>    

        </div>                    


       <!-- Panel de Imagens -->
       <?php foreach ($checklist->categoriaFotos as $categoria) {?>
       
        <div class="panel panel-default panel-webapp">

            <!-- Cabeçalho do  Panel-->
            <div class="panel-heading">
                <a data-toggle="collapse" href="" onclick=" showToggle('<?php echo "#$categoria->ID"?>')">
                 <?php echo $categoria->NOME ?>    
                 <i class="fa fa-minus-circle pull-right minimize"></i>
                </a>

            </div>

            <!-- Corpo do Panel-->
           <div class="panel-body panel-body-collapse collaspe in" id="<?php echo $categoria->ID?>"> 
                
                <?php foreach ($categoria->FOTOS as $fotos) {?>
                <!-- Div da Imagem-->
                <div class="col-md-3">
                    <a href="#" class="thumbnail disableLink" onclick="zoomImage(this)">
                        <img class="foto" src="<?php echo substr($fotos->CAMINHO,strripos($fotos->CAMINHO,'upload'))?>"  
                              desc="<?php echo $fotos->DESC ?>"> 
                           <div align="center" style="margin-top : 5px">
                                <h3 class="tituloImagens"><?php echo $fotos->DESC?></h3>
                           </div>   
                    </a>
                    
                </div>
                <?php }?>
                
            </div>    
         </div>
        <?php }?>
       <!-- //Panel de Imagens -->
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
</script>    
