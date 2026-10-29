# Backend MF — skill 1.2.0

Skill consultiva para criar, diagnosticar e adequar backends Laravel 12/PHP 8.4, com preservação de contratos, decisões com o desenvolvedor e guardrails de segurança.

- [Instruções da skill](SKILL.md)
- [Especificação consolidada 1.2.0](references/especificacao-1.2.0.md)
- [Guardrails e decisões consultivas](references/seguranca-consulta.md)
- [Projeto novo](references/projeto-novo.md)
- [Projeto existente](references/projeto-existente.md)
- [Validação e cenários](references/validacao.md)

## Uso

Instale esta pasta como `skill-backend-mf` no diretório de skills da sua ferramenta (no Codex, normalmente `~/.codex/skills/skill-backend-mf`). A clonagem do repositório em uma pasta de projetos não equivale à instalação. Não substitua uma instalação existente sem conferir suas alterações.

Exemplo de pedido: `Use $skill-backend-mf para avaliar este backend e propor a adequação incremental. Antes de implementar decisões pendentes, apresente opções e sua recomendação.`

A descoberta automática permanece habilitada. A skill lê a governança local e consulta apenas decisões relevantes ainda ausentes. Multi-tenancy, pagamentos, arquivos em nuvem e tempo real são módulos selecionáveis.

## Limites

Este repositório contém a skill e sua especificação, não uma aplicação Laravel pronta. Os componentes PHP e o gerador `make:crud` são contratos a localizar/implementar quando autorizados; não são ferramentas entregues aqui. Nenhum ERP é alterado pela criação deste pacote. Deploy de aplicação, migrações e mutações externas exigem escopo autorizado.

## Verificação

Com Python 3.9 ou superior, execute `python scripts/validar_skill.py`. O verificador usa apenas a biblioteca padrão, não acessa rede nem modifica o projeto. Ele valida estrutura e referências, não comportamento do agente ou compatibilidade de um backend. Cenários comportamentais estão documentados separadamente e não são apresentados como testes executados.
