<div class="space-y-4">
    <style>
        #reader__scan_region {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }
        #reader {
            border: none !important;
        }
        #reader video {
            border-radius: 12px !important;
            margin: 0 auto !important;
        }
    </style>
    <div wire:ignore 
         id="reader" 
         class="w-full overflow-hidden border-2 border-primary-500 rounded-xl shadow-md bg-gray-900" 
         style="min-height: 400px;">
    </div>

    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Manual Entry</label>
        <div class="flex gap-x-2">
            <input 
                type="text" 
                wire:model="asset_tag"
                placeholder="Tag or Serial #"
                class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                @keydown.enter="$wire.findAsset()"
            >
            <button 
                wire:click="findAsset"
                class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors"
            >
                Search
            </button>
        </div>
    </div>

    @pushOnce('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            @this.set('asset_tag', decodedText);
            @this.findAsset();
        }

        function initScanner() {
             const element = document.getElementById('reader');
             if (!element) return;

             const html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: {width: 250, height: 250},
                    rememberLastUsedCamera: true,
                    aspectRatio: 1.0
                },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess);
        }

        // Handle page load
        document.addEventListener('DOMContentLoaded', initScanner);
        
        // Handle modal opens/Livewire refreshes
        window.addEventListener('open-modal', (event) => {
            if (event.detail.id === 'scanner-modal') {
                 setTimeout(initScanner, 300);
            }
        });
        
        // Fallback for direct page access
        setTimeout(initScanner, 500);
    </script>
    @endPushOnce
</div>
