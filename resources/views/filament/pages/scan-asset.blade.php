<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center space-y-4">
        <div id="reader" style="width: 100%; max-width: 600px; min-height: 400px;" class="overflow-hidden border-2 border-primary-500 rounded-xl shadow-lg bg-gray-900"></div>
        

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
