"""Testes de segurança e resultado do runner; não avaliam comportamento da LLM."""

import tempfile
import unittest
from pathlib import Path

from avaliar import falhas_execucao, validar_saida


class SaidaTest(unittest.TestCase):
    def test_aceita_filho_simples(self):
        with tempfile.TemporaryDirectory() as pasta:
            base = Path(pasta)
            self.assertEqual(validar_saida(base, "rodada-2"), base / "rodada-2")

    def test_recusa_caminhos(self):
        with tempfile.TemporaryDirectory() as pasta:
            for nome in [
                "",
                ".",
                "..",
                "../fora",
                "a/b",
                "a\\b",
                "/tmp/fora",
                "C:\\fora",
                "C:fora",
                "a/../b",
            ]:
                with self.subTest(nome=nome), self.assertRaises(ValueError):
                    validar_saida(Path(pasta), nome)


class ResultadosTest(unittest.TestCase):
    def item(self, antes=1, depois=0):
        return [
            {
                "id": "B07",
                "comandos_executados": [
                    {"fase": "antes", "codigo": antes},
                    {"fase": "depois", "codigo": depois},
                ],
            }
        ]

    def test_falha_inicial_e_sucesso_posterior(self):
        self.assertEqual(falhas_execucao(self.item()), [])

    def test_regressao_posterior_falha(self):
        self.assertTrue(falhas_execucao(self.item(depois=1)))

    def test_caracterizacao_sem_falha_reprova(self):
        self.assertTrue(falhas_execucao(self.item(antes=0)))

    def test_caracterizacao_ausente_reprova(self):
        self.assertTrue(falhas_execucao([{"id": "B08", "comandos_executados": []}]))


if __name__ == "__main__":
    unittest.main()
