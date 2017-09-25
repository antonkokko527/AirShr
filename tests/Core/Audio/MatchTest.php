<?php

namespace Core\Audio;

class MatchTest extends \PHPUnit_Framework_TestCase {

	public function defaultsProvider()
	{
		$timeSyncDelta      = 4000;   // The different in milliseconds between the recordings and fingerprints.
 		$timeDelayStream    = 850;    // The delay in milliseconds between the stream broadcast and recording.
		$timeDelayBroadcast = 8000;   // The delay in milliseconds the terrestrial is broadcasted.

		return [
			[$timeSyncDelta, $timeDelayStream, $timeDelayBroadcast]
		];
	}

	/**
	 * @dataProvider defaultsProvider
	 */
	public function testIsDigital($timeSyncDelta, $timeDelayStream, $timeDelayBroadcast)
	{
		$timeRecord = 0;
		$timeMatch  = $timeRecord + 3500;

		$match = new Match();

		$isDigital     = $match->isDigital( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream );
		$isTerrestrial = $match->isTerrestrial( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream );

		$this->assertTrue( $isDigital );
		$this->assertFalse( $isTerrestrial );
	}

	/**
	 * @dataProvider defaultsProvider
	 */
	public function testIsTerrestrial($timeSyncDelta, $timeDelayStream, $timeDelayBroadcast)
	{
		$timeRecord = 0;
		$timeMatch  = $timeRecord + 13500;

		$match = new Match();

		$this->assertFalse( $match->isDigital( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream ) );
		$this->assertTrue( $match->isTerrestrial( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream ) );
	}

	/**
	 * @dataProvider defaultsProvider
	 */
	public function testGetRealTimeDigital($timeSyncDelta, $timeDelayStream, $timeDelayBroadcast)
	{
		$timeRecord = 0;
		$timeMatch  = $timeRecord + 3500;

		$match = new Match();

		$time = $match->getRealTime( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream, $timeDelayBroadcast );

		$this->assertEquals( -8850, $time );
	}

	/**
	 * @dataProvider defaultsProvider
	 */
	public function testGetRealTimeTerrestrial($timeSyncDelta, $timeDelayStream, $timeDelayBroadcast)
	{
		$timeRecord = 0;
		$timeMatch  = $timeRecord + 13500;

		$match = new Match();

		$time = $match->getRealTime( $timeRecord, $timeMatch, $timeSyncDelta, $timeDelayStream, $timeDelayBroadcast );

		$this->assertEquals( -8000, $time );
	}

}
