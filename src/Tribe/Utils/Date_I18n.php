<?php
/**
 * Extends DateTime and includes translation capabilities.
 *
 * @package Tribe\Utils
 * @since   TBD
 */
namespace Tribe\Utils;

use Tribe__Date_Utils as Dates;
use DateTime;
use DateTimeImmutable;

/**
 * Class Date i18n
 *
 * @package Tribe\Utils
 * @since   TBD
 */
class Date_I18n extends DateTime {
	/**
	 * {@inheritDoc}
	 *
	 * @return Date_I18n Localizable variation of DateTime.
	 */
	public static function createFromImmutable( $datetime ) {
		$date_object = new self;
		$date_object->setTimestamp( $datetime->getTimestamp() );
		$date_object->setTimezone( $datetime->getTimezone() );
		return $date_object;
	}

	/**
	 * Returns a translated string usign the params from this DateTime instance.
	 *
	 * @since  TBD
	 *
	 * @param  string $date_format Format to be used in the translation.
	 *
	 * @return string         Translated date.
	 */
	public function format_i18n( $date_format ) {
		$unix_with_tz = strtotime( $this->format( Dates::DBDATETIMEFORMAT ) );
		$translated   = date_i18n( $date_format, $unix_with_tz );
		return $translated;
	}
}
