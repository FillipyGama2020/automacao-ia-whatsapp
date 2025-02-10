<?php

namespace App\Http\Controllers;

use App\Models\Plano;

class LandingController extends Controller
{
    public function index()
    {
        $planos = Plano::where('ativo', true)
            ->orderBy('ordem')
            ->with('recursos')
            ->get();

        return view('landing.index', compact('planos'));
    }
}
