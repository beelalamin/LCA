<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center space-y-4">
        <div id="reader" style="width: 100%; max-width: 600px; min-height: 400px;" class="overflow-hidden border-2 border-primary-500 rounded-xl shadow-lg bg-gray-900"></div>
        
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-md w-full max-width-600">
            <h3 class="text-lg font-semibold mb-4">{{ __('Alternative: Manual Search') }}</h3>
            <div class="flex space-x-2">
                <input 
                    type="text" 
                    wire:model.defer="asset_tag"
                    placeholder="Enter Tag or Serial Number"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                <button 
                    wire:click="findAsset"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    Search
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Once we have a code, notify Livewire
            @this.set('asset_tag', decodedText);
            @this.findAsset();
            
            // stop scanning
            html5QrcodeScanner.clear();
        }

        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            false
        );
        html5QrcodeScanner.render(onScanSuccess);
    </script>
    @endpush
</x-filament-panels::page>
