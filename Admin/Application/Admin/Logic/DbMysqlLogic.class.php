<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/28
 * Time: 12:59
 */

namespace Admin\Logic;


class DbMysqlLogic implements DbMysql
{
    /**
     * DB connect
     *
     * @access public
     *
     * @return resource connection link
     */
    public function connect()
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Disconnect from DB
     *
     * @access public
     *
     * @return viod
     */
    public function disconnect()
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Free result
     *
     * @access public
     * @param resource $result query resourse
     *
     * @return viod
     */
    public function free($result)
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Execute simple query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return resource|bool query result
     */
    public function query($sql, array $args = array())
    {
        //如果是查询,则输出信息
        if(strpos('SELECT',$sql)!==false){
            echo __METHOD__;
            dump(func_get_args());
            echo '<hr/>';
        }
        //获取参数
        $args=func_get_args();
        //取得sql语句
        $sql=array_shift($args);
        //用正则将字符串转成数组
        $matchs=preg_split('/\?[NFT]/',$sql);
        //弹出最后一个无用的数据
        array_pop($matchs);
        $sql='';
        foreach($matchs as $k=>$v){
            $sql.=$v.$args[$k];
        }
        //执行一个写操作
        return M()->execute($sql);
    }

    /**
     * Insert query method
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false last insert id
     */
    public function insert($sql, array $args = array())
    {
        //获取参数
        $args=func_get_args();
        //取得sql语句
        $sql=$args[0];
        $tabName=$args[1];
        $data=$args[2];
        //将'?T'替换
        $sql=str_replace('?T',$tabName,$sql);
        $tmp=[];
//        这里的data是一个二维数组 键值对的形式,遍历出来后,拼接成sql语句的格式,
        foreach($data as $k=>$v){
            $tmp[]=$k.'="'.$v.'"';
        }
        //将数组分割成字符串
        $tj=implode($tmp,',');
        //将"?%"替换
        $sql=str_replace('?%',$tj,$sql);
        //执行sql语句

        if(M()->execute($sql)!==false){
            //  返回执行成功的id
        return M()->getLastInsID();
        }else{
            return false;
        }

    }

    /**
     * Update query method
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false affected rows
     */
    public function update($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Get all query result rows as associated array
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAll($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Get all query result rows as associated array with first field as row key
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAssoc($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Get only first row from query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array
     */
    public function getRow($sql, array $args = array())
    {
        //获取所有实参
        $args=func_get_args();
        //从实参里面获取sql语句,第一个就是sql语句,用array_shift弹出第一个
        $sql=array_shift($args);
        //将sql语句分割成数组的形式,循环去拼接
        $matchs=preg_split('/\?[NFT]/',$sql);
        //最后一个数据没用,将它弹出
        array_pop($matchs);
        $sql='';
        foreach($matchs as $key=>$val){
            $sql.=$val.$args[$key];
        }
        $rows = M()->query($sql);
        //这里是getrow,所以返回第一个
        return array_shift($rows);
    }

    /**
     * Get first column of query result
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array one level data array
     */
    public function getCol($sql, array $args = array())
    {
        echo __METHOD__;
        dump(func_get_args());
        echo '<hr/>';
    }

    /**
     * Get one first field value from query result
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return string field value
     */
    public function getOne($sql, array $args = array())
    {
        //获取所有实参
        $args=func_get_args();
        //从实参里面获取sql语句,第一个就是sql语句,用array_shift弹出第一个
        $sql=array_shift($args);
        //将sql语句分割成数组的形式,循环去拼接
        $matchs=preg_split('/\?[NFT]/',$sql);
        //最后一个数据没用,将它弹出
        array_pop($matchs);
        $sql='';
        foreach($matchs as $key=>$val){
            $sql.=$val.$args[$key];
        }
        $rows = M()->query($sql);
        $row=array_shift($rows);
        //这里是getrow,所以返回第一个
        return array_shift($row);
    }
}
