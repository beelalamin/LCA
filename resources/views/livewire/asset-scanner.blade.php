<div>
    <style>
        #reader-container {
            position: relative;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #reader-video {
            width: 100% !important;
            height: auto !important;
            object-fit: cover;
        }
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid rgba(245, 158, 11, 0.3);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .scanner-laser {
            width: 80%;
            height: 2px;
            background: #f59e0b;
            box-shadow: 0 0 8px #f59e0b;
            position: absolute;
            animation: scan 2s infinite ease-in-out;
        }
        @keyframes scan {
            0%, 100% { transform: translateY(-100px); opacity: 0; }
            50% { transform: translateY(100px); opacity: 1; }
        }
        .scanner-controls {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .scanner-btn {
            background-color: rgb(245, 158, 11) !important;
            color: white !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0.75rem !important;
            font-weight: 700 !important;
            font-size: 0.875rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.2s !important;
            text-align: center;
            width: 100%;
        }
        .scanner-btn:hover { background-color: rgb(217, 119, 6) !important; }
        .scanner-btn-secondary {
            background-color: #374151 !important;
            color: white !important;
        }
        #camera-select {
            background-color: #111827 !important;
            color: white !important;
            border: 1px solid #374151 !important;
            border-radius: 0.5rem !important;
            padding: 8px 12px !important;
            font-size: 0.85rem !important;
            width: 100%;
        }
    </style>

    <div wire:ignore>
        <div id="reader-container">
            <div id="reader-video-container" style="width: 100%;"></div>
            <div id="scanner-ui-placeholder" class="flex flex-col items-center">
                <div class="animate-pulse flex flex-col items-center space-y-2">
                    <x-heroicon-o-camera class="w-12 h-12 text-amber-500/20" />
                    <button onclick="startScanning()" class="scanner-btn">{{ __('Start Camera') }}</button>
                </div>
            </div>
            <div id="scanning-overlay" class="scanner-overlay" style="display: none;">
                <div class="scanner-laser"></div>
            </div>
        </div>

        <div class="scanner-controls">
            <select id="camera-select" style="display: none;"></select>
            <button id="stop-btn" onclick="stopScanning()" class="scanner-btn scanner-btn-secondary" style="display: none;">{{ __('Stop Camera') }}</button>
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
                >
                @error('asset_tag') <span class="text-xs text-red-500 mt-1 block px-1">{{ $message }}</span> @enderror
            </div>
            <button 
                wire:click="findAsset"
                class="scanner-btn !w-fit h-fit"
            >
                {{ __('Search') }}
            </button>
        </div>
    </div>

    <script>
        window.scannerObj = window.scannerObj || null;

        async function startScanning() {
            try {
                if (!window.scannerObj) {
                    window.scannerObj = new Html5Qrcode("reader-video-container");
                }

                const cameras = await Html5Qrcode.getCameras();
                if (cameras && cameras.length > 0) {
                    const select = document.getElementById('camera-select');
                    select.innerHTML = '';
                    cameras.forEach(cam => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.text = cam.label;
                        select.appendChild(opt);
                    });
                    
                    if (cameras.length > 1) select.style.display = 'block';

                    const config = {
                        fps: 20,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0,
                    };

                    await window.scannerObj.start(
                        { facingMode: "environment" }, 
                        config, 
                        (text) => {
                            console.log("Scanned:", text);
                            @this.set('asset_tag', text);
                            @this.findAsset();
                        }
                    );

                    document.getElementById('scanner-ui-placeholder').style.display = 'none';
                    document.getElementById('scanning-overlay').style.display = 'flex';
                    document.getElementById('stop-btn').style.display = 'block';
                }
            } catch (err) {
                console.error("Scanner Error:", err);
            }
        }

        async function stopScanning() {
            if (window.scannerObj && window.scannerObj.isScanning) {
                try {
                    await window.scannerObj.stop();
                    document.getElementById('scanner-ui-placeholder').style.display = 'flex';
                    document.getElementById('scanning-overlay').style.display = 'none';
                    document.getElementById('stop-btn').style.display = 'none';
                } catch (e) {
                    console.warn("Stop error:", e);
                }
            }
        }

        // Modal triggers
        function handleOpen(e) {
            if (!e.detail || e.detail.id === 'scanner-modal') {
                setTimeout(startScanning, 500);
            }
        }

        function handleClose(e) {
            if (!e.detail || e.detail.id === 'scanner-modal') {
                stopScanning();
            }
        }

        window.addEventListener('modal-opened', handleOpen);
        window.addEventListener('open-modal', handleOpen);
        window.addEventListener('filament-modal-open', handleOpen);

        window.addEventListener('modal-closed', handleClose);
        window.addEventListener('close-modal', handleClose);
        window.addEventListener('filament-modal-close', handleClose);

        // Auto-start if it's already visible (direct load/refresh)
        setTimeout(startScanning, 1000);

        // Cleanup on page hide or navigation
        window.addEventListener('pagehide', () => stopScanning());
        window.addEventListener('beforeunload', () => stopScanning());
    </script>
</div>
