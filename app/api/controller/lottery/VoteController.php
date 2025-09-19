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
use app\api\service\vote\VoteService;
use app\common\utils\AjaxUtils;
use think\response\Json;
use hg\apidoc\annotation as Apidoc;

/**
 * 投票接口
 * @Apidoc\Group("vote")
 */
class VoteController extends Api
{
    protected array $notNeedLogin = ['candidates', 'vote', 'record'];

    /**
     * @Apidoc\Title("获取候选人列表")
     * @Apidoc\Desc("用于展示在投票页面的候选人列表")
     * @Apidoc\Method("GET")
     * @Apidoc\Url("/api/lottery.vote/candidates")
     * @Apidoc\Returned("id", type="int", desc="候选人ID")
     * @Apidoc\Returned("username", type="string", desc="候选人名称")
     * @Apidoc\Returned("photo", type="string", desc="候选人图片URL")
     * @Apidoc\Returned("ballot", type="int", desc="票数")
     */
    public function candidates(): Json
    {
        $result = VoteService::candidates();
        return AjaxUtils::success('success', $result);
    }

    /**
     * @Apidoc\Title("执行投票")
     * @Apidoc\Desc("给指定候选人投票，为防止刷票，建议前端生成cookie_id和fingerprint")
     * @Apidoc\Method("POST")
     * @Apidoc\Url("/api/lottery.vote/vote")
     * @Apidoc\Param("id", type="int", require=true, desc="候选人ID")
     * @Apidoc\Param("cookie_id", type="string", require=false, desc="用于识别用户的Cookie ID")
     * @Apidoc\Param("fingerprint", type="string", require=false, desc="浏览器指纹")
     * @Apidoc\Returned("code", type="int", desc="状态码")
     * @Apidoc\Returned("msg", type="string", desc="消息")
     */
    public function vote(): Json
    {
        try {
            VoteService::vote($this->request->post());
            return AjaxUtils::success( '投票成功');
        } catch (OperateException $e) {
            return AjaxUtils::error($e->getMessage());
        }
    }

//    /**
//     * @Apidoc\Title("查询投票记录")
//     * @Apidoc\Desc("查询某个候选项的投票记录")
//     * @Apidoc\Method("GET")
//     * @Apidoc\Url("/api/lottery.vote/record")
//     * @Apidoc\Param("id", type="int", require=true, desc="候选人ID")
//     * @Apidoc\Returned("list", type="array", desc="投票记录列表")
//     */
//    public function record(): Json
//    {
//        try {
//            $id = $this->request->get('id', 0, 'intval');
//            if (!$id) {
//                throw new OperateException('参数错误');
//            }
//            $result = VoteService::getRecordByVid($id);
//            return AjaxUtils::success('success', $result);
//        } catch (OperateException $e) {
//            return AjaxUtils::error($e->getMessage());
//        }
//    }
}
