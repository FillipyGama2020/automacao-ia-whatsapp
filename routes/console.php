<?php

use App\Console\Commands\AtualizarCotacaoDolar;
use App\Console\Commands\ExpurgarConversasAntigas;
use App\Console\Commands\FecharConversasAbandonadas;
use App\Console\Commands\FecharMesFinanceiro;
use App\Console\Commands\ProcessarCampanhasAgendadas;
use App\Console\Commands\RetomarConversasAoAbrirHorario;
use App\Console\Commands\RetomarConversasSemResposta;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(AtualizarCotacaoDolar::class)->dailyAt('08:00');
Schedule::command(FecharConversasAbandonadas::class)->hourly();
Schedule::command(ExpurgarConversasAntigas::class)->dailyAt('03:30');
Schedule::command(FecharMesFinanceiro::class)->monthlyOn(1, '04:00');
Schedule::command(ProcessarCampanhasAgendadas::class)->everyFiveMinutes();
Schedule::command(RetomarConversasSemResposta::class)->everyFiveMinutes();
Schedule::command(RetomarConversasAoAbrirHorario::class)->everyFiveMinutes();
