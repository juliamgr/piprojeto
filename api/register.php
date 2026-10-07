<?php
require __DIR__.'/../../BACKEND/app/bootstrap.php';
require __DIR__.'/../../BACKEND/app/users.php';
api_run('POST',function(){
    register_user(input_json());
    json_out(['success'=>true,'message'=>'Cadastro salvo. Você já pode entrar na sua conta.'],201);
});
