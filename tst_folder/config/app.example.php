<?php
declare(strict_types=1);

return [
    'app' => [
        'name' => 'Market Radar',
        'environment' => 'development',
    ],
    'market_data' => [
        'provider' => getenv('MARKET_DATA_PROVIDER') ?: 'yahoo_delayed',
        'alpaca_key' => getenv('ALPACA_API_KEY') ?: '',
        'alpaca_secret' => getenv('ALPACA_API_SECRET') ?: '',
        'alpaca_feed' => getenv('ALPACA_DATA_FEED') ?: 'iex',
        'default_interval' => '1m',
        'default_range' => '1d',
        'watchlist' => ['AAPL','MSFT','NVDA','AMD','TSLA','META'],
        'refresh_seconds' => 60,
        'minimum_refresh_seconds' => 30,
        'request_timeout_seconds' => 8,
        'max_attempts' => 2,
        'minimum_provider_gap_ms' => 120,
        'cache_ttl_seconds' => 60,
    ],
    'storage' => [
        'sqlite_path' => __DIR__ . '/../storage/data/market_radar.sqlite',
        'cache_path' => __DIR__ . '/../storage/cache',
        'cache_max_age_hours' => 24,
        'scan_history_days' => 30,
        'max_cache_mb' => 250,
        'historical_format' => getenv('HISTORICAL_STORAGE_FORMAT') ?: 'sqlite',
        'parquet_path' => getenv('PARQUET_DATA_PATH') ?: __DIR__ . '/../storage/data/alpaca',
        'python_binary' => getenv('PYTHON_BINARY') ?: 'python3',
    ],
];
