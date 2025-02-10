<?php

namespace App\Http\Controllers;

use App\Console\Commands\AtualizarCotacaoDolar;
use App\Models\Configuracao;
use App\Models\PrecoModelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PrecoModeloController extends Controller
{
    public function index()
    {
        $precos = PrecoModelo::orderBy('modelo')->get();
        $cotacaoDolar = PrecoModelo::cotacaoDolar();
        $cotacaoAtualizadaEm = Configuracao::get('cotacao_dolar_atualizado_em');

        return view('precos-modelo.index', compact('precos', 'cotacaoDolar', 'cotacaoAtualizadaEm'));
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        PrecoModelo::create($data);

        return redirect()->route('precos-modelo.index')->with('status', 'Modelo cadastrado com sucesso.');
    }

    public function update(Request $request, PrecoModelo $precoModelo)
    {
        $data = $this->validar($request, $precoModelo);

        $precoModelo->update($data);

        return redirect()->route('precos-modelo.index')->with('status', 'Preço atualizado com sucesso.');
    }

    public function destroy(PrecoModelo $precoModelo)
    {
        $precoModelo->delete();

        return redirect()->route('precos-modelo.index')->with('status', 'Modelo removido.');
    }

    public function atualizarCotacao()
    {
        $codigo = Artisan::call(AtualizarCotacaoDolar::class);

        if ($codigo !== 0) {
            return redirect()->route('precos-modelo.index')
                ->with('error', 'Não foi possível atualizar a cotação do dólar agora. Tente novamente mais tarde.');
        }

        return redirect()->route('precos-modelo.index')->with('status', 'Cotação do dólar atualizada.');
    }

    private function validar(Request $request, ?PrecoModelo $precoModelo = null): array
    {
        return $request->validate([
            'modelo' => 'required|string|max:100|unique:precos_modelo,modelo,'.($precoModelo?->id),
            'preco_prompt_usd_por_mil' => 'required|numeric|min:0',
            'preco_resposta_usd_por_mil' => 'required|numeric|min:0',
        ]);
    }
}
