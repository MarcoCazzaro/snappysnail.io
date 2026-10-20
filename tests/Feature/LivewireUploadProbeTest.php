<?php

use App\Livewire\SsnailSearch;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Livewire\Livewire;

test('upload probes against components without file uploads are rejected as bad requests and not reported', function () {
    $snapshot = Livewire::test(SsnailSearch::class)->snapshot;

    // Exceptions::fake() checks shouldReport() before mapping, so listen on the real handler (as Nightwatch does).
    $reported = [];
    app(ExceptionHandler::class)->reportable(function (Throwable $e) use (&$reported) {
        $reported[] = $e;
    });

    $this->withHeaders(['X-Livewire' => 'true'])
        ->postJson(route('default-livewire.update'), [
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => [],
                'calls' => [[
                    'path' => '',
                    'method' => '_startUpload',
                    'params' => ['photo', [['name' => 'shell.php', 'size' => 1, 'type' => 'image/png']], false],
                ]],
            ]],
        ])
        ->assertBadRequest();

    expect($reported)->toBeEmpty();
});
