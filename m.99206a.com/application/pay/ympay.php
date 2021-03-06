<?php
// +----------------------------------------------------------------------
// | FileName: zhfpay.php
// +----------------------------------------------------------------------
// | CreateDate: 2017年11月20日
// +----------------------------------------------------------------------
// | Author: xiaoluo
// +----------------------------------------------------------------------
//优米支付
namespace app\pay;
class ympay extends basepay{
    public function pay(){
        date_default_timezone_set('PRC');
        $arr = $this->params;
        $bank_types = ['ICBC','ABC','BOC','CCB','COMM','CMB','SPDB','CIB','CMBC','GDB','CNCB','CEB','HXB','PSBC','PAB','BOBJ','BONB'];
        $post = [
            'apiName'=>request()->isMobile()?'WAP_PAY_B2C':'WEB_PAY_B2C',
            //'apiName'=>'WAP_PAY_B2C',
            'apiVersion'=>'1.0.0.1',
            'platformID'=>$arr['pid'],
            'merchNo'=>$arr['pid'],//'108001002002',
            'orderNo'=>$arr['order_id'],
            'tradeDate'=>date('Ymd'),
            'amt'=>$arr['order_money'],
            'merchUrl'=>$arr['callbackurl'],
            'merchParam'=>iconv("GBK","UTF-8", 'abcd'),
            'tradeSummary'=>"虚拟商品",
            'customerIP'=>request()->ip(),
            'bankCode'=>in_array($arr['tcode'],$bank_types)?$arr['tcode']:'',
            'choosePayType'=>!in_array($arr['tcode'],$bank_types)?$arr['tcode']:'1',

            'pkey' => $arr['pkey']
        ];

        if($arr['tcode'] == 11 || $arr['tcode'] == 13){
            $post['bankCode'] = 'weixin';
        }

        $post['signMsg'] = $this->create_sign($post);

//        foreach ($post as $k=>$val){
//            echo "<input type='hidden' name='{$k}' value='$val'/>";
//        }die;

        
        return $this->form($post, $arr['purl']);
    }
    
    /**
     * 创建签名
     */
    public function create_sign($data=[]) {
      

      
      
        if(empty($data)){
            $data = $this->params;
        }
        if($data['apiName'] == 'MOBO_TRAN_QUERY') {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt']
            );
            return MD5($result.$data['pkey']);
        } else if ($data['apiName'] == 'AUTO_SETT_QUERY') {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&startDate=%s&endDate=%s&startIndex=%s&endIndex=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['startDate'], $data['endDate'], $data['startIndex'],$data['endIndex']
            );
            return MD5($result.$data['pkey']);
        } else if ((($data['apiName'] == 'WEB_PAY_B2C') ||($data['apiName'] == 'WAP_PAY_B2C'))&& ($data['apiVersion'] == '1.0.0.0')) {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s&merchUrl=%s&merchParam=%s&tradeSummary=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['merchUrl'], $data['merchParam'], $data['tradeSummary']
            );
            return MD5($result.$data['pkey']);
        } else if ($data['apiName'] == 'MOBO_USER_WEB_PAY') {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&userNo=%s&accNo=%s&orderNo=%s&tradeDate=%s&amt=%s&merchUrl=%s&merchParam=%s&tradeSummary=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['userNo'], $data['accNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['merchUrl'], $data['merchParam'], $data['tradeSummary']
            );
            return MD5($result.$data['pkey']);
        } else if ($data['apiName'] == 'MOBO_TRAN_RETURN') {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s&tradeSummary=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['tradeSummary']
            );
            return MD5($result.$data['pkey']);
        } else if ($data['apiName'] == 'PAY_RESULT_NOTIFY') {
            $result = sprintf(
                "apiName=%s&notifyTime=%s&tradeAmt=%s&merchNo=%s&merchParam=%s&orderNo=%s&tradeDate=%s&accNo=%s&accDate=%s&orderStatus=%s",
                $data['apiName'], $data['notifyTime'], $data['tradeAmt'], $data['merchNo'], $data['merchParam'], $data['orderNo'], $data['tradeDate'], $data['accNo'], $data['accDate'], $data['orderStatus']
            );
            return MD5($result.$data['pkey']);
        }else if ((($data['apiName'] == 'WEB_PAY_B2C') ||($data['apiName'] == 'WAP_PAY_B2C')) && ($data['apiVersion'] == '1.0.0.1')) {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&amt=%s&merchUrl=%s&merchParam=%s&tradeSummary=%s&customerIP=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['amt'], $data['merchUrl'], $data['merchParam'], $data['tradeSummary'],$data['customerIP']
            );
            return MD5($result.$data['pkey']);
        }else if ($data['apiName'] == 'SINGLE_ENTRUST_SETT') {
            $result = sprintf(
                "apiName=%s&apiVersion=%s&platformID=%s&merchNo=%s&orderNo=%s&tradeDate=%s&merchUrl=%s&merchParam=%s&bankAccNo=%s&bankAccName=%s&bankCode=%s&bankName=%s&Amt=%s&tradeSummary=%s",
                $data['apiName'], $data['apiVersion'], $data['platformID'], $data['merchNo'], $data['orderNo'], $data['tradeDate'], $data['merchUrl'], $data['merchParam'], $data['bankAccNo'], $data['bankAccName'],$data['bankCode'], $data['bankName'],$data['Amt'], $data['tradeSummary']
            );
            return MD5($result.$data['pkey']);
        }

        $array = array();
        foreach ($data as $key=>$value) {
            if($key != 'pkey'){
                array_push($array, $key.'='.$value);
            }
        }
        $signStr = implode($array, '&');
        return MD5($signStr.$data['pkey']);
    }
    
    public function check_sign(){
$form = '<form action="http://99206a.com/pay/notify/thirdtype/ympay" >';
foreach($_REQUEST as $k => $v){
    $form .= "<input type='text' name='$k' value='$v' />";
}
$form .="<input type='submit'><form/></br>";
file_put_contents(dirname(__FILE__).'/ymlog.html', $form."\r\n",FILE_APPEND);
      
      
        $_REQUEST['pkey'] = $this->params['pkey'];
        $this->params($_REQUEST);
        $sign = trim(input('signMsg'));

        return  strcasecmp($this->create_sign(), $sign) == 0;
    }
    
    //SUCCESS 交易成功,FAILED 交易失败
    public function pay_ok(){
        return "1" == input('orderStatus');
    }
    
    public function transid(){
        return input('orderNo');//系统订单号
    }
    
    public function orderid(){
        return input('orderNo');
    }
    
    public function success() {
        echo "SUCCESS";
    }
}

