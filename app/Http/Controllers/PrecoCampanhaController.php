<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Models\PrecoCampanha;
use Illuminate\Http\Request;

class PrecoCampanhaController extends Controller
{
    public function index()
    {
        foreach (array_keys(MessageTemplate::categoriaLabels()) as $categoria) {
            PrecoCampanha::firstOrCreate(['categoria' => $categoria], ['preco_por_lead' => 0]);
        }

        $precos = PrecoCampanha::orderBy('categoria')->get();

        return view('precos-campanha.index', compact('precos'));
    }

    public function update(Request $request, PrecoCampanha $precoCampanha)
    {
        $data = $request->validate([
            'preco_por_lead' => 'required|numeric|min:0',
        ]);

        $precoCampanha->update($data);

        return redirect()->route('precos-campanha.index')->with('status', 'Preço atualizado com sucesso.');
    }
}
