<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $survey->title }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(3,189,137,0.18),_transparent_32%),linear-gradient(180deg,_#f6f8f7_0%,_#eef3f1_100%)] text-stone-900">
    @php
        $errors = $errors ?? session('errors', new \Illuminate\Support\ViewErrorBag());
        $questions = $survey->questions->values();
        $sections = [];

        foreach ($questions as $question) {
            $helpText = trim((string) ($question->help_text ?? ''));
            $startsNewSection = str_starts_with($helpText, 'SECTION ');

            if ($startsNewSection || count($sections) === 0) {
                $sections[] = [
                    'title' => $startsNewSection ? $helpText : 'Survey Questions',
                    'questions' => [],
                ];
            }

            $sections[array_key_last($sections)]['questions'][] = [
                'question' => $question,
                'help_text' => $startsNewSection ? null : ($question->help_text ?: null),
            ];
        }

        $totalQuestions = $questions->count();
        $requiredQuestions = $questions->where('is_required', true)->count();
    @endphp

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,22rem)_minmax(0,1fr)] lg:gap-8">
            <aside class="lg:sticky lg:top-6 lg:self-start">
                <div class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/90 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                    <div class="border-b border-stone-200/80 bg-[linear-gradient(135deg,_#015b45_0%,_#03bd89_55%,_#62d8b3_100%)] px-6 py-7 text-white sm:px-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Policy Research</p>
                                <h1 class="mt-3 text-3xl font-semibold leading-tight">{{ $survey->title }}</h1>
                            </div>

                            @if (filled(config('branding.logo_url')))
                                <img
                                    src="{{ config('branding.logo_url') }}"
                                    alt="{{ config('app.name') }}"
                                    class="h-14 w-14 rounded-2xl bg-white/90 object-contain p-2 shadow-sm"
                                >
                            @endif
                        </div>

                        @if (filled($survey->description))
                            <div class="prose prose-sm prose-invert mt-5 max-w-none prose-p:text-white/90 prose-strong:text-white">{!! $survey->description !!}</div>
                        @endif
                    </div>

                    <div class="space-y-6 px-6 py-6 sm:px-8">
                        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Questions</p>
                                <p class="mt-2 text-2xl font-semibold text-stone-900">{{ $totalQuestions }}</p>
                            </div>
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Required</p>
                                <p class="mt-2 text-2xl font-semibold text-stone-900">{{ $requiredQuestions }}</p>
                            </div>
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Sections</p>
                                <p class="mt-2 text-2xl font-semibold text-stone-900">{{ count($sections) }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                            <p class="text-sm font-semibold text-stone-900">Survey flow</p>
                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-stone-200">
                                <div class="h-full w-full rounded-full bg-[linear-gradient(90deg,_#03bd89_0%,_#0f766e_100%)]"></div>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ($sections as $index => $section)
                                    <div class="flex items-start gap-3 rounded-2xl border border-stone-200 bg-white px-3 py-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-700">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-stone-900">{{ $section['title'] }}</p>
                                            <p class="text-xs text-stone-500">{{ count($section['questions']) }} question{{ count($section['questions']) === 1 ? '' : 's' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-4 py-4 text-sm text-stone-600">
                            <p class="font-semibold text-stone-900">Before you submit</p>
                            <ul class="mt-3 space-y-2">
                                <li>Required questions are marked with <span class="font-semibold text-red-600">*</span>.</li>
                                <li>Your responses are submitted in one go at the end.</li>
                                <li>Use the optional respondent details only when needed.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="overflow-hidden rounded-[2rem] border border-white/70 bg-white/95 shadow-[0_20px_70px_rgba(15,23,42,0.08)] backdrop-blur">
                <div class="border-b border-stone-200 bg-white px-6 py-6 sm:px-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-teal-700">Public Response Form</p>
                            <h2 class="mt-2 text-2xl font-semibold text-stone-900">Shareable survey link</h2>
                        </div>
                        <div class="rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-600">
                            {{ $totalQuestions }} questions
                        </div>
                    </div>
                </div>

                <div class="px-6 py-8 sm:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-3xl border border-teal-200 bg-teal-50 px-5 py-4 text-sm text-teal-950 shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900 shadow-sm">
                            <p class="font-semibold">Please fix the highlighted fields and submit again.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('surveys.public.store', $survey->public_token) }}" class="space-y-8">
                        @csrf

                        <section class="rounded-[1.75rem] border border-stone-200 bg-[linear-gradient(180deg,_#ffffff_0%,_#f6faf8_100%)] p-6 shadow-sm">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-teal-700">Respondent Details</p>
                                    <h3 class="mt-2 text-xl font-semibold text-stone-900">Optional profile information</h3>
                                </div>
                                <div class="hidden rounded-full bg-stone-900 px-3 py-1 text-xs font-medium text-white sm:block">Optional</div>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-700" for="respondent_name">Name</label>
                                    <input id="respondent_name" name="respondent_name" type="text" value="{{ old('respondent_name') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-700" for="respondent_email">Email</label>
                                    <input id="respondent_email" name="respondent_email" type="email" value="{{ old('respondent_email') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-700" for="respondent_phone">Phone</label>
                                    <input id="respondent_phone" name="respondent_phone" type="text" value="{{ old('respondent_phone') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-stone-700" for="respondent_organization">Organization</label>
                                    <input id="respondent_organization" name="respondent_organization" type="text" value="{{ old('respondent_organization') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="mb-2 block text-sm font-medium text-stone-700" for="respondent_location">Location</label>
                                    <input id="respondent_location" name="respondent_location" type="text" value="{{ old('respondent_location') }}" class="w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                </div>
                            </div>
                        </section>

                        @foreach ($sections as $sectionIndex => $section)
                            <section class="rounded-[1.75rem] border border-stone-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col gap-4 border-b border-stone-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-600 text-base font-semibold text-white shadow-sm">
                                            {{ $sectionIndex + 1 }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-teal-700">Section {{ $sectionIndex + 1 }}</p>
                                            <h3 class="mt-2 text-2xl font-semibold text-stone-900">{{ $section['title'] }}</h3>
                                        </div>
                                    </div>
                                    <div class="rounded-full border border-stone-200 bg-stone-50 px-4 py-2 text-sm text-stone-600">
                                        {{ count($section['questions']) }} question{{ count($section['questions']) === 1 ? '' : 's' }}
                                    </div>
                                </div>

                                <div class="mt-6 space-y-5">
                                    @foreach ($section['questions'] as $entry)
                                        @php
                                            $question = $entry['question'];
                                            $questionHelpText = $entry['help_text'];
                                            $answerName = "answers[{$question->id}]";
                                            $oldValue = old("answers.{$question->id}");
                                        @endphp

                                        <div class="rounded-[1.5rem] border border-stone-200 bg-stone-50/80 p-5">
                                            <div class="flex items-start gap-4">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-semibold text-stone-700 shadow-sm ring-1 ring-stone-200">
                                                    {{ $question->sort }}
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <label class="block text-base font-semibold text-stone-900">
                                                        {{ $question->prompt }}
                                                        @if ($question->is_required)
                                                            <span class="text-red-600">*</span>
                                                        @endif
                                                    </label>

                                                    @if (filled($questionHelpText))
                                                        <p class="mt-2 text-sm leading-6 text-stone-500">{{ $questionHelpText }}</p>
                                                    @endif

                                                    <div class="mt-4">
                                                        @switch($question->type)
                                                            @case('long_text')
                                                                <textarea name="{{ $answerName }}" rows="5" placeholder="{{ $question->placeholder }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">{{ $oldValue }}</textarea>
                                                                @break

                                                            @case('number')
                                                                <input type="number" step="{{ data_get($question->settings, 'step', 'any') }}" min="{{ data_get($question->settings, 'min') }}" max="{{ data_get($question->settings, 'max') }}" placeholder="{{ $question->placeholder }}" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                @break

                                                            @case('date')
                                                                <input type="date" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                @break

                                                            @case('email')
                                                                <input type="email" placeholder="{{ $question->placeholder }}" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                @break

                                                            @case('phone')
                                                                <input type="tel" placeholder="{{ $question->placeholder }}" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                @break

                                                            @case('url')
                                                                <input type="url" placeholder="{{ $question->placeholder }}" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                @break

                                                            @case('single_choice')
                                                                <div class="grid gap-3 md:grid-cols-2">
                                                                    @foreach (($question->options ?? []) as $option)
                                                                        @php $optionValue = $option['value'] ?? $option['label'] ?? ''; @endphp
                                                                        <label class="flex cursor-pointer items-start gap-3 rounded-3xl border border-stone-200 bg-white px-4 py-4 text-sm text-stone-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50/60">
                                                                            <input type="radio" name="{{ $answerName }}" value="{{ $optionValue }}" @checked($oldValue == $optionValue) class="mt-1 h-4 w-4 border-stone-300 text-teal-600 focus:ring-teal-200">
                                                                            <span class="font-medium">{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                                @break

                                                            @case('multiple_choice')
                                                                <div class="grid gap-3 md:grid-cols-2">
                                                                    @foreach (($question->options ?? []) as $option)
                                                                        @php $optionValue = $option['value'] ?? $option['label'] ?? ''; @endphp
                                                                        <label class="flex cursor-pointer items-start gap-3 rounded-3xl border border-stone-200 bg-white px-4 py-4 text-sm text-stone-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50/60">
                                                                            <input type="checkbox" name="{{ $answerName }}[]" value="{{ $optionValue }}" @checked(in_array($optionValue, $oldValue ?? [], true)) class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-600 focus:ring-teal-200">
                                                                            <span class="font-medium">{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                                @break

                                                            @case('select')
                                                                <select name="{{ $answerName }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                                    <option value="">{{ $question->placeholder ?: 'Select an option' }}</option>
                                                                    @foreach (($question->options ?? []) as $option)
                                                                        @php $optionValue = $option['value'] ?? $option['label'] ?? ''; @endphp
                                                                        <option value="{{ $optionValue }}" @selected($oldValue == $optionValue)}>
                                                                            {{ $option['label'] ?? $option['value'] ?? '' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @break

                                                            @case('yes_no')
                                                                <div class="grid gap-3 sm:grid-cols-2">
                                                                    <label class="flex cursor-pointer items-center gap-3 rounded-3xl border border-stone-200 bg-white px-4 py-4 text-sm text-stone-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50/60">
                                                                        <input type="radio" name="{{ $answerName }}" value="1" @checked($oldValue === '1') class="h-4 w-4 border-stone-300 text-teal-600 focus:ring-teal-200">
                                                                        <span class="font-medium">Yes</span>
                                                                    </label>
                                                                    <label class="flex cursor-pointer items-center gap-3 rounded-3xl border border-stone-200 bg-white px-4 py-4 text-sm text-stone-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50/60">
                                                                        <input type="radio" name="{{ $answerName }}" value="0" @checked($oldValue === '0') class="h-4 w-4 border-stone-300 text-teal-600 focus:ring-teal-200">
                                                                        <span class="font-medium">No</span>
                                                                    </label>
                                                                </div>
                                                                @break

                                                            @case('rating')
                                                                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-5">
                                                                    @foreach (range((int) data_get($question->settings, 'min', 1), (int) data_get($question->settings, 'max', 5)) as $rating)
                                                                        <label class="flex cursor-pointer items-center justify-center gap-3 rounded-3xl border border-stone-200 bg-white px-4 py-4 text-sm text-stone-700 shadow-sm transition hover:border-teal-300 hover:bg-teal-50/60">
                                                                            <input type="radio" name="{{ $answerName }}" value="{{ $rating }}" @checked((string) $oldValue === (string) $rating) class="h-4 w-4 border-stone-300 text-teal-600 focus:ring-teal-200">
                                                                            <span class="font-semibold">{{ $rating }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                                @break

                                                            @default
                                                                <input type="text" placeholder="{{ $question->placeholder }}" name="{{ $answerName }}" value="{{ $oldValue }}" class="w-full rounded-3xl border border-stone-300 bg-white px-4 py-3 text-stone-900 shadow-sm transition focus:border-teal-500 focus:outline-none focus:ring-4 focus:ring-teal-100">
                                                        @endswitch
                                                    </div>

                                                    @error("answers.{$question->id}")
                                                        <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        <div class="rounded-[1.75rem] border border-stone-200 bg-stone-950 p-6 text-white shadow-sm">
                            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/60">Submission</p>
                                    <h3 class="mt-2 text-2xl font-semibold">Review and submit your response</h3>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-white/70">Once submitted, your answers will be recorded for this policy research survey.</p>
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-white px-7 py-3 text-sm font-semibold text-stone-950 transition hover:bg-teal-50">
                                    Submit survey
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
