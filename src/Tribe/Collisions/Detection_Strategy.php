<?php

abstract class Tribe__Collisions__Detection_Strategy {

	/**
	 * Computes the collision-based difference of two or more arrays of segments returning an array of elements from
	 * the first array not colliding with any element from the second array according to the collision detection
	 * strategy implemented by the class.
	 *
	 * Note: points are segments with matching start and end.
	 *
	 * @param array $a     An array of elements each defining the start and end of a segment in the format [<start>,
	 *                     <end>].
	 * @param array $b,... An array (ore more arrays) of elements each defining the start and end of a segment in the
	 *                     format [<start>, <end>].
	 *
	 * @return array An array of elements each defining the start and end of a segment in the format [<start>, <end>].
	 */
	public function diff( array $a, array $b ) {
		$args = func_get_args();
		$a    = array_shift( $args );

		return $this->collide( $a, $args, true );
	}

	/**
	 * Computes the collision-based intersection of two or more arrays of segments returning an array of elements from
	 * the first array colliding with at least one element from the second array according to the collision detection
	 * strategy implemented by the class.
	 *
	 * Note: points are segments with matching start and end.
	 *
	 * @param array $a     An array of elements each defining the start and end of a segment in the format [<start>,
	 *                     <end>].
	 * @param array $b,... An array (ore more arrays) of elements each defining the start and end of a segment in the
	 *                     format [<start>, <end>].
	 *
	 * @return array An array of elements each defining the start and end of a segment in the format [<start>, <end>].
	 */
	public function intersect( array $a, array $b ) {
		$args = func_get_args();
		$a    = array_shift( $args );

		return $this->collide( $a, $args, false );
	}

	/**
	 * Detects the collision of a segment with specified start and end points.
	 *
	 * @param array $segment  An array defining the end and start of a segment in the format [<start>, <end>].
	 * @param array $b_starts An array of starting points from the diff array
	 * @param array $b_ends   An array of end points form the diff array
	 *
	 * @return bool Whether a collision was detected or not.
	 */
	abstract protected function detect_collision( array $segment, array $b_starts, array $b_ends );

	/**
	 * Compares two segments.
	 *
	 * Used in `usort` calls.
	 *
	 * @param array $b_starts An array of starting points from the diff array
	 * @param array $b_ends   An array of end points form the diff array
	 *
	 * @return int
	 */
	public function compare_starts( array $a, array $b ) {
		if ( $a[0] == $b[0] ) {
			return 0;
		}

		return ( $a[0] < $b[0] ) ? - 1 : 1;
	}

	/**
	 * Returns an array of segments given starts and length.
	 *
	 * Note: points are segments with a length of 0.
	 *
	 * @param array $starts An array of starting points
	 * @param int   $length The length of each segment
	 *
	 * @return array An array of elements each defining the start and end of a segment in the format [<start>, <end>].
	 */
	public function points_to_segments( array $starts, $length ) {
		$segments = array();

		foreach ( $starts as $start ) {
			$segments[] = array( $start, $start + $length );
		}

		return $segments;
	}

	/**
	 *
	 * Detects collisions betwenn 2+ arrays of segments.
	 *
	 * @param array $a       An array of elements each defining the start and end of a segment in the format [<start>,
	 *                       <end>].
	 * @param array $b,...   An array (ore more arrays) of elements each defining the start and end of a segment in the
	 *                       format [<start>, <end>].
	 * @param bool  $discard Whether the detection of a collisions should discard (diff) or keep (intersect) a segment
	 *                       from the first array.
	 *
	 * @return array An array of elements each defining the start and end of a segment in the format [<start>, <end>].
	 */
	protected function collide( array $a, array $b, $discard = true ) {
		$bs = (array) $b;

		usort( $a, array( $this, 'compare_starts' ) );

		$diffed        = array();
		$diffed_starts = array();
		$diffed_ends   = array();

		// no matter the strategy a "duplicate" is always a segment with same start and end
		$duplicate_collision_detector = new Tribe__Collisions__Matching_Start_End_Detector();

		foreach ( $bs as $b ) {
			usort( $b, array( $this, 'compare_starts' ) );

			$b_starts = wp_list_pluck( $b, 0 );
			$b_ends   = wp_list_pluck( $b, 1 );

			foreach ( $a as $segment ) {
				if ( $discard === $this->detect_collision( $segment, $b_starts, $b_ends ) ) {
					if ( false !== $i = array_search( $segment, $diffed ) ) {
						unset( $diffed[ $i ] );
					}
					continue;
				}

				// avoid duplicates
				if ( $duplicate_collision_detector->detect_collision( $segment, $diffed_starts, $diffed_ends ) ) {
					continue;
				}

				$diffed_starts[] = $segment[0];
				$diffed_ends[]   = $segment[1];
				$diffed[]        = $segment;
			}

			if ( empty( $diffed ) ) {
				break;
			}

			$a = $diffed;
		}

		return array_values( $diffed );
	}
}