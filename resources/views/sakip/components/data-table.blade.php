@props(['id', 'columns' => [], 'data_source' => null, 'options' => []])

<div class="sakip-data-table-container" data-table-id="{{ $id }}">
    <div class="sakip-data-table-header">
        <div class="sakip-data-table-search">
            <input type="text" class="sakip-data-table-search-input" placeholder="Search..." data-table-search="{{ $id }}">
        </div>
        <div class="sakip-data-table-actions">
            <button type="button" class="sakip-btn sakip-btn-secondary" data-table-refresh="{{ $id }}">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <button type="button" class="sakip-btn sakip-btn-primary" data-table-export="{{ $id }}">
                <i class="fas fa-download"></i> Export
            </button>
            @if(isset($options['show_add_button']) && $options['show_add_button'])
            <button type="button" class="sakip-btn sakip-btn-success" data-table-add="{{ $id }}">
                <i class="fas fa-plus"></i> Add New
            </button>
            @endif
        </div>
    </div>

    @if(isset($options['filters']) && !empty($options['filters']))
    <div class="sakip-data-table-filters">
        @foreach($options['filters'] as $filterName => $filterOptions)
        <div class="sakip-data-table-filter">
            <label for="{{ $id }}-filter-{{ $filterName }}">{{ ucfirst(str_replace('_', ' ', $filterName)) }}:</label>
            <select id="{{ $id }}-filter-{{ $filterName }}" class="sakip-data-table-filter-select" data-table-filter="{{ $id }}" data-filter-name="{{ $filterName }}">
                <option value="">All {{ ucfirst(str_replace('_', ' ', $filterName)) }}</option>
                @foreach($filterOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @endforeach
    </div>
    @endif

    <div class="sakip-data-table-wrapper">
        <table id="{{ $id }}" class="sakip-data-table" data-table-type="{{ $options['type'] ?? 'default' }}" data-source="{{ $data_source }}">
            <thead>
                <tr>
                    @if(isset($options['selectable']) && $options['selectable'])
                    <th class="sakip-data-table-checkbox">
                        <input type="checkbox" class="sakip-data-table-select-all" data-table-select-all="{{ $id }}">
                    </th>
                    @endif
                    @foreach($columns as $column)
                    <th class="sakip-data-table-header-cell" data-field="{{ $column['field'] }}" data-sortable="{{ $column['sortable'] ?? false }}">
                        {{ $column['title'] }}
                        @if(isset($column['sortable']) && $column['sortable'])
                        <span class="sakip-data-table-sort-icon">
                            <i class="fas fa-sort"></i>
                        </span>
                        @endif
                    </th>
                    @endforeach
                    @if(isset($options['show_actions']) && $options['show_actions'])
                    <th class="sakip-data-table-actions-header">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="sakip-data-table-body">
                <tr class="sakip-data-table-loading">
                    <td colspan="{{ count($columns) + ($options['selectable'] ? 1 : 0) + ($options['show_actions'] ? 1 : 0) }}" class="sakip-data-table-loading-cell">
                        <div class="sakip-loading-spinner"></div>
                        <span>Loading data...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="sakip-data-table-footer">
        <div class="sakip-data-table-info">
            <span class="sakip-data-table-info-text" data-table-info="{{ $id }}">Showing 0 to 0 of 0 entries</span>
        </div>
        <div class="sakip-data-table-pagination" data-table-pagination="{{ $id }}">
            <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-table-page="{{ $id }}" data-page="prev" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="sakip-data-table-page-info" data-table-page-info="{{ $id }}">Page 1 of 1</span>
            <button type="button" class="sakip-btn sakip-btn-sm sakip-btn-secondary" data-table-page="{{ $id }}" data-page="next" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
        SAKIP_DATA_TABLE_INIT.initializeTable('{{ $id }}', @json($options));
    }
});
</script>
@endpush