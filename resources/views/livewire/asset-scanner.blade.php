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
            width: 100% !important;
            object-fit: cover !important;
        }
        /* Style the default buttons from the library */
        #reader button {
            background-color: rgb(245, 158, 11) !important; /* Amber 500 */
            color: white !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            font-weight: 700 !important;
            font-size: 0.875rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            margin: 10px auto !important;
            display: block !important;
        }
        #reader button:hover {
            background-color: rgb(217, 119, 6) !important; /* Amber 600 */
        }
        #reader a {
            color: rgb(245, 158, 11) !important;
            text-decoration: underline !important;
            font-size: 0.875rem !important;
            margin-top: 10px !important;
            display: block !important;
            text-align: center !important;
        }
    </style>
    <div wire:ignore 
         id="reader" 
         class="w-full overflow-hidden border-2 border-amber-500/20 rounded-xl shadow-inner bg-gray-950" 
         style="min-height: 350px;">
    </div>

    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">{{ __('Manual Entry') }}</label>
        <div class="flex gap-x-2">
            <input 
                type="text" 
                wire:model="asset_tag"
                placeholder="{{ __('Tag or Serial #') }}"
                class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                @keydown.enter="$wire.findAsset()"
            >
            <button 
                wire:click="findAsset"
                class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors shadow-sm"
            >
                {{ __('Search') }}
            </button>
        </div>
    </div>

    <script>
        let html5QrcodeScanner = null;

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan Result: ${decodedText}`);
            @this.set('asset_tag', decodedText);
            @this.findAsset();
        }

        async function initScanner() {
             const element = document.getElementById('reader');
             if (!element) return;

             // Clean up previous instance if exists
             if (html5QrcodeScanner) {
                 try {
                     await html5QrcodeScanner.clear();
                 } catch (e) {
                     console.error("Failed to clear scanner", e);
                 }
                 html5QrcodeScanner = null;
             }

             html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 20, // Increased for better barcode detection
                    qrbox: {width: 250, height: 250},
                    rememberLastUsedCamera: true,
                    aspectRatio: 1.0,
                    videoConstraints: {
                        facingMode: "environment"
                    },
                    formatsToSupport: [ 
                        0, // QR_CODE
                        1, // AZTEC
                        2, // CODABAR
                        3, // CODE_39
                        4, // CODE_93
                        5, // CODE_128
                        6, // DATA_MATRIX
                        7, // EAN_8
                        8, // EAN_13
                        9, // ITF
                        10, // MAXICODE
                        11, // PDF_417
                        12, // RSS_14
                        13, // RSS_EXPANDED
                        14, // UPC_A
                        15, // UPC_E
                        16  // UPC_EAN_EXTENSION
                    ]
                },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess);
        }

        // Handle modal opens/Livewire refreshes
        window.addEventListener('modal-opened', (event) => {
            if (event.detail.id === 'scanner-modal') {
                 console.log('Scanner modal opened, initializing...');
                 setTimeout(initScanner, 400);
            }
        });

        window.addEventListener('open-modal', (event) => {
            if (event.detail.id === 'scanner-modal') {
                 console.log('Scanner open-modal triggered, initializing...');
                 setTimeout(initScanner, 400);
            }
        });

        // Cleanup on modal close
        window.addEventListener('modal-closed', async (event) => {
            if (event.detail.id === 'scanner-modal' && html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                    console.log('Scanner cleared on modal close');
                } catch (e) {
                    console.error("Failed to clear scanner on close", e);
                }
            }
        });
        
        // Fallback for direct page access
        setTimeout(initScanner, 500);
    </script>
</div>
