<button class="btn btn-primary" onclick="removeAcess()">Remover Reservas IDSECURE</button>

<script>
    function removeAcess(){
       
        $.ajax({
            url : "rh_refeicao/removeAcess"
        });          
    }
</script>