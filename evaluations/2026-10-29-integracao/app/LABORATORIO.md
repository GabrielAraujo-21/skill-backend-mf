# Laboratório Laravel

Aplicação descartável Laravel 12/Sanctum, SQLite, PHPUnit. Não usar em produção. Credenciais, chave criptográfica e dados são sintéticos públicos. Configuração fixa no AppServiceProvider impede conexão a bancos operacionais; nenhum .env é necessário.

## Executar

Na raiz deste diretório, com PHP 8.4 e Composer:

```powershell
composer install --no-interaction --no-scripts
php artisan package:discover
php scripts/preparar-banco.php
php artisan serve --host=127.0.0.1 --port=18765
```

O script de banco só aceita database/laboratorio.sqlite deste fixture e apaga somente suas tabelas antes de semear dados sintéticos. Interromper o servidor antes de resetar. No navegador, o frontend em 127.0.0.1:15173 usa proxy /api e /sanctum para esta porta, preservando mesma origem e cookies.

Usuários a@example.test, b@example.test e leitor@example.test; senha pública de teste Teste123456!. Usuários a/b pertencem a A/B e podem editar. Leitor pertence a A sem edição. Não existe bypass de SuperAdmin.

## Validar

```powershell
php vendor/bin/pint --test
php vendor/bin/phpunit --log-junit evidencias/phpunit.xml
```

PHPUnit recria schema exclusivamente no SQLite :memory:. A suíte exercita HTTP/kernel/Eloquent/Policies/FormRequests, mas a proteção CSRF é desabilitada pelo framework em unit testing; o navegador deve testar419 com sessão real separadamente. Fonte: https://laravel.com/docs/12.x/sanctum.

## Limites

Não certifica PostgreSQL/MySQL, produção, PSPs, filas persistentes ou múltiplas instâncias. Não distribuir vendor, banco, cache gerado nem logs. O scaffold original possui comandos setup/dev que não são o roteiro deste laboratório; use somente comandos acima.

## Isolamento das quotas entre casos do navegador

Antes de cada caso, executar no CLI do backend:

```powershell
php scripts/limpar-cache.php
```

O script valida que cache.default é file e o path resolve exclusivamente para storage/framework/cache/data deste fixture, recusa links e caminhos externos e limpa somente esse store. O servidor usa file para que throttle10,1 sobreviva entre requests; PHPUnit usa array. Não existe endpoint HTTP de reset. Testes do navegador devem receber LAB_BACKEND_DIR apontando explicitamente para esta pasta e aguardar código0 antes de iniciar o caso. Não executar limpeza simultaneamente a um caso de throttle.
