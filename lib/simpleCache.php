<?php
/**
 * A small file cache for values that are expensive to compute and may be a
 * little out of date, such as the totals of the observer rankings.
 *
 * Values are serialized into files under the PHP temporary directory (the
 * private /tmp of php-fpm on the server), so nothing has to be set up and an
 * emptied /tmp only means the values are computed again.
 *
 * PHP Version 8
 *
 * @category Common
 * @package  DeepskyLog
 * @author   DeepskyLog Developers <deepskylog@groups.io>
 * @license  GPL2 <https://opensource.org/licenses/gpl-2.0.php>
 * @link     http://www.deepskylog.org
 */
class SimpleCache
{
    /**
     * Returns the cached value of $key when it is younger than $seconds,
     * else computes it with $compute and stores it.
     *
     * While one request computes an expired value, the others wait for it
     * (an exclusive lock per key) and then read the stored value, so a burst
     * of requests computes it only once. When the cache can't be written,
     * the value is computed and returned anyway.
     *
     * @param string   $key     The name of the value.
     * @param int      $seconds How long a stored value is used.
     * @param callable $compute Returns the value; it must be serializable.
     *
     * @return mixed The value.
     */
    public static function remember($key, $seconds, callable $compute)
    {
        $file = self::path($key);
        if ($file === null) {
            return $compute();
        }

        $value = self::read($file, $seconds, $found);
        if ($found) {
            return $value;
        }

        $lock = @fopen($file . '.lock', 'c');
        if ($lock === false) {
            return $compute();
        }
        flock($lock, LOCK_EX);
        try {
            // Another request may have stored it while this one waited.
            $value = self::read($file, $seconds, $found);
            if (!$found) {
                $value = $compute();
                $temp = @tempnam(dirname($file), 'tmp');
                if ($temp !== false) {
                    if (@file_put_contents($temp, serialize($value)) !== false) {
                        @rename($temp, $file);
                    } else {
                        @unlink($temp);
                    }
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }

        return $value;
    }

    /**
     * Returns the file of a key, or null when the directory can't be made.
     *
     * @param string $key The name of the value.
     *
     * @return string|null The path.
     */
    private static function path($key)
    {
        $dir = sys_get_temp_dir() . '/deepskylog-cache';
        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            return null;
        }

        return $dir . '/' . md5($key) . '.cache';
    }

    /**
     * Reads a stored value that is younger than $seconds.
     *
     * @param string $file    The file of the value.
     * @param int    $seconds How long a stored value is used.
     * @param bool   $found   Set to whether a value was found.
     *
     * @return mixed The value, or null.
     */
    private static function read($file, $seconds, &$found)
    {
        $found = false;
        $time = @filemtime($file);
        if ($time === false || $time < time() - $seconds) {
            return null;
        }
        $data = @file_get_contents($file);
        if ($data === false) {
            return null;
        }
        $value = @unserialize($data);
        if ($value === false && $data !== serialize(false)) {
            return null;
        }
        $found = true;

        return $value;
    }
}
