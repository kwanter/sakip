@props([
    'title' => '',
    'columns' => [],
    'rows' => [],
    'actions' => true,
    'search' => true,
    'pagination' => true,
])

<div class="modern-table-container">
    @if($title || $actions || $search)
    <div class="table-toolbar">
        @if($title)
        <h3 class="card-title" style="margin: 0;">{{ $title }}</h3>
        @endif

        <div class="table-actions">
            @if($search)
            <div class="table-search">
                <i class="fas fa-search table-search-icon"></i>
                <input type="text" placeholder="Cari..." class="table-search-input" data-table-search>
            </div>
            @endif

            @if($actions && isset($createAction) && $createAction)
            <a href="{{ $createAction }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tambah</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <div style="overflow-x: auto;">
        <table class="modern-table">
            <thead>
                <tr>
                    @foreach($columns as $column)
                    <th>{{ $column['label'] ?? $column }}</th>
                    @endforeach
                    @if($actions)
                    <th style="width: 100px;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(count($rows) > 0)
                    @foreach($rows as $row)
                    <tr>
                        @foreach($columns as $key => $column)
                        <td>
                            @if(isset($column['render']))
                                {!! $column['render']($row) !!}
                            @elseif(is_string($key))
                                {{ $row[$key] ?? '-' }}
                            @else
                                {{ $row[$column] ?? '-' }}
                            @endif
                        </td>
                        @endforeach
                        @if($actions)
                        <td>
                            <div class="table-row-actions">
                                @if(isset($row['id']))
                                <a href="{{ request()->url() }}/{{ $row['id'] }}/edit" class="btn-icon" style="color: var(--primary-500);" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn-icon" style="color: var(--danger);" title="Hapus" data-delete-action="{{ route(request()->route()->getName(), $row['id']) ?? '#' }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="{{ count($columns) + ($actions ? 1 : 0) }}" class="text-center">
                        <div class="empty-state" style="padding: var(--space-xl);">
                            <div class="empty-state-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="empty-state-title">Tidak ada data</div>
                            <div class="empty-state-description">Belum ada data yang tersedia untuk saat ini.</div>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($pagination && isset($paginator) && $paginator->hasPages())
    <div style="padding: var(--space-md) var(--space-lg); border-top: 1px solid var(--border-light); display: flex; justify-content: between; align-items: center;">
        <div class="text-sm text-secondary">
            Menampilkan {{ $paginator->firstItem() ?? 0 }} sampai {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data
        </div>
        {{ $paginator->links('pagination::tailwind') }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-table-search]').forEach(function(input) {
        input.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const table = this.closest('.modern-table-container').querySelector('table');
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('[data-delete-action]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const action = this.dataset.deleteAction;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                fetch(action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success || data.redirect) {
                        window.location.reload();
                    } else {
                        showNotification(data.message || 'Terjadi kesalahan', 'danger');
                    }
                })
                .catch(function() {
                    showNotification('Terjadi kesalahan koneksi', 'danger');
                });
            }
        });
    });
</script>
@endpush
