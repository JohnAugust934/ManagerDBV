@php
    $items = [
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
    ];

    $styles = [
        'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/30 text-green-800 dark:text-green-300',
        'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/30 text-red-800 dark:text-red-300',
        'warning' => 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/30 text-amber-800 dark:text-amber-300',
        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/30 text-blue-800 dark:text-blue-300',
    ];
@endphp

<div class="space-y-3" aria-live="polite" aria-atomic="true">
    @foreach ($items as $item)
        @if (!empty($item['message']))
            <div
                x-data="{
                    show: true,
                    timeoutMs: 5000,
                    timer: null,
                    startTimer() {
                        this.clearTimer();
                        this.timer = setTimeout(() => this.show = false, this.timeoutMs);
                    },
                    clearTimer() {
                        if (this.timer) {
                            clearTimeout(this.timer);
                            this.timer = null;
                        }
                    }
                }"
                x-init="startTimer()"
                @mouseenter="clearTimer()"
                @mouseleave="startTimer()"
                @focusin="clearTimer()"
                @focusout="startTimer()"
                x-show="show" x-transition
                class="ui-alert ui-animate-fade-up {{ $styles[$item['type']] }}"
                role="{{ $item['type'] === 'error'? 'alert' : 'status' }}">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm sm:text-[15px] font-semibold">{{ $item['message'] }}</p>
                    <button type="button" @click="show = false" class="text-current/70 hover:text-current leading-none">&times;</button>
                </div>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div
            x-data="{
                show: true,
                timeoutMs: 7000,
                timer: null,
                startTimer() {
                    this.clearTimer();
                    this.timer = setTimeout(() => this.show = false, this.timeoutMs);
                },
                clearTimer() {
                    if (this.timer) {
                        clearTimeout(this.timer);
                        this.timer = null;
                    }
                }
            }"
            x-init="startTimer()"
            @mouseenter="clearTimer()"
            @mouseleave="startTimer()"
            @focusin="clearTimer()"
            @focusout="startTimer()"
            x-show="show" x-transition
            class="ui-alert ui-animate-fade-up bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/30 text-red-800 dark:text-red-300"
            role="alert">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-bold">Revise os campos destacados antes de continuar.</p>
                    <ul class="mt-2 space-y-1 text-sm list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" @click="show = false" class="text-current/70 hover:text-current leading-none">&times;</button>
            </div>
        </div>
    @endif
</div>
