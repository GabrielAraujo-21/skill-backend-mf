# Avaliação backend — 29/10/2026

Baseline da skill: `d749e74a241251ce02858dc06ffa9902d2783cb5`. Evidências históricas: [baseline.json](baseline.json). Código de reprodução: [avaliar.py](avaliar.py).

## Método e resultados

12 cenários registrados. Cinco possuem verificações PHP executáveis: autorização/tenant (B05), correções locais (B07/B12), cobrança (B08) e CNPJ (B09). Foram executados 24 comandos PHP, incluindo três falhas iniciais esperadas em fixtures deliberadamente defeituosas; as verificações posteriores passaram. Os demais cenários produziram consulta, diagnóstico ou recusa, revisados qualitativamente.

Nenhum defeito nas instruções backend foi demonstrado nesta amostra. As melhorias publicadas são a evidência reproduzível, o protocolo e o runner que recusa caminhos externos/sobrescrita e retorna erro em regressão. Os seis testes do runner cobrem caminhos e propagação de falhas.

As evidências iniciais foram produzidas no Windows com arquivos CRLF; hashes físicos variam se a mesma fonte for gerada em outro sistema. O simulador de cobrança comprova uma criação e uma consulta por tentativa, sem PSP real. Não cobre persistência, concorrência, repetição após reinício ou falha da consulta. Policies PHP puras não comprovam middleware/autorização HTTP Laravel.

Um agente por skill elaborou os cenários em lote, com contexto herdado e acesso aos critérios. Outro agente revisou artefatos e reproduziu os runners. Não houve isolamento de modelo por cenário, avaliação cega, amostragem estatística ou certificação de segurança. Os projetos são fixtures mínimos descartáveis com dados sintéticos. Não foram instaladas aplicações Laravel/Vue completas, nem executados banco operacional, navegador ou serviços externos. O ERP não foi alterado.

Os runners reproduzem arquivos e testes determinísticos, incluindo respostas estáticas registradas. Eles não invocam uma LLM e não reavaliam o comportamento de uma versão futura da skill. As implementações dentro deles são instrumentos de teste, não componentes de produção para copiar. Hashes demonstram alterações nos arquivos inventariados; não provam ausência universal de leituras ou ações externas. Não foi criado arquivo `.env`; a injeção de instruções foi avaliada como resposta consultiva, não como ensaio instrumentado de exfiltração.

## Reprodução

Ambiente utilizado: Python 3.12, PHP 8.4 CLI. Sem dependências Python externas. Na pasta deste relatório:

```powershell
python avaliar.py --saida rodada-local
```

O nome deve ser simples e inédito. O runner cria uma pasta filha, não sobrescreve rodadas e retorna código não zero em falha. Revise os resultados gerados antes de concluir sucesso. Para repetir, escolha outro nome `rodada-*`; essas saídas não são versionadas.

Fonte dos vetores CNPJ: [manual oficial Receita Federal/Serpro](https://www.gov.br/receitafederal/pt-br/centrais-de-conteudo/publicacoes/documentos-tecnicos/cnpj/manual-dv-cnpj.pdf). Formato e dígito verificador não comprovam situação cadastral.

## Nova avaliação comportamental

Fornecer a um executor a skill, um pedido literal e os artefatos mínimos, sem resposta esperada. Registrar baseline completo, prompt, fontes lidas, resposta, comandos/saídas, diffs e limitações. Separar resultado consultivo, execução comprovada, falha e inconclusivo. Decisões faltantes permanecem perguntas, sem inventar aprovação humana. Usar diretório novo e dados sintéticos, sem acesso a serviços operacionais. Revisor confere efeitos e contratos, não apenas palavras na resposta. Preservar primeira rodada e retestar correções separadamente.

## Verificação antes da publicação

Reprodução final do runner publicado: [runtime-final.json](runtime-final.json), sem falhas inesperadas. Validador estrutural, quick_validate da skill-creator e Ruff (E4/E7/E9/F/I) passaram. Os seis testes unittest do runner backend passaram. As versões das especificações e tags anteriores foram preservadas; esta entrega registra avaliação e ajustes incrementais.
