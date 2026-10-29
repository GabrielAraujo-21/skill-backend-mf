# Revisão independente do laboratório completo

Revisão de fontes e evidências em 29/10/2026. Não executei requisições mutantes, reset, instalação, deploy ou testes concorrentes com a suíte principal. Não li `.env` e não alterei os projetos. As conclusões abaixo descrevem o estado inspecionado, antes das correções/retestes do coordenador.

## Achados concretos encaminhados

1. **Throttle sem persistência HTTP.** `backend/app/Providers/AppServiceProvider.php` configurava `cache.default=array` também fora de PHPUnit, enquanto `routes/web.php` aplicava `throttle:10,1` ao login. Cada requisição do servidor cria uma aplicação/cache nova; o contador não sobrevive. Recomendada reprodução com tentativas HTTP sequenciais e cache local persistente no runtime do laboratório. A correção não demanda Redis nem define um cache universal para projetos consumidores.
2. **Teste de resposta atrasada destruía o contexto em avaliação.** `browser/tests/integracao.spec.js` chamava o helper `entrar`, que fazia `page.goto('/login')`, depois de reter a resposta da conta A. Isso recria documento e Pinia, portanto não prova descarte por geração na mesma instância. Recomendada autenticação B pelo formulário já aberto via Vue Router, sem nova navegação de documento; concluir a listagem B e depois liberar exatamente a requisição antiga capturada.
3. **Asserções antes de terminar a requisição.** O teste de senha incorreta verificava apenas permanência em `/login`, possível antes da resposta. A ausência de cliente na lista B podia ser observada enquanto ela ainda estava vazia/carregando. Recomendado aguardar respostas reais e conferir status 422/login e conclusão da listagem antes das asserções negativas.

Os achados 2/3 são lacunas do teste, não evidência de vulnerabilidade do aplicativo. O achado 1 é configuração ineficaz do laboratório, não demonstra falha literal nas instruções das skills. A coordenação deve registrar os retestes efetivamente executados e sua revisão de fontes.

## Controles inspecionados

- Reset confere driver SQLite, caminho local esperado e recusa link no arquivo antes de `migrate:fresh --seed`. O provider elimina URL de banco e fixa `database/laboratorio.sqlite`; PHPUnit usa `:memory:`. Isto é guarda operacional do fixture, não sandbox de segurança contra arquivos/código adulterados.
- Rotas são registradas no grupo `web`, com sessão e CSRF; endpoints privados usam `auth:sanctum`. Login regenera sessão; logout invalida a sessão e regenera token CSRF. A suíte PHPUnit não prova proteção CSRF porque Laravel a desabilita em unit testing; a comprovação deve vir do navegador.
- Listagem filtra tenant derivado do usuário. FormRequest/Policy recusam atualização e exclusão de outro tenant, contexto ausente e usuário sem permissão; payload `tenant_id` é proibido. Não existe bypass por nome do papel SuperAdmin.
- Dados privados frontend ficam em memória; somente flag não sensível de logout pendente é persistida. Store invalida geração e identidade antes de aceitar resultado assíncrono. Recebe evento de outra aba. O contrato específico permite reconciliar 401 de logout e mantém pendência em 419; não generalizar qualquer 401 de qualquer endpoint como prova de revogação.
- Evidência backend final inspecionada registrava 15 testes/49 asserts e Pint aprovado. O ensaio de legado é explicitamente sintético: antes/depois da normalização de CNPJ na action, mantendo hash das rotas. Não é uma migração de sistema legado real.

## Empacotamento e reprodução

Publicar fontes próprias, manifestos/lockfiles, PHPUnit/Vitest/Playwright, decisões, instruções e evidências selecionadas. Não incluir `vendor`, `node_modules`, bancos SQLite, sessão/cache gerado, logs operacionais, `.env`, traces com cookies ou credenciais reais. Senhas e chave fixa do laboratório são sintéticas públicas e tornam o fixture impróprio para produção.

O scaffold Laravel contém scripts `setup/dev` que podem criar `.env`, instalar ferramentas adicionais e executar migrations; o roteiro documentado utiliza `composer install --no-scripts`, `artisan package:discover` e o preparador guardado. Evitar apresentar comandos genéricos do scaffold como roteiro seguro deste ensaio. Após excluir caches, manter as pastas que Laravel necessita ou criá-las no preparo.

Os resultados do navegador devem informar versão/engine, viewport e se a UI foi realmente inspecionada. Axe e fluxo de teclado parcial não certificam acessibilidade integral. SQLite não certifica PostgreSQL/MySQL; cookie em loopback HTTP não certifica HTTPS, domínios distintos ou configuração CORS de produção. Múltiplas instâncias, armazenamento indisponível, PSP, filas e concorrência distribuída permanecem fora do ensaio, salvo evidência nova explícita.

Esta revisão herdou o contexto e os critérios do coordenador. É uma segunda leitura independente dos artefatos, não benchmark cego nem inferência estatística sobre a skill.

## Releitura das correções

Fontes atualizadas inspecionadas após a rodada intermediária de seis casos conformes e três falhas:

- Provider passou a usar cache `file` no runtime e `array` somente em unit testing. O novo `scripts/limpar-cache.php` valida store, driver, caminho canônico e links/junctions antes de limpar exclusivamente o cache local. A suíte browser limpa a quota entre casos, mas mantém todas as tentativas do cenário de limite dentro de um mesmo caso.
- O helper de login ganhou a opção de não navegar o documento. A corrida faz login B pelo Vue Router na mesma instância, aguarda sua listagem, libera a resposta antiga e identifica a conclusão pela própria requisição retida. Isto remove o principal falso positivo apontado.
- Login inválido passou a conferir resposta 422. Saída normal aguarda resposta 204 e remoção da pendência. Listagens aguardam resposta 200 e processamento frontend antes de asserções negativas.
- O teste móvel mantém a resposta real de listagem retida durante a primeira execução axe, libera-a e executa axe novamente após a resposta. Recomendada asserção explícita da presença/ausência da barra de progresso para documentar os estados.
- A correção de acessibilidade inspecionada aumenta contraste pelo tema e dá nomes aos loaders Vuetify. Não remove os elementos, não desabilita regras axe nem mascara violações. A suíte continua sem retries; o timeout maior é tolerância ao host de avaliação e não certificação de performance.

Estas são confirmações de revisão de fontes. Não substituem o resultado final do navegador. Até esta releitura, o JSON disponível ainda era a rodada intermediária; o relatório principal deve apontar o JSON final efetivamente concluído. Nenhuma nova mutação foi executada nesta releitura.

## Conferência da rodada final

Conferido `browser/evidencias/resultado.json`, início `2026-10-29T12:22:45.431Z`, duração registrada de `206478.636` ms. Estatísticas: **10 expected, 0 unexpected, 0 skipped, 0 flaky**. Os dez casos possuem resultado `expected` e `retry=0`. SHA-256 do JSON inspecionado: `4496f3d31667c5ce48a56340718c51301efac49a5831788336cbbc33ada8c462`.

Essa rodada comprova, dentro do laboratório Chromium/loopback/SQLite: proteção da rota e CSRF real; credencial inválida; CRUD com CNPJ alfanumérico e isolamento; validação servidor ao contornar o cliente; logout remoto e recarga; logout offline e reconciliação; resposta tardia no mesmo documento após trocar conta; ativação do limite de login entre requisições; logout entre abas; teclado e estados acessíveis no viewport móvel; CNPJ inválido com mensagem de campo sem envio de cadastro.

As fontes finais também conferem explicitamente a presença do progressbar antes de liberar a resposta retida e sua ausência antes da segunda análise axe. O cenário de cadastro analisa o estado pós-sucesso; o novo cenário de CNPJ inválido analisa o estado de erro. Não houve exclusão de regras axe ou remoção das asserções que falhavam. As asserções `expect.soft` continuam contabilizando falhas no resultado do caso.

`sucesso-antes.json` preserva a rodada direcionada anterior, com um caso conforme e um com falha. A evidência mostra mensagens de obrigatório remanescentes depois do salvamento. A fonte corrigida aguarda `nextTick()` após limpar os models e antes de `resetValidation()`. O tema final define contraste específico de sucesso; os resultados finais axe não registram violações nos estados cobertos.

O limite de login é testado pela existência de 429 em doze tentativas, não pela exatidão de cada posição da sequência. O relatório não deve alegar certificação do limite exato ou de múltiplas instâncias. Tampouco esta conferência amplia cobertura para outros engines, HTTPS/CORS entre origens, todos os fluxos de teclado, falha de storage ou produção. Foi leitura independente de fontes/JSON; não executei outra rodada nem alterei o aplicativo.
