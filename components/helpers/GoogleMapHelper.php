<?php
namespace app\components\helpers;

use Yii;
use yii\helpers\Json;

class GoogleMapHelper {

    public static function searchPlace($place, $apiKey = null) {
        if (!$apiKey)
            $apiKey = Yii::$app->params['google_api_key'];

        $query = str_replace(' ', '+', $place);
        try {
            $details = file_get_contents('https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $query . '&key=' . $apiKey);

            $details = Json::decode(utf8_encode($details));
            if ($details['status']=="OK")
                return $details['results'];
        }
        catch (\Exception $e) {
            Yii::error(($e->getMessage()));
        }
        return null;
    }

    public static function getRoutePolyline($from, $to, $transport, $apiKey = null) {
        if (!$apiKey)
            $apiKey = Yii::$app->params['google_api_key'];

        try {
            //bus or train route
            if ($transport == 'bus' || $transport == 'train') {
                $params = [
                    'origin' => $from,
                    'destination' => $to
                ];
                if ($transport == 'train') {
                    $params['mode'] = 'transit';
                    $params['transit_mode'] = 'train';
                }
                $params['key'] = $apiKey;
                $directions = file_get_contents('https://maps.googleapis.com/maps/api/directions/json?'.http_build_query($params));
                $directions = Json::decode(utf8_encode($directions));
                if ($directions['status'] === 'OK')
                    $polyline = $directions['routes'][0]['overview_polyline']['points'];
                else
                    $polyline = null;

            }
            // plane, ferry or  other route
            else {
                $polyline = self::encodePolyline([explode(',', $from), explode(',', $to)]);
            }
            return $polyline;
        }
        catch (\Exception $e) {
            Yii::error(($e->getMessage()));
        }
        return null;
    }

    //encoding and decoding methods taken from https://github.com/emcconville/google-map-polyline-encoding-tool/
    public static function encodePolyline($points) {
        $points = self::flatten($points);
        $encodedString = '';
        $index = 0;
        $previous = [0,0];
        foreach ( $points as $number ) {
            $number = (float)($number);
            $number = (int)round($number * pow(10, 5));
            $diff = $number - $previous[$index % 2];
            $previous[$index % 2] = $number;
            $number = $diff;
            $index++;
            $number = ($number < 0) ? ~($number << 1) : ($number << 1);
            $chunk = '';
            while ( $number >= 0x20 ) {
                $chunk .= chr((0x20 | ($number & 0x1f)) + 63);
                $number >>= 5;
            }
            $chunk .= chr($number + 63);
            $encodedString .= $chunk;
        }
        return $encodedString;
    }

    private static function flatten($array) {
        $flatten = [];
        array_walk_recursive(
            $array, // @codeCoverageIgnore
            function ($current) use (&$flatten) {
                $flatten[] = $current;
            }
        );
        return $flatten;
    }

    public static function registerJs($scriptOptions = [], $options = [], $view = null) {
        if (!$view)
            $view = Yii::$app->controller->view;
        $scriptOptions = array_merge(['key' => Yii::$app->params['google_api_key']], $scriptOptions);
        $view->registerJsFile('https://maps.googleapis.com/maps/api/js?' . http_build_query($scriptOptions), $options);
    }
}
