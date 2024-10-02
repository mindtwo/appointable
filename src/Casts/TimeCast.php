<?php

namespace mindtwo\Appointable\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Class TimeCast
 *
 * @implements CastsAttributes<string, string>
 */
class TimeCast implements CastsAttributes
{
    /**
     * Create a new cast class instance.
     */
    public function __construct(
        protected ?string $precision = null,
    ) {}

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (empty($value)) {
            return null;
        }

        $precision = $this->precision ?? 'minute';

        $format = match ($precision) {
            'hour' => 'H',
            'minute' => 'H:i',
            'second' => 'H:i:s',
            default => 'H:i',
        };

        return Carbon::parse($value)->format($format);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $value
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }
}
