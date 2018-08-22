<?= botao_voltar(); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        Adicionar Notícias
    </div>
    <div class="panel-body">
        <?= form_open('', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <div class="col-sm-8">
                <label for="TITULO">Titulo *</label> 
                <?= form_input('TITULO', set_value('TITULO'))?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <label for="NOTICIA">Notícia *</label> 
                <?= form_textarea('NOTICIA', set_value('NOTICIA'))?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4">
                <fieldset>
                    <div>
                        <input type="checkbox" name="MOBILE" value="1">
                        <label>Mobile</label>                         
                    </div>
                    <div>
                        <input type="checkbox" name="WEBAPP" value="2"> 
                        <label>Webapp</label> 
                    </div>
                </fieldset>
            </div>
        </div>        
        <div class="form-group">
	    <div class="col-sm-4">
                <button type="submit" class="btn btn-success">Gravar</button>
	    </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>       