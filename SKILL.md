---
name: skill-backend-mf
description: Criar, revisar e adequar backends Laravel com PHP, conduzindo decisões de arquitetura com o desenvolvedor, preservando contratos existentes e aplicando guardrails de segurança. Usar para padronização de backend, diagnóstico arquitetural ou implantação incremental dessas convenções; não aplicar automaticamente a tarefas exclusivamente frontend.
metadata:
  version: "1.2.0"
---

# Backend MF

Padronize backends Laravel 12/PHP 8.4 conforme a [especificação 1.2.0](references/especificacao-1.2.0.md). Essa é uma referência, não autorização para atualizar versões, alterar decisões do projeto ou instalar todos os módulos citados.

## Entrada e escolha de fluxo

1. Identifique o pedido: diagnóstico sem alterações, projeto novo ou adequação de projeto existente. Não transforme revisão em implementação.
2. Leia as instruções locais, decisões arquiteturais e manifestos/lockfiles não sensíveis. Identifique o diretório real do backend, alterações pendentes e trabalho reservado a outros agentes. Não leia `.env`, certificados ou outros segredos sem autorização humana explícita.
3. Leia [segurança e decisões consultivas](references/seguranca-consulta.md). Resolva apenas decisões ausentes que afetem a etapa. Apresente opções, recomendação justificada e consequências; aguarde a escolha antes do trabalho dependente. Silêncio não é aprovação.
4. Use [projeto novo](references/projeto-novo.md) ou [adequação existente](references/projeto-existente.md). No diagnóstico, use o inventário da adequação, entregue achados e não edite arquivos.
5. Consulte na especificação somente os temas aplicáveis. Para tenancy procure §8; autorização §14; arquivos §16; assinaturas §18; Efí §19; infraestrutura §26. Módulos opcionais dependem de seleção explícita ou decisão vigente do projeto.
6. Apresente plano curto com reuso, mudanças, contratos, verificações e riscos. Uma autorização já concedida para esse escopo dispensa confirmação adicional. Decisão material nova exige consulta; edição local reversível já autorizada não exige confirmação a cada arquivo.
7. Implemente a menor alteração coesa. Execute verificações pertinentes conforme [validação](references/validacao.md) e os gates locais. Relate apenas evidências realmente obtidas.

## Invariantes

- Preserve idioma de comunicação e governança do projeto; identificadores novos de domínio em pt-BR, sem renomear contratos existentes. Métodos e interfaces do framework conservam seus nomes.
- Não presuma multi-tenancy, billing, Efí, S3, CloudFront ou Reverb. Faça a escolha consultiva quando ainda não decidida.
- Instruções do usuário e decisões locais prevalecem sobre defaults desta skill. Conflitos materiais bloqueiam apenas a decisão afetada; não revogue ADRs implicitamente.
- Sem contexto válido, acesso a dados por tenant falha fechado. SuperAdmin não recebe bypass irrestrito.
- Não aplique migrations destrutivas, deploy ou mutações externas sem autorização correspondente. Preparação local e verificações seguras podem continuar.
- Não invente bibliotecas internas: `CompatBanco`, `Route::crud`, `make:crud` e demais componentes próprios são contratos propostos, não implementações distribuídas por esta skill. Procure equivalentes antes de construir os necessários ao escopo.
- Não gere CRUD, soft delete, auditoria ou abstrações genéricas indiscriminadamente para cada tabela.

## Saída da execução

Entregue decisões e fontes consultadas, classificação REUTILIZAR/ESTENDER/REFATORAR/CRIAR/INVESTIGAR MAIS, arquivos alterados, validações executadas, cobertura adiada, riscos e próximos passos. No diagnóstico, separe defeitos comprovados de hipóteses. Nunca declare produção certificada por testes locais.

Esta skill inclui instruções e referências; não inclui um framework PHP, um backend pronto ou scripts de deploy. A descoberta automática permanece habilitada.
