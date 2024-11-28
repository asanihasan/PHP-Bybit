<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Indicator extends CI_Model {
    function __construct() {
        parent::__construct();
        $this->load->library('request');
    }

    public function rsi($prices, $rsiLength, $movingAvgLength = 1) {
        $rsi = [];
        $gains = [];
        $losses = [];
        $movingAverage = [];
    
        // Make sure prices are in chronological order (oldest to newest)
        $prices = array_reverse($prices);
    
        // Calculate initial gains and losses for the first period
        for ($i = 1; $i <= $rsiLength; $i++) {
            $change = $prices[$i][4] - $prices[$i - 1][4]; // Close price difference
            if ($change > 0) {
                $gains[] = $change;
                $losses[] = 0;
            } else {
                $gains[] = 0;
                $losses[] = abs($change);
            }
        }
    
        // Calculate average gain and loss for the first RSI period
        $averageGain = array_sum($gains) / $rsiLength;
        $averageLoss = array_sum($losses) / $rsiLength;
    
        // Calculate RSI for the first period
        if ($averageLoss == 0) {
            $rsi[] = 100; // Max RSI if there's no loss
        } else {
            $rs = $averageGain / $averageLoss;
            $rsi[] = 100 - (100 / (1 + $rs));
        }
    
        // Calculate RSI for the rest of the periods
        for ($i = $rsiLength + 1; $i < count($prices); $i++) {
            $change = $prices[$i][4] - $prices[$i - 1][4]; // Close price difference
    
            $gain = $change > 0 ? $change : 0;
            $loss = $change < 0 ? abs($change) : 0;
    
            // Smoothed averages
            $averageGain = (($averageGain * ($rsiLength - 1)) + $gain) / $rsiLength;
            $averageLoss = (($averageLoss * ($rsiLength - 1)) + $loss) / $rsiLength;
    
            // RSI calculation
            if ($averageLoss == 0) {
                $rsi[] = 100;
            } else {
                $rs = $averageGain / $averageLoss;
                $rsi[] = 100 - (100 / (1 + $rs));
            }
        }
    
        // Calculate moving average of RSI
        $queue = [];
        $sum = 0;
    
        foreach ($rsi as $value) {
            $queue[] = $value;
            $sum += $value;
    
            if (count($queue) > $movingAvgLength) {
                $sum -= array_shift($queue); // Remove the oldest value from the sum
            }
    
            if (count($queue) == $movingAvgLength) {
                $movingAverage[] = $sum / $movingAvgLength;
            } else {
                // For the initial period where the queue isn't filled yet
                $movingAverage[] = $sum / count($queue);
            }
        }
    
        return array_reverse($movingAverage);
    }

    public function ma($prices, $smaLength) {
        $sma = [];
    
        // Make sure prices are in chronological order (oldest to newest)
        $prices = array_reverse($prices);
    
        // Calculate SMA for the first valid period
        for ($i = $smaLength - 1; $i < count($prices); $i++) {
            $sum = 0;
    
            // Sum the closing prices for the current window
            for ($j = $i - $smaLength + 1; $j <= $i; $j++) {
                $sum += $prices[$j][4]; // Closing price at index 4
            }
    
            // Calculate the average (SMA) for this window
            $sma[] = $sum / $smaLength;
        }
    
        // Ensure the length of the $sma array matches $prices - $smaLength
        return array_reverse($sma);
    }

    public function wma($prices, $wmaLength) {
        $wma = [];
    
        // Reverse prices to process in chronological order
        $prices = array_reverse($prices);
    
        // Calculate the denominator (sum of weights) for the WMA formula
        $weightSum = array_sum(range(1, $wmaLength));
    
        // Loop through prices and calculate WMA for each valid period
        for ($i = $wmaLength - 1; $i < count($prices); $i++) {
            $weightedSum = 0;
    
            // Calculate weighted sum for the current window
            for ($j = 0; $j < $wmaLength; $j++) {
                $weightedSum += $prices[$i - $j][4] * ($wmaLength - $j); // Close price at index 4
            }
    
            // Compute the WMA and add to the result array
            $wma[] = $weightedSum / $weightSum;
        }
    
        // Reverse the WMA to match the original prices order
        return array_reverse($wma);
    }

    public function ema($prices, $emaLength) {
        $ema = [];
        $smoothingFactor = 2 / ($emaLength + 1);
    
        // Reverse prices to process in chronological order
        $prices = array_reverse($prices);
    
        // Step 1: Calculate the initial EMA value (use SMA of the first emaLength prices)
        $initialSum = 0;
        for ($i = 0; $i < $emaLength; $i++) {
            $initialSum += $prices[$i][4]; // Close price at index 4
        }
        $previousEMA = $initialSum / $emaLength;
        $ema[] = $previousEMA;
    
        // Step 2: Calculate the subsequent EMA values
        for ($i = $emaLength; $i < count($prices); $i++) {
            $currentPrice = $prices[$i][4]; // Close price at index 4
            $currentEMA = ($currentPrice * $smoothingFactor) + ($previousEMA * (1 - $smoothingFactor));
            $ema[] = $currentEMA;
            $previousEMA = $currentEMA;
        }
    
        // Reverse the EMA to match the original prices order
        return array_reverse($ema);
    }

    public function atr($prices, $atrLength) {
        $atr = [];
        $tr = [];
        $scaledATR = [];
    
        // Reverse prices to process in chronological order
        $prices = array_reverse($prices);
    
        // Step 1: Calculate True Range (TR) for each period
        for ($i = 0; $i < count($prices); $i++) {
            if ($i == 0) {
                // First TR is simply the high - low
                $tr[] = $prices[$i][2] - $prices[$i][3]; // High - Low
            } else {
                $highLow = $prices[$i][2] - $prices[$i][3]; // High - Low
                $highClosePrev = abs($prices[$i][2] - $prices[$i - 1][4]); // High - Previous Close
                $lowClosePrev = abs($prices[$i][3] - $prices[$i - 1][4]); // Low - Previous Close
                $tr[] = max($highLow, $highClosePrev, $lowClosePrev);
            }
        }
    
        // Step 2: Calculate the initial ATR value (use SMA of the first atrLength TR values)
        $initialSum = 0;
        for ($i = 0; $i < $atrLength; $i++) {
            $initialSum += $tr[$i];
        }
        $previousATR = $initialSum / $atrLength;
        $atr[] = $previousATR;
    
        // Add scaled ATR for the initial ATR period
        $scaledATR[] = 10 * ($previousATR / $prices[$atrLength - 1][4]); // Price at the end of the initial period
    
        // Step 3: Calculate the subsequent ATR values
        for ($i = $atrLength; $i < count($tr); $i++) {
            $currentATR = (($previousATR * ($atrLength - 1)) + $tr[$i]) / $atrLength;
            $atr[] = $currentATR;
            $previousATR = $currentATR;
    
            // Calculate the scaled ATR value for the current period
            $scaledATR[] = 10 * ($currentATR / $prices[$i][4]); // Current price
        }
    
        // Reverse the scaled ATR to match the original prices order
        return array_reverse($scaledATR);
    }
}