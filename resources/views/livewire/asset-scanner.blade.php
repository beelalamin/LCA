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
            max-height: 400px;
        }
        /* Beautify all library buttons */
        #reader button {
            background-color: rgb(245, 158, 11) !important; /* Amber 500 */
            color: white !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.75rem !important;
            font-weight: 700 !important;
            font-size: 0.875rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            margin: 8px auto !important;
            display: block !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
            text-transform: uppercase !important;
            letter-spacing: 0.025em !important;
        }
        #reader button:hover {
            background-color: rgb(217, 119, 6) !important; /* Amber 600 */
            transform: translateY(-1px);
        }
        /* Beautify the 'Select File' link */
        #reader a {
            color: rgb(156, 163, 175) !important; 
            text-decoration: none !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            margin-top: 12px !important;
            display: block !important;
            text-align: center !important;
            opacity: 0.8 !important;
        }
        #reader a:hover {
            opacity: 1 !important;
            text-decoration: underline !important;
        }
        /* Hide unnecessary text/status messages */
        #reader__status_span, #reader__dashboard_section_csr span {
            display: none !important;
        }
        /* Improve the camera selector */
        #reader select {
            background-color: #111827 !important; /* gray-900 */
            color: white !important;
            border: 1px solid #374151 !important;
            border-radius: 0.5rem !important;
            padding: 6px 10px !important;
            font-size: 0.8rem !important;
            margin: 10px auto !important;
            display: block !important;
            width: 80% !important;
        }
    </style>
    <div wire:ignore 
         id="reader" 
         class="w-full overflow-hidden border-2 border-amber-500/10 rounded-xl shadow-inner bg-gray-950 flex flex-col justify-center" 
         style="min-height: 350px;">
         <div id="scanner-placeholder" class="flex flex-col items-center justify-center p-8 space-y-4">
             <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>
             <p class="text-xs text-amber-500/50 font-bold uppercase tracking-widest">{{ __('Initializing Camera...') }}</p>
         </div>
    </div>

    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm mt-4">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 tracking-wider">{{ __('Manual Entry') }}</label>
        <div class="flex gap-x-2">
            <div class="flex-grow">
                <input 
                    type="text" 
                    wire:model="asset_tag"
                    placeholder="{{ __('Tag or Serial #') }}"
                    class="block w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-amber-500 focus:ring-amber-500 transition-colors"
                    @keydown.enter="$wire.findAsset()"
                >
                @error('asset_tag') <span class="text-xs text-red-500 mt-1 block px-1">{{ $message }}</span> @enderror
            </div>
            <button 
                wire:click="findAsset"
                class="inline-flex items-center px-5 py-2 text-sm font-bold text-white bg-amber-600 rounded-lg hover:bg-amber-700 active:bg-amber-800 transition-all shadow-sm h-fit"
            >
                {{ __('Search') }}
            </button>
        </div>
    </div>

    <script>
        window.html5QrcodeScanner = window.html5QrcodeScanner || null;

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan Result: ${decodedText}`);
            @this.set('asset_tag', decodedText);
            @this.findAsset();
        }

        async function initScanner() {
             const element = document.getElementById('reader');
             if (!element) return;

             // Ensure the div is reset if cleared by library
             if (element.innerHTML.trim() === '') {
                 element.innerHTML = '<div id="scanner-placeholder" class="flex flex-col items-center justify-center p-8 space-y-4">' +
                     '<div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-500"></div>' +
                     '<p class="text-xs text-amber-500/50 font-bold uppercase tracking-widest">{{ __('Initializing Camera...') }}</p>' +
                 '</div>';
             }

             const placeholder = document.getElementById('scanner-placeholder');

             // Clean up previous instance if exists fully
             if (window.html5QrcodeScanner) {
                 try {
                     await window.html5QrcodeScanner.clear();
                 } catch (e) {
                     console.warn("Expected cleanup", e);
                 }
                 window.html5QrcodeScanner = null;
             }

             if (placeholder) placeholder.style.display = 'flex';

             window.html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 20, 
                    qrbox: {width: 250, height: 250},
                    rememberLastUsedCamera: true,
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: true,
                    videoConstraints: {
                        facingMode: "environment"
                    },
                    formatsToSupport: [0, 2, 3, 4, 5, 7, 8, 9, 14, 15]
                },
                /* verbose= */ false
            );
            
            window.html5QrcodeScanner.render(onScanSuccess);

            setTimeout(() => {
                if (placeholder) placeholder.style.display = 'none';
            }, 1000);
        }

        async function cleanupScanner() {
            if (window.html5QrcodeScanner) {
                try {
                    await window.html5QrcodeScanner.clear();
                    window.html5QrcodeScanner = null;
                    console.log('Scanner destroyed');
                } catch (e) {
                    console.warn("Cleanup warning", e);
                }
            }
        }

        // Handle modal lifecycle
        const modalEvents = ['modal-closed', 'close-modal', 'filament-modal-close', 'filament-modal-closed'];
        modalEvents.forEach(evt => window.addEventListener(evt, (e) => {
            if (!e.detail || e.detail.id === 'scanner-modal') cleanupScanner();
        }));

        const openEvents = ['modal-opened', 'open-modal', 'filament-modal-open'];
        openEvents.forEach(evt => window.addEventListener(evt, (e) => {
            if (!e.detail || e.detail.id === 'scanner-modal') {
                setTimeout(initScanner, 400);
            }
        }));

        // Initial check for direct load
        setTimeout(initScanner, 600);
    </script>
</div>
