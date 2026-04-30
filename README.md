# ltw12g06

## Projeto LTW 2025/26

### Como executar localmente

A partir da raiz do repositório:

```bash
php -S localhost:9000
```

Depois abrir [`http://localhost:9000/templates/index.html`](http://localhost:9000/templates/index.html) no navegador.

### Páginas Implementadas (1ª Entrega)

- **[Página Principal (Index)](templates/index.html):** Landing page com secções de serviços, estatísticas, testemunhos e planos de adesão.
- **[Classes](templates/classes.html):** Página institucional para o horário de aulas (conteúdo detalhado previsto para a entrega final).
- **[Trainers](templates/trainers.html):** Página institucional para o perfil dos treinadores (conteúdo detalhado previsto para a entrega final).
- **[Registo](templates/register.html):** Formulário de criação de conta para novos membros.
- **[Login](templates/login.html):** Interface de acesso para membros registados.
- **[Dashboard](templates/dashboard.html):** Área personalizada para visualização de estatísticas de treino e progresso do membro.

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
