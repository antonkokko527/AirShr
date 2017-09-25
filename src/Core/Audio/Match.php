<?php

namespace Core\Audio;

class Match {

	const DELAY_BLUETOOTH = 1500;

	public function isDigital( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream )
	{
		return ($timeMatch - $timeRecord - $timeSyncDelta) <= $timeDelayStream;
	}

	public function isTerrestrial( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream )
	{
		return ($timeMatch - $timeRecord - $timeSyncDelta) > $timeDelayStream;
	}

	/**
	 * Returns a calculated "real" AirShr specific match time based on the available time variables. The 
	 * returned match time can then be used to look up the correct entry in real time meta data.
	 *
	 * NOTE: All time based variables used here are millisecond based.
	 *
	 * @param integer $timeRecord The record time in milliseconds when the matching process was started.
	 * @param integer $timeMatch The match time in milliseconds (returned by ACRCloud).
	 * @param integer $timeSyncDelta The delta in milliseconds between the recording and the fingerprinting process.
	 * @param integer $timeDelayStream The streaming delay in milliseconds, i.e. the delay of digital broadcast.
	 * @param integer $timeDelayBroadcast The broadcast time in milliseconds, a.k.a. the "profanity delay".
	 * @param boolean $isBluetooth If the recording was started through Bluetooth (true) or not (false).
	 * @return integer
	 */
	public function getRealTime( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream, $timeDelayBroadcast, $isBluetooth = FALSE )
	{
		$time = NULL;

		if( $this->isDigital( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream ) )
		{
			$time = $timeRecord - $timeDelayStream - $timeDelayBroadcast;
		}
		else
		if( $this->isTerrestrial( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream ) )
		{
			$time = $timeRecord - $timeDelayBroadcast;
		}
		else
		{
			throw new Exception\InvalidMatchException($timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream);
		}

		if( $isBluetooth )
		{
			$time -= self::DELAY_BLUETOOTH;
		}

		return $time;
	}

}
