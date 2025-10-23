# Access Control (Backend)

Este projeto tem o objetivo de apresentar uma aplicação web backend utilizando-se do [framework Laravel](https://laravel.com/) (PHP) como base, em conjunto com o sistema integrado de autenticação/autorização [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum), através da criação/utilização de contas autenticadas através de email/senha ou diretamente utilizando contas de email do google.

A aplicação é desenvolvida focando em três pontos-chave:

-  Usuário
-  Papel
-  Habilidade

## Definição dos pontos-chave

Dentre os pontos-chave, existe os seguintes relacionamentos:

-  Usuário `->` papel `->` habilidade
-  Usuário `->` habilidade

### Usuário

Define o usuário que pode logar e realizar (ou não) ações dentro do sistema. Isso somente é possível através do relacionamento que ele possui com algum tipo de papel já cadastrado dentro do sistema, necessitando que esse mesmo papel também possua algum relacionamento com algum tipo de habilidade já cadastrada dentro do sistema. Outra maneira do usuário realizar algum tipo de ação seria através de seu relacionamento direto com algum outro tipo de habilidade já cadastrada dentro do sistema.

Ao remover alguma habilidade do usuário, já presente em algum papel já relacionado ao usuário, o sistema remove a habilidade somente do usuário, e não do relacionamento presente entre o papel já relacionado ao usuário em questão.

Ao adicionar/remover um papel do usuário, o sistema verifica se já existe alguma inclusão/remoção de habilidade de forma direta ao usuário e realiza as devidas modificações necessárias.

### Papel

Define algo como a função/cargo utilizado pelo usuário logado no sistema, muito semelhante ao contexto de *grupo*, muito utilizado em permissões de acesso dentro de sistemas UNIX/Linux.

### Habilidade

Define qualquer tipo de ação permitida pelo usuário dentro do sistema.

## Recursos diversos

Sempre que um possível usuário desejar solicitar a criação de uma nova conta, é necessário que o mesmo envie, através da aplicação, uma solicitação de registro. Essa solicitação deve ser posteriormente aprovada por alguma conta (que possua relacionamento com o papel `super-admin`).

O usuário aprovado receberá um email no qual ele deve definir o email e senha (ou conta de email do Google já existente) que será utilizado como autenticação pela nova conta a ser criada. Após o cadastro desses dados, o novo usuário receberá um email de verificação de conta, para que o uso da aplicação pelo usuário seja completamente liberado.

Após criar a conta, o usuário poderá também modificar posteriormente as seguintes informações:

-  Nome
-  Foto
-  Telefone
