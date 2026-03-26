<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 5px;
            text-align: center;
            font-size: 8px;
            color: #333;
        }
        .container {
            width: 100%;
            display: block;
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
        }
        .barcode {
            margin: 0 auto;
            width: 90%;
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
                    $qr = \Endroid\QrCode\QrCode::create($asset->asset_tag)->setSize(100)->setMargin(0);
                    $writer = new \Endroid\QrCode\Writer\PngWriter();
                    $qrData = $writer->write($qr)->getDataUri();
                @endphp
                <img class="qr" src="{{ $qrData }}" alt="qr" style="width: 50px; height: 50px;" />
            </div>
            @endif
        </div>
        
        <div style="margin-top: 4px; border-top: 0.5px solid #ccc; padding-top: 2px;">
            LC ASSETS • IT DEPT
        </div>
    </div>
</body>
</html>
