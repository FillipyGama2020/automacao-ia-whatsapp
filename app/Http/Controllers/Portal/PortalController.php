<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function home(Request $request)
    {
        $cliente = $request->user()->cliente()->with('plano.recursos')->first();

        return view('portal.home', compact('cliente'));
    }
}
