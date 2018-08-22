<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Excluir perfil
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="confirmacao" value="1" required> Tenho certeza que desejo excluir esse perfil
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-danger">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>