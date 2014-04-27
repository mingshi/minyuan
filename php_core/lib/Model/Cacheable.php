<?php
abstract class Model_Cacheable extends Db_Model
{
	public $DISABLE_CACHE_TRIGGER = FALSE;
	
    protected static $CACHE = array();
    
    protected $CACHE_TIME = 7200;
    protected $MEMCACHE_CLUSTER_ID = 'default';
    protected $COLUMN_ID = 'id';
    protected $ITEM_NAME = '';
	
    function __construct($table = NULL, $clusterId = NULL, $objectId = NULL)
    {
        parent::__construct($table, $clusterId, $objectId);
		//$class = get_class($this);
		//$name = strtolower(preg_replace('@^Model_@', '', $class));
        $name = strtolower($table);
		$this->ITEM_NAME = $clusterId . '.' . $name;
    }
	
	public function getItemsByConds($conds, $cacheTime = 0)
	{
		return $this->select($conds, array(), $cacheTime);
	}
	
	public function select($where = array(), $attrs = array(), $cacheTime = 0)
	{
		$useCache = $cacheTime > 0;
		$cacheKey = $this->getQueryCacheKey($where, $attrs);
		if ($useCache) {
			$items = Cache_Memcache::sGet($cacheKey, $this->MEMCACHE_CLUSTER_ID);
			if ($items) {
				return $items;
			}
		}
		
		$items = parent::select($where, $attrs);	
		
		if ($useCache && $items) {
			Cache_Memcache::sSet(
				$cacheKey, $items, $cacheTime,
				$this->MEMCACHE_CLUSTER_ID
			);
		}
		
		return $items;
	}
	
	private function getQueryCacheKey($where, $attrs = array())
	{
		ksort($where);
		$key = 'SQL_'.$this->_table.'_'
			 . $this->_sqlHelper->where($where, $attrs);
		return md5($key);
	}
	
	protected static function _get($class, $ids, $useCache = TRUE)
	{
		$instance = Factory::getInstance($class);
		return $instance->getItems($ids, $useCache);
	}
	
	//数据更新触发器
	protected function afterUpdate($conds, $data)
	{
		if ($this->DISABLE_CACHE_TRIGGER) {
            parent::afterUpdate($conds, $data);
			return;
		}
		
		if (isset($conds[$this->COLUMN_ID])) {
			$this->clearItemCache($conds[$this->COLUMN_ID]);
		} else {
		    $this->clearItemByConds($conds);
		}

        parent::afterUpdate($conds, $data);
	}
	
	protected function afterInsertReplace($ins, $replace)
	{
		if ($this->DISABLE_CACHE_TRIGGER) {
            parent::afterInsertReplace($ins, $replace);
			return;
		}
		
		if (isset($ins[$this->COLUMN_ID])) {
			$this->clearItemCache($ins[$this->COLUMN_ID]);
		} else {
		    $this->clearItemByConds($ins);
		}

        parent::afterInsertReplace($ins, $replace);
	}
	
	//数据删除触发器
	protected function afterDelete($conds)
	{
		if (isset($conds[$this->COLUMN_ID])) {
			$this->clearItemCache($conds[$this->COLUMN_ID]);
		} else {
		    $this->clearItemByConds($conds);
		}

        parent::afterDelete($conds);
	}
	
	private function clearItemByConds($conds) {
	    $attrs = array(
	        'select' => $this->COLUMN_ID
	    );
	    
	    $results = $this->select($conds, $attrs);
	    if (!is_array($results)) {
	        //XXX 错误处理
	        return;
	    }
	    
	    $ids = array_get_column($results, $this->COLUMN_ID);
	    if (!empty($ids)) {
	        $this->clearItemCache($ids);
	    }
	}
	
    public function getItems($ids, $useCache = TRUE)
    {
        $ret = array();
        if (empty($ids)) {
            return $ret;
        }
        
        $returnArray = TRUE;
        
        if (!is_array($ids)) {
            $ids = array($ids);
            $returnArray = FALSE;
        }
        
        $ids = array_unique($ids);
        
        if ($useCache) {
            $idsUncached = NULL;
            $itemsCached = $this->getItemsCache($ids, $idsUncached);
        } else {
            $idsUncached = $ids;
        }
        
        $itemsUncached = NULL;
        
        if (!empty($idsUncached)) {
            $conds = array($this->COLUMN_ID => $idsUncached);
            $otherConds = array();
            $itemsUncached = $this->select($conds, 0, 0, NULL, $otherConds);
            if ($itemsUncached) {
                log_message(
                    $this->ITEM_NAME.":Get items(".count($itemsUncached).") from db",
                    LOG_DEBUG
                );
                array_change_key($itemsUncached, $this->COLUMN_ID);
	            if ($useCache) {
	                $this->setItemsCache($itemsUncached);
	            }
            }
        }
        
        if ($useCache) {
            $ret = $itemsCached + ($itemsUncached ? $itemsUncached : array());
        } else {
            $ret = $itemsUncached;
        }
        
        if (!$returnArray) {
            $ret = isset($ret[$ids[0]]) ? $ret[$ids[0]] : NULL;
        }
        
        return $ret;
    }
    
    private function getItemsCache($ids, &$idsUncached = NULL)
    {
        $itemsInPageCache = array();
		
        if (!empty(self::$CACHE[$this->ITEM_NAME])) {
            $idsUncached = array();
            foreach ($ids as $id) {
				$cackeKey = $this->getItemCacheKey($id);
                if (!empty(self::$CACHE[$this->ITEM_NAME][$cackeKey])) {
                    $itemsInPageCache[$id] = self::$CACHE[$this->ITEM_NAME][$cackeKey];
                } else {
                    $idsUncached[] = $id;
                }
            }
			if ($itemsInPageCache) {
				log_message($this->ITEM_NAME.':get items('.count($itemsInPageCache).') from page cache.', LOG_DEBUG);
			}
            if (empty($idsUncached)) {
                return $itemsInPageCache;
            }
            $ids = $idsUncached;
        }
		
        $keys = $this->getItemCacheKey($ids);
        $items = Cache_Memcache::sGet($keys, $this->MEMCACHE_CLUSTER_ID);
        if (empty($items) && !is_array($items)) {
            $items = array();
        }
        $idsUncached = array();
        foreach ($keys as $key) {
            if (empty($items[$key])) {
                $idsUncached[] = $this->getIdFromItemCacheKey($key);
            } else {
                self::$CACHE[$this->ITEM_NAME][$key] = $items[$key];
			}
        }
        
        array_change_key($items, $this->COLUMN_ID);
        
        if ($itemsInPageCache) {
            $items = $items + $itemsInPageCache;
        }
        log_message(
            $this->ITEM_NAME.":Get items(".count($items).") from cache",
            LOG_DEBUG
        );
        return $items;    
    }
    
    private function setItemsCache($items)
    {
        foreach ($items as $id => $item) {
            $cacheKey = $this->getItemCacheKey($id);
            self::$CACHE[$this->ITEM_NAME][$cacheKey] = $item;
            Cache_Memcache::sSet(
                $cacheKey, $item,
                $this->CACHE_TIME,
                $this->MEMCACHE_CLUSTER_ID
            );
        }
    }
    
    public function clearItemCache($ids)
    {
        if (! is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ($ids as $id) {
            $cacheKey = $this->getItemCacheKey($id);
            if (isset(self::$CACHE[$this->ITEM_NAME][$cacheKey])) {
                unset(self::$CACHE[$this->ITEM_NAME][$cacheKey]);
            }
            Cache_Memcache::sDelete($cacheKey, $this->MEMCACHE_CLUSTER_ID);
			trigger_event('on_clear_'.$this->ITEM_NAME.'_cache', array($id));
        }
    }
    
    private function getItemCacheKey($id)
    {
        if (is_array($id)) {
            $ret = array();
            foreach ($id as $val) {
                $ret[] = $this->getItemCacheKey($val);
            }
            return $ret;
        }
		if (empty($this->ITEM_NAME)) {
			throw new Exception($this->ITEM_NAME.":The cache item name is empty.");
		}
        return $this->ITEM_NAME.'_'.$id;
    }
    
    private function getIdFromItemCacheKey($key)
    {
		$len = strlen($this->ITEM_NAME);
        return intval(substr($key, $len + 1));
    }
}
