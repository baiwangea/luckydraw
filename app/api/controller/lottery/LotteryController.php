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

namespace app\api\controller\lottery;

use app\common\basics\Api;
use app\common\exception\OperateException;
use app\api\service\lottery\LotteryService;
use app\common\utils\AjaxUtils;
use think\response\Json;
use hg\apidoc\annotation as Apidoc;

/**
 * 抽奖接口
 * @Apidoc\Group("lottery")
 */
class LotteryController extends Api
{
    protected array $notNeedLogin = ['prizes', 'draw', 'record'];

    /**
     * @Apidoc\Title("获取奖品列表")
     * @Apidoc\Desc("用于展示在抽奖页面的奖品列表")
     * @Apidoc\Method("GET")
     * @Apidoc\Url("/api/lottery.lottery/prizes")
     * @Apidoc\Returned("id", type="int", desc="奖品ID")
     * @Apidoc\Returned("name", type="string", desc="奖品名称")
     * @Apidoc\Returned("img", type="string", desc="奖品图片URL")
     */
    public function prizes(): Json
    {
        $result = LotteryService::prizes();
        return AjaxUtils::success($result);
    }

    /**
     * @Apidoc\Title("执行抽奖")
     * @Apidoc\Desc("根据抽奖码执行抽奖动作")
     * @Apidoc\Method("POST")
     * @Apidoc\Url("/api/lottery.lottery/draw")
     * @Apidoc\Param("code", type="string", require=true, desc="抽奖码")
     * @Apidoc\Returned("id", type="int", desc="奖品ID, 0表示未中奖")
     * @Apidoc\Returned("name", type="string", desc="奖品名称, ‘谢谢参与’表示未中奖")
     * @Apidoc\Returned("img", type="string", desc="奖品图片URL")
     */
    public function draw(): Json
    {
        try {
            $result = LotteryService::draw($this->request->post());
            return AjaxUtils::success($result);
        } catch (OperateException $e) {
            return AjaxUtils::error($e->getMessage());
        }
    }

    /**
     * @Apidoc\Title("查询抽奖记录")
     * @Apidoc\Desc("根据抽奖码查询抽奖结果")
     * @Apidoc\Method("GET")
     * @Apidoc\Url("/api/lottery.lottery/record")
     * @Apidoc\Param("code", type="string", require=true, desc="抽奖码")
     * @Apidoc\Returned("prize_name", type="string", desc="奖品名称")
     * @Apidoc\Returned("is_win", type="int", desc="是否中奖 (1=是, 0=否)")
     * @Apidoc\Returned("draw_time", type="string", desc="抽奖时间")
     */
    public function record(): Json
    {
        try {
            $code = $this->request->get('code', '');
            if (empty($code)) {
                throw new OperateException('抽奖码不能为空');
            }
            $result = LotteryService::getRecordByCode($code);
            return AjaxUtils::success($result);
        } catch (OperateException $e) {
            return AjaxUtils::error($e->getMessage());
        }
    }
}
