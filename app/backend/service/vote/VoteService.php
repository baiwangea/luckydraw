<?php
declare (strict_types = 1);

namespace app\backend\service\vote;

use app\common\basics\Service;
use app\common\exception\OperateException;
use app\common\model\lottery\Vote;
use app\common\model\lottery\VoteRecord;
use think\facade\Db;
use think\Request;

class VoteService extends Service
{
    /**
     * 投票人列表
     *
     * @param array $get
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function lists(array $get): array
    {
        $model = new Vote();
        $model = $model->scope('search', $get);
        $lists = $model->paginate([
            'list_rows' => $get['limit'] ?? 15,
            'page'      => $get['page'] ?? 1,
            'var_page'  => 'page'
        ]);

        return [
            'count' => $lists->total(),
            'list'  => $lists->items(),
        ];
    }

    /**
     * 增加票数（后台管理员操作）
     *
     * @param array $post
     * @throws OperateException
     */
    public static function addBallot(array $post): void
    {
        $id = intval($post['id'] ?? 0);
        $num = intval($post['num'] ?? 0);

        if (!$id) {
            throw new OperateException('参数错误: 无效的ID');
        }

        if ($num <= 0) {
            throw new OperateException('票数必须是正整数');
        }

        Db::startTrans();
        try {
            // 1. 使用悲观锁锁定该行，防止并发问题
            $candidate = Vote::where('id', $id)->lock(true)->find();
            if (!$candidate) {
                throw new OperateException('操作失败: 候选项不存在');
            }

            // 2. 手动增加票数并保存
            $candidate->ballot += $num;
            if ($candidate->save() === false) {
                throw new \Exception('更新票数时数据库保存失败');
            }

            // 3. 记录管理员操作日志
            /** @var Request $request */
            $request = request();
            $adminUser = session('adminUser');
            $adminIdentifier = $adminUser['username'] ?? ('AdminID:' . ($adminUser['id'] ?? 0));
            $userAgent = sprintf('[BACKEND] Admin %s added %d votes.', $adminIdentifier, $num);

            VoteRecord::create([
                'vid'         => $id,
                'ballot'      => $num,
                'ip'          => $request->ip(),
                'user_agent'  => $userAgent,
                'voted_at'    => date('Y-m-d H:i:s'),
            ]);

            // 4. 提交事务
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException('上票失败: ' . $e->getMessage());
        }
    }
}
