<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= !empty($item) ? 'Editar' : 'Novo'; ?> módulo
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <input type="text" name="NOME" value="<?= !empty($item) ? $item->NOME : ''; ?>" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Pasta *</label>
            <div class="col-sm-10">
                <input type="text" name="PASTA" value="<?= !empty($item) ? $item->PASTA : ''; ?>" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Módulo</label>
            <div class="col-sm-10">
                <?= form_dropdown('SISTEMA_MODULO_ID', $modulos, !empty($item) ? $item->SISTEMA_MODULO_ID : '', 'class="selectpicker"'); ?>                
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Icone</label>
            <div class="col-sm-10">
                <input type="text" name="ICONE" value="<?= !empty($item) ? $item->ICONE : ''; ?>" class="form-control">
            </div>
        </div>
        <?php
        if (!empty($item)) {
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10 form-control-static">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="ATIVO" value="1" <?= !empty($item->ATIVO) ? 'checked' : ''; ?>>
                            Ativo  
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-sm-10 form-control-static">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="DISPONIVEL_MOBILE" value="1" <?= !empty($item->DISPONIVEL_MOBILE) ? 'checked' : ''; ?>>
                        Mobile  
                    </label>
                </div>
            </div>
        </div>        
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>