<?php

namespace Tests\Unit;

use App\Support\OperationalWindow;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class OperationalWindowTest extends TestCase
{
    public function test_parse_windows_ignora_valores_invalidos(): void
    {
        $windows = OperationalWindow::parseWindows('02:45-04:45, invalido, 25:00-26:00, 03:00-03:00');

        $this->assertSame([
            ['start' => '02:45', 'end' => '04:45'],
        ], $windows);
    }

    public function test_contains_time_aceita_janela_no_mesmo_dia(): void
    {
        $this->assertTrue(OperationalWindow::containsTime('02:45', '04:45', '03:10'));
        $this->assertFalse(OperationalWindow::containsTime('02:45', '04:45', '05:10'));
    }

    public function test_contains_time_aceita_janela_virando_meia_noite(): void
    {
        $this->assertTrue(OperationalWindow::containsTime('23:00', '01:00', '23:30'));
        $this->assertTrue(OperationalWindow::containsTime('23:00', '01:00', '00:30'));
        $this->assertFalse(OperationalWindow::containsTime('23:00', '01:00', '15:00'));
    }

    public function test_is_now_in_any_window_respeita_fuso(): void
    {
        $now = CarbonImmutable::create(2026, 3, 24, 4, 0, 0, 'America/Sao_Paulo');

        $this->assertTrue(OperationalWindow::isNowInAnyWindow('02:45-04:45', 'America/Sao_Paulo', $now));
        $this->assertFalse(OperationalWindow::isNowInAnyWindow('05:00-06:00', 'America/Sao_Paulo', $now));
    }
}

