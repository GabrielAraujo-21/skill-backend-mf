# Projeto novo

1. Confirmar raiz desejada e ausência de projeto existente; ler regras locais sem acessar segredos. `backend/` é default, não imposição.
2. Resolver decisões consultivas para a primeira etapa: escopo, tenancy, banco, clientes e autenticação. Não gerar schema dependente de decisão pendente.
3. Registrar perfil: núcleo obrigatório e módulos opcionais selecionados. Fixar versões compatíveis e política de atualizações; Laravel 12/PHP 8.4 são referências desta versão.
4. Procurar pacote interno/starter existente e verificar sua versão, contratos e testes. Se não existir, indicar componentes que precisarão ser implementados; não afirmar que foram copiados de um modelo inexistente.
5. Apresentar plano incremental com mudanças, verificações e riscos. Usar autorização já existente quando suficiente.
6. Construir primeiro uma fatia funcional representativa: rota, request, autorização, caso de uso, persistência e resource. Implementar apenas as abstrações justificadas por essa fatia. Controllers não são obrigatoriamente CRUD.
7. Implementar autenticação/contexto antes de expor recursos protegidos. Quando multi-tenant, testar leitura e escrita sem contexto e entre tenants, inclusive relacionamentos.
8. Acrescentar módulos selecionados em etapas. Cada módulo recebe requisitos, critérios de aceite e documentação operacional proporcional; seguir categorias documentais obrigatórias do projeto.
9. Validar mudanças conforme `validacao.md`; criar CI compatível com drivers e gates aprovados. Preparar exemplos sem segredos e instruções executáveis verificadas.
10. Entregar decisões, arquivos, evidências, limitações e estado operacional. Deploy não está implícito em criação de projeto.

Aceite: aplicação inicia, fatia autorizada funciona, acessos indevidos falham, contratos estão documentados e verificações selecionadas têm evidências. Um pacote de instruções não prova que qualquer desses critérios foi atendido por um backend.
