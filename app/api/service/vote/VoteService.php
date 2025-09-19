<?php
declare (strict_types = 1);

namespace app\api\service\vote;

use app\common\basics\Service;
use app\common\exception\OperateException;
use app\common\model\lottery\Vote;
use app\common\model\lottery\VoteRecord;
use app\common\utils\UrlUtils;
use think\facade\Db;
use think\Request;

class VoteService extends Service
{
    /**
     * 获取候选人列表
     */
    public static function candidates(): array
    {
        $lists = Vote::where('status', 1)
            ->field(['id', 'username', 'photo', 'ballot'])
            ->select()
            ->toArray();

        foreach ($lists as &$item) {
            if (!empty($item['photo'])) {
                $item['photo'] = UrlUtils::toAbsoluteUrl($item['photo']);
            }
        }

        return $lists;
    }

    /**
     * 投票
     * @throws OperateException
     * @throws \Exception
     */
    public static function vote(array $post): void
    {
        $now = time();
        $startDate = strtotime('2025-09-20 00:00:00');
        $endDate = strtotime('2026-01-01 23:59:59');

        if ($now < $startDate || $now > $endDate) {
            throw new OperateException('Voting period: September 20, 2025, to January 1, 2026');
        }

        $vid = intval($post['id'] ?? 0);
        if (!$vid) {
            throw new OperateException('Invalid parameter');
        }

        // 预先检查，减少不必要的事务开启
        $preCheckCandidate = Vote::find($vid);
        if (!$preCheckCandidate) {
            throw new OperateException('Candidate does not exist');
        }

        if ($preCheckCandidate->status != 1) {
            throw new OperateException('This candidate is not participating in the event');
        }

        /** @var Request $request */
        $request = request();
        $ip = $request->ip();
        $cookieId = $post['cookie_id'] ?? null;
        $fingerprint = $post['fingerprint'] ?? null;

        $orConditions = [];
        if ($ip) {
            $orConditions[] = ['ip', '=', $ip];
        }
        if (!empty($cookieId)) {
            $orConditions[] = ['cookie_id', '=', $cookieId];
        }
        if (!empty($fingerprint)) {
            $orConditions[] = ['fingerprint', '=', $fingerprint];
        }

        if (!empty($orConditions)) {
            $hasVoted = VoteRecord::where($orConditions, 'or')
                ->whereTime('voted_at', 'today')
                ->find();
            if ($hasVoted) {
                throw new OperateException('You can only vote once a day');
            }
        }

        Db::startTrans();
        try {
            $candidateToUpdate = Vote::where('id', $vid)->lock(true)->find();
            if (!$candidateToUpdate) {
                 throw new OperateException('Candidate does not exist or has been deleted');
            }

            $candidateToUpdate->ballot += 1;
            
            // 增加严格的保存结果检查
            if ($candidateToUpdate->save() === false) {
                throw new \Exception('Failed to update votes, save() method returned false');
            }

            VoteRecord::create([
                'vid'         => $vid,
                'ip'          => $ip,
                'ballot'      => 1,
                'cookie_id'   => $cookieId,
                'fingerprint' => $fingerprint,
                'user_agent'  => $request->header('user-agent'),
                'voted_at'    => date('Y-m-d H:i:s'),
            ]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            // 将更详细的数据库错误信息暴露出来，便于调试
            throw new OperateException('Vote failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取投票记录
     */
    public static function getRecordByVid(int $vid): array
    {
        return VoteRecord::where('vid', $vid)
            ->order('id', 'desc')
            ->select()
            ->toArray();
    }
}
