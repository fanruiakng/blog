<?php
/**
 *
 * 管理行为
 * Created by PhpStorm.
 * User: 帅康
 * Date: 2017.3.2
 * Time: 上午 6:35
 */

class Admin extends DB_Connect
{
    /**
     * 确定用于散列密码中的盐的长度
     * @var int 用于密码盐的字符串长度
     */
    private $_saltLength=7;

    /**
     * 保存或生成一个DB对象,设定盐的长度
     * @param object $db 数据库对象
     * @param int $saltLength 密码盐的长度
     */
    public function  __construct($db=NULL,$saltLength=NULL)
    {
        parent::__construct($db);
        //若传入活动ID,则用它来设定saltlength的值
        if(is_int($saltLength))
        {
            $this->_saltLength=$saltLength;
        }
    }
    /**
     * 检查用户登录信息
     *
     * @return 成功返回TRUE,失败返回错误信息
     */
    public function processLoginForm()
    {
        /**
         * 若未提交正确action,返回错误信息
         */
        if($_POST['action']!='user_login')
        {
            return "非法行为";
        }

        /**
         * 安全起见转移用户输入的数据
         */
        $uname="fan";
        $pword=$_POST['pass'];
        /**
         *若用户存在则返回数据库信息
         */
        $sql = "SELECT * FROM users WHERE user_name=:name LIMIT 1";

        try
        {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":name",$uname,PDO::PARAM_STR);
            $stmt->execute();
            $user=$stmt->fetchAll()[0];
            $stmt->closeCursor();
        }
        catch (Exception $e)
        {
            die($e->getMessage());
        }

        // 若用户名不存在则返回出错信息
        if(!isset($user))
        {
        return "no user";
        }

        //根据用户输入的密码进行散列
        $hash = $this->_getSaltedHash($pword,$user['user_pass']);

        //核对密码
        if($user['user_pass']==$hash)
        {
            /**
             * 将用户信息存入session
             */
            $_SESSION['user']=array(
                'id'=>$user['user_id'],
                'name'=>$user['user_name'],
                'email'=>$user['user_email']
            );
            return TRUE;
        }


        //密码错误信息
        else
        {
            return "密码错误";
        }
    }

    /**
     * 用户登出
     * @return 成功返回TRUE,失败返回出错信息
     */
    public function processLogout()
    {
        //若没有action,返回出错信息
        if($_POST['action']!='user_logout')
        {
            return "非法登出";
        }
        //删除用户信息
        session_destroy();
        return TRUE;
    }

    /**
     * 为给定的字符串生成一个家盐的散列值
     *
     * @param string $string 即将被散列的字符串
     * @param string $salt 从这个串中提取盐
     * @return string 加盐后的散列值
     */
    private function _getSaltedHash($string,$salt=NULL)
    {
        //没有盐则生成一个
        if($salt==NULL)
        {
            $salt = substr(md5(time()),0 ,$this->_saltLength);
        }

        //传入则提取
        else
        {
            $salt = substr($salt,0, $this->_saltLength);
        }
        //将盐加到散列值之前
        return $salt.sha1($salt.$string);
    }

    public function  t($string,$salt=NULL)
{
    return $this->_getSaltedHash($string,$salt);
}
}
