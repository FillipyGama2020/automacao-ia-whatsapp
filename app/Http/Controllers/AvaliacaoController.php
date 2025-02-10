<?php

namespace App\Http\Controllers;

use App\Models\Conversa;
use Illuminate\Http\Request;

class AvaliacaoController extends Controller
{
    public function show(string $token)
    {
        $conversa = Conversa::with('cliente')->where('token_avaliacao', $token)->firstOrFail();

        return view('avaliacao.show', compact('conversa'));
    }

    public function store(Request $request, string $token)
    {
        $conversa = Conversa::where('token_avaliacao', $token)->firstOrFail();

        if ($conversa->avaliada_em) {
            return redirect()->route('avaliacao.show', $token);
        }

        $data = $request->validate([
            'avaliacao' => 'required|integer|min:1|max:5',
        ]);

        $conversa->update([
            'avaliacao' => $data['avaliacao'],
            'avaliada_em' => now(),
        ]);

        return redirect()->route('avaliacao.show', $token);
    }
}
