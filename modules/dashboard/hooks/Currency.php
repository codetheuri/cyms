<?php

namespace dashboard\hooks; 

use Yii;

class Currency extends \yii\base\Component
{
    /**
     * Fetches the current USD to KES exchange rate.
     * Uses caching to ensure we only hit the API once every 12 hours.
     */
    public static function getUsdToKesRate()
    {
        $cacheKey = 'usd_to_kes_rate';
        $rate = Yii::$app->cache->get($cacheKey);

        if ($rate === false) {
            // Get the dynamic fallback rate set by the Admin
            $fallbackRate = (float) Yii::$app->config->get('fallback_exchange_rate') ?: 130.00;

            $url = "https://open.er-api.com/v6/latest/USD";
            
            try {
                $json = @file_get_contents($url); // Added @ to suppress standard PHP warnings if offline
                if ($json !== false) {
                    $data = json_decode($json, true);

                    if (isset($data['rates']['KES'])) {
                        $rate = (float) $data['rates']['KES'];
                        
                        // Save to cache for 12 hours (43200 seconds)
                        Yii::$app->cache->set($cacheKey, $rate, 43200);
                    } else {
                        // API responded, but KES was missing
                        $rate = $fallbackRate; 
                    }
                } else {
                    // file_get_contents failed (No internet)
                    $rate = $fallbackRate;
                }
            } catch (\Exception $e) {
                // Emergency Fallback if an exception is thrown
                Yii::error("Currency API failed: " . $e->getMessage());
                $rate = $fallbackRate; 
            }
        }

        return $rate;
    }
}