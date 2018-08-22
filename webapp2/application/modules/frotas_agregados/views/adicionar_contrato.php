<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Gerar contrato
    </div>
    <div class="panel-body">
        <?php
        echo form_open('', 'class="form-horizontal"');
        ?>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <label class="radio-inline">
                    <input type="radio" name="tipo" value="CONTRATACAO" checked="checked">
                    Contratação
                </label>
                <label class="radio-inline">
                    <input type="radio" name="tipo" value="DISTRATO">
                    Distrato  
                </label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <label class="radio-inline">
                    <input type="radio" name="pessoa" value="PF">
                    Pessoa física
                </label>
                <label class="radio-inline">
                    <input type="radio" name="pessoa" value="PJ" checked="checked">
                    Pessoa jurídica  
                </label>
            </div>
        </div>
        <div class="form-group hidden" id="motorista_proprietario">
            <div class="col-sm-4 col-sm-offset-2 form-control-static">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="motorista_proprietario" value="1">
                        Motorista NÃO é o proprietário  
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group ">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Avançar</button>
            </div>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>
<script>
    $(function () {
        $('[name="pessoa"]').change(function () {
            var tipo = $(this).val();
            if (tipo == 'PF') {
                $('#motorista_proprietario').removeClass('hidden');
            } else {
                $('#motorista_proprietario').addClass('hidden');
            }
        });
    });
</script>