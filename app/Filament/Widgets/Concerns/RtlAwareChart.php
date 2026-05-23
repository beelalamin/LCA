<?php

namespace App\Filament\Widgets\Concerns;

trait RtlAwareChart
{
    protected function isRtl(): bool
    {
        return app()->getLocale() === 'ar';
    }

    protected function rtlAware(array $options): array
    {
        if (! $this->isRtl()) {
            return $options;
        }

        $type = $options['chart']['type'] ?? null;
        $isHorizontalBar = $type === 'bar' && ($options['plotOptions']['bar']['horizontal'] ?? false);

        $options['chart']['fontFamily'] = "'Noto Sans Arabic', " . ($options['chart']['fontFamily'] ?? 'inherit');

        if (isset($options['legend'])) {
            $options['legend']['horizontalAlign'] = 'right';
            $options['legend']['fontFamily'] = "'Noto Sans Arabic', sans-serif";
        }

        if ($type === 'bar') {
            if ($isHorizontalBar) {
                $options['yaxis']['opposite'] = true;
                $options['plotOptions']['bar']['borderRadiusApplication'] = 'around';
                if (isset($options['dataLabels']['offsetX'])) {
                    $options['dataLabels']['offsetX'] = -($options['dataLabels']['offsetX']);
                }
            } else {
                $options['yaxis']['opposite'] = true;
            }
        }

        return $options;
    }
}
