# Agrolink — painel administrativo e backend

Backend em PHP + MySQL/MariaDB para o front enviado no projeto. O pacote contém uma cópia integrada do front; os anexos originais foram preservados.

## Instalação no XAMPP (Windows)

1. Extraia a pasta `agrolink` deste ZIP dentro de `C:\xampp\htdocs\`.
2. No XAMPP Control Panel, inicie **Apache** e **MySQL**.
3. Acesse `http://localhost/phpmyadmin`. Na aba **Importar**, escolha `agrolink/database/schema.sql` e execute. Importe uma única vez, em banco novo. O arquivo cria o banco `agrolink` e suas tabelas.
4. Opcionalmente, importe `database/demo.sql` para cadastrar quatro produtos fictícios com estoque. São dados de apresentação, não produtores ou preços reais. Importe somente uma vez.
5. Abra `app/config.php`. O padrão é banco `agrolink`, usuário `root`, senha vazia e servidor `127.0.0.1`, comuns no XAMPP local. Se seu MySQL usa senha ou outra porta, ajuste. Exemplo: `mysql:host=127.0.0.1;port=3307;dbname=agrolink;charset=utf8mb4`.
6. Crie sua conta administrativa pelo **Prompt de Comando**:

   ```bat
   cd C:\xampp\htdocs\agrolink
   C:\xampp\php\php.exe tools\create-admin.php
   ```

   Informe nome, e-mail e senha de 12 a 72 caracteres. A senha aparece enquanto você digita neste terminal local. Ela é armazenada como hash no banco. Não existe senha padrão ou tela pública de criação de administrador.
7. Copie suas fotos para a pasta `agrolink/img`, junto do arquivo de imagem de exemplo já incluído. As imagens originais não vieram nos anexos; preserve os mesmos nomes utilizados no seu front.
8. Abra:
   - **Loja:** `http://localhost/agrolink/`
   - **Administração:** `http://localhost/agrolink/admin/`
   - **Mapa existente:** `http://localhost/agrolink/index2.html`

Use PHP 8.0 ou superior, extensão `pdo_mysql` e MySQL 5.7+/MariaDB 10.4+. Acesse pelo Apache, não pelo duplo clique no HTML nem pelo Live Server do VS Code.

## Como usar

### Estoque

Entre em **Estoque → Novo produto**. Preencha nome, categoria, produtor, cidade, preço, unidade de venda, estoque mínimo e, se houver, lote e validade. A foto usa um caminho local, como `img/alface.jpg`; não é preciso colocar um link da internet. Salve e depois registre a primeira **Entrada** de unidades.

- **Entrada:** recebimento de alimentos.
- **Correção (+/−):** ajuste após conferência, sempre com motivo.
- **Doação:** saída com destino identificado no motivo.
- **Perda / descarte:** saída por vencimento ou deterioração.
- **Reserva / cancelamento:** registradas automaticamente pelos pedidos.

O saldo exibido é o disponível: já desconta pedidos ainda não entregues. Cada movimentação registra quantidade, saldo após a operação, data, motivo e responsável. O painel mostra as últimas 50 por produto; todas permanecem no banco.

As quantidades são inteiras. Para venda por peso, cadastre, por exemplo, a unidade **pacote · 500 g**; quantidade 2 significa dois pacotes. Cada cadastro representa um lote. Para dois lotes com validades diferentes, use cadastros separados. Não troque a unidade de venda de um produto com estoque; faça outro cadastro. Lote e validade não podem ser alterados enquanto houver pedidos abertos desse produto.

Produtos inativos ou vencidos não aparecem na loja. Produtos com saldo zero aparecem como **Esgotado**. A validade informada é inclusiva: o produto sai da loja no dia seguinte à data. A visão geral sinaliza produtos que vencem em até três dias e os já vencidos. O vencimento bloqueia novas reservas, mas pedidos anteriormente recebidos precisam ser conferidos pela equipe antes do envio. Doações de alimentos vencidos são bloqueadas.

Para remover um produto da venda, desmarque **Exibir na loja**. Os registros são preservados para manter o histórico de pedidos.

### Pedidos

O cliente monta a sacola e envia nome, e-mail, telefone e endereço. A API calcula preços e frete pelo banco, verifica a disponibilidade, registra o pedido e reserva o estoque na mesma transação.

No painel, abra **Pedidos**, filtre pelo nome/código/status e clique em **Abrir**. A ficha mostra cliente, endereço, itens, total, forma de pagamento escolhida, observações internas e histórico.

Fluxo: **Recebido → Confirmado → Em separação → Enviado → Concluído**.

É possível cancelar antes de **Enviado**. Escolha **Cancelado**, marque a confirmação e salve. O estoque retorna uma única vez. Pedidos concluídos ou cancelados ficam encerrados. Devoluções após envio e pagamentos não são automatizados nesta versão.

A tela atualiza quando é aberta/recarregada; não há notificações automáticas. Para ver novos pedidos enquanto o painel está aberto, atualize a página.

## Integração já incluída

- `index.html`, `index.css` e `script.js`: loja original com API e ajustes no carrinho.
- `index2.html`, `index2.css` e `index2.js`: página existente de mapa, preservada.
- `admin/`: telas administrativas.
- `api/session.php`: inicia a sessão e fornece token CSRF e regra de frete.
- `api/products.php`: catálogo disponível.
- `api/orders.php`: recebe pedidos.
- `api/newsletter.php`: registra o e-mail; não dispara mensagens.
- `app/`: configuração, conexão PDO e regras de negócio.
- `database/`: estrutura SQL e dados opcionais de demonstração.
- `tools/create-admin.php`: criação de administradores somente pelo terminal.
- `tests/`: verificações reproduzíveis.

Os nomes dos anexos foram normalizados: `index(2).html` virou `index.html`, `index(2).css` virou `index.css` e `script(1).js` virou `script.js`, correspondendo às referências internas do seu HTML. O link de cadastro que apontava para `login.html`, não enviado, foi substituído pelo acesso à administração.

A loja e a API devem ficar no mesmo domínio e pasta-base. Não há configuração CORS: a sessão e o token protegem as requisições do próprio projeto. Para integrar outro front, consulte `INTEGRACAO.md`.

## Relação com o artigo

A implementação usa como referência o artigo **Ferramenta de acesso a mercadorias e produtos alimentícios acessíveis e naturais**, de Dimitri Hrihorowitsch Toffoli Culau e Júlia Moreira Gregis dos Reis, enviado com o projeto. Os módulos de origem do alimento, estoque, validade, doações, perdas e pedidos atendem à proposta de aproximar produtores e consumidores e acompanhar excedentes.

Este pacote implementa o núcleo administrativo solicitado. A página de feiras continua informativa; o checkout recebido trabalha com entrega por endereço. Seleção de ponto de retirada no checkout, contas de produtores/consumidores, pagamentos online, envio de e-mails e indicadores de impacto em kg são possíveis próximas etapas, não funcionalidades implementadas aqui.

## Configuração e limites

- Frete padrão: R$ 9,90; gratuito a partir de R$ 120,00. Edite `app/config.php`; o carrinho consulta a mesma regra no servidor.
- Regiões: Porto Alegre, Canoas, Viamão e Guaíba, mantidas do seu front. Ao alterar a lista no servidor, atualize também o select no checkout de `script.js`.
- A mensagem de previsão de entrega da loja é o texto estático do front original; não existe um calendário de logística implementado.
- Preços são armazenados em centavos. A API aceita de 1 a 50 produtos diferentes, de 1 a 999 unidades por produto e total de mercadorias de até R$ 1 milhão por pedido.
- O saldo de cada produto é limitado a 1 milhão de unidades pelas regras da aplicação.
- Reservas não expiram automaticamente. A equipe deve cancelar solicitações abandonadas para liberar o estoque.
- Não existe confirmação de pagamento: a escolha entre Pix e pagamento na entrega é apenas registrada. A equipe combina as instruções com o cliente.
- O painel é restrito a administradores autenticados. A sessão administrativa expira após 30 minutos de inatividade.
- Cinco falhas de login pelo mesmo IP bloqueiam novas tentativas por 15 minutos.
- A repetição do mesmo pedido reutiliza a chave na mesma sessão, evitando uma segunda reserva. Se a sessão for apagada/expirar, essa garantia não atravessa a criação de outra sessão; confira o painel em caso de dúvida após uma falha de conexão.
- Para uso público, configure HTTPS, usuário MySQL próprio com as permissões necessárias, credenciais do servidor e backup. Mantenha `app`, `database`, `tools` e `tests` sem acesso HTTP. Os `.htaccess` incluídos fazem esse bloqueio no Apache com `AllowOverride` habilitado. Em outro servidor, configure bloqueios equivalentes. Não use `root` com senha vazia fora do ambiente local.

## Se algo não funcionar

- **Produtos não aparecem:** confira Apache/MySQL, importação do SQL, credenciais, produtos ativos e validade. Sem `demo.sql`, cadastre produtos e registre entradas no painel.
- **Fotos não aparecem:** coloque suas imagens na pasta `img` e confira os nomes; as fotos do projeto não estavam anexadas.
- **Pedido rejeitado por estoque:** recarregue a loja e confira a sacola; outro pedido pode ter reservado o saldo.
- **Sessão expirada:** recarregue a página, especialmente se fez login/logout administrativo em outra aba.
- **Serviço indisponível:** confira o log de erros do Apache/PHP. O sistema não mostra credenciais ou detalhes SQL ao cliente.
- **Mapa/ícones não carregam:** a página de mapa original depende de serviços externos. O painel administrativo funciona sem essas bibliotecas.

Consulte `TESTES.md` para o que foi validado e como testar o banco localmente.
