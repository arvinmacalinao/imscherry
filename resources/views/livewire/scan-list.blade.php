<div
    x-data="{
        // short beep: high = added, low double = problem (no sound files needed)
        beep(ok) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const tones = ok ? [[880, 0]] : [[220, 0], [220, 0.18]];
                tones.forEach(([freq, at]) => {
                    const osc = ctx.createOscillator(), gain = ctx.createGain();
                    osc.frequency.value = freq;
                    gain.gain.value = 0.15;
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.start(ctx.currentTime + at);
                    osc.stop(ctx.currentTime + at + 0.12);
                });
            } catch (e) {}
        },
        // the scanner types the code and presses Enter: clear the box at once so the next
        // scan can start while this one is being added (Livewire sends them in order)
        submit(box) {
            const code = box.value.trim();
            box.value = '';
            if (code) $wire.scan(code);
            box.focus();
        },
    }"
    x-init="$refs.box.focus()"
    x-on:scan-result.window="beep($event.detail.ok); $refs.box.focus()"
>
    {{-- Scan box --}}
    <x-card>
        <x-slot:header>
            <x-slot:title>
                {{ __('Scan Parcel') . ' (' . $type . ')' }}
            </x-slot:title>
            <x-slot:actions>
                <x-action.close route="{{ route('dashboard') }}" />
            </x-slot:actions>
        </x-slot:header>

        <x-slot:content>
            <label for="tracking_number" class="form-label">{{ __('Order Number/Tracking Number') }}</label>
            <input type="text" id="tracking_number" x-ref="box" class="form-control form-control-lg"
                   placeholder="Scan or type order/tracking number, then Enter..." autocomplete="off"
                   x-on:keydown.enter.prevent="submit($el)">
            <small class="form-hint">
                Each scan is added to the list below right away. Click Confirm when all parcels are scanned.
            </small>

            @if ($message)
                <div @class(['alert mt-3 mb-0', 'alert-success' => $ok, 'alert-danger' => ! $ok]) role="status" aria-live="polite">
                    {{ $message }}
                </div>
            @endif
        </x-slot:content>
    </x-card>

    {{-- Scanned orders --}}
    @if ($cartItems->isNotEmpty())
        <x-card class="mt-4">
            <x-slot:header>
                <x-slot:title>{{ __('Scanned Orders') }} ({{ $cartItems->count() }})</x-slot:title>
            </x-slot:header>

            <x-slot:content>
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tracking Number</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- newest scan on top --}}
                        @foreach ($cartItems->reverse() as $item)
                            <tr wire:key="scan-{{ $item->rowId }}">
                                <td>{{ $cartItems->count() - $loop->index }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->options->customer ?? 'N/A' }}</td>
                                <td>{{ $item->options->status_id ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            wire:click="remove('{{ $item->rowId }}')">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- processes the whole list and downloads the CSV, as before --}}
                <form action="{{ route('order.confirm', $type) }}" method="POST" target="_blank"
                      onsubmit="setTimeout(() => location.reload(), 500);">
                    @csrf
                    <x-button type="submit" class="btn btn-success">
                        {{ __('Confirm') }} ({{ $cartItems->count() }})
                    </x-button>
                </form>
            </x-slot:content>
        </x-card>
    @else
        <p class="mt-4 text-muted">No scanned orders yet.</p>
    @endif
</div>
