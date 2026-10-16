# Validação da entrega

## Executado neste ambiente

- `node --check script.js`: passou.
- `node --check index2.js`: passou.
- `node tests/frontend.cjs`: passou.

O teste do front usa Node.js e um DOM/API simulados. Verifica escape de HTML vindo do catálogo, produto esgotado, limite de quantidade, preservação da sacola após falha, envio do token CSRF, reutilização de requestId ao repetir uma solicitação, formato dos itens enviados, limpeza da sacola após sucesso e funcionamento do formulário de newsletter após uma operação assíncrona.

Esses testes não executam o PHP, o banco ou a renderização em um navegador. PHP, MySQL/MariaDB e um navegador executável não estavam disponíveis neste ambiente. Portanto, o backend não foi executado nem recebeu validação de sintaxe por `php -l` aqui. As regras PHP e SQL foram revisadas, mas a validação de ponta a ponta precisa ser feita no XAMPP.

## Testar o PHP localmente

No Prompt de Comando, dentro da pasta `agrolink`, execute:

```bat
for /R %f in (*.php) do @C:\xampp\php\php.exe -l "%f"
```

Se colocar esse comando em um arquivo `.bat`, use `%%f` em vez de `%f`.

## Teste de integração com banco isolado

Não execute testes no banco da loja. O script recusa qualquer nome diferente de `agrolink_test` e exige um banco vazio.

1. No phpMyAdmin, crie um banco vazio chamado `agrolink_test`, com collation `utf8mb4_unicode_ci`.
2. No Prompt de Comando:

   ```bat
   cd C:\xampp\htdocs\agrolink
   set AGROLINK_DSN=mysql:host=127.0.0.1;dbname=agrolink_test;charset=utf8mb4
   C:\xampp\php\php.exe tests\backend.php
   set AGROLINK_DSN=
   ```

   Se usa outra porta, altere o DSN. As credenciais continuam sendo as de `app/config.php`, ou podem ser definidas em `AGROLINK_DB_USER` e `AGROLINK_DB_PASSWORD`.

O script cria tabelas e dados exclusivamente nesse banco de teste e verifica:

- Preço e frete calculados pelo servidor.
- Reserva de estoque.
- Reenvio sem duplicação de pedido ou baixa adicional.
- Rejeição de mesma chave com outro conteúdo.
- Estoque insuficiente, quantidades negativas e itens repetidos.
- Salto inválido de status.
- Cancelamento com devolução única ao estoque.
- Doação e perda com controle de saldo.
- Ocultação e bloqueio de produtos vencidos.

Os dados ficam em `agrolink_test` para inspeção. Para repetir, recrie somente esse banco de teste vazio. O script não testa concorrência em múltiplas conexões, interface visual ou login via HTTP.

## Conferência manual de ponta a ponta

1. Entre no painel e cadastre um produto com preço R$ 4,50 e saldo 3.
2. Na loja, peça 2 unidades. O total padrão deve ser R$ 18,90.
3. Confira no painel: pedido recebido, cliente e endereço corretos; saldo disponível 1; movimentação −2.
4. Cancele o pedido marcando a confirmação: saldo volta a 3, com apenas uma devolução.
5. Crie outro pedido e percorra os status até Concluído; o estoque não deve ser descontado uma segunda vez.
6. Altere a foto, o preço e a visibilidade de um produto. Recarregue a loja para conferir.
7. Registre entrada, doação e perda e confira os saldos e os motivos no histórico.
8. Cadastre validade anterior à data atual: o item não deve aparecer na loja.
9. Saia da conta e tente abrir `admin/orders.php`: deve redirecionar ao login. Confira também o menu e o botão Sair no celular.
10. No Apache, tente abrir `database/schema.sql`: o acesso HTTP deve ser negado.

## Concorrência

Para verificar a proteção de estoque real, deixe apenas 1 unidade e envie pedidos de 1 unidade simultaneamente por dois navegadores/perfis com sessões diferentes. Deve haver um pedido aceito e uma rejeição por falta de estoque, sem saldo negativo. Isso exige o servidor PHP e o banco em execução e não foi executado neste ambiente.
