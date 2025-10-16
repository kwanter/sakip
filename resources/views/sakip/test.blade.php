@extends('sakip.layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-800 mb-6">SAKIP Integration Test</h1>
                
                <!-- Test Notification -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Test Notification System</h2>
                    <div class="flex space-x-2">
                        <button onclick="testNotification('success')" class="sakip-btn sakip-btn-success">
                            Test Success
                        </button>
                        <button onclick="testNotification('error')" class="sakip-btn sakip-btn-danger">
                            Test Error
                        </button>
                        <button onclick="testNotification('warning')" class="sakip-btn sakip-btn-secondary">
                            Test Warning
                        </button>
                        <button onclick="testNotification('info')" class="sakip-btn sakip-btn-primary">
                            Test Info
                        </button>
                    </div>
                </div>

                <!-- Test Data Table -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Test Data Table</h2>
                    <div id="test-table" 
                         data-sakip-datatable
                         data-type="indicator"
                         data-api-url="{{ route('sakip.api.datatables.indicator') }}"
                         data-searchable="true"
                         data-exportable="true">
                    </div>
                </div>

                <!-- Test Dashboard -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Test Dashboard</h2>
                    <div id="test-dashboard" 
                         data-sakip-dashboard
                         data-api-url="{{ route('sakip.api.dashboard.data') }}"
                         data-period="current_year">
                    </div>
                </div>

                <!-- Test Configuration -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Test Configuration</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre id="config-output" class="text-sm text-gray-600"></pre>
                    </div>
                </div>

                <!-- Test API Calls -->
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-700 mb-3">Test API Integration</h2>
                    <div class="flex space-x-2">
                        <button onclick="testApi('dashboard')" class="sakip-btn sakip-btn-primary">
                            Test Dashboard API
                        </button>
                        <button onclick="testApi('datatable')" class="sakip-btn sakip-btn-primary">
                            Test DataTable API
                        </button>
                        <button onclick="testApi('configuration')" class="sakip-btn sakip-btn-primary">
                            Test Configuration API
                        </button>
                    </div>
                    <div id="api-results" class="mt-4 bg-gray-50 p-4 rounded-lg hidden">
                        <pre id="api-output" class="text-sm text-gray-600"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Test notification system
    function testNotification(type) {
        if (typeof SAKIP_NOTIFICATION === 'undefined') {
            alert('SAKIP_NOTIFICATION not available');
            return;
        }

        const messages = {
            success: { title: 'Success Test', message: 'This is a success notification test' },
            error: { title: 'Error Test', message: 'This is an error notification test' },
            warning: { title: 'Warning Test', message: 'This is a warning notification test' },
            info: { title: 'Info Test', message: 'This is an info notification test' }
        };

        const config = messages[type];
        SAKIP_NOTIFICATION[type](config.title, config.message);
    }

    // Test API integration
    function testApi(endpoint) {
        const apiUrls = {
            dashboard: '{{ route("sakip.test.dashboard") }}',
            datatable: '{{ route("sakip.test.datatable") }}',
            configuration: '{{ route("sakip.test.configuration") }}'
        };

        const url = apiUrls[endpoint];
        if (!url) {
            alert('Unknown endpoint: ' + endpoint);
            return;
        }

        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('api-results').classList.remove('hidden');
            document.getElementById('api-output').textContent = JSON.stringify(data, null, 2);
            
            if (data.success) {
                SAKIP_NOTIFICATION.success('API Test Success', endpoint + ' API test completed successfully');
            } else {
                SAKIP_NOTIFICATION.error('API Test Failed', endpoint + ' API test failed');
            }
        })
        .catch(error => {
            document.getElementById('api-results').classList.remove('hidden');
            document.getElementById('api-output').textContent = 'Error: ' + error.message;
            SAKIP_NOTIFICATION.error('API Test Error', 'Failed to test ' + endpoint + ' API');
        });
    }

    // Initialize test page
    document.addEventListener('DOMContentLoaded', function() {
        // Display configuration
        if (window.SAKIP_CONFIG) {
            document.getElementById('config-output').textContent = JSON.stringify(window.SAKIP_CONFIG, null, 2);
        } else {
            document.getElementById('config-output').textContent = 'SAKIP_CONFIG not available';
        }

        // Test SAKIP modules availability
        const modules = {
            'SAKIP_DATA_TABLES': typeof SAKIP_DATA_TABLES,
            'SAKIP_HELPERS': typeof SAKIP_HELPERS,
            'SAKIP_NOTIFICATION': typeof SAKIP_NOTIFICATION,
            'SAKIP_DATA_TABLE_INIT': typeof SAKIP_DATA_TABLE_INIT,
            'SAKIP_DASHBOARD': typeof SAKIP_DASHBOARD
        };

        console.log('SAKIP Modules Status:', modules);

        // Display module status
        const moduleStatus = Object.entries(modules)
            .map(([name, status]) => `${name}: ${status}`)
            .join('\n');
        
        console.log('SAKIP Modules:\n' + moduleStatus);

        // Test data table initialization
        if (typeof SAKIP_DATA_TABLE_INIT !== 'undefined') {
            SAKIP_DATA_TABLE_INIT.initFromDataAttributes();
        }

        // Test dashboard initialization
        if (typeof SAKIP_DASHBOARD !== 'undefined') {
            const dashboardElement = document.querySelector('[data-sakip-dashboard]');
            if (dashboardElement && dashboardElement.id) {
                SAKIP_DASHBOARD.init(dashboardElement.id);
            }
        }
    });
</script>
@endpush