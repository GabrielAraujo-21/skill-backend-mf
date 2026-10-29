# Backend MF — skill 1.2.0

Skill consultiva para criar, diagnosticar e adequar backends Laravel 12/PHP 8.4, preservando contratos existentes e conduzindo decisões de arquitetura com o desenvolvedor.

## Conteúdo

- [Instruções da skill](SKILL.md)
- [Especificação consolidada 1.2.0](references/especificacao-1.2.0.md)
- [Guardrails e decisões consultivas](references/seguranca-consulta.md)
- [Projeto novo](references/projeto-novo.md)
- [Projeto existente](references/projeto-existente.md)
- [Validação e cenários](references/validacao.md)

## Pré-requisitos

- Codex com suporte a skills locais.
- Git, caso use instalação e atualização por clone.
- Python 3.9 ou superior somente para executar o verificador opcional.
- PHP, Composer, banco e demais serviços são requisitos do backend que será trabalhado, não da instalação desta skill.

Este pacote não instala Laravel nem serviços externos. A pasta de desenvolvimento `C:\projetos-mf\skills-mf\skill-backend-mf` guarda o projeto da skill; mantê-la ali, por si só, não a disponibiliza em todos os outros projetos.

## Instalação no Codex

Escolha apenas um dos métodos abaixo para evitar cópias duplicadas com o mesmo nome. Os comandos são instruções para instalação; não são executados automaticamente ao ler este README.

A documentação atual do Codex indica `~/.agents/skills` para skills pessoais e `.agents/skills` dentro do projeto para skills compartilhadas pelo repositório. Se seu ambiente usa outro diretório configurado, confirme o caminho efetivamente reconhecido. [Documentação oficial de skills](https://learn.chatgpt.com/docs/build-skills).

### Opção A — instalação pessoal no Windows (PowerShell)

Recomendada para usar a skill em vários projetos. O clone acompanha a branch `main`, que contém também as melhorias posteriores à publicação da tag 1.2.0.

```powershell
$diretorioSkills = Join-Path $env:USERPROFILE '.agents\skills'
$destinoSkill = Join-Path $diretorioSkills 'skill-backend-mf'

if (Test-Path -LiteralPath $destinoSkill) {
    throw 'A pasta da skill já existe. Confira seu conteúdo e siga a seção de atualização.'
}

New-Item -ItemType Directory -Path $diretorioSkills -Force | Out-Null
git clone https://github.com/GabrielAraujo-21/skill-backend-mf.git $destinoSkill
if ($LASTEXITCODE -ne 0) { throw 'Falha ao clonar a skill.' }

Test-Path -LiteralPath (Join-Path $destinoSkill 'SKILL.md')
```

O último comando deve retornar `True`. Não use execução como administrador apenas para instalar uma skill pessoal.

### Opção B — instalação pessoal no Linux/macOS (Bash)

```bash
destino_skill="$HOME/.agents/skills/skill-backend-mf"
if [ -e "$destino_skill" ]; then
  printf '%s\n' 'A pasta já existe; confira a seção de atualização.'
else
  mkdir -p "$HOME/.agents/skills" &&
    git clone https://github.com/GabrielAraujo-21/skill-backend-mf.git "$destino_skill"
fi
```

### Opção C — instalação somente em um projeto

Na raiz do projeto escolhido, crie `.agents/skills/skill-backend-mf/` e copie para ela estes itens deste repositório:

```text
skill-backend-mf/
├── SKILL.md
├── agents/
├── references/
└── scripts/
```

Copie as pastas completas. Não copie `.git`, não crie um repositório Git aninhado e não copie apenas `SKILL.md`, pois ele depende das referências. Revise o diff e versione os arquivos junto ao projeto se a equipe deve compartilhar a skill.

Esse método altera somente o projeto escolhido. Para manter o ERP sem alterações, use a instalação pessoal. Uma cópia por projeto não acompanha automaticamente as atualizações da pasta de desenvolvimento.

### Confirmar a instalação

Abra o projeto de backend no Codex e envie:

```text
Use $skill-backend-mf somente para diagnóstico. Confirme que encontrou a skill,
identifique o diretório do backend e liste as decisões existentes e pendentes.
Não altere arquivos nem execute comandos que modifiquem dados.
```

O Codex deve localizar a skill e iniciar o levantamento. A descoberta de alterações é automática; se a skill não aparecer, reinicie o Codex. [Comportamento documentado](https://learn.chatgpt.com/docs/build-skills).

Opcionalmente, valide a pasta instalada:

```powershell
$destinoSkill = Join-Path $env:USERPROFILE '.agents\skills\skill-backend-mf'
python (Join-Path $destinoSkill 'scripts\validar_skill.py')
```

Resultado esperado: `Validação estrutural: OK`.

## Como usar

Os exemplos abaixo são mensagens para o chat do Codex, não comandos de terminal. Abra o projeto correto e informe o escopo. Mencionar `$skill-backend-mf` solicita explicitamente seu uso; a descoberta automática também está habilitada. [Invocação de skills](https://learn.chatgpt.com/docs/build-skills).

### Criar um backend novo

```text
Use $skill-backend-mf para criar um backend Laravel 12/PHP 8.4 em backend/.
O produto será um sistema de gestão de serviços. Antes de implementar,
apresente as decisões necessárias, as opções e sua recomendação para cada uma,
incluindo multi-tenancy, banco e autenticação. Aguarde minhas escolhas para
as partes dependentes. Não configure pagamentos nem publique em servidores.
```

### Diagnosticar um backend existente

```text
Use $skill-backend-mf para revisar este backend sem alterar arquivos.
Leia a governança e localize as implementações existentes. Apresente uma matriz
com requisitos atendidos, parciais, ausentes, não aplicáveis e conflitantes,
indicando evidências, riscos e a ordem recomendada de adequação.
```

### Adequar um projeto existente

```text
Use $skill-backend-mf para adequar o módulo de produtos deste projeto.
Preserve rotas, payloads, identificadores, permissões e versões existentes.
Procure componentes reutilizáveis, proponha um plano incremental e apresente
as decisões pendentes com recomendações. Após resolvermos essas decisões,
execute o escopo aprovado e suas verificações. Não execute deploy.
```

### Retomar uma etapa aprovada

```text
Use $skill-backend-mf para executar a etapa aprovada no plano do projeto.
Reutilize as decisões registradas, sem repetir perguntas já resolvidas.
Consulte-me se surgir conflito de arquitetura ou alteração de contrato fora
do escopo. Ao concluir, informe arquivos alterados, verificações e pendências.
```

### Avaliar um módulo opcional

```text
Use $skill-backend-mf para avaliar se este projeto precisa de multi-tenancy.
Compare instalação dedicada e compartilhada considerando isolamento, operação
e custo. Recomende uma alternativa e aguarde minha escolha antes de alterar
schema, autenticação ou autorização.
```

## Como funciona o fluxo consultivo

1. O agente identifica se o pedido é diagnóstico, projeto novo ou adequação.
2. Consulta instruções locais, decisões, código e dependências relevantes.
3. Apresenta opções, recomendação e consequências para decisões ainda ausentes.
4. Aguarda a escolha antes de implementar o que depende dela.
5. Executa o plano autorizado, preservando contratos e trabalho existente.
6. Entrega evidências, limitações e próximos passos.

Multi-tenancy, assinaturas/Efí, S3/CloudFront e Reverb são opcionais. A skill não precisa perguntar por todos em toda tarefa. Decisões válidas já registradas são reutilizadas. Aprovação de implementação local não autoriza deploy, cobrança ou migração destrutiva.

## Atualização

### Instalação pessoal por clone no Windows

Confira as alterações locais antes de atualizar. Execute o bloco na mesma sessão PowerShell; se houver divergência ou alterações próprias, resolva-as antes de prosseguir, sem usar reset destrutivo.

```powershell
$destinoSkill = Join-Path $env:USERPROFILE '.agents\skills\skill-backend-mf'
$alteracoes = git -C $destinoSkill status --porcelain
if ($LASTEXITCODE -ne 0) { throw 'Não foi possível consultar o clone instalado.' }
if ($alteracoes) { throw 'Há alterações locais. Revise e preserve-as antes de atualizar.' }

$branchAtual = git -C $destinoSkill branch --show-current
if ($LASTEXITCODE -ne 0 -or $branchAtual -ne 'main') {
    throw 'Este procedimento exige a branch main; confira se a instalação foi fixada em uma tag.'
}

git -C $destinoSkill pull --ff-only origin main
if ($LASTEXITCODE -ne 0) { throw 'Atualização interrompida; confira a mensagem do Git.' }
```

No Linux/macOS, execute `git -C "$HOME/.agents/skills/skill-backend-mf" status --short` e confira que não há alterações; depois, estando na branch `main`, execute `git -C "$HOME/.agents/skills/skill-backend-mf" pull --ff-only origin main`.

Para cópia por projeto, atualize a origem, compare os arquivos instalados e incorpore o diff revisado no projeto. Não sobrescreva personalizações sem conferência.

A tag `v1.2.0` preserva o lançamento original. Para fixar esse conteúdo, use `git checkout --detach v1.2.0` em um clone limpo; ele não acompanhará `main` e não incluirá correções documentais posteriores à tag. Não movemos tags já publicadas para atualizar o README.

## Solução de problemas

| Sintoma | O que conferir |
|---|---|
| Skill não aparece | Caminho reconhecido, `SKILL.md` diretamente dentro de `skill-backend-mf`, referências presentes; reiniciar o Codex se necessário. |
| Nome aparece duplicado | Verificar instalações pessoal e por projeto; manter somente o escopo desejado. |
| A pasta de desenvolvimento mudou, mas a skill não | A instalação é outro clone/cópia; atualize a pasta efetivamente carregada. |
| Git informa pasta existente | Conferir instalação anterior; não clonar por cima nem apagar trabalho local. |
| Atualização não permite fast-forward | Inspecionar commits/alterações e resolver a divergência; não forçar substituição. |
| Python não foi encontrado | Instalar Python 3.9+ se desejar rodar o verificador; a leitura da skill não depende dele. |
| Agente faz perguntas antes de implementar | É esperado quando falta uma decisão necessária; escolhas registradas não devem ser perguntadas novamente. |
| Regras da skill conflitam com ADR do projeto | Resolver explicitamente a decisão afetada; a skill não revoga a governança local. |

## Validação e limites

Na raiz deste repositório, execute `python scripts/validar_skill.py`. O verificador usa apenas a biblioteca padrão, não acessa rede nem modifica o projeto. Ele verifica estrutura, versão e referências; não comprova a qualidade das decisões do agente nem a compatibilidade de um backend.

Os cenários em [validação](references/validacao.md) são um roteiro de avaliação comportamental; sua presença não significa que foram executados.

Este repositório contém instruções e especificação, não uma aplicação Laravel pronta. Componentes como `CompatBanco`, `RespostaApi`, `TenantAtual` e `make:crud` são contratos a localizar ou implementar quando autorizados. A instalação não aplica migrations, não configura infraestrutura e não altera automaticamente o backend. Guardrails instrucionais não substituem permissões do ambiente, revisão humana e controles técnicos.
