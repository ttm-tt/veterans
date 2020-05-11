<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/**
 * Replacement of core TimeType marshaller, which allows for more time formats
 */
namespace App\Model\Type;

use Cake\Database\Type\TimeType as CakeTimeType;
use DateTimeInterface;

/**
 * Time type converter.
 *
 * Use to convert time instances to strings & back.
 */
class TimeType extends CakeTimeType
{

    /**
     * Time format for DateTime object
     *
     * @var string|array
     */
    protected $_marshalFormats = [
        'H:i:s',    // hours:minutes:seconds, 24h, leading 0
        'H:i',      // hours:minutes, 24h, leading 0
        'G:i',      // hours:minutes, 24h, single digit hour
        'h:ia',     // hours:minutes, 12h, leading 0 
        'g:ia'      // hours:minutes, 12h, single digit hour
    ];

    public function marshal($value): ?DateTimeInterface
    {
        $time = parent::marshal($value);
        return $time;
    }
}
