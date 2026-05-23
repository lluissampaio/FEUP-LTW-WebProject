# Iron Haven — ltw12g06

## Projeto LTW 2025/26

Site de gestão de um ginásio privado. Membros podem navegar e inscrever-se em
aulas, ver perfis de treinadores, consultar a disponibilidade de equipamento e
deixar avaliações. Administradores gerem o catálogo de aulas, equipamento e
contas. Inclui sistema de lista de espera automática e notificações in-app.

### Como executar localmente

A partir da raiz do repositório:

```bash
php -S localhost:9000
```

Depois abrir [`http://localhost:9000`](http://localhost:9000) no navegador
(faz redirect para `pages/`).

### Contas de demonstração (password: `password`)

| Tipo     | Email                          |
| -------- | ------------------------------ |
| Admin    | `admin@ironhaven.com`          |
| Trainer  | `anika.patel@ironhaven.com`    |
| Member   | `jordan.miller@example.com`    |

### Estrutura

```
├── actions/      # POST handlers (login, register, enrol, review, admin…)
├── api/          # Endpoints JSON / AJAX
├── css/          # Folha de estilos única (mobile-first, BEM)
├── database/     # Schema SQL, ficheiro SQLite e classes PHP (PDO)
├── images/       # Fotografia (treinadores, locais, founders)
├── javascript/   # Scripts client-side
├── pages/        # Páginas (controllers + partials)
├── templates/    # Funções drawX() para renderizar HTML
└── utils/        # Helpers (Session class)
```

### Tecnologias

HTML5 semântico · CSS3 (mobile-first, sem frameworks) · PHP 8 (PDO/SQLite)
· JavaScript vanilla + Ajax/JSON. Senhas com `password_hash` (bcrypt),
prepared statements em todas as queries, anti-CSRF tokens.

### Membros do Grupo

- José Lopes — up202406360
- Francisco Mendes — up202407152
- Luís Sampaio — up202404078
