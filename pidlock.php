<?php

namespace phpUtils;

/**
 * Class PidLock
 */
class PidLock
{	
	/**
	 * @param string $name Name of process to lock. "pidlock" by default.
	 * @return bool True on success, False on error
	 */
	public static function lock($name='pidlock')
	{
		$res = true;
		$fn = '/tmp/'.$name.'.lock';
		$pid = getmypid();
		try {
			// try to put the lock
			if (@symlink('/proc/'.$pid, $fn) !== false) {
				throw new Exception('Lock succeed', 100);
			}
			// We do not succeed. let's play around
			if (is_link($fn)) {
				// We have link
				if (!$link = readlink($fn)) {
					throw new Exception('Lock failed. Can not read link info', -100);
				}
				// We can read it
				if (!file_exists($link)) {
					// There is no target of the link
					// Let's remove it and try again
					@unlink($fn);
					if (@symlink('/proc/'.$pid, $fn) !== false) {
						throw new Exception('Lock succeed', 100);
					}
					throw new Exception('Lock failed with existing and removed link', -700);
				}
				// Check PID stored in link
				$pid_link = preg_replace ('/\D+/', '', $link);
				if ($pid != $pid_link) {
					// We have different PID
					throw new Exception('Lock failed. Other process with PID:'.$pid_link, -200);
				} else {
					// Ooops... again the same PID, we are lucky!
					throw new Exception('Lock succeed o_0', 200);
				}
			}

			throw new Exception('Lock failed. Undetectable.', -600);

		} catch (Exception $e) {
			if ($e->getCode()<0) {
				$res = false;
			}
		}

		return $res;
	}

	/**
	 * @param string $name Name of process to unlock. "pidlock" by default
	 */
	public static function unlock($name='pidlock')
	{
		$fn = '/tmp/'.$pidlock.'.lock';
		@unlink ($fn);
	}
}
