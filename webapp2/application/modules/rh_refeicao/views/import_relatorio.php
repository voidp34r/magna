 <div class="panel panel-default">
 	<div class="panel-heading">
 		Acessos
 		 <small>
            (<?= $total; ?>) - <?= anchor(current_url(), 'Atualizar'); ?>     
            -&nbsp&nbsp<a id="aFiltro" data-toggle="collapse" href="#filtro">Filtrar</a>       
        </small>
		<div class="collapse" id="filtro">

            <div class="panel-body">
                <div class="row">
                    <form>
                        <div class="row">  
                            <div class="col-md-2">
                                <label>Nome</label>
                                <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                            </div>  
                            <div id="dateRefeicaoi" class="col-sm-2">
                                <label>Data Refeição Inicial</label>
                                <?= campo_filtro($filtro, 'DTREFEICAOI', 'date'); ?>
                            </div> 
                            <div id="dateRefeicaof" class="col-sm-2">
                                <label>Data Refeição Final</label>
                                <?= campo_filtro($filtro, 'DTREFEICAOF', 'date'); ?>
                            </div> 
                            <div class="col-sm-2">
                                <label>&nbsp</label>
                                <input type="submit" value="Filtrar" class="btn btn-sm btn-primary btn-block">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> 
    </div>
   	 <div class="panel-body table-responsive">
    <?php
    if (!$_GET){
    	echo 'Utilize o menu "Filtrar" para exibição dos dados<br><br><b>*** Atenção ***</b><br> Caso seja informado um usuario será mostrado os dados individuais deste usuario;<br>  Caso seja informado apenas intervalo de datas, será mostrado o relatório geral totalizado por usuario';
    }
    else{
	    $v1 = $_GET[filtro][like][NOME];
	    if (!$v1){
	    	if (isset($lista))
	        {
	            ?>
	            <table class="table table-hover">
	                <thead>
	                    <tr>
	                        <th>Nome</th>
	                        <th>Reservada</th>
	                        <th>Consumida</th>
	                        <th>NÃO Consumida</th>
	                        <th>Total</th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <?php
	                    foreach ($lista as $item)
	                    {   
	                    	$QTRESERVATOTAL = $item->QTRESERVA + $QTRESERVATOTAL;
	                    	$QTACESSOUTOTAL = $item->QTACESSOU + $QTACESSOUTOTAL;
	                    	$QTNAOACESSOUTOTAL = $item->QTNAOACESSOU + $QTNAOACESSOUTOTAL;
	                        ?>
	                        <tr>
	                            <td>
	                              <?= $item->NOME; ?>
	                            </td>
	                            <td align="center">
	                              <?= $item->QTRESERVA; ?>
	                            </td>
	                            <td align="center">
	                              <?= $item->QTACESSOU; ?>
	                            </td>
	                             <td align="center">
	                              <?= $item->QTNAOACESSOU; ?>
	                            </td>
	                             <td>
	                              R$ <?= number_format($item->VLREFEICAO, 2, ',', '.'); ?>
	                              
	                            </td>
	                            
	                        </tr>
	                        <?php
	                    }
	                    $VLRESERVATOTAL = $QTRESERVATOTAL * 2.24;
	                    $VLACESSOUTOTAL = $QTACESSOUTOTAL * 2.24;
	                    $VLNAOACESSOUTOTAL = $QTNAOACESSOUTOTAL * 11.19;
	                    ?>
	                     <tr>
	                    	<td><b>Totais</b></td>
	                    	<td align="center"><b><?php echo $QTRESERVATOTAL?> <br>R$ <?php echo number_format($VLRESERVATOTAL, 2, ',', '.');?> </td>
	                    	<td align="center"><b><?php echo $QTACESSOUTOTAL;?> <br>R$ <?php echo number_format($VLACESSOUTOTAL, 2, ',', '.');?> </td>
	                    	<td align="center"><b><?php echo $QTNAOACESSOUTOTAL;?> <br>R$ <?php echo number_format($VLNAOACESSOUTOTAL, 2, ',', '.');?> </td>
	                    </tr>
	                </tbody>
	            </table> 
	            <?php
	        }
	    }
	    else {
	        if (isset($lista))
	        {
	            ?>
	            <table class="table table-hover">
	                <thead>
	                    <tr>
	                        <th>#</th>
	                        <th>Nome</th>
	                        <th>Data Refeição</th>
	                        <th>Horário Refeição</th>
	                        <th>Status Importação</th>
	                        <th>Status Acesso</th>
	                        <th>Valor Refeição</th>
	                    </tr>
	                </thead>
	                <tbody>
	                    <?php
	                    foreach ($lista as $item)
	                    {   
	                    	$i++;
	                    	$totalrefeicao = $item->VLREFEICAO+$totalrefeicao;
	                        ?>
	                        <tr>
	                            <td>
	                                <?= $item->ID; ?>
	                            </td>
	                            <td>
	                              <?= $item->NOME; ?>
	                            </td>

	                            <td>
	                                <?= $item->DATA; ?>
	                            </td>
	                            <td>
	                                <?php 
	                                    switch ($item->HORARIO_REFEICAO) {
	                                        case '1':
	                                            echo "12H";
	                                            break;
	                                        case '2':
	                                            echo "19H";
	                                            break;
	                                        case '3':
	                                            echo "22H";
	                                            break;
	                                        case '4':
	                                            echo "00H";
	                                            break;
	                                    }
	                                ?>
	                            </td>
	                            <td>
	                                <?= $item->STATUS; ?>
	                            </td>
	                            <td>
	                                <?php 
	                                    switch ($item->ACESSOU) {
	                                        case 2:
	                                            echo "Não Acessou";
	                                            break;
	                                        case 1:
	                                            echo "Acessou";
	                                            break;
	                                        case '3':
	                                            echo "22H";
	                                            break;
	                                        default:
	                                            echo " - ";
	                                            break;
	                                    }
	                                ?>
	                            </td>
	                            <td>
	                                R$ <?= number_format($item->VLREFEICAO, 2, ',', '.'); ?>
	                            </td>
	                        </tr>
	                        <?php
	                    }
	                    ?>
	                    <tr>
	                    	<td><b>Totais</b></td>
	                    	<td colspan='5'><b><?php echo $i; ?> Registro(s)</td>
	                    	<td><b>R$ <?php echo number_format($totalrefeicao, 2, ',', '.');?></td>
	                    </tr>
	                </tbody>
	            </table> 
	            <?php
	        }
	    }
	}
    ?>
    
</div>

<script> 

	$('document').ready(function(){
	    $("#dateRefeicaoi").find("input").mask("99/99/9999");
	    $("#dateRefeicaof").find("input").mask("99/99/9999");        
	});

</script>