<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-01-05
 */
namespace Uniondrug\Validation\Validators;

use Phalcon\Validation\Message;
use Uniondrug\Validation\Validator;

/**
 * 身份证号验证
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [];
 * $validator = new EmailValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class IdCardValidator extends Validator
{
    private static $IDRe18 = "/^([1-6][1-9]|50)\d{4}(18|19|20)\d{2}((0[1-9])|10|11|12)(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$";
    private static $IDre15 = "/^([1-6][1-9]|50)\d{4}\d{2}((0[1-9])|10|11|12)(([0-2][1-9])|10|20|30|31)\d{3}$/";

    /**
     * 执行验证
     *
     * @param \Phalcon\Validation $validation Validation对象
     * @param string              $attribute 待验证的字段/参数名
     *
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        // 1. 必须和非空验证
        if (!$this->validateRequired($validation, $attribute) || !$this->validateEmpty($validation, $attribute)) {
            return false;
        }
        // 2. 格式检查
        $value = $validation->getValue($attribute);
        // 3. 允许为空(当禁止为空时已由validateEmpty()过滤)
        if ($value === '') {
            return true;
        }
        // 根据长度判断
        if (strlen($value) == 18) {
            // 4. 格式检查
            if (preg_match(self::$IDRe18, $value) > 0) {
                return true;
            }
        } else if (strlen($value) == 15) {
            if (preg_match(self::$IDre15, $value) > 0) {
                return true;
            }
        }
        // 5. 格式有错
        $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的身份证号", $attribute));
        return false;
    }
}