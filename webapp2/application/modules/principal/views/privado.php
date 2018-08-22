<div class="row">
    <div class="col-sm-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                Atualizações Recentes
            </div>
            <div >
            <?php
            if (!empty($noticias))
            {              
                foreach ($noticias as $item)
                {

                ?>
                <div class="card" style="border-radius: 10px; background: #ecf0f1; padding: 10px; margin : 5px" >
                    <div class="card-block">
                       <h4 class="card-title" style="color : #222; font-size: 20px"><?php echo $item->DSTITULO ?></h4> 
                       <h6 class="card-subtitle mb-2 text-muted">Publicado em: <?php echo data_oracle_para_web($item->DTNOTICIA) ?></h6>
                       <div style="border: 0.5px solid #bdc3c7; margin-bottom: 15px; margin-top: 0"></div>
                       <p class="card-text"><?php echo  $item->DSNOTICIA ?></p>
                    </div>  
                </div>
                <?php
                }
                ?>
            <?php
            }
            ?>
            </div>
        </div>  
    </div>
</div>