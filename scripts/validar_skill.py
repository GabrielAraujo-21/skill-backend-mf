#!/usr/bin/env python3
"""Valida estrutura e links locais da skill, sem rede ou mutações."""

import re
import sys
from pathlib import Path


def validar(raiz):
    erros = []
    obrigatorios = [
        "SKILL.md",
        "agents/openai.yaml",
        "references/especificacao-1.2.0.md",
        "references/seguranca-consulta.md",
        "references/projeto-novo.md",
        "references/projeto-existente.md",
        "references/validacao.md",
    ]
    for nome in obrigatorios:
        if not (raiz / nome).is_file():
            erros.append(f"Arquivo ausente: {nome}")
    entrada = raiz / "SKILL.md"
    if entrada.is_file():
        texto = entrada.read_text(encoding="utf-8")
        cabecalho = re.match(r"\A---\n(.*?)\n---\n", texto, re.DOTALL)
        if not cabecalho:
            erros.append("Frontmatter ausente ou delimitadores inválidos")
        else:
            for campo in ("name", "description"):
                if not re.search(rf"^{campo}: \S.+$", cabecalho[1], re.MULTILINE):
                    erros.append(f"Metadado obrigatório inválido: {campo}")
            if not re.search(r"^name: skill-backend-mf$", cabecalho[1], re.MULTILINE):
                erros.append("Nome da skill divergente")
            if 'version: "1.2.0"' not in cabecalho[1]:
                erros.append("Versão do pacote divergente")
    for arquivo in raiz.rglob("*.md"):
        if ".git" in arquivo.parts:
            continue
        texto = arquivo.read_text(encoding="utf-8")
        for link in re.findall(r"\[[^\]]*\]\(([^)]+)\)", texto):
            if re.match(r"^[a-zA-Z][a-zA-Z0-9+.-]*:", link) or link.startswith("#"):
                continue
            caminho = (arquivo.parent / link.split("#", 1)[0]).resolve()
            if not caminho.is_relative_to(raiz) or not caminho.is_file():
                erros.append(
                    f"Referência local inválida em {arquivo.relative_to(raiz)}: {link}"
                )
    especificacao = raiz / "references/especificacao-1.2.0.md"
    if especificacao.is_file():
        texto = especificacao.read_text(encoding="utf-8")
        if "**Versão:** 1.2.0" not in texto:
            erros.append("Versão da especificação divergente")
        secoes = [
            int(numero) for numero in re.findall(r"^## (\d+)\.", texto, re.MULTILINE)
        ]
        if secoes != list(range(1, 32)):
            erros.append("Especificação deve conter as seções 1 a 31 em ordem")
    return erros


if __name__ == "__main__":
    raiz = (
        Path(sys.argv[1]).resolve()
        if len(sys.argv) > 1
        else Path(__file__).resolve().parents[1]
    )
    erros = validar(raiz)
    for erro in erros:
        print(f"ERRO: {erro}")
    print(f"Validação estrutural: {'FALHOU' if erros else 'OK'}")
    raise SystemExit(1 if erros else 0)
