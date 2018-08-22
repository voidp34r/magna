<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= !empty($item) ? 'Editar' : 'Nova'; ?> tela
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">Módulo *</label>
            <div class="col-sm-10">
                <?= form_dropdown('SISTEMA_MODULO_ID', $modulos, !empty($item->SISTEMA_MODULO_ID) ? $item->SISTEMA_MODULO_ID : '', 'class="selectpicker" required'); ?>                
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Nome *</label>
            <div class="col-sm-10">
                <input type="text" name="NOME" value="<?= !empty($item->NOME) ? $item->NOME : ''; ?>" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Visão *</label>
            <div class="col-sm-10">
                <input type="text" name="VISAO" value="<?= !empty($item->VISAO) ? $item->VISAO : ''; ?>" class="form-control" required>
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
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success">Gravar</button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>