# Adequação de projeto existente

## Inventário antes de alterar

Inspecionar instruções/ADRs, manifesto/lockfiles, estrutura real, rotas, consumers, autenticação, policies, tenancy, migrations, services/actions, repositories, jobs, arquivos, testes e pipeline conforme escopo. Consultar histórico Git e revisão implantada apenas quando relevante e autorizado. Não ler `.env` sem autorização. Respeitar alterações pendentes e arquivos reservados.

Construir matriz por requisito: atendido, parcialmente atendido, ausente, não aplicável ou conflitante; indicar arquivo/evidência, impacto e ação recomendada. Busca sem resultado não comprova ausência; registrar limite de cobertura.

Classificar cada mudança como REUTILIZAR, ESTENDER, REFATORAR, CRIAR ou INVESTIGAR MAIS. CRIAR exige explicar por que alternativas existentes não atendem. Não extrair abstração apenas por semelhança superficial.

## Consulta e plano

Reutilizar decisões registradas. Perguntar apenas o que falta ou conflita, com recomendação e consequências. Não mover backend para `backend/`, substituir PHPUnit por Pest, converter IDs/dinheiro, habilitar multi-tenancy, adicionar gateway ou atualizar versões sem escopo aprovado.

Planejar por fluxo ou módulo, preservando API, dados e integração. Adicionar testes de caracterização quando necessário para documentar comportamento real antes de refatorar. Distinguir comportamento contratado de defeito comprovado; não perpetuar falha de segurança apenas para preservar teste.

## Execução incremental

- Mudanças de schema: expandir primeiro, preencher dados em lotes reiniciáveis, conferir consistência, migrar consumidores e só depois remover o legado com autorização. Não editar migrations já aplicadas.
- Identificadores públicos: aliases/adaptação ou versionamento quando necessário; sem troca silenciosa.
- Tenancy: mapear propriedade de cada registro e relações antes de preencher tenant_id; não atribuir dados ambíguos ao primeiro tenant.
- Dinheiro: preservar precisão, moeda, arredondamento e histórico; reconciliar antes/depois. Nenhuma conversão global automática.
- Autorização: preservar slugs contratados; mapear concessões, revogações e poderes administrativos; não conceder tudo ao adaptar perfis.
- Abstrações: admitir coexistência temporária de padrões; não refatorar todo o sistema para completar uma pequena tarefa.
- Infraestrutura: preservar destino e gates vigentes. Melhorias propostas não autorizam migração de hosting nem deploy.

## Conclusão

Registrar o que mudou, o que ficou fora, evidências, contratos preservados, riscos e cobertura adiada. Atualizar apenas documentos/índices com mudança comprovada. Se somente diagnóstico foi solicitado, não alterar arquivos, não instalar pacotes e não executar comandos mutantes.
