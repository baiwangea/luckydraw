<?php
// +----------------------------------------------------------------------
// | WaitAdmin快速开发后台管理系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习程序代码,建议反馈是我们前进的动力
// | 程序完全开源可支持商用,允许去除界面版权信息
// | gitee:   https://gitee.com/wafts/waitadmin-php
// | github:  https://github.com/topwait/waitadmin-php
// | 官方网站: https://www.waitadmin.cn
// | WaitAdmin团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | Author: WaitAdmin Team <2474369941@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\api\validate\lottery;

use app\common\basics\Validate;

/**
 * 抽奖验证器
 */
class LotteryValidate extends Validate
{
    protected $rule = [
        'id'         => 'require|integer|gt:0',
        'code'       => 'require|length:6,20|alphaNum',
        'codes'      => 'require|array|max:50',
        'user_email' => 'email|length:5,100',
        'page'       => 'integer|gt:0',
        'limit'      => 'integer|between:1,100',
    ];

    protected $message = [
        'id.require'           => '参数缺失',
        'id.integer'           => 'ID必须是整数',
        'id.gt'                => 'ID必须大于0',
        'code.require'         => '抽奖码不能为空',
        'code.length'          => '抽奖码长度须在6-20位字符',
        'code.alphaNum'        => '抽奖码只能是字母和数字',
        'codes.require'        => '抽奖码列表不能为空',
        'codes.array'          => '抽奖码列表必须是数组',
        'codes.max'            => '一次最多验证50个抽奖码',

        'user_email.email'     => '用户邮箱格式不正确',
        'user_email.length'    => '用户邮箱长度须在5-100位字符',
        'page.integer'         => '页码必须是整数',
        'page.gt'              => '页码必须大于0',
        'limit.integer'        => '每页数量必须是整数',
        'limit.between'        => '每页数量必须在1-100之间',
    ];

    protected $scene = [
        'draw'         => ['code', 'user_email'],
        'verify'       => ['code'],
        'records'      => ['user_email', 'page', 'limit'],
        'checkCode'    => ['code'],
        'batchVerify'  => ['codes'],
        'prizeDetail'  => ['id'],
    ];
}