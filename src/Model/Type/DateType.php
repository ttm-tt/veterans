<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/**
 * Replacement of core DateType marshaller, which allows for more date formats
 */
namespace App\Model\Type;

use Cake\Database\Type\DateType as CakeDateType;

/**
 * Class DateType
 */
class DateType extends CakeDateType
{
    /**
     * Date format for DateTime object
     *
     * @var string|array
     */
    protected $_marshalFormats = [
        'Y-m-d',    // iso
        'j.n.Y',    // day.month.year, single digit
        'd.m.Y',    // day.month.year, leading 0
        'n/j/Y',    // month/day/year, single digit
        'm/d/Y'     // month/day/year, leading 0
    ];
}
