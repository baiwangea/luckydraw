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

namespace app\backend\validate\lottery;

use app\common\basics\Validate;

/**
 * 抽奖码验证器
 */
class LotteryCodeValidate extends Validate
{
    protected $rule = [
        'id'       => 'require|integer|gt:0',
        'ids'      => 'require|array',
        'code'     => 'require|length:6,20|alphaNum',
        'prize_id' => 'require|integer|gt:0',
        'quantity' => 'require|integer|between:1,10000',
    ];

    protected $message = [
        'id.require'         => '参数缺失',
        'id.integer'         => 'ID必须是整数',
        'id.gt'              => 'ID必须大于0',
        'ids.require'        => '请选择要操作的数据',
        'ids.array'          => 'IDS必须是数组',
        'code.require'       => '抽奖码不能为空',
        'code.length'        => '抽奖码长度须在6-20位字符',
        'code.alphaNum'      => '抽奖码只能是字母和数字',
        'prize_id.require'   => '奖品ID不能为空',
        'prize_id.integer'   => '奖品ID必须是整数',
        'prize_id.gt'        => '奖品ID必须大于0',
        'quantity.require'   => '生成数量不能为空',
        'quantity.integer'   => '生成数量必须是整数',
        'quantity.between'   => '生成数量必须在1-10000之间',
    ];

    protected $scene = [
        'detail'        => ['id'],
        'batchGenerate' => ['prize_id', 'quantity'],
        'del'           => ['id'],
        'batchDel'      => ['ids'],
        'verify'        => ['code'],
    ];
}