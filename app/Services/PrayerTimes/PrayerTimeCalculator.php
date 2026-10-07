<?php

namespace App\Services\PrayerTimes;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Astronomical prayer-times calculator (solar position per the U.S. Naval
 * Observatory approximation, as used by PrayTimes.org and AlAdhan).
 */
class PrayerTimeCalculator
{
    /**
     * fajr/isha are twilight angles in degrees; isha_minutes replaces the isha angle when set.
     *
     * @var array<string, array{fajr: float, isha: float|null, isha_minutes?: int}>
     */
    public const METHODS = [
        'mwl' => ['fajr' => 18.0, 'isha' => 17.0],
        'isna' => ['fajr' => 15.0, 'isha' => 15.0],
        'egyptian' => ['fajr' => 19.5, 'isha' => 17.5],
        'makkah' => ['fajr' => 18.5, 'isha' => null, 'isha_minutes' => 90],
        'karachi' => ['fajr' => 18.0, 'isha' => 18.0],
        'gulf' => ['fajr' => 19.5, 'isha' => null, 'isha_minutes' => 90],
        'kuwait' => ['fajr' => 18.0, 'isha' => 17.5],
        'qatar' => ['fajr' => 18.0, 'isha' => null, 'isha_minutes' => 90],
        'singapore' => ['fajr' => 20.0, 'isha' => 18.0],
        'france' => ['fajr' => 12.0, 'isha' => 12.0],
        'turkey' => ['fajr' => 18.0, 'isha' => 17.0],
    ];

    public const ASR_FACTORS = ['standard' => 1, 'hanafi' => 2];

    public const HIGH_LATITUDE_RULES = ['middle_of_night', 'seventh_of_night', 'twilight_angle', 'none'];

    private const SUNRISE_ANGLE = 0.833;

    private float $latitude;

    private float $longitude;

    private float $julianDate;

    /**
     * @param  array<string, int>  $adjustments  minutes per time key
     * @return array<string, CarbonImmutable> UTC instants keyed fajr, sunrise, dhuhr, asr, maghrib, isha
     */
    public function calculate(
        float $latitude,
        float $longitude,
        int $year,
        int $month,
        int $day,
        string $method = 'egyptian',
        string $asrMethod = 'standard',
        string $highLatitudeRule = 'middle_of_night',
        array $adjustments = [],
    ): array {
        $params = self::METHODS[$method] ?? throw new InvalidArgumentException("Unsupported calculation method [{$method}].");
        $asrFactor = self::ASR_FACTORS[$asrMethod] ?? throw new InvalidArgumentException("Unsupported asr method [{$asrMethod}].");

        if (! in_array($highLatitudeRule, self::HIGH_LATITUDE_RULES, true)) {
            throw new InvalidArgumentException("Unsupported high latitude rule [{$highLatitudeRule}].");
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->julianDate = $this->julian($year, $month, $day) - $longitude / (15 * 24);

        $times = [
            'fajr' => $this->sunAngleTime($params['fajr'], 5 / 24, true),
            'sunrise' => $this->sunAngleTime(self::SUNRISE_ANGLE, 6 / 24, true),
            'dhuhr' => $this->midDay(12 / 24),
            'asr' => $this->asrTime($asrFactor, 13 / 24),
            'maghrib' => $this->sunAngleTime(self::SUNRISE_ANGLE, 18 / 24, false),
            'isha' => $params['isha'] !== null ? $this->sunAngleTime($params['isha'], 18 / 24, false) : 0.0,
        ];

        foreach ($times as $key => $time) {
            $times[$key] = $time - $longitude / 15;
        }

        if (isset($params['isha_minutes'])) {
            $times['isha'] = $times['maghrib'] + $params['isha_minutes'] / 60;
        }

        if ($highLatitudeRule !== 'none') {
            $night = $this->hourDiff($times['maghrib'], $times['sunrise']);

            $times['fajr'] = $this->adjustHighLatitude(
                $times['fajr'], $times['sunrise'], $params['fajr'], $night, $highLatitudeRule, true
            );

            if ($params['isha'] !== null) {
                $times['isha'] = $this->adjustHighLatitude(
                    $times['isha'], $times['maghrib'], $params['isha'], $night, $highLatitudeRule, false
                );
            }
        }

        $midnight = CarbonImmutable::create($year, $month, $day, 0, 0, 0, 'UTC');
        $result = [];

        foreach ($times as $key => $hours) {
            $minutes = (int) floor($hours * 60 + 0.5) + (int) ($adjustments[$key] ?? 0);
            $result[$key] = $midnight->addMinutes($minutes);
        }

        return $result;
    }

    private function julian(int $year, int $month, int $day): float
    {
        if ($month <= 2) {
            $year--;
            $month += 12;
        }

        $a = intdiv($year, 100);
        $b = 2 - $a + intdiv($a, 4);

        return floor(365.25 * ($year + 4716)) + floor(30.6001 * ($month + 1)) + $day + $b - 1524.5;
    }

    /**
     * @return array{declination: float, equation: float}
     */
    private function sunPosition(float $jd): array
    {
        $d = $jd - 2451545.0;
        $g = $this->fixAngle(357.529 + 0.98560028 * $d);
        $q = $this->fixAngle(280.459 + 0.98564736 * $d);
        $l = $this->fixAngle($q + 1.915 * $this->sin($g) + 0.020 * $this->sin(2 * $g));
        $e = 23.439 - 0.00000036 * $d;

        $rightAscension = $this->arctan2($this->cos($e) * $this->sin($l), $this->cos($l)) / 15;

        return [
            'declination' => $this->arcsin($this->sin($e) * $this->sin($l)),
            'equation' => $q / 15 - $this->fixHour($rightAscension),
        ];
    }

    private function midDay(float $time): float
    {
        return $this->fixHour(12 - $this->sunPosition($this->julianDate + $time)['equation']);
    }

    private function sunAngleTime(float $angle, float $time, bool $beforeNoon): float
    {
        $declination = $this->sunPosition($this->julianDate + $time)['declination'];
        $noon = $this->midDay($time);

        $cosine = (-$this->sin($angle) - $this->sin($declination) * $this->sin($this->latitude))
            / ($this->cos($declination) * $this->cos($this->latitude));

        $hourAngle = $this->arccos(max(-1.0, min(1.0, $cosine))) / 15;

        return $noon + ($beforeNoon ? -$hourAngle : $hourAngle);
    }

    private function asrTime(int $factor, float $time): float
    {
        $declination = $this->sunPosition($this->julianDate + $time)['declination'];
        $angle = -$this->arccot($factor + $this->tan(abs($this->latitude - $declination)));

        return $this->sunAngleTime($angle, $time, false);
    }

    private function adjustHighLatitude(float $time, float $base, float $angle, float $night, string $rule, bool $beforeBase): float
    {
        $portion = match ($rule) {
            'twilight_angle' => $angle / 60,
            'seventh_of_night' => 1 / 7,
            default => 1 / 2,
        } * $night;

        $diff = $beforeBase ? $this->hourDiff($time, $base) : $this->hourDiff($base, $time);

        if (is_nan($time) || $diff > $portion) {
            return $base + ($beforeBase ? -$portion : $portion);
        }

        return $time;
    }

    private function hourDiff(float $from, float $to): float
    {
        return $this->fixHour($to - $from);
    }

    private function fixAngle(float $angle): float
    {
        return $this->fix($angle, 360);
    }

    private function fixHour(float $hour): float
    {
        return $this->fix($hour, 24);
    }

    private function fix(float $value, float $mod): float
    {
        $value -= $mod * floor($value / $mod);

        return $value < 0 ? $value + $mod : $value;
    }

    private function sin(float $degrees): float
    {
        return sin(deg2rad($degrees));
    }

    private function cos(float $degrees): float
    {
        return cos(deg2rad($degrees));
    }

    private function tan(float $degrees): float
    {
        return tan(deg2rad($degrees));
    }

    private function arcsin(float $value): float
    {
        return rad2deg(asin($value));
    }

    private function arccos(float $value): float
    {
        return rad2deg(acos($value));
    }

    private function arctan2(float $y, float $x): float
    {
        return rad2deg(atan2($y, $x));
    }

    private function arccot(float $value): float
    {
        return rad2deg(atan(1 / $value));
    }
}
