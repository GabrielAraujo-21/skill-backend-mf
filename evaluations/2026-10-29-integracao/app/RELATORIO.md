# Avaliação Laravel completa — 29/10/2026

## Fontes e método

Skill backend baseline 732d402c94a0a88b36d548efd81da323d0af3320, SKILL.md, referências seguranca-consulta, projeto-novo, projeto-existente e especificação. Um agente construiu a aplicação e testes com contexto herdado e contrato fornecido pelo coordenador; não é ensaio cego. Fontes técnicas: https://laravel.com/docs/12.x/sanctum e código instalado do framework. Classificação CRIAR domínio clientes e REUTILIZAR serviços do framework.

Ambiente real: PHP8.4.12, Laravel12.70.2, Sanctum4.3.3, PHPUnit11.5.57, SQLite. Composer lock preserva versões instaladas. A opção de tenancy compartilhada por linha e banco SQLite é exclusiva do laboratório. API explicitamente usa grupo web, sessão/cookie e proteção CSRF, com auth:sanctum; não é autenticação fake no browser.

## Resultados

`evidencias/phpunit-final.txt` e XML: 15 testes/49 asserções passaram. Destes, 13 testes de domínio HTTP/banco em ClientesTest e 2 testes básicos do scaffold. Cobertura:401 anônimo,422 login inválido/validação por campo, login/logout pelo kernel, listagem tenant, contexto ausente403, SuperAdmin sem edição403, CNPJ numérico com zeros/alfanumérico, payload tenant rejeitado, update/delete entre tenants403, CRUD autorizado e contrato de rota legado. PHPUnit usa SQLite :memory:; não toca banco do servidor do navegador.

Pint executado com correções de estilo e retestado: `evidencias/pint-final.txt` passed. Script de reset executado: `evidencias/preparar-banco.txt`. Banco de navegador é exclusivamente database/laboratorio.sqlite, com guarda de driver e caminho. Credenciais/chave do fixture são públicas sintéticas.

## Falhas e correções observadas

- Rede sandbox recusou Packagist; instalação autorizada fora do bloqueio conseguiu concluir.
- Launcher Composer Windows consumiu caret de ^4.0 e selecionou Sanctum4.0.0 incompatível com Laravel12. Usado4.* e lock fixou4.3.3.
- Primeira suíte foi iniciada antes da instalação/descoberta Sanctum terminar:12 falhas `Auth guard [sanctum] is not defined` em15 testes. Log inicial preservado. Após dependência compatível e package:discover, todos passaram. Trata-se de ordenação da montagem do laboratório, não defeito demonstrado nas instruções da skill.
- Ensaio legado deliberado removeu normalização do CNPJ da action, reproduziu falha e restaurou alteração pontual. `evidencias/legado.json` informa antes1/depois0 e hashes de rotas iguais. `SalvarCliente-legado.txt` é fonte anterior sintética. Não atribuir esse defeito injetado à skill ou a projeto real.
- A alteração temporária do ensaio legado coincidiu potencialmente com início do navegador em outra frente; o coordenador foi informado e repetirá o browser com fonte congelada. Não usar a primeira rodada browser como evidência final sem essa repetição.

## Melhorias sugeridas à skill

Não há contradição demonstrada na regra de arquitetura/segurança nesta amostra. Acrescentar ao protocolo de validação a distinção entre testes HTTP do Laravel e CSRF real: o framework desabilita proteção CSRF nos testes e sucesso do PHPUnit não prova cookies/XSRF reais. Após instalações com --no-scripts, verificar package discovery antes de concluir que uma integração está disponível. Essas melhorias são de evidência/reprodução, não instalação obrigatória em projetos consumidores.

## Reprodução e exclusões de publicação

Usar LABORATORIO.md. Publicar aplicação, composer.json/lock, scripts/preparar-banco.php, testes e evidências selecionadas. Excluir vendor, banco sqlite, caches de bootstrap, storage/framework e storage/logs gerados, .phpunit.result.cache e .pint.cache. Não publicar preparar.py (script transitório de autoria) nem scripts/avaliar-legado.py (mutação transitória de fonte, evidência já preservada). Não houve leitura/alteração manual de .env nem ERP. Guardrails instrucionais não provam sandbox de filesystem.

## Limites

Não cobre PostgreSQL/MySQL, produção, pagamentos, Redis, concorrência distribuída, recuperação de backup nem trabalho de worker persistente. Navegador é avaliado pelo coordenador, em evidência separada. Rotas legadas são sintéticas e o teste não certifica migração de aplicação real. Não declarar todos cenários consultivos anteriores reexecutados cegamente.

## Correção após ensaio no navegador: throttle persistente

O coordenador reproduziu no Chromium 12 logins incorretos consecutivos retornando422, sem429. A rota possuía throttle:10,1, mas o fixture usava cache array também no servidor real; o contador se perdia entre requests. Este é um defeito real de configuração da aplicação de teste, não falha de assert artificial.

Correção: AppServiceProvider usa cache file no runtime, com path/lock_path explicitamente dentro de storage/framework/cache/data do fixture, e mantém array somente no PHPUnit. scripts/limpar-cache.php valida o driver, o caminho real e descendentes; recusa links/junctions ou alvos externos antes de chamar cache:clear no store file. O script não reseta banco, sessão nem contratos.

Reteste local: limpeza local passou; Pint passou; PHPUnit15/49 passou novamente (`phpunit-cache-final.xml/txt`, `pint-cache-final.txt`, `limpar-cache.txt`). O reteste429 entre requisições reais pertence ao coordenador e deve ser confirmado no relatório do navegador, sem inferir sucesso do PHPUnit.

Recomendação específica à skill: ao validar controles stateful como limitação de tentativas, comprovar o contador entre requisições/processos reais e verificar backend de cache compatível. Array pode ser adequado para teste unitário isolado e não comprova limitação no servidor. Não impor Redis a todo projeto: o requisito é persistência/compartilhamento conforme topologia; file serve somente a este laboratório local de processo único.
