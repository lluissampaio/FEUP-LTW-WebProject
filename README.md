# ltw12g06

## Projeto LTW 2025/26

### Como executar localmente

A partir da raiz do repositório:

```bash
php -S localhost:8080
```

Depois abrir [`http://localhost:8080/templates/index.php`](http://localhost:8080/templates/index.php) no navegador.

### Páginas Implementadas (1ª Entrega)

- **[Página Principal (Index)](templates/index.php):** Landing page com secções de serviços, estatísticas, testemunhos e planos de adesão.
- **[Classes](templates/classes.php):** Página institucional para o horário de aulas (conteúdo detalhado previsto para a entrega final).
- **[Trainers](templates/trainers.php):** Página institucional para o perfil dos treinadores (conteúdo detalhado previsto para a entrega final).
- **[Registo](templates/register.php):** Formulário de criação de conta para novos membros.
- **[Login](templates/login.php):** Interface de acesso para membros registados.
- **[Dashboard](templates/dashboard.php):** Área personalizada para visualização de estatísticas de treino e progresso do membro.

### Arquitetura CSS

Abordagem **mobile-first**, com nomenclatura **BEM** e organização em camadas de especificidade:

- **[main.css](css/main.css):** Gestão de importações (único ficheiro ligado pelo HTML).
- **[style.css](css/style.css):** *Design Tokens* (variáveis `:root`), resets e componentes reutilizáveis (botões, formulários, *tags*).
- **[layout.css](css/layout.css):** Estruturas macro de página (navegação, hero, secções, dashboard, formulários de autenticação).
- **[responsive.css](css/responsive.css):** Adaptações progressivas para `min-width: 641px / 769px / 1024px`.

### Estrutura de Pastas

```
├── css/          # Folhas de estilo (4 ficheiros em camadas)
├── img/          # Imagens (ex.: hero.jpg)
├── templates/    # Páginas HTML
└── README.md
```

### Membros do Grupo
- José Lopes up202406360
- Francisco Mendes up202407152
- Luís Sampaio up202404078
