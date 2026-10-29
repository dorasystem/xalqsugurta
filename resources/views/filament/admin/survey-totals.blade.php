{{-- Questionnaire totals (SurveyResponseResource list page) --}}
<x-filament-widgets::widget>
    <x-filament::section :heading="'Natijalar: ' . $total . ' ta javob'" icon="heroicon-o-chart-bar">
        <div class="xs-survey">
            <div>
                <b>Nega «XALQ SUG‘URTA»</b>
                @foreach ($reasons as [$label, $count])
                    <div class="xs-survey__row">
                        <span>{{ $label }}</span>
                        <span class="xs-survey__bar"><i style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></i></span>
                        <b>{{ $count }}</b>
                    </div>
                @endforeach
            </div>
            <div>
                <b>Qiziqtirgan mahsulotlar</b>
                @foreach ($products as [$label, $count])
                    <div class="xs-survey__row">
                        <span>{{ $label }}</span>
                        <span class="xs-survey__bar"><i style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></i></span>
                        <b>{{ $count }}</b>
                    </div>
                @endforeach
            </div>
            <div>
                <b>Mezonlar (o‘rtacha o‘rin, 1 — eng muhim)</b>
                @foreach ($criteria as [$label, $place, $count])
                    <div class="xs-survey__row">
                        <span>{{ $label }}</span>
                        <b>{{ $place ?? '—' }}</b>
                        <span class="xs-survey__muted">{{ $count }} ta baho</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
