<?php

namespace App\Support;

class ProjectStage
{
    public static function ordered(): array
    {
        return ['market_research', 'website_operations', 'content_creative', 'traffic_growth'];
    }

    public static function label(string $stage): string
    {
        return [
            'market_research' => '市场研究',
            'website_operations' => '网站运营',
            'content_creative' => '内容创意',
            'traffic_growth' => '流量增长',
        ][$stage] ?? $stage;
    }
}
