<?php
namespace Kestrelbright\PhpUtils;

class Tool
{
    public static function uncamelize($data, $separator = '_')
    : string|array
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_string($k)) {
                    $temp        = strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $k));
                    $data[$temp] = $v;
                }
            }
            return $data;
        }
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $data));
    }

    /**
     * 转换一个string字符串为byte数组
     * @param $str
     * @return array
     */
    public static function getBytes($str)
    {
        $len   = strlen($str);
        $bytes = array();
        for ($i = 0; $i < $len; $i++) {
            if (ord($str[$i]) >= 128) {
                $byte = ord($str[$i]) - 256;
            } else {
                $byte = ord($str[$i]);
            }
            $bytes[] = $byte;
        }
        return $bytes;
    }

    public static function base64EncodeUrlSafe($str)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
    }

    public static function base64DecodeUrlSafe($str)
    {
        $base64 = str_replace(array('-', '_'), array('+', '/'), $str);
        $mod4   = strlen($base64) % 4;
        if ($mod4) {
            $base64 .= substr('====', $mod4);
        }
        return base64_decode($base64);
    }

    /**
     * 校验身份证号是否合法
     * @param string $num 待校验的身份证号
     * @return bool
     */
    public static function isValid(string $num) {
        //老身份证长度15位，新身份证长度18位
        $length = strlen($num);
        if ($length == 15) { //如果是15位身份证

            //15位身份证没有字母
            if (!is_numeric($num)) {
                return false;
            }
            // 省市县（6位）
            $areaNum = substr($num, 0, 6);
            // 出生年月（6位）
            $dateNum = substr($num, 6, 6);

        } else {
            if ($length == 18) { //如果是18位身份证

                //基本格式校验
                if (!preg_match('/^\d{17}[0-9xX]$/', $num)) {
                    return false;
                }
                // 省市县（6位）
                $areaNum = substr($num, 0, 6);
                // 出生年月日（8位）
                $dateNum = substr($num, 6, 8);

            } else { //假身份证
                return false;
            }
        }

        //验证地区
        if (!self::isAreaCodeValid($areaNum)) {
            return false;
        }

        //验证日期
        if (!self::isDateValid($dateNum)) {
            return false;
        }

        //验证最后一位
        if (!self::isVerifyCodeValid($num)) {
            return false;
        }

        return true;
    }

    /**
     * 省市自治区校验
     * @param string $area 省、直辖市代码
     * @return bool
     */
    private static function isAreaCodeValid(string $area) {
        $provinceCode = substr($area, 0, 2);

        // 根据GB/T2260—999，省市代码11到65
        if (11 <= $provinceCode && $provinceCode <= 65) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证出生日期合法性
     * @param string $date 日期
     * @return bool
     */
    private static function isDateValid(string $date) {
        if (strlen($date) == 6) { //15位身份证号没有年份，这里拼上年份
            $date = '19' . $date;
        }
        $year  = intval(substr($date, 0, 4));
        $month = intval(substr($date, 4, 2));
        $day   = intval(substr($date, 6, 2));

        //日期基本格式校验
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        //日期格式正确，但是逻辑存在问题(如:年份大于当前年)
        $currYear = date('Y');
        if ($year > $currYear) {
            return false;
        }
        return true;
    }

    /**
     * 验证18位身份证最后一位
     * @param string $num 待校验的身份证号
     * @return bool
     */
    private static function isVerifyCodeValid(string $num) {
        if (strlen($num) == 18) {
            $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
            $tokens = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

            $checkSum = 0;
            for ($i = 0; $i < 17; $i++) {
                $checkSum += intval($num[$i]) * $factor[$i];
            }

            $mod   = $checkSum % 11;
            $token = $tokens[$mod];

            $lastChar = strtoupper($num[17]);

            if ($lastChar != $token) {
                return false;
            }
        }
        return true;
    }

    /**
     * 手机号邮件加*
     * @param $str
     * @return mixed|string
     */
    public static function mask($str)
    {
        if (empty($str)) {
            return $str;
        }
        if (self::isMobile($str)) {
            return preg_replace('/(\d{3})([\d\s]+)(\d{4})/', '$1****$3', $str);
        } elseif (is_numeric($str)) {
            return preg_replace('/(\d{2})([\d\s]+)(\d{2})/', '$1****$3', $str);
        } else {
            $realLen = mb_strlen($str);
            if($realLen <= 1) {
                return $str;
            }
            if($realLen <= 2) {
                return mb_substr($str, 0, 1) . '*';
            }
            if($realLen <= 3) {
                $maskLen = 1;
            } elseif ($realLen <= 4) {
                $maskLen = 2;
            } else {
                $maskLen = 4;
            }
            $calcLen = min(5, max($realLen - $maskLen, 1));
            $prefix = mb_substr($str, 0, floor($calcLen / 2));
            $suffix = mb_substr($str, -($calcLen - floor($calcLen / 2)));
            $mask = str_repeat('*', $maskLen);
            return "{$prefix}{$mask}{$suffix}";
        }
    }

    /**
     * 手机号判断
     * @param $Argv
     * @return bool
     */
    public static function isMobile($Argv) {
        $RegExp = '/^(0|86|17951)?(13[0-9]|15[012356789]|166|198|199|17[03678]|18[0-9]|14[57])[0-9]{8}$/';
        return (bool)preg_match($RegExp, $Argv);
    }

    /**
     * 密码强度判断
     * @param string $password
     * @return int
     */
    public static function CheckPwd($password) {
        if (preg_match('/^(?:[a-z]{6,8}|[0-9]{6-8}|[^a-z0-9]{6-8})$/i', $password)) {
            $strength = 0;
        } elseif (strlen($password) > 8 && preg_match('/[0-9]/', $password) && preg_match('/[a-z]/i', $password) && preg_match('/[^0-9a-z]/i', $password)) {
            $strength = 2;
        } else {
            if (strlen($password) < 6) {
                $strength = 0;
            } else {
                $strength = 1;
            }
        }
        return $strength ? $strength : 0;
    }

    /**
     * php 二维数组按键值排序
     * @param array $a 需要排序的数组
     * @param string $sort 排序的键值
     * @param string $d 默认是降序排序，带上参后是升序
     * @return array
     */
    public static function arraySort($a, $sort, $d = '') {
        $z = [];
        if(!is_array($a) || count($a) <= 0) {
            return [];
        }
        foreach ($a as $k=>$v) {
            $z = array_unique(array_merge($z, array_keys($v)));
        }
        foreach ($a as $k=>$v) {
            foreach ($z as $va) {
                if(!isset($v[$va])) {
                    $a[$k][$va] = '';
                }
            }
        }
        $num = count($a);
        if (!$d) {
            for ($i = 0; $i < $num; $i++) {
                for ($j = 0; $j < $num - 1; $j++) {
                    if ($a[$j][$sort] > $a[$j + 1][$sort]) {
                        foreach ($a[$j] as $key => $temp) {
                            $t               = $a[$j + 1][$key];
                            $a[$j + 1][$key] = $a[$j][$key];
                            $a[$j][$key]     = $t;
                        }
                    }
                }
            }
        } else {
            for ($i = 0; $i < $num; $i++) {
                for ($j = 0; $j < $num - 1; $j++) {
                    if ($a[$j][$sort] < $a[$j + 1][$sort]) {
                        foreach ($a[$j] as $key => $temp) {
                            $t               = $a[$j + 1][$key];
                            $a[$j + 1][$key] = $a[$j][$key];
                            $a[$j][$key]     = $t;
                        }
                    }
                }
            }
        }
        return $a;
    }

    /**
     * PHP 二维数组去重
     * @param array $arr 需要去重的数组
     * @param string $key 键值
     * @return array
     */
    public static function assocUnique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr);
        return $arr;
    }
    /**
     * 是否正确的金额
     * @param $val
     * @return bool
     */
    public static function isMoney($val) {
        if (preg_match("/^[0-9]{1,}$/", $val) || preg_match("/^[0-9]{1,}\.[0-9]{1,2}$/", $val)) {
            return true;
        }
        return false;
    }

    /**
     * 检测是否为正确的邮件格式
     * @param $Argv
     * @return bool
     */
    public static function IsMail($Argv) {
        $RegExp = '/^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/';
        if (preg_match($RegExp, $Argv)) {
            if (strlen($Argv) > 4) {
                $lvMailPostfix = strtolower(substr($Argv, strlen($Argv) - 4, strlen($Argv)));
                $lvMailPostfix1 = strtolower(substr($Argv, strlen($Argv) - 3, strlen($Argv)));
                if ($lvMailPostfix == ".com" || $lvMailPostfix == ".net" || $lvMailPostfix == ".org" || $lvMailPostfix1 == ".cn") {
                    return true;
                } else {
                    return false; //后缀不正确
                }
            } else {
                return false; //不允许的邮件格式
            }
        } else {
            return false; // 格式不正确
        }
    }

    /**
     * @param $Argv
     * @return bool
     */
    public static function IsQQ($Argv) {
        $RegExp = '/^[1-9][0-9]{5,11}$/';
        return (bool)preg_match($RegExp, $Argv);
    }
}
