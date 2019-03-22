<?php

declare(strict_types=1);

/**
 * This file is part of the TRIOTECH adminer-bundle project.
 *
 * @copyright TRIOTECH <open-source@triotech.fr>
 * @license https://joinup.ec.europa.eu/page/eupl-text-11-12 EUPL v1.2 or higher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Triotech\AdminerBundle\Adminer;

final class Functions
{
    /**
     * Get path of the temporary directory
     *
     * @return string
     */
    public static function getTempDir(): ?string
    {
        $return = \ini_get('upload_tmp_dir'); // session_save_path() may contain other storage path
        if (!$return) {
            if (\function_exists('sys_get_temp_dir')) {
                $return = \sys_get_temp_dir();
            } else {
                $filename = @\tempnam('', ''); // @ - temp directory can be disabled by open_basedir
                if (!$filename) {
                    return null;
                }
                $return = \dirname($filename);
                \unlink($filename);
            }
        }

        return $return;
    }

    /**
     * Read password from file adminer.key in temporary directory or create one
     *
     * @param bool $create
     *
     * @return string or false if the file can not be created
     */
    public static function passwordFile(bool $create): string
    {
        $filename = static::getTempDir() . '/adminer.key';
        $return = @\file_get_contents($filename); // @ - may not exist

        if ($return || !$create) {
            return $return;
        }

        $fp = @\fopen($filename, 'w'); // @ - can have insufficient rights //! is not atomic
        if ($fp) {
            \chmod($filename, 0660);
            $return = static::randString();
            \fwrite($fp, $return);
            \fclose($fp);
        }

        return $return;
    }

    /**
     * Get a random string
     *
     * @return string 32 hexadecimal characters
     */
    public static function randString(): string
    {
        return \md5(\uniqid(\mt_rand(), true));
    }
}
