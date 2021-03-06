<?php
namespace app\common\model;
use think\Model;

class LhcTime extends Base{

    protected $table = 'gygy_lhc_time';
    //protected $createTime = 'created_at';
    //protected $updateTime = 'updated_at';
    //protected $autoWriteTimestamp = 'datetime';

    public function getActionNoAttr($value)
    {
        return $this->data['qishu'];
    }

    public function setActionNoAttr($value,$data)
    {
        return $this->data['qishu'] = $value;
    }    

    public function getActionTimeAttr($value)
    {
        return $this->data['kaipan'];
    }

    public function setActionTimeAttr($value,$data)
    {
        return $this->data['kaipan'] = $value;
    }   
    
    public function TypeName(){

        return $this->belongsTo('Type','code','name');
    }

    //----------------后台------------------

    public static function getList($params){
        $query = self::order('id');
        if($params['type']??null){
            $query->where('type',$params['type']);
        }
        $data = $query->paginate(15);
        return $data;
    }

}
