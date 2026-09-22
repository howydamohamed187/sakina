@php
    /** @var \App\Models\DailyQuestion $record */
    $record = $record ?? $this->record ?? null;
@endphp

@if ($record)

<div class="sakina-daily-question-hero">
    <div class="sakina-daily-question-hero__title">{{ __('app.daily_question') }}</div>
    <p class="sakina-daily-question-hero__body">{{ $record->body }}</p>

    <dl class="sakina-daily-question-hero__stats">
        <div>
            <dt>{{ __('app.fields.impressions') }}</dt>
            <dd>{{ $record->impressionsCount() }}</dd>
        </div>
        <div>
            <dt>{{ __('app.fields.participations') }}</dt>
            <dd>{{ $record->participationsCount() }}</dd>
        </div>
        <div>
            <dt>{{ __('app.fields.correct_answers') }}</dt>
            <dd class="is-correct">{{ $record->correctAnswersCount() }}</dd>
        </div>
        <div>
            <dt>{{ __('app.fields.wrong_answers') }}</dt>
            <dd>{{ $record->wrongAnswersCount() }}</dd>
        </div>
        <div>
            <dt>{{ __('app.fields.success_rate') }}</dt>
            <dd class="is-correct">{{ $record->successRateLabel() }}</dd>
        </div>
    </dl>
</div>
@endif
