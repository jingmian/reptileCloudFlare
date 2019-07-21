<?php

define('INCLUDING_NOTHING', 0);
define('INCLUDING_LEFT', 1 << 0);
define('INCLUDING_RIGHT', 1 << 1);
define('INCLUDING_BOTH', INCLUDING_LEFT + INCLUDING_RIGHT);

/**
 * 取中间文本
 * @param  string         $wholeText 寻找文本
 * @param  string         $leftText  左边文本
 * @param  string         $rightText 右边文本
 * @param  int            $offset    开始查找位置
 * @param  int            &$position 返回第一个找到文本的位置，找不到返回-1
 * @param  int            $padding   填充
 * @return string|false
 */
function getMiddleText($wholeText, $leftText, $rightText, $offset = 0, &$position = 0, $padding = INCLUDING_NOTHING)
{
    $length = strlen($wholeText);

    if ($length == 0) {
        $position = -1;
        return false;
    }

    // 负数偏移转换（7.1+才原生支持负数偏移）
    // 偏移有效性检测
    // 有效范围：[-len,len-1]
    if ($offset < 0) {
        $offset = $offset < -$length ? 0 : ($length + $offset);
    } else if ($offset > $length - 1) {
        $offset = $length - 1;
    }

    /**
     * 约定
     *
     * 当左边文本为空时表示从字符串开头开始取值
     * 当右边文本为空时表示取值到字符串结尾
     */

    $leftPos = 0;

    if (empty($leftText)) {
        $leftPos = 0;
    } else {
        $leftPos = strpos($wholeText, $leftText, $offset);
        if ($leftPos === false) {
            $position = -1;
            return false;
        }
        $leftPos += strlen($leftText);
    }

    // 保存找到的位置
    $position = $leftPos;

    $rightPos = 0;

    if (empty($rightText)) {
        // [$leftPos,$rightPos -1]
        // [$leftPos,$rightPos)
        $rightPos = $length;
    } else {
        $rightPos = strpos($wholeText, $rightText, $leftPos);
        if ($rightPos === false) {
            $position = -1;
            return false;
        }
    }

    $middleText = substr($wholeText, $leftPos, $rightPos - $leftPos);
    if (($padding & INCLUDING_LEFT) === INCLUDING_LEFT) {
        $middleText = "{$leftText}{$middleText}";
    }
    if (($padding & INCLUDING_RIGHT) === INCLUDING_RIGHT) {
        $middleText .= $rightText;
    }

    return $middleText;
}

/**
 * 取中间文本组
 * @param  string           $wholeText 寻找文本
 * @param  string           $leftText  左边文本
 * @param  string           $rightText 右边文本
 * @param  int              $offset    开始查找位置
 * @param  int              &$position 返回最后找到文本的位置，找不到返回-1
 * @param  int              $padding   填充
 * @return string[]|false
 */
function getMiddleTexts($wholeText, $leftText, $rightText, $offset = 0, &$position = 0, $padding = INCLUDING_NOTHING)
{
    $textGroup = [];
    $tmp       = '';
    $position  = -1;
    do {
        // 这里不能使用padding，否则匹配出来的文本和 $position 是对不上的
        $tmp = getMiddleText($wholeText, $leftText, $rightText, $offset + strlen($tmp), $offset, INCLUDING_NOTHING);
        if ($tmp === false) {
            break;
        } else {
            $position = $offset;
        }

        $result = $tmp;

        // 最后自己做 padding 处理
        if (($padding & INCLUDING_LEFT) === INCLUDING_LEFT) {
            $result = "{$leftText}{$result}";
        }
        if (($padding & INCLUDING_RIGHT) === INCLUDING_RIGHT) {
            $result .= $rightText;
        }

        $textGroup[] = $result;
    } while (true);

    return $textGroup;
}
