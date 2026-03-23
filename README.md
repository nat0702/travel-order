# Travel Order System

Sistema full stack para gerenciamento de pedidos de viagem, com autenticação JWT, controle de acesso por perfil (admin/usuário), notificações e testes automatizados.

---

## Visão Geral

O sistema permite que usuários solicitem viagens e acompanhem o status de seus pedidos, enquanto administradores podem aprovar ou cancelar essas solicitações.

A aplicação foi desenvolvida com foco em boas práticas, validação de regras de negócio e organização de código.

---

## Tecnologias Utilizadas

### Backend
- Laravel
- JWT (tymon/jwt-auth)
- MySQL
- Redis
- Pest (testes automatizados)

### Frontend
- Vue 3
- Vite
- Axios
- Vue Router

### Infraestrutura
- Docker
- Docker Compose
- Nginx

---

## Como executar o projeto

### 1. Clonar o repositório

```bash
git clone https://github.com/nat0702/travel-order.git
cd travel-order
``` 

### 2. Subir os containers

```bash
docker compose up -d --build
````

### 3. Acessar o container do backend

```bash
docker exec -it travel_app bash
```
### 4. Configurar ambiente Laravel

```bash
cp .env.example .env 
php artisan key:generate 
php artisan jwt:secret
```

### 5. Rodar migrations e seeders
```bash
p hp artisan migrate --seed
```
---

### Acesso ao sistema

- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api

## Usuários de teste
|Tipo |	Email |	Senha |
|-----|-------|-------|
|Admin|	admin@example.com |	password | 
|User |	user@example.com  |	password |
|User |	user2@example.com |	password |
|User |	user3@example.com |	password |


## Funcionalidades

### Autenticação
Login com JWT
Proteção de rotas
Controle de acesso por perfil

### Pedidos de viagem
Criação de pedidos
Listagem de pedidos
Visualização detalhada

### Filtros
Por status
Por destino
Por período

### Administração
Aprovação de pedidos
Cancelamento de pedidos
Regras:
Usuário não pode alterar próprio pedido
Pedido aprovado não pode ser cancelado

### Notificações
Notificação ao usuário quando pedido é aprovado ou cancelado
Armazenadas no banco
Exibidas no frontend via polling

### Testes Automatizados

Testes implementados com Pest cobrindo:

Criação de pedidos
Listagem por perfil
Permissões de acesso
Atualização de status
Regras de negócio

### Rodar testes:
php artisan test


## Estrutura do Projeto
backend/
frontend/
docker/
docker-compose.yml

## Endpoints da API

### Autenticação
POST /api/login  
GET /api/me  

### Pedidos
GET /api/travel-orders  
POST /api/travel-orders  
GET /api/travel-orders/{id}  
PATCH /api/travel-orders/{id}/status  

### Notificações
GET /api/notifications  
PATCH /api/notifications/{id}/read 


## Decisões Técnicas
Uso de FormRequest para validação
Regras de negócio centralizadas no controller
Notificações via database
Polling para atualização no frontend
Docker para padronizar execução

### Autora
Desenvolvido por Natália Ribeiro 