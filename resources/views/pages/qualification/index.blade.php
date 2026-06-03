@extends('layouts.app')

@section('title', 'Qualification')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Qualification</h1>
            <p class="mt-1 text-sm text-text-secondary">Select academic records that meet your standards</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
            <p class="text-xs text-text-secondary">Total Records</p>
            <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-success bg-success-light bg-opacity-30 p-4 shadow-sm">
            <p class="text-xs text-text-secondary">Qualified</p>
            <p class="text-2xl font-bold text-success mt-1">{{ $stats['qualified'] }}</p>
        </div>
        <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
            <p class="text-xs text-text-secondary">Unqualified</p>
            <p class="text-2xl font-bold text-text-primary mt-1">{{ $stats['unqualified'] }}</p>
        </div>
    </div>

    <div class="mb-4 rounded-xl border border-border bg-surface p-4 shadow-sm">
        <form method="GET" action="{{ route('qualification.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[200px] flex-1">
                <label class="block text-xs font-medium text-text-secondary mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, NIM, or NIK..." class="w-full rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary placeholder-text-secondary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-medium text-text-secondary mb-1">Status</label>
                <select name="status" class="rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">All</option>
                    <option value="unqualified" @selected(request('status') === 'unqualified')>Unqualified</option>
                    <option value="qualified" @selected(request('status') === 'qualified')>Qualified</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-text-secondary mb-1">Min GPA</label>
                <select name="min_gpa" class="rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">Any</option>
                    <option value="2.00" @selected(request('min_gpa') == '2.00')>≥ 2.00</option>
                    <option value="2.50" @selected(request('min_gpa') == '2.50')>≥ 2.50</option>
                    <option value="3.00" @selected(request('min_gpa') == '3.00')>≥ 3.00</option>
                    <option value="3.50" @selected(request('min_gpa') == '3.50')>≥ 3.50</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-text-secondary mb-1">Study Program</label>
                <select name="study_program" class="rounded-lg border border-border bg-bg px-3 py-2 text-sm text-text-primary focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">All</option>
                    @foreach($studyPrograms as $prog)
                        <option value="{{ $prog }}" @selected(request('study_program') === $prog)>{{ $prog }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-dark">
                Filter
            </button>
            <a href="{{ route('qualification.index') }}" class="rounded-lg border border-border px-4 py-2 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-50">
                Reset
            </a>
        </form>
    </div>

    <form method="POST" action="{{ route('qualification.qualify') }}" class="inline">
        @csrf
        <div class="rounded-xl border border-border bg-surface shadow-sm overflow-hidden">
            <div class="flex items-center justify-between gap-2 border-b border-border bg-gray-50 px-4 py-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="select-all" class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                    <label for="select-all" class="text-sm font-medium text-text-primary cursor-pointer select-none">Select All</label>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-lg bg-success px-4 py-1.5 text-sm font-medium text-white transition-colors hover:bg-success-dark" id="qualify-btn" disabled>
                        Qualify Selected
                    </button>
                    <button type="submit" formaction="{{ route('qualification.unqualify') }}" class="rounded-lg border border-border px-4 py-1.5 text-sm font-medium text-text-secondary transition-colors hover:bg-gray-100" id="unqualify-btn" disabled>
                        Remove Selected
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-text-secondary">
                            <th class="px-4 py-3 w-10"></th>
                            <th class="px-4 py-3 font-medium">Student</th>
                            <th class="px-4 py-3 font-medium">NIM</th>
                            <th class="px-4 py-3 font-medium">Study Program</th>
                            <th class="px-4 py-3 font-medium">GPA</th>
                            <th class="px-4 py-3 font-medium">Total SKS</th>
                            <th class="px-4 py-3 font-medium">Predikat</th>
                            <th class="px-4 py-3 font-medium">Graduation</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($records as $record)
                            <tr class="transition-colors hover:bg-gray-50 {{ $record->is_qualified ? 'bg-success-light bg-opacity-10' : '' }}">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="ids[]" value="{{ $record->id }}" class="row-checkbox h-4 w-4 rounded border-border text-primary focus:ring-primary">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-text-primary">{{ $record->student_name ?? '-' }}</p>
                                    <p class="text-xs text-text-secondary">{{ $record->university ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-text-primary">{{ $record->student_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-text-primary">{{ $record->study_program ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium {{ $record->gpa && $record->gpa >= 3 ? 'text-success' : 'text-text-primary' }}">{{ $record->gpa ? number_format($record->gpa, 2) : '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-text-primary">{{ $record->total_sks ?? '-' }}</td>
                                <td class="px-4 py-3 text-text-primary">{{ $record->predikat_kelulusan ?? '-' }}</td>
                                <td class="px-4 py-3 text-text-primary">{{ $record->tanggal_lulus ? $record->tanggal_lulus->format('d/m/Y') : ($record->graduation_year ?? '-') }}</td>
                                <td class="px-4 py-3">
                                    @if($record->is_qualified)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-success-light px-2.5 py-0.5 text-xs font-medium text-success">
                                            Qualified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-text-secondary">
                                            Unqualified
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-8 w-8 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="mt-3 text-sm font-medium text-text-primary">No records found</p>
                                    <p class="mt-1 text-xs text-text-secondary">Try adjusting your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    @if(method_exists($records, 'links'))
        <div class="mt-6">
            {{ $records->withQueryString()->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const qualifyBtn = document.getElementById('qualify-btn');
    const unqualifyBtn = document.getElementById('unqualify-btn');

    function updateButtons() {
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        qualifyBtn.disabled = checked === 0;
        unqualifyBtn.disabled = checked === 0;
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateButtons();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) selectAll.checked = false;
            updateButtons();
        });
    });

    document.querySelectorAll('select[name="status"], select[name="min_gpa"], select[name="study_program"]').forEach(el => {
        el.addEventListener('change', function() { this.form.submit(); });
    });
</script>
@endpush
