<?php
/**
 * 框架级验证
 *
 * @author wsfuyibing <websearch@163.com>
 * @date   2017-12-26
 */

namespace Uniondrug\Validation;

use Phalcon\Validation as PhalconValidation;
use Phalcon\Validation\Message\Group;
use Phalcon\Validation\ValidatorInterface;
use Phalcon\ValidationInterface;

/**
 * 参数检查, 运行以下类型
 * <ul>
 * <li>date</li>
 * <li>datetime</li>
 * <li>double</li>
 * <li>email</li>
 * <li>integer</li>
 * <li>mobile</li>
 * <li>string</li>
 * <li>time</li>
 * </ul>
 *
 * @package Pails\Helpers
 */
class Validation extends PhalconValidation
{
    /**
     * 参数(字段)的记数器
     * 1. 指定参数使用了几种规则['validators' => 3]
     * 2. 指定的规则中验证失败的有几个['failures' => 2]
     * 3. 当 validators == failures 时为未能过验证
     * [如下例]
     * 电话号码参数: 可以填写手机号或固定电话, 任意一项通过即正确。
     *
     * @var array
     */
    private $stats = [];

    /**
     * @var array 待合并的数据组
     */
    private $mergeData = [];

    /**
     * 初始化错误信息
     */
    public function initialize()
    {
        $this->setDefaultMessages([
            "Alnum"             => "字段 :field 只能包括字母和数字",
            "Alpha"             => "字段 :field 只能包含字母",
            "Between"           => "字段 :field 必须在 :min 和 :max 之间",
            "Confirmation"      => "字段 :field 和 :with 一致",
            "Digit"             => "字段 :field 必须是数字",
            "Email"             => "字段 :field 必须是一个有效的Email地址",
            "ExclusionIn"       => "字段 :field 不可为列表 :domain 内的值",
            "FileEmpty"         => "字段 :field 不能为空",
            "FileIniSize"       => "文件 :field 超过了最大大小",
            "FileMaxResolution" => "文件 :field 不能超过 :max",
            "FileMinResolution" => "文件 :field 不能小于 :min",
            "FileSize"          => "文件 :field 大小超过了限制，最大 :max",
            "FileType"          => "文件 :field 必须是以下类型: :types",
            "FileValid"         => "字段 :field 文件不正确",
            "Identical"         => "字段 :field does not have the expected value",
            "InclusionIn"       => "字段 :field 必须在列表 :domain 内",
            "Numericality"      => "字段 :field 不是有效的数字格式",
            "PresenceOf"        => "字段 :field 不能为空",
            "Regex"             => "字段 :field 格式不正确",
            "TooLong"           => "字段 :field 长度不能超过 :max",
            "TooShort"          => "字段 :field 长度不能小于 :min",
            "Uniqueness"        => "字段 :field 必须唯一",
            "Url"               => "字段 :field 必须是一个有效的URL",
            "CreditCard"        => "字段 :field 不是一个有效的卡号",
            "Date"              => "字段 :field 不是一个有效的日期/时间格式",
            "Crontab"           => "字段 :field 不是一个有效的Crontab格式",
            "Json"              => "字段 :field 不是有一个有效的JSON: :err",
            "Callback"          => "字段 :field 不合法",
        ]);
    }

    /**
     * 添加验证规则
     *
     * @param mixed|string       $field     字段/参数名称
     * @param ValidatorInterface $validator 验证对象
     *
     * @return ValidationInterface
     */
    public function add($field, ValidatorInterface $validator)
    {
        /**
         * 同步计数器
         */
        if (!isset($this->stats[$field])) {
            $this->stats[$field] = [
                'validators' => 0,
                'failures'   => 0,
            ];
        }
        $this->stats[$field]['validators'] += 1;

        /**
         * 加入规则
         */
        return parent::add($field, $validator);
    }

    /**
     * 后置验证
     *
     * @param array|object $data     待验证的数据源
     * @param null         $entity   unknown
     * @param Group        $messages 消费集合
     */
    public function afterValidation($data, $entity, $messages)
    {
        /**
         * @var PhalconValidation\Message $message
         */
        foreach ($messages as $message) {
            $field = $message->getField();
            if (isset($this->stats[$field])) {
                $this->stats[$field]['failures'] += 1;
            }
        }
    }

    /**
     * 前置验证
     *
     * @param array|object $data     待验证的数据源
     * @param null         $entity   unknown
     * @param Group        $messages 消费集合
     *
     * @return bool
     */
    public function beforeValidation($data, $entity, $messages)
    {
        return true;
    }

    /**
     * 读取需合并的默认值
     *
     * @return array
     */
    public function getMergeDefault()
    {
        return $this->mergeData;
    }

    /**
     * 读取错误原因
     *
     * @return string
     */
    public function getFailureMessage()
    {
        $attribute = null;
        $message = null;
        foreach ($this->stats as $key => $stat) {
            if ($stat['validators'] === $stat['failures']) {
                $attribute = $key;
                break;
            }
        }
        foreach ($this->getMessages() as $message) {
            if ($message->getField() === $attribute) {
                $message = $message->getMessage();
                break;
            }
        }

        return $message;
    }

    /**
     * 是否有错误, 通过检测错误消息的数量是否大于0
     *
     * @return bool
     */
    public function hasFailure()
    {
        return $this->getMessages()->count() > 0;
    }

    /**
     * 合并默认数据, 符合如下条件
     * 1. 指定的参数未传递
     * 2. 配置项中已为此字段指定了默认值
     *
     * @param string $key   字段名
     * @param mixed  $value 字段值
     */
    public function mergeDefault($key, $value)
    {
        $this->mergeData[$key] = $value;
    }

    /**
     * 验证过程
     *
     * @param array|object $data   待验证的数据源
     * @param null         $entity unknown
     *
     * @return Group
     */
    public function validate($data = null, $entity = null)
    {
        return parent::validate($data, $entity);
    }
}
