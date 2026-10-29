# Decisões do laboratório
Escopo autorizado: aplicação Laravel 12 real e testes descartáveis. Não usar em produção.
O ensaio escolhe SQLite local, sessão cookie Sanctum e isolamento por tenant_id derivado do usuário para exercitar controles. Não são defaults universais da skill.
Identificadores inteiros preservam o contrato legado sintético; não migrar para UUID neste exercício.
CRIAR fatia clientes: scaffold não oferece esse domínio. REUTILIZAR Auth, FormRequest, Policy, Resource e PHPUnit do Laravel.
Não há PSP, emails externos, Redis, cloud ou dados reais. Senha e chave fixas são públicas e sintéticas, exclusivamente deste fixture.
Rotas /api/clientes e campos id/nome/cnpj são o contrato existente a preservar na correção incremental.
