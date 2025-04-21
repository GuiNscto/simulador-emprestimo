<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SimuladorController extends Controller
{
    private function loadJson($arquivo){
        $path = storage_path("app/json/{$arquivo}");
        if (!file_exists($path)) {
        abort(500, "Arquivo JSON não encontrado: {$arquivo}");
    }
    $conteudo = file_get_contents($path);
    return json_decode($conteudo, true);
    }

    public function instituicoes(){
        $instituicoes = $this->loadJson('instituicoes.json');

        $resposta = [];
        foreach ($instituicoes as $instituicao) {
            $resposta[$instituicao['id']] = $instituicao['nome'];
        }

        return response()->json($resposta);
    }

    public function convenios()
    {
        $convenios = $this->loadJson('convenios.json');

        $resposta = [];
        foreach ($convenios as $convenio) {
            $resposta[$convenio['id']] = $convenio['nome'];
        }

        return response()->json($resposta);
    }

    public function simular(Request $request)
    {
        $dados = $request->validate([
            'valor_emprestimo' => 'required|numeric',
            'instituicoes' => 'array',
            'convenios' => 'array',
            'parcela' => 'numeric'
        ]);

        $taxas = $this->loadJson('taxas.json');
        $instituicoes = $this->loadJson('instituicoes.json');
        $convenios = $this->loadJson('convenios.json');

        $resultado = [];

        foreach ($taxas as $taxa) {
            if (isset($dados['instituicoes']) && !in_array($taxa['instituicao_id'], $dados['instituicoes'])) {
                continue;
            }
            if (isset($dados['convenios']) && !in_array($taxa['convenio_id'], $dados['convenios'])) {
                continue;
            }
            if (isset($dados['parcela']) && $taxa['parcelas'] != $dados['parcela']) {
                continue;
            }

            $valor_parcela = $dados['valor_emprestimo'] * $taxa['coeficiente'];
            $nome_instituicao = collect($instituicoes)->firstWhere('id', $taxa['instituicao_id'])['nome'] ?? '';
            $nome_convenio = collect($convenios)->firstWhere('id', $taxa['convenio_id'])['nome'] ?? '';

            $resultado[$nome_instituicao][] = [
                'taxa' => $taxa['taxaJuros'],
                'parcelas' => $taxa['parcelas'],
                'valor_parcela' => round($valor_parcela, 2),
                'convenio' => $nome_convenio
            ];
        }

        return response()->json($resultado);
    }
}
