<?php

declare(strict_types=1);

/**
 * Grabbed from Adminer, wrapped in class to avoid name collisions
 */

/**
 * PHP implementation of XXTEA encryption algorithm
 * @author Ma Bingyao <andot@ujn.edu.cn>
 * @link http://www.coolcode.cn/?action=show&id=128
 */
final class XXTEA
{
    /**
     * @param int $n
     *
     * @return int
     */
    private static function int32(int $n): int
    {
        while ($n >= 2147483648) {
            $n -= 4294967296;
        }
        while ($n <= -2147483649) {
            $n += 4294967296;
        }
        return (int)$n;
    }

    /**
     * @param mixed[] $v
     * @param bool $w
     *
     * @return string
     */
    private static function long2str(array $v, bool $w): string
    {
        $s = '';

        foreach ($v as $val) {
            $s .= \pack('V', $val);
        }

        if ($w) {
            return \substr($s, 0, \end($v));
        }

        return $s;
    }

    /**
     * @param string $s
     * @param bool $w
     *
     * @return mixed[]
     */
    private static function str2long(string $s, bool $w): array
    {
        $v = \array_values(\unpack('V*', \str_pad($s, (int) (4 * \ceil(\strlen($s) / 4)), "\0")));

        if ($w) {
            $v[] = \strlen($s);
        }

        return $v;
    }

    /**
     * @param int $z
     * @param int $y
     * @param int $sum
     * @param int $k
     *
     * @return int
     */
    private static function xxtea_mx(int $z, int $y, int $sum, int $k): int
    {
        return self::int32((($z >> 5 & 0x7FFFFFF) ^ $y << 2) + (($y >> 3 & 0x1FFFFFFF) ^ $z << 4)) ^ self::int32(($sum ^ $y) + ($k ^ $z));
    }

    /**
     * Cipher
     *
     * @param string $str plain-text password
     * @param string $key
     *
     * @return string binary cipher
     */
    public static function encrypt(string $str, string $key): string
    {
        if ($str == "") {
            return "";
        }

        $key = \array_values(\unpack("V*", \pack("H*", \md5($key))));
        $v = self::str2long($str, true);
        $n = \count($v) - 1;
        $z = $v[$n];
        $y = $v[0];
        $q = \floor(6 + 52 / ($n + 1));
        $sum = 0;

        while ($q-- > 0) {
            $sum = self::int32($sum + 0x9E3779B9);
            $e = $sum >> 2 & 3;

            for ($p = 0; $p < $n; $p++) {
                $y = $v[$p + 1];
                $mx = self::xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
                $z = self::int32($v[$p] + $mx);
                $v[$p] = $z;
            }

            $y = $v[0];
            $mx = self::xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
            $z = self::int32($v[$n] + $mx);
            $v[$n] = $z;
        }

        return self::long2str($v, false);
    }

    /** Decipher
     *
     * @param string $str binary cipher
     * @param string $key
     *
     * @return string plain-text password
     */
    public static function decrypt(string $str, string $key): string
    {
        if ($str == "") {
            return "";
        }

        if (!$key) {
            return '';
        }

        $key = \array_values(\unpack("V*", \pack("H*", \md5($key))));
        $v = self::str2long($str, false);
        $n = \count($v) - 1;
        $z = $v[$n];
        $y = $v[0];
        $q = \floor(6 + 52 / ($n + 1));
        $sum = self::int32($q * 0x9E3779B9);

        while ($sum) {
            $e = $sum >> 2 & 3;

            for ($p = $n; $p > 0; $p--) {
                $z = $v[$p - 1];
                $mx = self::xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
                $y = self::int32($v[$p] - $mx);
                $v[$p] = $y;
            }

            $z = $v[$n];
            $mx = self::xxtea_mx($z, $y, $sum, $key[$p & 3 ^ $e]);
            $y = self::int32($v[0] - $mx);
            $v[0] = $y;
            $sum = self::int32($sum - 0x9E3779B9);
        }

        return long2str($v, true);
    }
}
