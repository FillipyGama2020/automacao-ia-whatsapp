<?php

use App\Http\Controllers\AgenteController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ClienteFinanceiroController;
use App\Http\Controllers\ContatoBloqueadoController;
use App\Http\Controllers\ConversaController;
use App\Http\Controllers\CustoInfraestruturaController;
use App\Http\Controllers\FechamentoFinanceiroController;
use App\Http\Controllers\FinanceiroDashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LgpdController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\PrecoCampanhaController;
use App\Http\Controllers\Portal\PortalAgenteController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\PortalContatoBloqueadoController;
use App\Http\Controllers\Portal\PortalConversaController;
use App\Http\Controllers\Portal\PortalFinanceiroController;
use App\Http\Controllers\Portal\PortalLeadController;
use App\Http\Controllers\Portal\PortalRelatorioController;
use App\Http\Controllers\Portal\PortalSuporteController;
use App\Http\Controllers\Portal\PortalWhatsappController;
use App\Http\Controllers\RelatorioConsolidadoController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\SuporteController;
use App\Http\Controllers\PrecoModeloController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WhatsappIntegracaoController;
use App\Models\Cliente;
use Illuminate\Support\Facades\Route;

Route::domain('example.com')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('throttle:30,1')->group(function () {
    Route::get('/avaliar/{token}', [AvaliacaoController::class, 'show'])->name('avaliacao.show');
    Route::post('/avaliar/{token}', [AvaliacaoController::class, 'store'])->name('avaliacao.store');
});

Route::get('/termos-de-uso', fn () => view('legal.termos-uso'))->name('legal.termos-uso');
Route::get('/politica-de-privacidade', fn () => view('legal.politica-privacidade'))->name('legal.politica-privacidade');

Route::get('/dashboard', function () {
    $totalClientes = Cliente::count();
    $clientesAtivos = Cliente::where('status', 'ativo')->count();
    $clientesPausados = Cliente::where('status', 'pausado')->count();
    $clientesArquivados = Cliente::where('status', 'arquivado')->count();

    return view('dashboard', compact('totalClientes', 'clientesAtivos', 'clientesPausados', 'clientesArquivados'));
})->middleware(['auth', 'role:admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::patch('/clientes/{cliente}/status', [ClienteController::class, 'updateStatus'])->name('clientes.status');

    Route::get('/clientes/{cliente}/whatsapp', [WhatsappIntegracaoController::class, 'edit'])->name('clientes.whatsapp.edit');
    Route::put('/clientes/{cliente}/whatsapp', [WhatsappIntegracaoController::class, 'store'])->name('clientes.whatsapp.store');
    Route::put('/clientes/{cliente}/whatsapp/{integracao}', [WhatsappIntegracaoController::class, 'update'])->name('clientes.whatsapp.update');
    Route::post('/clientes/{cliente}/whatsapp/{integracao}/testar', [WhatsappIntegracaoController::class, 'testar'])->name('clientes.whatsapp.testar');
    Route::post('/clientes/{cliente}/whatsapp/{integracao}/enviar-teste', [WhatsappIntegracaoController::class, 'enviarMensagemTeste'])->name('clientes.whatsapp.enviar-teste');
    Route::post('/clientes/{cliente}/whatsapp/{integracao}/token', [WhatsappIntegracaoController::class, 'gerarToken'])->name('clientes.whatsapp.token');
    Route::post('/clientes/{cliente}/whatsapp/conectar-embedded', [WhatsappIntegracaoController::class, 'conectarEmbedded'])->name('clientes.whatsapp.conectar-embedded');
    Route::post('/clientes/{cliente}/whatsapp/iniciar-conexao', [WhatsappIntegracaoController::class, 'iniciarConexao'])->name('clientes.whatsapp.iniciar-conexao');
    Route::post('/clientes/{cliente}/whatsapp/{integracao}/desconectar', [WhatsappIntegracaoController::class, 'desconectar'])->name('clientes.whatsapp.desconectar');
    Route::patch('/clientes/{cliente}/whatsapp/{integracao}/modo-equipe', [WhatsappIntegracaoController::class, 'toggleModoEquipe'])->name('clientes.whatsapp.modo-equipe');
    Route::put('/clientes/{cliente}/whatsapp/{integracao}/prompt-classificacao', [WhatsappIntegracaoController::class, 'atualizarPromptClassificacao'])->name('clientes.whatsapp.prompt-classificacao');

    Route::resource('clientes.agentes', AgenteController::class)->except(['show']);
    Route::patch('/clientes/{cliente}/agentes/{agente}/toggle', [AgenteController::class, 'toggle'])->name('clientes.agentes.toggle');

    Route::get('/clientes/{cliente}/conversas', [ConversaController::class, 'index'])->name('clientes.conversas.index');
    Route::get('/clientes/{cliente}/conversas/exportar', [ConversaController::class, 'exportarCsv'])->name('clientes.conversas.exportar');
    Route::get('/clientes/{cliente}/conversas/{conversa}', [ConversaController::class, 'show'])->name('clientes.conversas.show');
    Route::post('/clientes/{cliente}/conversas/{conversa}/responder', [ConversaController::class, 'responder'])->name('clientes.conversas.responder');
    Route::post('/clientes/{cliente}/conversas/{conversa}/retomar-ia', [ConversaController::class, 'retomarIa'])->name('clientes.conversas.retomar-ia');

    Route::get('/clientes/{cliente}/contatos-bloqueados', [ContatoBloqueadoController::class, 'index'])->name('clientes.contatos-bloqueados.index');
    Route::get('/clientes/{cliente}/contatos-bloqueados/create', [ContatoBloqueadoController::class, 'create'])->name('clientes.contatos-bloqueados.create');
    Route::post('/clientes/{cliente}/contatos-bloqueados', [ContatoBloqueadoController::class, 'store'])->name('clientes.contatos-bloqueados.store');
    Route::get('/clientes/{cliente}/contatos-bloqueados/{contato}/edit', [ContatoBloqueadoController::class, 'edit'])->name('clientes.contatos-bloqueados.edit');
    Route::put('/clientes/{cliente}/contatos-bloqueados/{contato}', [ContatoBloqueadoController::class, 'update'])->name('clientes.contatos-bloqueados.update');
    Route::delete('/clientes/{cliente}/contatos-bloqueados/{contato}', [ContatoBloqueadoController::class, 'destroy'])->name('clientes.contatos-bloqueados.destroy');
    Route::post('/clientes/{cliente}/contatos-bloqueados/importar', [ContatoBloqueadoController::class, 'importar'])->name('clientes.contatos-bloqueados.importar');

    Route::get('/clientes/{cliente}/leads', [LeadController::class, 'index'])->name('clientes.leads.index');
    Route::get('/clientes/{cliente}/leads/kanban', [LeadController::class, 'kanban'])->name('clientes.leads.kanban');
    Route::patch('/clientes/{cliente}/leads/{lead}/status', [LeadController::class, 'atualizarStatus'])->name('clientes.leads.status');

    Route::get('/clientes/{cliente}/financeiro', [ClienteFinanceiroController::class, 'show'])->name('clientes.financeiro.show');

    Route::get('/clientes/{cliente}/relatorio', [RelatorioController::class, 'show'])->name('clientes.relatorio.show');
    Route::get('/clientes/{cliente}/relatorio/pdf', [RelatorioController::class, 'pdf'])->name('clientes.relatorio.pdf');

    Route::get('/relatorios', [RelatorioConsolidadoController::class, 'index'])->name('relatorios.consolidado');
    Route::get('/relatorios/pdf', [RelatorioConsolidadoController::class, 'pdf'])->name('relatorios.pdf');
    Route::get('/clientes/{cliente}/leads/create', [LeadController::class, 'create'])->name('clientes.leads.create');
    Route::post('/clientes/{cliente}/leads', [LeadController::class, 'store'])->name('clientes.leads.store');
    Route::get('/clientes/{cliente}/leads/{lead}/edit', [LeadController::class, 'edit'])->name('clientes.leads.edit');
    Route::put('/clientes/{cliente}/leads/{lead}', [LeadController::class, 'update'])->name('clientes.leads.update');
    Route::post('/clientes/{cliente}/leads/importar', [LeadController::class, 'importar'])->name('clientes.leads.importar');

    Route::get('/clientes/{cliente}/campanhas', [CampanhaController::class, 'index'])->name('clientes.campanhas.index');
    Route::get('/clientes/{cliente}/campanhas/create', [CampanhaController::class, 'create'])->name('clientes.campanhas.create');
    Route::post('/clientes/{cliente}/campanhas', [CampanhaController::class, 'store'])->name('clientes.campanhas.store');
    Route::get('/clientes/{cliente}/campanhas/{campanha}', [CampanhaController::class, 'show'])->name('clientes.campanhas.show');
    Route::post('/clientes/{cliente}/campanhas/{campanha}/enviar', [CampanhaController::class, 'enviar'])->name('clientes.campanhas.enviar');
    Route::post('/clientes/{cliente}/campanhas/{campanha}/cancelar', [CampanhaController::class, 'cancelar'])->name('clientes.campanhas.cancelar');

    Route::resource('precos-modelo', PrecoModeloController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['precos-modelo' => 'precoModelo']);
    Route::post('/precos-modelo/atualizar-cotacao', [PrecoModeloController::class, 'atualizarCotacao'])->name('precos-modelo.atualizar-cotacao');

    Route::get('/lgpd', [LgpdController::class, 'index'])->name('lgpd.index');
    Route::delete('/lgpd', [LgpdController::class, 'destroy'])->name('lgpd.destroy');

    Route::resource('planos', PlanoController::class)->except(['show']);

    Route::resource('message-templates', MessageTemplateController::class)->except(['show']);
    Route::post('/message-templates/{message_template}/submeter', [MessageTemplateController::class, 'submeter'])->name('message-templates.submeter');

    Route::resource('precos-campanha', PrecoCampanhaController::class)
        ->only(['index', 'update'])
        ->parameters(['precos-campanha' => 'precoCampanha']);

    Route::resource('custos-infraestrutura', CustoInfraestruturaController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['custos-infraestrutura' => 'custo']);

    Route::get('/financeiro', [FinanceiroDashboardController::class, 'index'])->name('financeiro.dashboard');
    Route::get('/financeiro/fechamentos', [FechamentoFinanceiroController::class, 'index'])->name('financeiro.fechamentos.index');
    Route::post('/financeiro/fechamentos', [FechamentoFinanceiroController::class, 'store'])->name('financeiro.fechamentos.store');

    Route::get('/suporte', [SuporteController::class, 'index'])->name('suporte.index');
    Route::get('/suporte/{ticket}', [SuporteController::class, 'show'])->name('suporte.show');
    Route::post('/suporte/{ticket}/responder', [SuporteController::class, 'responder'])->name('suporte.responder');
    Route::patch('/suporte/{ticket}/fechar', [SuporteController::class, 'fechar'])->name('suporte.fechar');
});

Route::middleware(['auth', 'role:cliente', 'cliente.ativo'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'home'])->name('home');
    Route::get('/agente', [PortalAgenteController::class, 'index'])->name('agente');
    Route::put('/agente/{agente}/geral', [PortalAgenteController::class, 'updateGeral'])->name('agente.update-geral');
    Route::put('/agente/{agente}/horarios', [PortalAgenteController::class, 'updateHorarios'])->name('agente.update-horarios');
    Route::put('/agente/{agente}/regras', [PortalAgenteController::class, 'updateRegras'])->name('agente.update-regras');
    Route::put('/agente/{agente}/conhecimento', [PortalAgenteController::class, 'updateConhecimento'])->name('agente.update-conhecimento');
    Route::get('/contatos-bloqueados', [PortalContatoBloqueadoController::class, 'index'])->name('contatos-bloqueados.index');
    Route::get('/contatos-bloqueados/create', [PortalContatoBloqueadoController::class, 'create'])->name('contatos-bloqueados.create');
    Route::post('/contatos-bloqueados', [PortalContatoBloqueadoController::class, 'store'])->name('contatos-bloqueados.store');
    Route::get('/contatos-bloqueados/{contato}/edit', [PortalContatoBloqueadoController::class, 'edit'])->name('contatos-bloqueados.edit');
    Route::put('/contatos-bloqueados/{contato}', [PortalContatoBloqueadoController::class, 'update'])->name('contatos-bloqueados.update');
    Route::delete('/contatos-bloqueados/{contato}', [PortalContatoBloqueadoController::class, 'destroy'])->name('contatos-bloqueados.destroy');
    Route::post('/contatos-bloqueados/importar', [PortalContatoBloqueadoController::class, 'importar'])->name('contatos-bloqueados.importar');
    Route::get('/leads', [PortalLeadController::class, 'index'])->name('leads.index');
    Route::get('/whatsapp', [PortalWhatsappController::class, 'index'])->name('whatsapp');
    Route::post('/whatsapp/conectar-embedded', [PortalWhatsappController::class, 'conectarEmbedded'])->name('whatsapp.conectar-embedded');
    Route::post('/whatsapp/iniciar-conexao', [PortalWhatsappController::class, 'iniciarConexao'])->name('whatsapp.iniciar-conexao');
    Route::post('/whatsapp/{integracao}/desconectar', [PortalWhatsappController::class, 'desconectar'])->name('whatsapp.desconectar');
    Route::get('/conversas', [PortalConversaController::class, 'index'])->name('conversas.index');
    Route::get('/conversas/{conversa}', [PortalConversaController::class, 'show'])->name('conversas.show');
    Route::post('/conversas/{conversa}/responder', [PortalConversaController::class, 'responder'])->name('conversas.responder');
    Route::post('/conversas/{conversa}/retomar-ia', [PortalConversaController::class, 'retomarIa'])->name('conversas.retomar-ia');
    Route::get('/relatorio', [PortalRelatorioController::class, 'show'])->name('relatorio');
    Route::get('/relatorio/pdf', [PortalRelatorioController::class, 'pdf'])->name('relatorio.pdf');
    Route::get('/financeiro', [PortalFinanceiroController::class, 'index'])->name('financeiro');

    Route::get('/suporte', [PortalSuporteController::class, 'index'])->name('suporte.index');
    Route::get('/suporte/criar', [PortalSuporteController::class, 'create'])->name('suporte.create');
    Route::post('/suporte', [PortalSuporteController::class, 'store'])->name('suporte.store');
    Route::get('/suporte/{ticket}', [PortalSuporteController::class, 'show'])->name('suporte.show');
    Route::post('/suporte/{ticket}/responder', [PortalSuporteController::class, 'responder'])->name('suporte.responder');
});

require __DIR__.'/auth.php';
