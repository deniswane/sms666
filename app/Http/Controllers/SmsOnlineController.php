<?php
/**
 * 连网订单
 */
namespace App\Http\Controllers;
header("Content-type: text/html; charset=utf-8");

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mail;

/**
 * Class ApiController
 * @package App\Http\Controllers
 * 是否有新短信标记
 */
class SmsOnlineController extends Controller
{

    public function online_phone($user,$keywords,$receive,$p)
    {
        //有权限访问 返回的标记
        $content = 'id' . $user->id;

        //写入日志的信息
        $profile = $user->name . '/online_set_gjz.txt';
        $data = [
            'email' => $user->email,
            'keywords' => "$keywords[0]&$keywords[1]",
            'phone' => $receive,
            'provice' => $p,
            'success' => 'success'
        ];

        $sctstart = $maskkeyone = $this->transcode($keywords[0]);;
        $sctend = $maskkeytwo = $this->transcode($keywords[1]);
        $snumstart = 'hello';
        $province = [
            '安徽' => '1',  '安徽省' => '1',  '福建' => '2',  '福建省' => '2',  '甘肃' => '3', '甘肃省' => '3',   '广东' => '4',   '广东省' => '4',   '广西' => '5',    '广西省' => '5',    '贵州' => '6',  '贵州省' => '6',
            '海南' => '7', '海南省' => '7',   '河北' => '8',  '河北省' => '8',  '河南' => '9', '河南省' => '9',   '黑龙江' => '10', '黑龙江省' => '10', '湖北' => '11', '湖北省' => '11',   '湖南' => '12', '湖南省' => '12',
            '吉林' => '13', '吉林省' => '13', '江苏' => '14', '江苏省' => '14', '江西' => '15', '江西省' => '15', '辽宁' => '16',  '辽宁省' => '16',  '内蒙古' => '17', '内蒙古省' => '17', '宁夏' => '18', '宁夏省' => '18',
            '青海' => '19', '青海省' => '19', '山东' => '20', '山东省' => '20', '山西' => '21', '山西省' => '21', '陕西' => '22',  '陕西省' => '22',  '四川' => '23',   '四川省' => '24',   '西藏' => '24', '西藏省' => '24',
            '新疆' => '25', '新疆省' => '25', '云南' => '26', '云南省' => '26', '浙江' => '27', '浙江省' => '27', '重庆' => '560', '重庆市' => '560', '北京' => '561',  '北京市' => '561',  '上海' => '562', '上海市' => '562',
            '天津' => '563', '天津市' => '563'
        ];
        if (!$p){
            $provinceid =rand(1,27);
        }else{
            $provinceid = $province[$p];
        }

        $new_data   = [
            'phone'     =>      $receive,
            'smstext'   =>      $content,
            'addtime'   =>      microtime(true),
            'stype'     =>      '1',
            'sctstart'  =>      $sctstart,
            'provinceid'=>      $provinceid,
            'sctend'    =>      $sctend,
            'snumstart' =>      $snumstart,
            'maskkeyone'=>      $maskkeyone,
            'maskkeytwo'=>      $maskkeytwo,
            'nowtime'   =>      date("Y-m-d H:i:s"),
            'software' =>       '',

        ];

        $this->setorder($new_data,$profile, $data);
    }


    /*设置订单 联网订单不加密直接插入数据表
     * phone    发送的手机号
     * content  返回的关键字
     * receive  接收的手机号
     * gjz      返回截取的位置
     */
    public function setorder($new_data, $profile, $parame)
    {

        /**
         * phone    短信猫号
         * content  老人机回复内容
         * stype    1
         * sctstart 关键字前半部分 Unicode码
         * sctend   关键字后半部分 Unicode码
         * snumstart 固定的    Unicode码
         * maskkeyone 关键字前半部分 Unicode码
         * maskkeytwo   关键字后半部分 Unicode码
         */


        //短信订单状态 0暂停 1开启
        $switch = 1;

        //添加到订单
        $tablename = "SMSONLINE" . date('Ymd') ;
        $order_res = DB::connection('ourcms')->table('cms_order')
//        $order_res = DB::table('cms_order')
            ->select('id', 'order_tnum')
            ->where('order_name', '=', $tablename)
            ->where('state', '!=', '-1')
            ->where('state', '!=', '-2')
            ->first();
        if (!$order_res) {
            //新建
            //获取订单总表存储数据
            $info                   = array();
            $info['order_name']     = $tablename;
            $info['order_tnum']     = 1;
            $info['order_num']      = 0;
            $info['state']          = $switch;//1 open
            $info['type']           = 20;  //短信订单
            $info['addtime']        = time();
            $info['LateSendTime']   = $info['LateReturnTime'] = date("Y-m-d H:i:s");
            $info['spnumber']       = '';
            $info['note']           = "连网接收短信订单 ";
//            $id = DB::table('cms_order')->insertGetId($info);
            $id = DB::connection('ourcms')->table('cms_order')->insertGetId($info);
//            创建订单详细表
            //手机号,指令,发送手机号,发送时间,发送状态(012),用户project,software,返回时间
            $ordtb = "cms_orddata_" . $id;
            Schema::connection('ourcms')->create($ordtb, function (Blueprint $table) {
//            Schema::create($ordtb, function (Blueprint $table) {
                $table->charset = 'utf8';
                $table->engine = 'MyISAM';
                $table->increments('id');
                $table->string('phone', 20)->default('')->comment('订单电话号');
                $table->string('smstext', 100)->comment('指令回复内容');
                $table->string('addtime', 30)->default('')->comment('访问时间');
                $table->string('userphone', 20)->default('')->comment('访问手机号');
                $table->string('imsi', 50)->default('')->comment('访问imsi');
                $table->integer('state')->default(0)->comment('1发送0未发送');
                $table->string('provinceid', 3)->default('0')->comment('省份id');
                $table->string('software', 200)->default('')->comment('软件名');
                $table->string('cityid', 3)->default('0')->comment('城市id');
                $table->string('projectid', 4)->default('0')->comment('项目号');
                $table->tinyInteger('is_return')->default(0)->comment('0未返回1返回');
                $table->string('return_times', 3)->default('0')->comment('返回时长');
                $table->string('backtime', 30)->default('')->comment('返回时间');
                $table->tinyInteger('msg')->default(0)->comment('是否有短信返回');
                $table->string('msgdate', 30)->default('')->comment('短信接收时间');
                $table->string('spnumber', 30)->default('')->comment('通道号');
                $table->string('nowtime', 30)->default('')->comment('自动订单添加时间');
                $table->string('stype', 3)->default('')->comment('短信二次业务回复类型');
                $table->string('sctstart', 100)->default('')->comment('验证码关键前半部分');
                $table->string('sctend', 100)->default('')->comment('验证码关键后半部分');
                $table->string('snumstart', 100)->default('')->comment('回复到某号码的关键半部分');
                $table->string('maskkeyone', 100)->default('')->comment('关键字1');
                $table->string('maskkeytwo', 100)->default('')->comment('关键字2');
            });

            DB::connection('ourcms')->table($ordtb)->insert($new_data);
//            DB::table($ordtb)->insert($new_data);

            //记录日志
            $this->setLog($profile, $parame);
            echo json_encode(['code' => '200', 'msg' => 'success']);
            die;

        } else {
            //追加
            # 订单总表不是空 追加进去
            $info = array();
            $info['order_tnum'] = $order_res->order_tnum + 1; # 订单 总表 里的订单数据 自增 1
            $info['state'] = $switch;//1 open
            $info['addtime'] = time();
            //插入数据
            # 订单总表里的id 对应 外边订单详细表的表名
            $ordtb = "cms_orddata_" . $order_res->id;
            # 成功之后的订单不管了

            $res = DB::connection('ourcms')->table($ordtb)->insert($new_data);
//            $res = DB::table($ordtb)->insert($new_data);
            if ($res) {
                DB::connection('ourcms')->table('cms_order')
//                DB::table('cms_order')
                    ->where('id', '=', "$order_res->id")
                    ->update(['order_tnum' => $info['order_tnum'], 'state' => $info['state'], 'addtime' => $info['addtime']]);
            }
            //记录日志
            $this->setLog($profile, $parame);

            echo json_encode(['code' => '200', 'msg' => 'success']);
            die;

        }
    }



    /**记日志
     * @param $profile
     * @param array $parame
     */
    public function setLog($profile, $parame = [])
    {
        $dt = Carbon::now();
        $txt = $dt . '   ' . implode('--', $parame);
        $day = date('Ymd', time());
        Storage::disk('local')->append($day . '/' . $profile, $txt);
    }

    public function transcode($gjz)
    {
        $gjz = json_encode($gjz);
        $gjz = str_replace("\u", "%u", $gjz);
        $gjz = trim($gjz, '"');
        return $gjz;
    }

}