<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/**
 * DateTimeType, which doesn't fall back to FrozenTime when set to immutable
 */
namespace App\Model\Type;

use Cake\Database\Type\DateTimeType as CakeDateTimeType;
use DateTimeImmutable;
use App\I18n\FrozenDateTime;

/**
 * Class DateTimeType
 */
class DateTimeType extends CakeDateTimeType
{
	public function useImmutable() {
        $this->_setClassName(FrozenDateTime::class, DateTimeImmutable::class);
	}
}
