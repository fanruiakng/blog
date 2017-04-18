<?php
/**
* 数据库操作(数据库访问,认证等)
*
*@author 樊瑞康<1525297411@qq.com>
*@copyright 2017 樊瑞康版权所有
*/
class DB_Connect{

    /**
    *保存数据库对象
    *
    *@var object 数据库对象
    */
    protected $db;
    
    /**
    * 检查数据库对象, 若不存在则生成一个
    *
    *@param object $dbo数据库对象
    */
    protected function __construct($dbo=NULL)
    {
        if(is_object($dbo))
        {

            $this->db=$dbo;
        }
        else
        {
            // 在/sys/config/db-cred.inc.php中定义常量
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            try//???    
            {
                $this->db = new PDO($dsn,DB_USER,DB_PASS);
            }
            catch(Exception $e)
            {
                //如果数据库连接失败,输出错误
            die ($e->getMessage());
            }
        }
        
            
    }
}