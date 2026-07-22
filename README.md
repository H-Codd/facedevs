# Facedevs

Facedevs é uma API de rede social desenvolvida com Laravel, pensada para permitir que usuários criem perfis, compartilhem postagens, sigam outros membros, curtam conteúdos e comentem em publicações.

## Sobre o projeto

Este projeto foi construído com foco em uma arquitetura simples e escalável para aplicações backend de redes sociais. A API oferece recursos para:

- cadastro e autenticação de usuários;
- atualização de perfil, avatar e capa;
- publicação de posts;
- curtidas e comentários;
- feed de conteúdo;
- busca de usuários e publicações.

## Tecnologias utilizadas

- PHP 8.3
- Laravel 13
- Sanctum
- JWT Auth
- Intervention Image
- Pest para testes
- Vite e Tailwind para o frontend básico

## Requisitos

Antes de iniciar, certifique-se de ter instalado:

- PHP 8.3+
- Composer
- Node.js e npm
- Banco de dados compatível com Laravel

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/facedevs.git
   cd facedevs
   ```

2. Instale as dependências PHP:
   ```bash
   composer install
   ```

3. Instale as dependências do frontend:
   ```bash
   npm install
   ```

4. Copie o arquivo de ambiente e ajuste as configurações:
   ```bash
   cp .env.example .env
   ```

5. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```

6. Execute as migrations:
   ```bash
   php artisan migrate
   ```

## Executando o projeto

Para iniciar o ambiente de desenvolvimento:

```bash
composer run dev
```

Isso iniciará o servidor Laravel e os recursos necessários para o frontend.

## Testes

Para executar os testes:

```bash
php artisan test
```

## Estrutura principal

- app/: controllers, models e serviços
- routes/: definição das rotas da API
- database/migrations/: estrutura do banco de dados
- resources/: views e assets do frontend
- tests/: testes automatizados

## Licença

Este projeto está sob a licença MIT.
