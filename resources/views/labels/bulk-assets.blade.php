<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: {{ $settings['columns'] > 1 ? '10mm' : '0' }};
            background-color: white;
        }
        .labels-grid {
            width: 100%;
            display: block;
            @if($settings['columns'] > 1)
            display: flex;
            flex-wrap: wrap;
            gap: 2mm;
            @endif
        }
        .container {
            width: {{ $settings['width'] }}pt;
            height: {{ $settings['height'] }}pt;
            padding: {{ $settings['padding'] }}px;
            text-align: center;
            font-size: 8px;
            color: #333;
            box-sizing: border-box;
            display: inline-block;
            vertical-align: top;
            position: relative;
            overflow: hidden;
            @if($settings['columns'] > 1)
            border: 0.1px solid #ccc;
            margin-bottom: 2mm;
            @endif
            @if($settings['columns'] == 1)
            page-break-after: always;
            @endif
        }
        .tag {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .name {
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .barcode {
            margin: 0 auto;
            width: 100%;
            height: 25px;
            display: block;
        }
        .codes {
             display: table;
             width: 100%;
        }
        .column {
            display: table-cell;
            vertical-align: middle;
        }
        .qr {
            width: 40px;
            height: 40px;
        }
    </style>
</head>
<body>
    <div class="labels-grid">
        @foreach($assets as $asset)
        <div class="container">
            <div class="tag">{{ $asset->asset_tag }}</div>
            <div class="name">{{ $asset->name }}</div>
            
            <div class="codes">
                @if($type === 'barcode' || $type === 'both')
                <div class="column" style="width: {{ $type === 'both' ? '70%' : '100%' }};">
                    <img class="barcode" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($asset->asset_tag, 'C128') }}" alt="barcode" />
                    <div style="margin-top: 2px;">{{ $asset->serial_number ?: 'N/A' }}</div>
                </div>
                @endif

                @if($type === 'qrcode' || $type === 'both')
                <div class="column" style="width: {{ $type === 'both' ? '30%' : '100%' }};">
                    @php
                        try {
                            $qr = \Endroid\QrCode\QrCode::create($asset->asset_tag)->setSize(100)->setMargin(0);
                            $writer = new \Endroid\QrCode\Writer\PngWriter();
                            $qrData = $writer->write($qr)->getDataUri();
                        } catch (\Exception $e) {
                            $qrData = '';
                        }
                    @endphp
                    @if($qrData)
                    <img class="qr" src="{{ $qrData }}" alt="qr" style="width: {{ $type === 'both' ? '45px' : '60px' }}; height: {{ $type === 'both' ? '45px' : '60px' }};" />
                    @endif
                </div>
                @endif
            </div>
            
            <div style="margin-top: 4px; border-top: 0.5px solid #ccc; padding-top: 2px; font-size: 6px;">
                LC ASSETS • IT DEPT
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
