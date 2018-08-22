
<center> 
    <h4>Selecione o Horário para remover todas as refeições</h4>
    <button class="btn btn-primary" onclick="startImport(1)">Refeição - 12h</button>
    <button class="btn btn-primary" onclick="startImport(2)">Refeição - 19h</button>
    <button class="btn btn-primary" onclick="startImport(3)">Refeição - 22h</button>
    <button class="btn btn-primary" onclick="startImport(4)">Refeição - 00h</button>
</center>

<script> 
     function startImport(ref){
        $.ajax({
            url : "rh_refeicao/removeAcessoGeral/"+ref
        }).done(function(data){
            console.log(data);
            data = JSON.parse(data);
            console.log(data);                  
        }); 
    }
</script>