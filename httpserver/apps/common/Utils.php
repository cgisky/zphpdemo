<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 13-12-6
 * Time: 下午3:26
 */

namespace common;
use ZPHP\Core\Config as ZConfig,
    ZPHP\Cache\Factory as ZCache,
    ZPHP\Conn\Factory as ZConn;


class Utils
{

    public static function checkToken($uid, $token)
    {
        if(empty($uid) || empty($token)) {
            return false;
        }
        $config = ZConfig::getField('cache', 'net');
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = "{$uid}_t_" . ZConfig::getField('project', 'name');
        $realToken = $cacheHelper->get($key);
        return $realToken === $token;
    }

    public static function setToken($uid)
    {
        $token = uniqid();
        $config = ZConfig::getField('cache', 'net');
        $cacheHelper = ZCache::getInstance($config['adapter'], $config);
        $key = "{$uid}_t_" . ZConfig::getField('project', 'name');
        if ($cacheHelper->set($key, $token)) {
            return $token;
        }
        throw new \Exception("token set error", ERROR::TOKEN_ERROR);
    }

    public static function jump($action, $method, $params)
    {
        $url = self::makeUrl($action, $method, $params);
        return array(
            '_view_mode'=>'Php',
            '_tpl_file'=>'jump.php',
            'url'=>$url,
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public static function showMsg($msg)
    {
        return array(
            '_view_mode'=>'Php',
            '_tpl_file'=>'error.php',
            'msg'=>$msg,
            'static_url'=>ZConfig::getField('project', 'static_url'),
        );
    }

    public static function makeUrl($action, $method, $params=array())
    {
        $appUrl = ZConfig::getField('project', 'app_host', "");
        $actionName = ZConfig::getField('project', 'action_name', 'a');
        $methodName = ZConfig::getField('project', 'method_name', 'm');
        if(empty($appUrl)) {
            $appUrl = '/';
        } else {
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
                $appUrl = 'https://'.$appUrl;
            } else {
                $appUrl = 'http://'.$appUrl;
            }
        }
        return $appUrl."?{$actionName}={$action}&{$methodName}={$method}&".http_build_query($params);
    }

    public static function online($channel='ALL')
    {
        $config = ZConfig::get('connection');
        $connection = ZConn::getInstance($config['adapter'], $config);
        return $connection->getChannel($channel);
    }
} 