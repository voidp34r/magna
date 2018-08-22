 <div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-body">
            <form>
                <div class="row">  
                    <div class="col-md-2">
                        <label>PIS</label>
                        <?= campo_filtro($filtro, 'NOME', 'like'); ?>
                    </div>  
                    <div class="col-md-3">
                         <label>Horário Refeição</label>
                                    <select name="int[HORARIO_REFEICAO]" class="form-control input-small" size="1">
                                    <option value="1">12H</option>
                                    <option value="2">19H</option>
                                    <option value="3">22H</option>
                                    <option value="4">00H</option>
                                    </select>
                    </div>
                </div>
                <div class="row"> 
                    <div class="col-md-2">
                        <input type="submit" value="Cadastrar Reserva" class="btn btn-sm btn-primary btn-block">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body table-responsive">
        <?php
        $PIS = $_GET[filtro][like][NOME];
        $ref = $_GET[int][HORARIO_REFEICAO];
        if (strlen($PIS) < 10 or $ref <= 0){
            echo '<div class="alert alert-danger" role="alert" align="center">O numero de PIS deve conter no minimo 10 caracteres <b>E</b> deve ser selecionado um Horario de Refeição</div>';
        }
        else{
            if ($retorno->STATUS <> 'Sucesso'){
                echo '<div class="alert alert-danger" role="alert" align="center">'.$retorno->STATUS.'</div>';
            }
            else{
               echo '<div class="alert alert-success" role="alert" align="center">Reserva para '.$retorno->NOME.' efetuada com '.$retorno->STATUS.', Data Refeição: '.$retorno->DATA.'.</div>';
            }
        }
?>