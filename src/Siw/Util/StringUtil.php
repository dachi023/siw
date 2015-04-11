<?php
namespace Siw\Util;

class StringUtil {

  public static function deleteLineSeparator($str) {
    return str_replace("\n", '', $str);
  }
}