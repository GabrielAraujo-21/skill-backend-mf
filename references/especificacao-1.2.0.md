# Spec de Convenções de Backend

**Versão:** 1.2.0
**Data:** 2026-10-29
**Status:** Referência consolidada; adoção depende do perfil e das decisões de cada projeto.
**Escopo:** criação, diagnóstico e adequação incremental de backends Laravel, preservando contratos existentes.

Esta versão substitui a proposta 1.1.0 como referência desta skill. Não revoga ADRs de projetos consumidores. Componentes próprios citados são contratos a localizar ou implementar, não código fornecido pelo pacote. Nenhum projeto modelo foi disponibilizado com a proposta.

DEVE/NÃO DEVE indicam requisito no perfil aplicável; DEVERIA indica recomendação cujo desvio requer justificativa; PODE indica alternativa. Regras de módulos opcionais só se aplicam após seleção. Instruções do usuário e governança local prevalecem; conflitos materiais devem ser resolvidos explicitamente.

## 1. Stack e perfis

Referência: Laravel 12, PHP 8.4.x em patch suportado e compatível, Sanctum quando adequado aos clientes; JavaScript nas integrações frontend, sem impor frontend a uma skill de backend. Não atualizar versões existentes automaticamente. Conferir manifests, lockfiles, extensões, ambiente e compatibilidade de dependências.

Núcleo: contratos HTTP, validação, autorização, persistência, erros seguros, qualidade e documentação. Pest é default de projeto novo; preservar suíte PHPUnit existente quando atende. Pint e Larastan (meta inicial nível 6) com baseline justificada para legado, sem ocultar novos erros.

Opcionais: multi-tenancy; Redis/cache/filas/locks; Horizon para filas Redis; Reverb/Echo para tempo real; S3/CloudFront; planos/assinaturas/Efí. Scramble é opção de documentação OpenAPI a validar com a versão instalada.

PostgreSQL, MySQL, SQL Server e SQLite são drivers candidatos. Definir versões e matriz oficialmente suportada por projeto. SQLite não substitui validação do banco operacional. Se os quatro forem declarados suportados, implementar e testar os quatro; se a cobertura for adiada, registrar sem declarar certificação integral.

## 2. Estrutura

Novo projeto pode usar `backend/` com `app/{Models,Enums,Http,Policies,Services,Actions,Contracts,Repositories,Observers,Events,Jobs,Support}`, `config/`, `database/{migrations,factories,seeders}`, `routes/`, `lang/`, `tests/{Unit,Feature,Arch}`. Criar diretórios somente quando houver responsabilidade real. `Http` contém Controllers, Requests e Resources. `Support/CompatBanco.php` é a localização default da fachada, com namespace `App\Support\CompatBanco`; estratégias maiores podem ficar em subdiretório próprio.

Preservar raiz e organização de projeto existente. Providers novos devem ser registrados conforme o bootstrap efetivo do Laravel. Dockerfiles, stubs e frontend só existem quando requeridos pelo perfil. Não gerar árvores vazias como evidência de implementação.

## 3. Nomenclatura

Identificadores novos de domínio em pt-BR sem acentos; classes PascalCase, tabelas/colunas snake_case, tabelas no plural e FK `<singular>_id`. Preservar nomes do framework (`index`, `store`, `update`, `destroy`, `restore`, `forceDelete`) e contratos existentes. Declarar tabela/relacionamentos explicitamente quando a inferência não corresponder ao português.

Rotas e slugs existentes não são renomeados automaticamente. Default novo: `produto.index`, `.show`, `.store`, `.update`, `.destroy`, `.restore`, `.force_delete`, `.input_list`, `.ativar`, `.inativar`, somente para capacidades expostas. Pivôs usam nomes convencionados explicitamente. Nomes de índices devem respeitar o menor limite dos drivers suportados e ser determinísticos; não depender apenas do limite MySQL.

Artefatos possíveis para Produto: migration, model, policy, StoreRequest, UpdateRequest, controller, service/action, contrato/repositório quando justificado, observer quando necessário, resource, factory, seeder quando apropriado e testes. Não exigir 14 arquivos para todo model.

## 4. Responsabilidades

FormRequest normaliza e valida entrada e pode autorizar via policy antes da validação sensível. Controller autoriza conforme convenção adotada, chama caso de uso e devolve Resource; não concentra queries, transações ou regras. Service/Action recebe dados validados/DTO, contexto e dependências explícitos; não depende de Request, resposta HTTP ou `request()`.

Repository concentra queries quando sua abstração for útil; não deve ser mero espelho obrigatório do Eloquent. Services/Actions definem a fronteira transacional e orquestram repositórios. Queries simples podem seguir organização existente segura; a escolha deve ser documentada. Policies verificam permissão, tenant, vínculo e estado pertinente; regras de negócio também protegem entradas não HTTP.

Resource serializa campos permitidos sem formatação visual. Observers não concentram regras de negócio. Bases (`BaseService`, `BaseEloquentRepository`, `BasePolicy`, `BaseCrudController`) são opcionais e pequenas; contratos são interfaces, não classes. Preferir composição a bases com exceções por entidade. Bindings de repositórios ficam em provider registrado.

Separar eventos síncronos de preenchimento antes de salvar, auditoria durável na transação e efeitos após commit (cache, jobs, broadcast). Capturar ator e valores na operação, não depender de autenticação futura no worker. Operações em massa não disparam observers: o caminho explícito deve preservar autorização, auditoria, invalidação e eventos, não apenas cache.

## 5. Migrations e dados

Colunas por capacidade, não pacote universal: `id` interno, `codigo` público UUIDv7 quando escolhido, `tenant_id` em tabelas por tenant, `ativo` só onde faça sentido, autores quando necessários, timestamps técnicos UTC e soft delete somente quando adequado. Colunas de controle ficam fora do fillable. UUID não substitui autorização.

Macro `colunasPadrao` pode padronizar capacidades selecionadas, mas não deve impor FK a users/tenants antes de essas tabelas existirem. Definir ordem de bootstrap e exceções para pivôs, logs, históricos, tabelas globais e financeiras. Macros usadas por migrations antigas devem preservar seu comportamento histórico, por versionamento ou definição estável.

Não aplicar `cascadeOnDelete` ao tenant universalmente; exclusão organizacional requer processo de retenção/recuperação e autorização. Restringir relações entre tenants na aplicação e, quando viável, por constraints compostas portáveis.

Dinheiro: representação exata com moeda ISO 4217 e escala definida. Inteiros em unidades mínimas são adequados a alguns domínios; decimal pode ser necessário para preço unitário/cálculo. Não usar float nem assumir duas casas para toda moeda. Definir arredondamento, limites e serialização segura para consumidores JavaScript. Não converter representação existente sem reconciliação.

JSON com schema de aplicação, limites e queries portáveis. Enums de domínio em string + enum PHP; evitar enum nativo sem alternativa testada. Migrations aplicadas não são editadas. Planejar reversibilidade: `down()` quando seguro, recuperação/roll-forward quando perda de dados não é reversível. Ter `down()` não prova restauração.

## 6. Models

Fillable explícito; não usar guarded vazio. Relações tipadas; casts explícitos. Ativar detecção de lazy loading e descarte silencioso fora de produção quando compatível com o projeto. Usar Resources como allowlist; hidden é defesa complementar.

Traits de identidade, tenancy e auditoria somente quando aplicáveis. UUID em `codigo` pode usar `HasUuids` com `uniqueIds()`; verificar comportamento da versão. Definir route key. Eager loading por caso de uso, com constantes como `RELACIONAMENTOS_LISTA/DETALHE` se úteis. Não exigir relações inexistentes.

## 7. Compatibilidade de bancos

Centralizar divergências reais; preferir recursos portáveis do query builder antes de criar SQL próprio. `CompatBanco` precisa de contratos, bindings seguros e testes, não apenas assinatura de métodos.

| Capacidade | Contrato necessário |
|---|---|
| Busca textual | Semântica de caixa, acentos, collation, escape de `%`/`_`, colunas permitidas e custo de índice. |
| Unique com soft delete | PostgreSQL/SQLite: parcial; SQL Server: filtrado; MySQL: estratégia de coluna gerada compatível com a versão. Definir NULL, collation e restauração em conflito. |
| JSON | Tipo de retorno, valores ausentes/null, caminhos validados e diferenças entre drivers. |
| Grandes conjuntos IN | Limite total de parâmetros, inclusive outros filtros. Vários OR IN na mesma query não reduzem o total. Consultas separadas exigem semântica correta de ordenação, paginação e consistência. |
| Datas/concatenação/NULL | Unidade e fuso, tratamento de NULL, coerção e ordem estável, com equivalência testada. |

Nenhum identificador SQL vindo do cliente é interpolado sem allowlist. Validação unique acompanha índice, usa tenant confiável e ignora ID obtido do registro autorizado. Constraint do banco é defesa contra concorrência; traduzir colisão esperada sem expor SQL. Reutilização de chave após soft delete depende do domínio, não é requisito universal.

## 8. Multi-tenancy opcional

Escolher estratégia consultivamente. Banco único com tenant_id é uma alternativa, não default universal. Em instalações dedicadas, definir contexto da instalação e controle administrativo sem presumir mesma base compartilhada.

No isolamento por linha: contexto `TenantAtual` resolvido por identidade/vínculo validado; scope de leitura e preenchimento de escrita falham sem tenant. Listar tabelas globais explicitamente; pivôs e relações também respeitam isolamento. Resolver contexto antes do route binding e de queries sensíveis.

Jobs recebem tenant e identificadores escalares; estabelecer contexto antes de buscar modelos, inclusive antes de qualquer restauração automática que faria queries. Limpar em finally; mesma regra em comandos, agendadores e workers persistentes. Tenant participa de cache, locks, idempotência, caminhos, eventos, logs e constraints relevantes.

Não confiar em tenant_id do payload. `exists`, relacionamentos, anexos e imports devem validar propriedade. Operações globais/entre tenants exigem caso de uso explícito, autorização e auditoria; nenhum bypass por comentário ou título SuperAdmin.

## 9. Rotas e HTTP

Default novo `/api/v1`; preservar contratos existentes. Usar parâmetro nomeado conforme o recurso, por exemplo `{produto:codigo}`, com argumento/controller e `$request->route('produto')` consistentes. Recursos estáticos como input-list devem ser registrados sem colisão com parâmetros. withTrashed somente nas ações autorizadas.

Capacidades possíveis: GET coleção/index; GET input-list; GET detalhe/show; POST store; PUT/PATCH update; PATCH ativar/inativar; DELETE destroy; PATCH restore; DELETE force. Não publicar todas automaticamente. Históricos, logs e eventos de gateway não recebem CRUD mutável genérico.

Status: 200 leitura/alteração e exclusão com envelope; 201 criação; 401 não autenticado; 403 negado; 404 ausente/ocultação conforme política; 409 conflito; 422 entrada inválida; 429 limite; 500 erro interno. 419 é convenção Laravel para falha CSRF/sessão onde aplicável. Se usar 204, não enviar envelope. Preservar semântica de PUT/PATCH e diferenças entre ausência e null.

`Route::crud` é extensão própria opcional: deve definir ações habilitadas, binding, middleware, nomes e permissões. Não confundir com comando nativo.

## 10. Listagens e visibilidade

Validar page >= 1, per_page default 15/máximo 100 ajustáveis, busca com limite de tamanho, filtros e ordenações por allowlist. Ordenação inclui desempate estável. Avaliar cursor pagination para grandes volumes. Query Builder da Spatie é opcional, com versão conferida.

Situação ativo/inativo/deletado depende de capacidade específica; título administrativo não concede visibilidade total automaticamente. input-list: default 50, máximo 200 ajustável, inclui apenas codigo e label e usa o envelope padrão. `incluir` não contorna tenant, autorização ou exclusão; limitar quantidade e permitir inativo apenas para vínculo autorizado. Definir filtro aplicado a show e coleções consistentemente.

## 11. Cache

Cache somente quando justificado. Chave inclui aplicação, ambiente, tenant/global, recurso, ação, parâmetros canônicos, idioma e escopo efetivo de visibilidade. Pode usar usuário e versão de autorização; admin/comum não basta para acesso granular.

TTL inicial de listas 600s ajustável; show sem cache por padrão. Tags só se o driver suportar. Invalidar após commit; declarar dependências de relações. Evitar que leitura concorrente republique dado antigo após flush: usar geração/versionamento de chave ou estratégia equivalente testada quando necessário.

Permissões: invalidação cobre vínculos, perfil/grupo, concessões, revogações e mudanças nas permissões. Adotar expiração de segurança/versionamento. Guardar dados mínimos antes do Resource, não JSON com URLs assinadas. Evitar serializar grafos Eloquent indiscriminadamente. Não armazenar segredos em cache compartilhado sem controles.

## 12. Resources

Allowlist de campos; codigo público quando adotado; relações via Resources e whenLoaded; nunca depender de lazy loading. Não expor IDs internos ou paths se o contrato os oculta. Isso não significa que uma URL S3 esconda seu próprio caminho: URL assinada contém informações de localização.

Instantes técnicos ISO 8601 UTC; datas civis Y-m-d sem conversão de fuso. Decimais como string. Dinheiro conforme moeda/escala/limites acordados; valores fora da precisão segura do cliente não são JSON number. Booleanos e enums brutos, label traduzido quando necessário. URLs privadas só depois de autorização do arquivo e do registro, inclusive em listas e relações aninhadas.

## 13. Respostas e erros

Contrato default: `success`, `message`, `data` em sucesso; `success`, `message`, `errors` e código estável de erro em falha; `meta.request_id`, e `meta.paginacao` quando paginado. input-list usa o mesmo contrato. `RespostaApi` é componente próprio opcional, sem duplicar wrapping de Resources. Documentar exemplos e status em OpenAPI.

Tratar exceções de API centralmente em bootstrap/app.php conforme versão; não converter indiscriminadamente páginas HTML, downloads, streams e webhooks que possuem contrato próprio. Exceções de domínio carregam código/chave sem detalhes HTTP quando possível; adaptador mapeia status.

Gerar request_id e retornar header; aceitar ID externo somente de origem confiável ou após validação de formato/tamanho, sem caracteres de controle. Correlacionar logs/jobs.

Debug restrito a desenvolvimento controlado. Homologação compartilhada e produção usam APP_DEBUG=false; nunca devolver SQL, credenciais ou trace ao cliente. Mascarar campos aninhados e também URLs/headers/mensagens; preferir allowlist de metadados.

Registro em banco é opcional: não tornar observabilidade dependente só do banco/fila que pode falhar. Log stderr independente como fallback, inclusive quando o enqueue falha; evitar recursão e tempestade de logs. Definir retenção, acesso, amostragem e alertas; 90 dias é proposta a avaliar, não obrigação universal.

## 14. Autorização

Modelo GrupoAcesso -> Perfil -> Permissao é opção. Confirmar cardinalidades, atribuição direta/em lote e limites de concessão. Permissões globais podem ser catálogo, mas vínculos/perfis respeitam tenant. Não impor esse modelo a projetos com autorização existente adequada.

Policy por capacidade, valida tenant e demais restrições. Sem Gate::before irrestrito. Autorizar antes de validação que revele dados sensíveis; usar FormRequest::authorize, middleware ou Gate conforme fluxo. Se controller usar `$this->authorize`, garantir trait AuthorizesRequests; não supor sua presença no scaffold.

Sincronização de permissões por manifesto de capacidades expostas, não por todo model. Criar faltantes e marcar obsoletas sem apagar/grant automático. Comando idempotente e explícito, não efeito colateral de geração. Cache com revogação testada. Garantir que administrador não conceda poderes superiores à sua autoridade.

## 15. Tempo real opcional

Reverb/Echo quando selecionados. Canais privados por tenant/recurso/registro com autorização equivalente à API. Reavaliar revogação e ciclo de conexão; autorização de inscrição não é garantia eterna. IDs mínimos também podem revelar existência: canais de coleção respeitam visibilidade granular ou usam sinal genérico de invalidação.

Eventos depois do commit, por fila; payload mínimo sem dados sensíveis/URLs. Front recarrega via API. Deduplicar eventos de massa; considerar ordem/versão e reconnect. `ShouldBroadcast` e `ShouldDispatchAfterCommit` conforme versão. Evento websocket não substitui entrega durável de processo crítico.

## 16. Arquivos opcionais

Coleções configuram visibilidade, MIME, tamanho, variações e regras de acesso. Privado por padrão; publicação explícita. Caminhos imutáveis com identificadores não adivinháveis não constituem autorização. Não justificar publicação de rascunho apenas por ULID.

Se S3 escolhido: separar ambientes e acesso público/privado; bucket de conteúdo público pode permanecer bloqueado ao público e ser servido via CloudFront OAC. Aplicar IAM mínimo, criptografia e retenção pertinentes. Persistir key/disco/metadados, não URL. Map polimórfico explícito para não acoplar registros ao nome PHP e não aceitar tipo arbitrário do cliente.

Validar MIME real, tamanho, extensão permitida e conteúdo perigoso conforme risco; remover EXIF, limitar dimensões/descompressão e sanitizar Content-Disposition. Quarentena/antimalware quando necessário. Em upload direto, autorizar antes e confirmar propriedade, tamanho e tipo depois. Não aceitar paths arbitrários do cliente.

Banco e S3 não compartilham transação: registrar estado pendente, finalizar de modo idempotente, compensar falhas e reconciliar órfãos. Capturar keys antes de excluir registros; remoção por job após commit respeita retenção. Soft delete não apaga automaticamente arquivos; force delete também deve respeitar políticas de retenção.

URL privada gerada após autorização, TTL exemplo 900s e cache máximo exemplo 600s, com expira_em real e margem mínima; considerar expiração da credencial de assinatura. Cache não dispensa autorização. Revogar permissão não revoga necessariamente URL já emitida; definir necessidade de proxy/TTL menor ou mecanismo de revogação. URLs públicas imutáveis via CDN; retirar objeto e invalidar CDN quando exigido, sem prometer apagar cópias baixadas.

Resource retorna codigo/nome/mime/tamanho/visibilidade/url/expira_em/variacoes conforme contrato, sem campo de key interno. Storage::fake cobre unidade/feature; não comprova IAM, OAC ou expiração real. Integração dedicada deve validar esses limites quando requerida.

## 17. Internacionalização

Idiomas por projeto, pt_BR default proposto; en_US e es_PY opcionais, es_AR quando escopo exigir. Mapear Accept-Language BCP 47 para locales internos, respeitando pesos/fallback e allowlist. Preferência do usuário/tenant tem precedência documentada. Limpar idioma em workers.

Mensagens, validação, atributos, enums e permissões traduzidos; códigos de erro estáveis independem de tradução. Testar paridade dos idiomas selecionados. Não criar traduções fictícias para marcar checklist.

## 18. Planos e assinaturas opcionais

Se selecionado, definir titular, planos, versões de preço, moeda, periodicidade, trial, carência, limites e direitos. Assinatura do tenant é opção de SaaS, não regra universal. Models candidatos Plano/Assinatura/Cobranca/EventoGateway com histórico; eventos brutos têm acesso restrito e retenção explícita.

Máquina de estados e transições aprovadas: trial/ativa/inadimplente/suspensa/cancelada e demais estados necessários; definir reativação, pagamento tardio, estorno, cancelamento e ordem de eventos. Leitura durante suspensão e acesso para regularização dependem de decisão comercial. Não impor pro rata ou cinco dias de carência sem aprovação.

Limites verificados e consumidos atomicamente, inclusive concorrência. Versionar condições contratadas para não alterar contratos existentes ao editar plano. Upgrade/downgrade, corte, excesso de uso e fuso de cobrança devem ser definidos. Rotas públicas de planos expõem apenas publicados; gestão exige autorização específica.

## 19. Efí e integrações opcionais

Escolher capacidades verificadas (Pix, boleto, cartão, recorrência, Pix Automático), ambientes e contratos atuais do provedor. Não presumir equivalência. `PaymentGatewayContract` recebe DTOs/valores, não Request. Um adaptador HTTP de webhook fora de Services converte headers/body em envelope/DTO para validação técnica e processamento. Não impor um contrato único gigante a provedores com capacidades diferentes.

Configurar um conjunto de credenciais por ambiente por padrão; ler env apenas em config. Resolver configuração real, sem referenciar `services.efi.ativo` inexistente. Impedir mistura de ambiente. Certificados fora do Git/imagem, via secrets/cofre; exemplos sem valores. Token cacheado com margem, lock e namespace que distingue ambiente/conta/credencial sem revelar segredo.

Webhooks seguem autenticação exigida pelo produto/provedor: mTLS quando aplicável; segredo em URL sozinho não é prova suficiente e pode vazar em logs. Validar proxy e origem conforme documentação atual. Rotas fora do Sanctum apenas por contrato, sem dispensar autenticação do provedor.

Persistir recebimento durável com chave única por gateway/conta/ambiente/evento adequado; responder sucesso só após aceitação durável. Eventos duplicados já aceitos podem receber sucesso. Enfileirar após commit e prever recuperação se fila falhar. Reconsultar provedor quando o fluxo exigir, tratar reordenação e não regredir estado confirmado.

Idempotência de saída por operação, tenant e conta; mesma chave/outro payload é conflito. Timeout após execução remota exige consulta/reconciliação, não cobrança cega repetida. Retentativas limitadas com backoff/jitter e alertas. Não manter transação de banco aberta durante chamada lenta ao PSP. Outbox/saga/compensação quando consistência exigir.

Reconciliação cobre perdidos, pendentes, resultado desconhecido e estados posteriores relevantes (estorno/devolução), conforme capacidades. Dados financeiros e comerciais preservados; não fornecer enquadramento fiscal automático.

## 20. Filas e agendamentos

Escolher driver; Redis/Horizon quando selecionado. Filas alta/default/broadcast/baixa são convenção ajustável. Jobs idempotentes com política de tentativas, timeout, backoff e tratamento de falha. Timeout do job/worker/Horizon deve ser coerente e menor que retry_after com margem; duração de encerramento superior ao trabalho em curso.

Jobs dependentes de gravação só após commit. `afterCommit` evita consumo prematuro, mas não fecha a janela banco confirmado/enqueue perdido; efeitos críticos precisam de recuperação durável. Passar tenant e identificadores; limpar contexto. Prever replay autorizado, dead letters/failed jobs e prevenção de duplicação.

Scheduler conforme necessidade: reconciliação, estados de assinatura, pruning e métricas. Definir frequência/fuso por regra; atualização diária não basta para validade que expira em instante exato, que deve ser conferida na operação. onOneServer/withoutOverlapping com backend de lock compartilhado e expiração adequada. Evitar starvation de filas baixas.

## 21. Segurança

Aplicar guardrails da skill. Sanctum SPA exige configuração stateful, cookies, CSRF, CORS com origens explícitas e topologia de domínio compatível; token exige abilities/expiração/revogação e policies. Abilities não substituem autorização de negócio. Login/logout, reset, verificação de e-mail e MFA conforme risco e requisitos.

Rate limit por identidade/tenant/IP conforme superfície, com limites diferenciados; valores 120/min API, 5/min login e 300/min webhook são propostas, não universais. Configurar proxies confiáveis sem aceitar headers arbitrários; hosts e origens permitidos. TLS e headers adequados; HSTS só com domínio preparado.

Política de senha aprovada, hashing do framework e proteção contra enumeração. Definir comportamento quando serviço externo de verificação de senha indisponível. Criptografia de dados sensíveis com plano de gestão/rotação de chaves e impactos em busca/índices, não cast indiscriminado. Minimização, retenção, exportação e anonimização de dados conforme requisitos jurídicos validados, sem alegar conformidade apenas pelo uso de criptografia.

## 22. Auditoria

Último autor em colunas não substitui histórico. Registrar operação, entidade/codigo, tenant, ator/origem, request_id e diff minimizado. Auditoria crítica na mesma transação; publicação externa após commit com mecanismo durável quando requerido. Mascarar dados antes de persistir. Definir acesso, retenção e resistência a adulteração; restringir exclusão.

Jobs/comandos registram origem e ator iniciador quando conhecido, sem falsificar usuário autenticado. Bulk update, delete, restore e alteração de permissões precisam de cobertura explícita. spatie/laravel-activitylog é alternativa a validar, não garantia automática desses contratos.

## 23. Datas, documentos e números

Instantes técnicos em UTC; datas civis não recebem conversão de fuso. Rotinas por dia comercial usam fuso do tenant/regra, com comportamento de mudança de horário definido. Dinheiro exato conforme §5, sem coerção float em intermediários.

CPF e CEP brasileiros preservam zeros; CNPJ aceita formatos numérico e alfanumérico, normaliza letras conforme regra oficial e valida dígitos verificadores apropriados. Nunca remover letras de CNPJ. Telefones consideram país e representação internacional; documentos estrangeiros têm normalizador/validador por jurisdição, não regex brasileira universal.

## 24. Helpers e componentes internos

Preferir classes pequenas e coesas, funções puras ou serviços injetáveis quando há dependência. Traits somente para composição realmente apropriada. Não criar helper estático para esconder dependência externa ou contexto global. Comandos são adaptadores operacionais, não substituem serviço de domínio testável.

Antes de depender de componentes internos, localizar implementação, versão, contratos, consumers e testes. Se ausentes, declarar e implementar somente o necessário ao escopo. Esta skill não distribui CompatBanco, RespostaApi, TenantAtual, bases PHP ou gerador.

## 25. Factories e seeders

Factories para entidades relevantes aos testes; estados inativo/deletado só quando suportados. Separar catálogo obrigatório, bootstrap administrativo e demonstração. Seeders idempotentes por ambiente e tenant, sem criar contas de teste em produção. Credenciais de bootstrap entregues por canal seguro e não publicadas.

Não conceder todas as permissões, inclusive globais, a perfil de tenant. Ordem de seed deve respeitar dependências reais de tabelas/vínculos, não lista fixa universal. Não chamar sincronizações mutantes como efeito inesperado de geração.

## 26. Ambientes, containers e deploy

Escolher topologia conforme projeto. Desenvolvimento pode ser nativo ou containerizado; não exigir Redis/Horizon nativos em Windows. Homologação compartilhada/produção com debug false, segredos e dados separados. Não alterar hosting/gates existentes implicitamente.

Docker quando adotado: Dockerfile multi-stage, runtime sem dependências dev/segredos, usuário não-root com permissões explícitas, extensões para drivers suportados, imagem/versionamento rastreáveis, scan conforme política e atualização de segurança. Tags parcialmente versionadas são mutáveis; usar digest quando reprodutibilidade exigir. Separar estágio de teste.

Compose de homologação e produção com nomes próprios é opção recomendada; arquivos base/overrides/desenvolvimento são permitidos com invocação inequívoca documentada. Não exigir exatamente dois arquivos nem proibir buildx/CI por estilo. Só criar serviço frontend quando houver frontend no escopo.

Serviços necessários: proxy/web, app, workers, scheduler, websocket quando selecionado e dependências locais quando não gerenciadas. Expor somente portas necessárias. Logs stderr com rotação, limites de recursos, redes restritas e encerramento gracioso. Cache e filas no Redis precisam de política de persistência/eviction que não descarte jobs; considerar separação de instâncias.

Healthchecks locais sem ciclos: app/FPM não depende de web para o teste que condiciona a subida de web. Separar liveness/readiness e verificar workers/scheduler. depends_on não substitui retentativas e reconexão. Entrypoint deve encaminhar sinais (exec) e falhar visivelmente; caches de configuração gerados com variáveis de runtime, sem publicar segredos na imagem. Migrations não no entrypoint de cada réplica.

Deploy explícito: conferir autorização, destino e revisão; validar artefatos/configuração sem imprimir segredos; backup imediato quando exigido, promover imagem por digest, executar migrations compatíveis uma vez, sincronizar catálogo se previsto, iniciar/recarregar processos e verificar saúde. Usar `--env-file` para interpolação Compose quando necessário: `env_file` do serviço não equivale ao arquivo de interpolação. Comandos exatos dependem dos serviços existentes; não chamar `up banco` com banco gerenciado.

Expand/contract para convivência de versões. Rollback de imagem não desfaz dados: definir reversão/roll-forward, restauração e compatibilidade. Backup deve ter restauração ensaiada e RPO/RTO definidos. CI/CD é preferível quando aprovado. Documentar roteiro operacional exato por rotina criada, com responsável, comandos verificados, recusas, conferência e registro.

## 27. Testes e verificações

Lint/análise proporcionais a código alterado, testes que provem comportamento e riscos. Não executar suítes por alteração apenas documental. Respeitar gates locais; testes de desenvolvimento não se tornam gate de homologação automaticamente. Produção requer certificação e autorização próprias.

Cobrir sucesso, entrada inválida, acesso negado, ausência de autenticação, tenant cruzado/ausente, relações cruzadas, visibilidade e revogação. Exercitar concorrência, idempotência, rollback, efeitos após commit e falhas externas nos fluxos relevantes. Testes de cache/filas não podem depender apenas de fake para afirmar integração real.

Testes arquiteturais distinguem concretos, bases, interfaces, adapters HTTP, controllers não CRUD e módulos opcionais; evitar regras impossíveis sobre toda pasta. Nenhum teste exige quantidade fixa de artefatos por model. Medida de cobertura (exemplo 80% nas camadas críticas) é objetivo auxiliar, não prova de segurança.

Matriz dos drivers declarados e banco operacional; SQL/constraints/locking exigem evidência nesse banco. Registrar drivers/integrações não testados. Em adequação, adicionar caracterização proporcional e não substituir suíte madura por preferência de framework.

## 28. Qualidade e documentação

Pint, análise estática e testes compatíveis com o projeto; verificar versões e scripts antes de executar. OpenAPI com exemplos de sucesso/erro/paginação, autenticação, filtros e idempotência. Proteger documentação sensível. Commits em convenção/idioma do projeto. Atualizar versão desta spec quando seus contratos mudarem.

## 29. Geração de código opcional

`make:crud` não é comando nativo nem distribuído aqui. Se autorizado implementá-lo: modo dry-run, nomes/paths validados, ações explícitas, stubs versionados, recusa de overwrite por padrão, execução repetível sem duplicar registros e resumo do diff. Parser não executa conteúdo dos argumentos como código/comando.

Gerar apenas capacidades selecionadas; registrar providers/bindings/rotas sem editar cegamente código existente. Não executar migrations, seeders ou sincronização de permissões automaticamente. Testar colisões, diretórios existentes, geração repetida, rotas, autorização e ausência de efeitos externos. Não gerar boilerplate obrigatório para históricos imutáveis.

## 30. Checklists de aceite

Novo: decisões resolvidas; núcleo e opcionais identificados; fatia funcional; identidade/contexto; autorização; contratos HTTP; persistência e índices; erros seguros; verificações pertinentes; operação documentada; pendências explícitas.

Existente: inventário com evidências; classificação de divergências; contratos preservados; caracterização quando necessária; plano incremental; migrations aditivas/backfill seguro; reconciliação; verificações; exceções registradas; nenhum deploy implícito.

Nova entidade: confirmar se é CRUD; relações e tenant; permissões efetivas; filtros/ordenação; unicidade/restore; concorrência; arquivos/eventos quando aplicáveis; testes proporcionais. Não aplicar checklist de billing ou quatro bancos a perfil que não os selecionou.

## 31. Histórico e decisões por projeto

1.0.0 (2026-09-27): proposta inicial. 1.1.0 (2026-09-27): containers, arquivos/CDN e compatibilidade SQL Server ampliados. 1.2.0 (2026-10-29): consolidação revisada; fluxo consultivo, perfis opcionais, adequação incremental, guardrails; correção de auditoria/commit, bypass de tenant, CNPJ, parâmetros SQL, fronteiras HTTP, healthchecks, geração sem mutação, dinheiro/concorrência, dados/backup, contratos e validações proporcionais.

Decisões antes abertas (grupo/perfil, Sanctum, métodos de cobrança, DTOs, banco, imagens privadas) passam a ser escolhas contextuais registradas por projeto. `destroy` é default novo; contratos existentes mantidos. Configuração de integração por ambiente é default proposto. Não converter escolhas em obrigação universal.

## Fontes técnicas para manutenção

Consultar a documentação da versão instalada ao implementar; links são referências, não prova de compatibilidade de todos os pacotes.

- Laravel 12 Eloquent (events, observers, scopes): https://laravel.com/docs/12.x/eloquent
- Laravel 12 filas (commit, timeout, retry_after): https://laravel.com/docs/12.x/queues
- Laravel 12 autorização: https://laravel.com/docs/12.x/authorization
- Laravel 12 Sanctum: https://laravel.com/docs/12.x/sanctum
- Microsoft, limites SQL Server: https://learn.microsoft.com/en-us/sql/sql-server/maximum-capacity-specifications-for-sql-server
- Receita Federal, CNPJ alfanumérico: https://www.gov.br/receitafederal/pt-br/acesso-a-informacao/acoes-e-programas/programas-e-atividades/cnpj-alfanumerico
- Efí, webhooks Pix: https://dev.efipay.com.br/docs/api-pix/webhooks/
