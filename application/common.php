<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 过滤非法html标签 去掉换行符
function filter_line_tab($text)
{
    $text = str_replace(array(
        "\r\n",
        "\r",
        "\n",
        " "
    ), '', $text);
    // 过滤标签
    $text = nl2br($text);
    $text = real_strip_tags($text);
    $text = addslashes($text);
    $text = trim($text);
    return addslashes($text);
}

function real_strip_tags($str, $allowable_tags = "")
{
    $str = stripslashes(htmlspecialchars_decode($str));
    return strip_tags($str, $allowable_tags);
}

// 防超时的file_get_contents改造函数
function sys_file_get_contents($url)
{
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 30
        )
    )); // 超时时间，单位为秒

    return file_get_contents($url, 0, $context);
}

// 全局的安全过滤函数
function safe($text, $type = 'html')
{
    // 无标签格式
    $text_tags = '';
    // 只保留链接
    $link_tags = '<a>';
    // 只保留图片
    $image_tags = '<img>';
    // 只存在字体样式
    $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
    // 标题摘要基本格式
    $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike><section><header><footer><article><nav><audio><video>';
    // 兼容Form格式
    $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
    // 内容等允许HTML的格式
    $html_tags = $base_tags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
    // 全HTML格式
    $all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
    // 过滤标签
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = strip_tags($text, ${$type . '_tags'});

    // 过滤攻击代码
    if ($type != 'all') {
        // 过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat [0], $mat [1] . $mat [3], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat [0], $mat [1] . $mat [3], $text);
        }
    }
    return $text;
}

// 阿拉伯数字转中文表述，如101转成一百零一
function num2cn($number)
{
    $number = intval($number);
    $capnum = array(
        "零",
        "一",
        "二",
        "三",
        "四",
        "五",
        "六",
        "七",
        "八",
        "九"
    );
    $capdigit = array(
        "",
        "十",
        "百",
        "千",
        "万"
    );

    $data_arr = str_split($number);
    $count = count($data_arr);
    for ($i = 0; $i < $count; $i++) {
        $d = $capnum [$data_arr [$i]];
        $arr [] = $d != '零' ? $d . $capdigit [$count - $i - 1] : $d;
    }
    $cncap = implode("", $arr);

    $cncap = preg_replace("/(零)+/", "0", $cncap); // 合并连续“零”
    $cncap = trim($cncap, '0');
    $cncap = str_replace("0", "零", $cncap); // 合并连续“零”
    $cncap == '一十' && $cncap = '十';
    $cncap == '' && $cncap = '零';
    // echo ( $data.' : '.$cncap.' <br/>' );
    return $cncap;
}

function week_name($number = null)
{
    if ($number === null)
        $number = date('w');

    $arr = array(
        "日",
        "一",
        "二",
        "三",
        "四",
        "五",
        "六"
    );

    return '星期' . $arr [$number];
}

// 日期转换成星期几
function daytoweek($day = null)
{
    $day === null && $day = date('Y-m-d');
    if (empty ($day))
        return '';

    $number = date('w', strtotime($day));

    return week_name($number);
}

/**
 * 检查是否是以手机浏览器进入(IN_MOBILE)
 */
function isMobile()
{
    $mobile = array();
    static $mobilebrowser_list = 'Mobile|iPhone|Android|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|SonyEricsson|Sony|BlackBerry|Cellphone|dopod|Nokia|samsung|PalmSource|Xphone|Xda|Smartphone|PIEPlus|MEIZU|MIDP|CLDC';
    // note 获取手机浏览器
    if (preg_match("/$mobilebrowser_list/i", $_SERVER ['HTTP_USER_AGENT'], $mobile)) {
        return true;
    } else {
        if (preg_match('/(mozilla|chrome|safari|opera|m3gate|winwap|openwave)/i', $_SERVER ['HTTP_USER_AGENT'])) {
            return false;
        } else {
            if ($_GET ['mobile'] === 'yes') {
                return true;
            } else {
                return false;
            }
        }
    }
}

function isiPhone()
{
    return strpos($_SERVER ['HTTP_USER_AGENT'], 'iPhone') !== false;
}

function isiPad()
{
    return strpos($_SERVER ['HTTP_USER_AGENT'], 'iPad') !== false;
}

function isiOS()
{
    return isiPhone() || isiPad();
}

function isAndroid()
{
    return strpos($_SERVER ['HTTP_USER_AGENT'], 'Android') !== false;
}

/**
 * 判断值是否是正整数
 *
 * @param $value
 * @param bool $mustGt0 是否大于0
 * @return bool
 */
function isPositiveInteger($value, $mustGt0 = false)
{
    if (is_numeric($value) && is_int($value + 0)) {
        if ($mustGt0) {
            if (($value + 0) > 0) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    } else {
        return false;
    }
}

/**
 * 使用正则验证数据
 *
 * @access public
 * @param string $value
 *            要验证的数据
 * @param string $rule
 *            验证规则
 * @return boolean
 */
function regex($value, $rule)
{
    $validate = array(
        'require' => '/\S+/',
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'mobile' => '/^(((13[0-9]{1})|(14[5,7]{1})|(15[0-35-9]{1})|(17[0678]{1})|(18[0-9]{1}))+\d{8})$/',
        'phone' => '/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/',
        'url' => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
        'currency' => '/^\d+(\.\d+)?$/',
        'number' => '/^\d+$/',
        'zip' => '/^\d{6}$/',
        'integer' => '/^[-\+]?\d+$/',
        'double' => '/^[-\+]?\d+(\.\d+)?$/',
        'english' => '/^[A-Za-z]+$/',
        'bankcard' => '/^\d{14,19}$/',
        'safepassword' => '/^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/',
        'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u',
    );
    // 检查是否有内置的正则表达式
    if (isset ($validate [strtolower($rule)]))
        $rule = $validate [strtolower($rule)];
    return 1 === preg_match($rule, $value);
}

// 生成签名
function make_sign($paraMap = array(), $partner_key = '')
{
    $buff = "";
    ksort($paraMap);
    $paraMap ['key'] = $partner_key;
    foreach ($paraMap as $k => $v) {
        if (null != $v && "null" != $v && '' != $v && "sign" != $k) {
            $buff .= strtolower($k) . "=" . $v . "&";
        }
    }
    $reqPar = '';
    if (strlen($buff) > 0) {
        $reqPar = substr($buff, 0, strlen($buff) - 1);
    }
    return strtoupper(md5($reqPar));
}

/**
 * 数据签名
 * @param array $data 被认证的数据
 * @return string 签名
 */
function data_signature($data = [])
{
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data);
    $code = http_build_query($data);
    $sign = sha1($code);
    return $sign;
}

/**
 * 随机字符
 * @param int $length 长度
 * @param string $type 类型
 * @param int $convert 转换大小写 1大写 0小写
 * @return string
 */
function createNonceStr($length = 32, $type = 'all', $convert = 0)
{
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if (!isset($config[$type])) $type = 'all';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string{mt_rand(0, $strlen)};
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/**
 * 加入时间戳，保证值不重复
 * 如需要对签名进行防重放攻击时使用
 * 将值存入到Memcached中，存在则已经使用过，
 * 后续相关请求为重放攻击，直接PASS
 *
 * @return mixed 生成唯一字符串
 */
function createUniqidNonceStr()
{
    return md5(uniqid(microtime(true), true));
}

// 二维数组根据键排序
function array_sort($arr, $keys, $type = 'desc')
{
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue [$k] = $v [$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array [$k] = $arr [$k];
    }
    return $new_array;
}

/**
 * 获取字符串的长度
 *
 * 计算时, 汉字或全角字符占1个长度, 英文字符占0.5个长度
 *
 * @param string $str
 * @param boolean $filter
 *            是否过滤html标签
 * @return int 字符串的长度
 */
function get_str_length($str, $filter = false)
{
    if ($filter) {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        $str = strip_tags($str);
    }
    return (strlen($str) + mb_strlen($str, 'UTF8')) / 4;
}

/**
 * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
 *
 * @access public
 * @param string $value
 *            验证数据
 * @param mixed $rule
 *            验证表达式
 * @param string $type
 *            验证方式 默认为正则验证
 * @return boolean
 */
function checkParam($value, $rule, $type = 'regex')
{
    $type = strtolower(trim($type));
    switch ($type) {
        case 'in' : // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
        case 'notin' :
            $range = is_array($rule) ? $rule : explode(',', $rule);
            return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
        case 'between' : // 验证是否在某个范围
        case 'notbetween' : // 验证是否不在某个范围
            if (is_array($rule)) {
                $min = $rule [0];
                $max = $rule [1];
            } else {
                list ($min, $max) = explode(',', $rule);
            }
            return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
        case 'equal' : // 验证是否等于某个值
        case 'notequal' : // 验证是否等于某个值
            return $type == 'equal' ? $value == $rule : $value != $rule;
        case 'length' : // 验证长度
            $length = mb_strlen($value, 'utf-8'); // 当前数据长度
            if (strpos($rule, ',')) { // 长度区间
                list ($min, $max) = explode(',', $rule);
                return $length >= $min && $length <= $max;
            } else { // 指定长度
                return $length == $rule;
            }
        case 'expire' :
            list ($start, $end) = explode(',', $rule);
            if (!is_numeric($start))
                $start = strtotime($start);
            if (!is_numeric($end))
                $end = strtotime($end);
            return NOW_TIME >= $start && NOW_TIME <= $end;
        case 'ip_allow' : // IP 操作许可验证
            return in_array(GetIP(), explode(',', $rule));
        case 'ip_deny' : // IP 操作禁止验证
            return !in_array(GetIP(), explode(',', $rule));
        case 'regex' :
        default : // 默认使用正则验证 可以使用验证类中定义的验证名称
            // 检查附加规则
            return regex($value, $rule);
    }
}

/**
 * 检查日期是否合法
 * @param string $str 传入日期
 * @param string $format 指定日期格式
 * @return number
 */
function is_date($date, $format = "Y-m-d H:i:s")
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1 起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2 终点纬度
 * @param  Int $unit 单位 1:米 2:公里
 * @param  Int $decimal 精度 保留小数位数
 * @return Decimal
 */
function getMerchantDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 1, $decimal = 2)
{

    $EARTH_RADIUS = 6378.137; // 地球半径系数
    $PI = 3.1415926535898;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI / 180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if ($unit == 2) {
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);

}

/**
 * 得出二个金额的差额，并显示为元角分
 *
 * @param $origin_price
 * @param $current_price
 * @return string
 */
function discount_calc($origin_price, $current_price)
{
    $str_balance = '';
    $balance = $origin_price - $current_price;

    if ($balance <= 0) {
        return '';
    }

    $balance_array = explode('.', $balance);

    if (intval($balance_array[0])) {
        $str_balance .= intval($balance_array[0]) . '元';
    }

    $str_decimal = $balance_array[1];
    if (intval(substr($str_decimal, 0, 1))) {
        $str_balance .= intval(substr($str_decimal, 0, 1)) . '角';
    }

    if (intval(substr($str_decimal, 1, 1))) {
        $str_balance .= intval(substr($str_decimal, 1, 1)) . '分';
    }

    return $str_balance;
}

// 去除URL后缀
function remove_url_suffix($url)
{
    $url_info = explode(".", $url);
    $url_suffix = end($url_info);

    if (!$url_suffix || !in_array($url_suffix, array('html', 'php', C('URL_HTML_SUFFIX')))) {
        return $url;
    } else {
        return substr($url, 0, (strlen($url_suffix) + 1) * -1);
    }
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @return string
 */
function getBroswer()
{
    $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
        preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
        $exp[0] = "Firefox";
        $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
        preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
        $exp[0] = "傲游";
        $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
        preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
        $exp[0] = "IE";
        $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
        preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
        $exp[0] = "Opera";
        $exp[1] = $opera[1];
    } elseif (stripos($sys, "Edge") > 0) {
        //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
        preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
        $exp[0] = "Edge";
        $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
        preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
        $exp[0] = "Chrome";
        $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
        preg_match("/rv:([\d\.]+)/", $sys, $IE);
        $exp[0] = "IE";
        $exp[1] = $IE[1];
    } elseif (stripos($sys, 'Safari') > 0) {
        preg_match("/safari\/([^\s]+)/i", $sys, $safari);
        $exp[0] = "Safari";
        $exp[1] = $safari[1];
    } else {
        $exp[0] = "未知浏览器";
        $exp[1] = "";
    }
    return $exp[0] . '(' . $exp[1] . ')';
}

/**
 * 获取客户端操作系统信息包括win10
 * @return string
 */
function getOs()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
        $os = 'Windows 95';
    } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
        $os = 'Windows ME';
    } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
        $os = 'Windows 98';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
        $os = 'Windows Vista';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
        $os = 'Windows 7';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
        $os = 'Windows 8';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
        $os = 'Windows 10';#添加win10判断
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
        $os = 'Windows XP';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
        $os = 'Windows 2000';
    } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
        $os = 'Windows NT';
    } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
        $os = 'Windows 32';
    } else if (preg_match('/linux/i', $agent)) {
        $os = 'Linux';
    } else if (preg_match('/unix/i', $agent)) {
        $os = 'Unix';
    } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'SunOS';
    } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
        $os = 'IBM OS/2';
    } else if (preg_match('/Mac/i', $agent)) {
        $os = 'Mac';
    } else if (preg_match('/PowerPC/i', $agent)) {
        $os = 'PowerPC';
    } else if (preg_match('/AIX/i', $agent)) {
        $os = 'AIX';
    } else if (preg_match('/HPUX/i', $agent)) {
        $os = 'HPUX';
    } else if (preg_match('/NetBSD/i', $agent)) {
        $os = 'NetBSD';
    } else if (preg_match('/BSD/i', $agent)) {
        $os = 'BSD';
    } else if (preg_match('/OSF1/i', $agent)) {
        $os = 'OSF1';
    } else if (preg_match('/IRIX/i', $agent)) {
        $os = 'IRIX';
    } else if (preg_match('/FreeBSD/i', $agent)) {
        $os = 'FreeBSD';
    } else if (preg_match('/teleport/i', $agent)) {
        $os = 'teleport';
    } else if (preg_match('/flashget/i', $agent)) {
        $os = 'flashget';
    } else if (preg_match('/webzip/i', $agent)) {
        $os = 'webzip';
    } else if (preg_match('/offline/i', $agent)) {
        $os = 'offline';
    } elseif (preg_match('/ucweb|MQQBrowser|J2ME|IUC|3GW100|LG-MMS|i60|Motorola|MAUI|m9|ME860|maui|C8500|gt|k-touch|X8|htc|GT-S5660|UNTRUSTED|SCH|tianyu|lenovo|SAMSUNG/i', $agent)) {
        $os = 'mobile';
    } else {
        $os = '未知操作系统';
    }
    return $os;
}

/**
 * Decode a string with URL-safe Base64.
 *
 * @param string $input A Base64 encoded string
 *
 * @return string A decoded string
 */
function urlsafeB64Decode($input)
{
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

/**
 * Encode a string with URL-safe Base64.
 *
 * @param string $input The string you want encoded
 *
 * @return string The base64 encode of what you passed in
 */
function urlsafeB64Encode($input)
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}

/**
 * 返回按层级加前缀的数组
 *
 * @author  rainfer
 * @param array|mixed $menu 待处理数组
 * @param string $id_field 主键id字段名
 * @param string $pid_field 父级字段名
 * @param string $lefthtml 前缀
 * @param int $pid 父级id
 * @param int $lvl 当前lv
 * @param int $leftpin 左侧距离
 * @return array
 */
function level_left($menu, $id_field = 'id', $pid_field = 'pid', $lefthtml = '─', $pid = 0, $lvl = 0, $leftpin = 0)
{
    $arr = array();
    foreach ($menu as $v) {
        if ($v[$pid_field] == $pid) {
            $v['level'] = $lvl + 1;
//            $v['leftpin']=$leftpin;
//            $v['lefthtml']='├'.str_repeat($lefthtml,$lvl);
            $arr[] = $v;
            $arr = array_merge($arr, level_left($menu, $id_field, $pid_field, $lefthtml, $v[$id_field], $lvl + 1, $leftpin + 20));
        }
    }
    return $arr;
}

/**
 * 递归数组，将子类归于childern
 *
 * @param $array
 * @param string $id_field
 * @param string $pid_field
 * @param int $pid
 * @return array
 */
function level_left_childern($array, $id_field = 'id', $pid_field = 'parentid', $pid = 0)
{
    $manages = array();
    foreach ($array as $row) {
        if ($row[$pid_field] == $pid) {
            $children = level_left_childern($array, $id_field, $pid_field, $row[$id_field]);
            $children && $row['children'] = $children;
            $manages[] = $row;
        }
    }
    return $manages;
}

function remoteUploadFile($remoteUrl, $filename)
{
    if (empty($remoteUrl) || empty($filename)) {
        return false;
    }

    if (class_exists('\CURLFile')) {
        $data['file'] = new \CURLFile($filename);
    } else {
        $data['file'] = '@' . $filename;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remoteUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $responseText = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($responseText, true);

    return $response;
}

function buildReturnData($data, $info, $status)
{
    return array('data' => $data, 'info' => $info, 'status' => $status);
}

/**
 * 获取前端post的数据
 * @return mixed
 */
function getPostData()
{
    return json_decode(file_get_contents('php://input'), true);
}

// 判断是否是在微信浏览器里
function isWeixinBrowser($from = 0)
{
    if ((!$from && defined('IN_WEIXIN') && IN_WEIXIN) || isset ($_GET ['is_stree']))
        return true;

    $agent = $_SERVER ['HTTP_USER_AGENT'];
    if (!strpos($agent, "icroMessenger")) {
        return false;
    }
    return true;
}

// 获取当前访问的完整url地址
function getCurrentUrl($forced = 0)
{
    $url = \think\Request::instance()->domain() . \think\Request::instance()->url();
    $forced && $url = str_replace('https://', 'http://', $url);
    // 兼容后面的参数组装
    if (stripos($url, '?') === false) {
        $url .= '?t=' . time();
    }
    return $url;
}

/**
 * 计算剩余时间
 *
 * @param $time 日期格式 Y-m-d H:i:s
 * @param int $format
 *  时间格式 1 - 到天; 2 - 到小时; 3 - 到分钟; 4 - 到秒;
 */
function calcTimeRemaining($time, $format = 2)
{
    $show_date = '';
    $cur_time = time();
    $time = strtotime($time);
    $time_interval = $time - $cur_time;

    $date = floor($time_interval / 86400);

    $time_interval = $time_interval - ($date * 86400);
    $hour = floor($time_interval / 3600);

    $time_interval = $time_interval - ($hour * 3600);
    $minute = floor($time_interval / 60);

    $time_interval = $time_interval - ($minute * 60);
    $second = floor($time_interval);

    $date = empty($date) ? '' : $date . '天';
    $hour = empty($hour) ? '' : $hour . '小时';
    $minute = empty($minute) ? '' : $minute . '分钟';
    $second = empty($second) ? '' : $second . '秒';

    switch ($format) {
        case 1:
            $show_date = $date;
            break;
        case 2:
            $show_date = $date . $hour;
            break;
        case 3:
            $show_date = $date . $hour . $minute;
            break;
        case 4:
            $show_date = $date . $hour . $minute . $second;
            break;

    }
    return $show_date;
}

/**
 * 转换地区路径
 *
 * @param  string $path 地区路径，格式：0-1-2-3-4
 * @param  bool   $withCountry ，是否带上国家
 * @return array|string $region = ['0-1', '0-1-2', '0-1-2-3', '0-1-2-3-4']
 */
function transform_region_path($path, $withCountry = false)
{
    $address = explode('-', $path);
    $count   = count($address);
    $region  = [];

    if ($count > 2) {
        $pathStr = '0';
        for ($i = 0; $i < ($count - 1); $i++) {
            $pathStr .= '-'.$address[$i+1];
            $region[$i] = $pathStr;
        }

        if (false == $withCountry) {
            array_shift($region);
        }

        return $region;
    }
    return '';
}

/**
 * 计算年龄
 *
 * @param $birthday
 * @return float|int
 */
function calc_age($birthday)
{
    if (!empty($birthday)) {
        $diff = time() - strtotime($birthday);
        return floor($diff/86400/360);
    }

    return 0;
}

/**
 * 计算经营年限
 *
 * @param string $registerTime 2018-02-26
 * @return float|int
 */
function calc_operate_years($registerTime)
{
    if (!empty($registerTime)) {
        $diff = time() - strtotime($registerTime);
        return ceil($diff/86400/360);
    }

    return 0;
}
function getExtension($fileName)
{
    $position = strripos($fileName, '.');
    return strtolower(substr($fileName, $position+1));
}