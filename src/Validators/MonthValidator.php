<?php
/**
 * 框架级Validator
 * @author wqq <wuqiangqiang@uniondrug.cn>
 * @date   2019-09-17
 */
namespace Uniondrug\Validation\Validators;

use Uniondrug\Validation\Validation;

/**
 * @package Uniondrug\Validation\Validators
 */
class MonthValidator extends Validation
{
    private static $regexp = "/^\d{4}[\-|\/|\.](0?[1-9]|1[012])$/";

    /**
     * 执行验证
     *
     * @param \Phalcon\Validation $validation Validation对象
     * @param string              $attribute  待验证的字段/参数名
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
        // 4. 格式检查
        if (preg_match(self::$regexp, $value) > 0) {
            return true;
        }
        // 5. 格式有错
        $validation->appendMessage(new Message("参数'{$attribute}'的值不是有效的月份", $attribute));

        return false;
    }
}
