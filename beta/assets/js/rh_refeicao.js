/**
 *
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 **/


$(document).ready(function () {

    console.log('init sync id Secure');
});

function sync(e) {
    if (e) {
        $.ajax({
            url: "rh_refeicao/sync_idsecure/1",
            method: "POST",
            data: { sync: true },
            success: function (ret) {
                if (ret) {
                    // console.log(ret);
                    console.log(ret.retorno);
                    if(ret) {
                        alert(ret.retorno);
                    } else {
                        alert("Ocorreu algum erro");    
                    }
                    
                }
            }
        });
    }
}



function remover(pis, ref) {
    if (pis) {
        $.ajax({
            url: "rh_refeicao/remove_group",
            method: "POST",
            data: { user: pis, refhr: ref },
            success: function (ret) {
                console.log(ret);
                // remover_user_group(ret);
                if(ret) {
                    alert("Solicitação de realizada com sucesso!");
                } else {
                    alert("Erro na solicitação de remoção de usuário do cadastro no grupo de refeições");
                }
                
            }
        });
    }
}

function remover_user_group(user) {
    if (user != null || user != "") {
        $.ajax({
            url: "https://192.168.99.25:30443/api/user/",
            contentType: 'application/json',
            type: 'POST',
            // method: "POST",
            data: user,
            async: false,
            success: function (ret) {
                console.log(ret);
                if(ret) {
                    alert("Usuário foi cadastrado no grupo de refeições com sucesso!");
                } else {
                    alert("Usuário não está cadastrado no grupo de refeições ou não foi removido do grupo de refeições com sucesso");
                }
            },
            beforeSend: function(xhr, settings) { xhr.setRequestHeader('Authorization','Bearer ' + "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJjaWRVc2VyVHlwZSI6IjEiLCJjaWRVc2VyTmFtZSI6IkFkbWluaXN0cmFkb3IiLCJjaWRVc2VySWQiOiIxIiwiaXNzIjoiR2VyZW5jaWFkb3IgaURBY2Nlc3MiLCJleHAiOjE1MzQ5NjMzNDQsIm5iZiI6MTUzNDg3Njk0NH0.oKeNs_uirYeUZ91rea-K6LOK4tdAKy2YXZlr1STYFjg" ); } //set tokenString before send
        });
    }
}