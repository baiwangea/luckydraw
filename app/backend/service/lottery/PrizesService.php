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
use app\common\enums\PrizeTypeEnum;
use app\common\exception\OperateException;
use app\common\model\lottery\Prizes;
use app\common\utils\UrlUtils;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 奖品服务类
 */
class PrizesService extends Service
{
    /**
     * 奖品列表
     *
     * @param array $get
     * @return array
     * @throws DbException
     */
    public static function lists(array $get): array
    {
        self::setSearch([
            '='       => ['type@p.type'],
            'keyword' => ['name@p.name']
        ]);

        $model = new Prizes();
        $lists = $model->alias('p')
            ->field('p.id, p.name, p.img, p.price, p.stock, p.sort, p.type, p.create_time')
            ->where(self::$searchWhere)
            ->where(['p.is_delete' => 0])
            ->order('p.sort asc, p.id desc')
            ->paginate([
                'page'      => $get['page'] ?? 1,
                'list_rows' => $get['limit'] ?? 20,
                'var_page'  => 'page'
            ])->toArray();

        foreach ($lists['data'] as &$item) {
            $item['img'] = UrlUtils::toAbsoluteUrl($item['img']);
            $item['type_text'] = PrizeTypeEnum::getMsgByCode($item['type']);
        }

        return ['count' => $lists['total'], 'list' => $lists['data']] ?? [];
    }

    /**
     * 奖品详情
     *
     * @param int $id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function detail(int $id): array
    {
        $model = new Prizes();
        $detail = $model->where(['is_delete' => 0])->findOrFail($id)->toArray();
        $detail['img'] = UrlUtils::toAbsoluteUrl($detail['img']);
        return $detail;
    }

    /**
     * 新增奖品
     *
     * @param array $post
     * @throws OperateException
     */
    public static function add(array $post): void
    {
        try {
            $model = new Prizes();
            $model->name  = $post['name'];
            $model->img   = $post['img'];
            $model->price = $post['price'] ?? 0.00;
            $model->stock = $post['stock'] ?? 0;
            $model->sort  = $post['sort'] ?? 0;
            $model->type  = $post['type'];
            $model->save();
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }
    }

    /**
     * 编辑奖品
     *
     * @param array $post
     * @throws OperateException
     */
    public static function edit(array $post): void
    {
        try {
            $model = Prizes::findOrFail($post['id']);
            $model->name  = $post['name'];
            $model->img   = $post['img'];
            $model->price = $post['price'] ?? 0.00;
            $model->stock = $post['stock'] ?? 0;
            $model->sort  = $post['sort'] ?? 0;
            $model->type  = $post['type'];
            $model->save();
        } catch (ModelNotFoundException $e) {
            throw new OperateException('奖品不存在');
        } catch (\Exception $e) {
            throw new OperateException($e->getMessage());
        }
    }

    /**
     * 删除奖品(软删除)
     *
     * @param array $ids
     * @throws OperateException
     */
    public static function del(array $ids): void
    {
        if (empty($ids)) {
            throw new OperateException('参数错误');
        }

        Prizes::update([
            'is_delete'   => 1,
            'delete_time' => time()
        ], [['id', 'in', $ids]]);
    }

    /**
     * 获取奖品选项
     *
     * @return array
     * @throws DbException
     */
    public static function options(): array
    {
        return Prizes::where(['is_delete' => 0])
            ->field(['id as value', 'name'])
            ->order('sort asc, id desc')
            ->select()
            ->toArray();
    }
}
