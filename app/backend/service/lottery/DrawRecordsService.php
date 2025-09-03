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

namespace app\backend\service\lottery;

use app\common\basics\Service;
use app\common\enums\DrawStatusEnum;
use app\common\model\lottery\DrawRecords;
use app\common\utils\UrlUtils;
use think\db\exception\DbException;

/**
 * 抽奖记录服务类
 */
class DrawRecordsService extends Service
{
    /**
     * 抽奖记录列表
     *
     * @param array $get
     * @return array
     * @throws DbException
     */
    public static function lists(array $get): array
    {
        self::setSearch([
            '='        => ['status@dr.status', 'prize@dr.prize_id'],
            'datetime' => ['datetime@dr.draw_time'],
            'keyword'  => [
                'email' => ['%like%', 'dr.user_email'],
                'code'  => ['%like%', 'lc.code']
            ]
        ]);

        $model = new DrawRecords();
        $lists = $model->alias('dr')
            ->field([
                'dr.id', 'dr.user_email', 'dr.draw_time', 'dr.status',
                'lc.code as lottery_code',
                'p.name as prize_name',
                'p.img as prize_image'
            ])
            ->leftJoin('lottery_codes lc', 'dr.code_id = lc.id')
            ->leftJoin('prizes p', 'dr.prize_id = p.id')
            ->where(self::$searchWhere)
            ->order('dr.id desc')
            ->paginate([
                'page'      => $get['page']  ?? 1,
                'list_rows' => $get['limit'] ?? 20,
                'var_page'  => 'page'
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['prize_name']  = $item['prize_name'] ?: '未中奖';
            $item['prize_image'] = $item['prize_image'] ? UrlUtils::toAbsoluteUrl($item['prize_image']) : '';
            $item['draw_time'] = date('Y-m-d H:i:s', $item['draw_time']);
            $item['status_text'] = DrawStatusEnum::getMsgByCode($item['status']);
        }

        return ['count' => $lists['total'], 'list' => $lists['data']] ?? [];
    }

    /**
     * 抽奖记录详情
     *
     * @param int $id
     * @return array
     * @throws DbException
     */
    public static function detail(int $id): array
    {
        $model = new DrawRecords();
        $detail = $model->alias('dr')
            ->field([
                'dr.id', 'dr.user_email', 'dr.draw_time', 'dr.status',
                'lc.code as lottery_code',
                'p.name as prize_name',
                'p.img as prize_image',
                'p.type as prize_type'
            ])
            ->leftJoin('lottery_codes lc', 'dr.code_id = lc.id')
            ->leftJoin('prizes p', 'dr.prize_id = p.id')
            ->where('dr.id', $id)
            ->findOrFail()
            ->toArray();

        $detail['prize_name']  = $detail['prize_name'] ?: '未中奖';
        $detail['draw_time']  = date('Y-m-d H:i:s', $detail['draw_time']);
        $detail['prize_image'] = $detail['prize_image'] ? UrlUtils::toAbsoluteUrl($detail['prize_image']) : '';
        $detail['status_text'] = DrawStatusEnum::getMsgByCode($detail['status']);

        return $detail;
    }
}
