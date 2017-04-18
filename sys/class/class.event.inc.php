<?php
/**
 * 保存活动信息
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.2.25
 * Time: 下午 1:45
 */
class Event
{
    /**
     * 活动ID
     * @var int
     */

    public $id;
    /**
     * 活动标题
     * @var string
     */
    public $title;

    /**
     *活动描述
     * @var string
     */
    public $description;

    /**
     * 活动起始时间
     * @var string
     */
    public $start;

    /**
     * 活动结束时间
     * @var string
     */
    public $end;


    /**
     * 接受一个活动的数据并储存该活动
     *
     * @param array $event 保存活动的关联数据
     * @return void
     */
    public function __construct($event)
    {
        if(is_array($event))
        {
            $this->id=$event['event_id'];
            $this->title=$event['event_title'];
            $this->description=$event['event_desc'];
            $this->start=$event['event_start'];
            $this->end=$event['event_end'];
        }
        else
        {
            throw new Exception("没有事件 ><");
        }
    }

}