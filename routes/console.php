<?php

use App\Jobs\ProcesarPedido;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('pedidos:encolar', function () {
    ProcesarPedido::dispatch()->onQueue('pedidos');
    $this->info('Job de pedidos enviado a la cola.');
});

Artisan::command('pedidos:procesar', function () {
    $this->info('Procesando trabajos en la cola "pedidos"...');

    \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--queue' => 'pedidos',
        '--tries' => 3, // Número de intentos por trabajo
        '--timeout' => 120, // Tiempo máximo de ejecución
    ]);

    $this->info('Procesamiento de la cola "pedidos" finalizado.');
});
