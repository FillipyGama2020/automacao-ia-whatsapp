<?php

use App\Http\Controllers\Api\LeadIngestaoController;
use App\Http\Controllers\Api\MensagemIngestaoController;
use App\Http\Controllers\Api\MetaAccountUpdateController;
use App\Http\Controllers\Api\MetaMessageTemplateStatusController;
use App\Http\Controllers\Api\N8nContextoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.ingestao', 'throttle:120,1'])->group(function () {
    Route::post('/mensagens', [MensagemIngestaoController::class, 'store'])->name('api.mensagens.store');
    Route::post('/leads', [LeadIngestaoController::class, 'store'])->name('api.leads.store');
});

Route::middleware(['auth.n8n', 'throttle:120,1'])->group(function () {
    Route::get('/n8n/contexto', [N8nContextoController::class, 'show'])->name('api.n8n.contexto');
    Route::get('/n8n/contexto-conversa', [N8nContextoController::class, 'showPorConversa'])->name('api.n8n.contexto-conversa');
    Route::post('/n8n/agendar-resposta', [N8nContextoController::class, 'agendarResposta'])->name('api.n8n.agendar-resposta');
    Route::post('/n8n/marcar-resolvida', [N8nContextoController::class, 'marcarResolvida'])->name('api.n8n.marcar-resolvida');
    Route::post('/n8n/transferir-agente', [N8nContextoController::class, 'transferirAgente'])->name('api.n8n.transferir-agente');
    Route::get('/n8n/ultima-mensagem', [N8nContextoController::class, 'ultimaMensagem'])->name('api.n8n.ultima-mensagem');
    Route::post('/meta/account-update', [MetaAccountUpdateController::class, 'store'])->name('api.meta.account-update');
    Route::post('/meta/message-template-status', [MetaMessageTemplateStatusController::class, 'store'])->name('api.meta.message-template-status');
});
