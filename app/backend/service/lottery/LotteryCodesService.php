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
declare (strict_types=1);

namespace app\backend\service\lottery;

use app\common\basics\Service;
use app\common\enums\IsUsedEnum;
use app\common\exception\OperateException;
use app\common\model\lottery\LotteryCodes;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 抽奖码服务类
 */
class LotteryCodesService extends Service
{
    /**
     * 抽奖码列表
     *
     * @param array $get
     * @return array
     * @throws DbException
     */
    public static function lists(array $get): array
    {
        self::setSearch([
            '=' => ['prize@lc.prize_id', 'used@lc.is_used'],
            'keyword' => ['code@lc.code']
        ]);

        $model = new LotteryCodes();
        $lists = $model->alias('lc')
            ->field([
                'lc.id', 'lc.code', 'lc.is_used', 'lc.used_time', 'lc.create_time',
                'p.name as prize_name'
            ])
            ->leftJoin('prizes p', 'lc.prize_id = p.id')
            ->where(self::$searchWhere)
            ->order('lc.id desc')
            ->paginate([
                'page' => $get['page'] ?? 1,
                'list_rows' => $get['limit'] ?? 20,
                'var_page' => 'page'
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['is_used_text'] = IsUsedEnum::getMsgByCode($item['is_used']);
            $item['prize_name'] = $item['prize_name'] ?? '未关联奖品';
            $item['used_time'] = $item['used_time'] > 0 ? date('Y-m-d H:i:s', $item['used_time']) : '--';
        }

        return ['count' => $lists['total'], 'list' => $lists['data']] ?? [];
    }

    /**
     * 抽奖码详情
     *
     * @param int $id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function detail(int $id): array
    {
        $model = new LotteryCodes();
        $detail = $model->alias('lc')
            ->field([
                'lc.id',
                'lc.code',
                'lc.prize_id',
                'lc.is_used',
                'lc.used_time',
                'lc.create_time',
                'lc.update_time',
                'p.name as prize_name'
            ])
            ->leftJoin('prizes p', 'lc.prize_id = p.id')
            ->where('lc.id', $id)
            ->findOrFail()
            ->toArray();

        $detail['is_used_text'] = IsUsedEnum::getMsgByCode($detail['is_used']);
        $detail['prize_name'] = $detail['prize_name'] ?? '未关联奖品';
        $detail['used_time'] = $detail['used_time'] > 0 ? date('Y-m-d H:i:s', $detail['used_time']) : '--';

        return $detail;
    }

    /**
     * 批量生成抽奖码
     *
     * @param array $post
     * @throws OperateException
     */
    public static function add(array $post): void
    {
        $prizeId = intval($post['prize_id'] ?? 0);
        $quantity = intval($post['quantity'] ?? 0);
        $prefix = trim($post['prefix'] ?? 'LC');

        if ($quantity <= 0 || $quantity > 10000) {
            throw new OperateException('生成数量必须在1-10000之间');
        }

        try {
            $codes = [];
            for ($i = 0; $i < $quantity; $i++) {
                $codes[] = [
                    'code' => $prefix . '-' . uniqid(),
                    'prize_id' => $prizeId > 0 ? $prizeId : null,
                    'is_used' => 0,
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }

            (new LotteryCodes())->insertAll($codes);
        } catch (Exception $e) {
            throw new OperateException('抽奖码生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 编辑抽奖码
     *
     * @param array $post
     * @throws OperateException
     */
    public static function edit(array $post): void
    {
        $id = intval($post['id'] ?? 0);
        $prizeId = intval($post['prize_id'] ?? 0);

        try {
            $model = LotteryCodes::findOrFail($id);

            if ($model->is_used) {
                throw new OperateException('已使用的抽奖码不能编辑');
            }

            $model->prize_id = $prizeId > 0 ? $prizeId : null;
            $model->save();

        } catch (ModelNotFoundException $e) {
            throw new OperateException('抽奖码不存在');
        } catch (Exception $e) {
            throw new OperateException($e->getMessage());
        }
    }

    /**
     * 删除抽奖码
     *
     * @param array $ids
     * @throws OperateException
     */
    public static function del(array $ids): void
    {
        if (empty($ids)) {
            throw new OperateException('参数错误');
        }

        $usedCount = LotteryCodes::whereIn('id', $ids)->where('is_used', 1)->count();
        if ($usedCount > 0) {
            throw new OperateException('包含已使用的抽奖码，无法删除');
        }

        LotteryCodes::destroy($ids);
    }
}
