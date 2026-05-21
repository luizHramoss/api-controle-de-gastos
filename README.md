# 💰 Finance API

API REST para controle financeiro pessoal — MVP construído com **Laravel 10+**, **Sanctum** e boas práticas de arquitetura pragmática.

---

## 📋 Índice

- [Stack](#-stack)
- [Instalação local](#-instalação-local)
- [Variáveis de ambiente](#-variáveis-de-ambiente)
- [Como rodar](#-como-rodar)
- [Como rodar os testes](#-como-rodar-os-testes)
- [Endpoints](#-endpoints)
- [Credenciais do seed](#-credenciais-do-seed)
- [Deploy (Railway / Render)](#-deploy)
- [Estrutura do projeto](#-estrutura-do-projeto)

---

## 🛠 Stack

| Tecnologia | Versão |
|---|---|
| PHP | 8.1+ |
| Laravel | 10.x |
| Laravel Sanctum | 3.x |
| MySQL / PostgreSQL / SQLite | — |
| Pest / PHPUnit | 2.x / 10.x |

---

## 🚀 Instalação local

### Pré-requisitos

- PHP 8.1+
- Composer 2+
- MySQL 8+ ou PostgreSQL 14+ (ou SQLite para dev rápido)

### Passo a passo

```bash
# 1. Clone o repositório
git clone https://github.com/seu-usuario/finance-api.git
cd finance-api

# 2. Instale as dependências PHP
composer install

# 3. Copie o arquivo de ambiente
cp .env.example .env

# 4. Gere a chave da aplicação
php artisan key:generate

# 5. Configure o banco no .env (veja seção abaixo)

# 6. Execute as migrations e o seed
php artisan migrate --seed

# 7. Inicie o servidor de desenvolvimento
php artisan serve
```

A API estará disponível em `http://localhost:8000/api/v1`.

---

## 🔧 Variáveis de ambiente

Edite o `.env` após copiar o `.env.example`:

```env
APP_NAME="Finance API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# ── Banco de dados (MySQL) ───────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finance_api
DB_USERNAME=root
DB_PASSWORD=sua_senha

# ── Banco de dados (PostgreSQL) ──────────────────────
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=finance_api
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha

# ── SQLite (dev rápido, sem servidor) ────────────────
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# ── Sanctum (domínios do frontend Vue.js) ────────────
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
```

---

## ▶️ Como rodar

```bash
# Servidor de desenvolvimento
php artisan serve

# Migrations + seed (banco limpo)
php artisan migrate:fresh --seed

# Seed sem recriar tabelas
php artisan db:seed
```

---

## 🧪 Como rodar os testes

Os testes usam **SQLite in-memory**, sem necessidade de banco configurado.

```bash
# Todos os testes
php artisan test

# Com cobertura de código (requer Xdebug ou PCOV)
php artisan test --coverage

# Somente uma suite
php artisan test --testsuite=Feature

# Filtrar um teste específico
php artisan test --filter=ExpenseTest

# Via Pest diretamente
./vendor/bin/pest
./vendor/bin/pest --coverage
```

### Cobertura dos testes

| Arquivo | Cenários cobertos |
|---|---|
| `AuthTest` | Registro, login, logout, rotas protegidas |
| `CategoryTest` | CRUD completo, isolamento entre usuários, unicidade de nome |
| `ExpenseTest` | Valor positivo, categoria do usuário, isolamento, data futura |
| `DashboardTest` | Estrutura, total do mês, últimas 5, isolamento de dados |

---

## 📡 Endpoints

Base URL: `http://localhost:8000/api/v1`

### Autenticação

| Método | Endpoint | Descrição | Auth |
|---|---|---|---|
| `POST` | `/auth/register` | Cadastro de usuário | ❌ |
| `POST` | `/auth/login` | Login | ❌ |
| `POST` | `/auth/logout` | Logout (invalida token) | ✅ |

**POST `/auth/register`**
```json
{
  "name": "João Silva",
  "email": "joao@email.com",
  "password": "minhasenha123",
  "password_confirmation": "minhasenha123"
}
```

**Resposta `201`:**
```json
{
  "message": "User registered successfully.",
  "data": {
    "user": { "id": 1, "name": "João Silva", "email": "joao@email.com" },
    "token": "1|abc123..."
  }
}
```

**POST `/auth/login`**
```json
{ "email": "joao@email.com", "password": "minhasenha123" }
```

---

### Categorias

> Todas as rotas requerem `Authorization: Bearer {token}`

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/categories` | Listar categorias do usuário |
| `POST` | `/categories` | Criar categoria |
| `GET` | `/categories/{id}` | Detalhar categoria |
| `PUT` | `/categories/{id}` | Atualizar categoria |
| `DELETE` | `/categories/{id}` | Excluir categoria |

**POST / PUT `/categories`**
```json
{ "name": "Alimentação" }
```

**Resposta `201`:**
```json
{
  "message": "Category created successfully.",
  "data": {
    "id": 1,
    "name": "Alimentação",
    "user_id": 1,
    "created_at": "2024-01-15 10:00:00",
    "updated_at": "2024-01-15 10:00:00"
  }
}
```

---

### Despesas

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/expenses` | Listar despesas (paginado, 15/pág) |
| `POST` | `/expenses` | Criar despesa |
| `GET` | `/expenses/{id}` | Detalhar despesa |
| `PUT` | `/expenses/{id}` | Atualizar despesa |
| `DELETE` | `/expenses/{id}` | Excluir despesa |

**POST / PUT `/expenses`**
```json
{
  "description": "Almoço no restaurante",
  "amount": 45.90,
  "date": "2024-01-15",
  "category_id": 1
}
```

**Resposta `201`:**
```json
{
  "message": "Expense created successfully.",
  "data": {
    "id": 1,
    "description": "Almoço no restaurante",
    "amount": "45.90",
    "date": "2024-01-15",
    "category": { "id": 1, "name": "Alimentação" },
    "created_at": "2024-01-15 10:00:00",
    "updated_at": "2024-01-15 10:00:00"
  }
}
```

**GET `/expenses` — resposta paginada:**
```json
{
  "message": "Expenses retrieved successfully.",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

---

### Dashboard

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/dashboard` | Resumo financeiro do mês atual |

**Resposta `200`:**
```json
{
  "message": "Dashboard data retrieved successfully.",
  "data": {
    "current_month": "2024-01",
    "total_this_month": "1234.50",
    "latest_expenses": [
      {
        "id": 5,
        "description": "Mercado",
        "amount": "320.00",
        "date": "2024-01-14",
        "category": { "id": 1, "name": "Alimentação" }
      }
    ],
    "category_breakdown": [
      {
        "category_id": 1,
        "category_name": "Alimentação",
        "total": "820.00",
        "count": 8
      },
      {
        "category_id": 2,
        "category_name": "Transporte",
        "total": "414.50",
        "count": 12
      }
    ]
  }
}
```

---

### Health Check

| Método | Endpoint | Descrição |
|---|---|---|
| `GET` | `/api/health` | Status da API |

---

### Padrão de erros

**422 — Validação:**
```json
{
  "message": "Validation failed.",
  "errors": {
    "amount": ["The amount must be greater than zero."],
    "category_id": ["The selected category does not exist or does not belong to you."]
  }
}
```

**401 — Não autenticado:**
```json
{ "message": "Unauthenticated. Please login to continue.", "data": null }
```

**403 — Sem permissão:**
```json
{ "message": "You do not have permission to access this expense.", "data": null }
```

**404 — Não encontrado:**
```json
{ "message": "Resource not found.", "data": null }
```

---

## 🌱 Credenciais do seed

Após rodar `php artisan migrate --seed`:

| Email | Senha | Descrição |
|---|---|---|
| `demo@financeapi.com` | `password` | Usuário demo com 15 despesas |
| `admin@financeapi.com` | `password` | Usuário admin com 15 despesas |

---

## 🚢 Deploy

### Railway

1. Crie um projeto no [Railway](https://railway.app)
2. Conecte seu repositório GitHub
3. Adicione um serviço **MySQL** ou **PostgreSQL**
4. Configure as variáveis de ambiente:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=           # gerado automaticamente ou rode: php artisan key:generate --show
APP_URL=https://seu-app.railway.app

DB_CONNECTION=mysql
DATABASE_URL=${{MySQL.DATABASE_URL}}   # Railway injeta automaticamente

SANCTUM_STATEFUL_DOMAINS=seu-frontend.vercel.app
SEED_DB=true       # semeia o banco no primeiro deploy
```

5. Em **Settings → Deploy**, adicione o Start Command:
```bash
sh docker/start.sh && supervisord -c /etc/supervisor/conf.d/supervisord.conf
```

### Render

1. Crie um **Web Service** no [Render](https://render.com)
2. Selecione seu repositório
3. Configure:
   - **Environment:** Docker
   - **Dockerfile Path:** `./Dockerfile`
4. Adicione as variáveis de ambiente (igual ao Railway acima)
5. Adicione um banco **PostgreSQL** pelo painel do Render

```env
DB_CONNECTION=pgsql
DATABASE_URL=$DATABASE_URL   # Render injeta automaticamente
```

### Link do deploy

> 🔗 **`https://finance-api-production.up.railway.app`**  
> *(Substitua pela URL real após o deploy)*

---

## 🗂 Estrutura do projeto

```
finance-api/
├── app/
│   ├── Exceptions/
│   │   └── Handler.php              # Tratamento global de erros JSON
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── DashboardController.php
│   │   │   └── ExpenseController.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   ├── Category/
│   │   │   │   ├── StoreCategoryRequest.php
│   │   │   │   └── UpdateCategoryRequest.php
│   │   │   └── Expense/
│   │   │       ├── StoreExpenseRequest.php
│   │   │       └── UpdateExpenseRequest.php
│   │   └── Resources/
│   │       ├── CategoryResource.php
│   │       └── ExpenseResource.php
│   └── Models/
│       ├── Category.php
│       ├── Expense.php
│       └── User.php
├── database/
│   ├── factories/
│   │   ├── CategoryFactory.php
│   │   ├── ExpenseFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── ..._create_users_table.php
│   │   ├── ..._create_personal_access_tokens_table.php
│   │   ├── ..._create_categories_table.php
│   │   └── ..._create_expenses_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
├── docker/
│   ├── nginx.conf
│   ├── php-fpm.conf
│   ├── start.sh
│   └── supervisord.conf
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
│       ├── AuthTest.php
│       ├── CategoryTest.php
│       ├── DashboardTest.php
│       └── ExpenseTest.php
├── .env.example
├── Dockerfile
├── phpunit.xml
└── README.md
```

---

## 🔐 Segurança

- Senhas com hash via `bcrypt`
- Tokens Sanctum invalidados no logout
- Isolamento total de dados por usuário (verificado em cada operação)
- Erros genéricos em produção (`APP_DEBUG=false`)
- Validação rigorosa em todos os inputs

---

## 💡 Commits semânticos sugeridos

```
feat: add user registration and login with Sanctum
feat: add categories CRUD with per-user isolation
feat: add expenses CRUD with business rules validation
feat: add dashboard endpoint with monthly summary
test: add feature tests for auth, categories, expenses and dashboard
chore: add Dockerfile and Railway/Render deploy config
docs: add complete README with endpoints and deploy guide
```

---

## 🤝 Consumo pelo frontend Vue.js

Configure o Axios no Vue.js:

```javascript
// src/services/api.js
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api/v1',
  headers: { 'Accept': 'application/json' }
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export default api
```

```javascript
// Exemplo de uso — Login
const { data } = await api.post('/auth/login', { email, password })
localStorage.setItem('token', data.data.token)

// Listar categorias
const { data } = await api.get('/categories')

// Criar despesa
const { data } = await api.post('/expenses', {
  description: 'Almoço',
  amount: 45.90,
  date: '2024-01-15',
  category_id: 1
})

// Dashboard
const { data } = await api.get('/dashboard')
```
