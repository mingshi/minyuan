<?php
class MY_Form_validation extends CI_Form_validation
{
    public function __construct($rules = array())
    {
        parent::__construct($rules);
    }
    
    function valid_captcha($str)
    {
        if (!validate_captcha($_POST['captcha'])) {
            $this->set_message('valid_captcha', '验证码不正确'); 

            return FALSE;
        }

        return TRUE;
    }

    /**
     * 验证中文
     * @param string $str
     */
    function valid_chinese($str)
    {
        if (empty($str)) {
            return TRUE;
        }

        if (!preg_match('@^[\x{4e00}-\x{9fa5}]+$@u', $str)) {
            $this->set_message('chinese', '%s必须为中文');
            
            return FALSE;
        }
        
        return TRUE;
    }
    
    function valid_mobile($str)
    {
        if (empty($str)) {
            return TRUE;
        }

        if (!preg_match('@^1\d{10}$@', $str)) {
            $this->set_message('valid_mobile', '%s不是合法的手机号');

            return FALSE;
        }

        return TRUE;
    }
       
    function valid_url($str){

        if (empty($str)) {
            return TRUE;
        }

        $pattern = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
         if (!preg_match($pattern, $str))
         {
            $this->set_message('valid_url', '%s不是合法的URL');

             return FALSE;
         }

         return TRUE;
    }
    
    function valid_json($str)
    {
        if (empty($str)) {

            return TRUE;
        }

        $json = @json_decode($str); 

        if (is_null($json)) {
            $this->set_message('valid_json', '%s不是一个合法的JSON数据');

            return FALSE;
        }

        return TRUE;
    }

    //检查必须是时间字符串,如果为空也返回正确,是否为空用require来判断
    function valid_timestr($str)
    {
        if (empty($str)) {
            return TRUE;
        }
        
        $ret = strtotime($str);
        if ($ret < 0 || $ret === FALSE) {
            $this->set_message('valid_timestr', '%s不是有效的时间');

            return FALSE;
        }

        return TRUE;
    }
       
    function valid_roles($str)
    {
        if (empty($str)) {

            return TRUE;
        }

        if (!$this->permission->isValidRoles($str)) {
            $this->set_message('valid_roles', '%s不是有效的角色'); 

            return FALSE;
        }

        return TRUE;
    }
    
    function safe_password($str)
    {
        if (empty($str)) {

            return TRUE;
        }

        if (strlen($str) < 8) {
            $this->set_message('safe_password', '%s长度最少为8位');

            return FALSE;
        }
        
        $level = 0;

        if (preg_match('@\d@', $str)) $level++;
        if (preg_match('@[a-z]@', $str)) $level++;
        if (preg_match('@[A-Z]@', $str)) $level++;
        if (preg_match('@[^0-9a-zA-Z]@', $str)) $level++;

        if ($level < 3) {
            $this->set_message('safe_password',
                '您设置的%s太简单，密码必须包含数字、大小写字母、其它符号中的三种及以上'
            );

            return FALSE;
        }

        return TRUE;
    }

    function exists_row($str, $table)
    {
        if (empty($str)) {

            return TRUE;
        }

        $tokens = explode('.', $table);

        $key = array_pop($tokens);
        $table = array_pop($tokens);
        $dbClusterId = NULL;

        if ($tokens) {
            $dbClusterId = $tokens[0];
        }

        $vals = explode(',', $str);
        $valCount = count($vals);

        $count = M($table, $dbClusterId)->selectCount(array(
            $key => $vals
        ));

        if ($count != $valCount) {
            $this->set_message('exists_row', '%s包含不存在的记录');

            return FALSE;
        }

        return TRUE;
    }

    function valid_ips($str)
    {
        if (empty($str)) {
            return TRUE;
        }

        $ips = explode(',', $str);

        foreach ($ips as $ip) {
            if ($this->valid_ip($ip) === FALSE) {
                $this->set_message('valid_ips', '%s包含无效的IP');

                return FALSE;
            }
        }

        return TRUE;
    }

    function unique_row($str, $table)
    {
        if (empty($str)) {
            
            return TRUE;
        }
        
        $tokens = explode('.', $table);
        
        $key = array_pop($tokens);
        $table = array_pop($tokens);
        $dbClusterId = NULL;

        if ($tokens) {
            $dbClusterId = $tokens[0];
        }

        param_request(array(
            'id' => 'UINT'
        ));
        
        $where = array(
            $key => $str, 
        );

        if (!empty($GLOBALS['req_id'])) {
            $where['id'] = array(
                '!=' => $GLOBALS['req_id']
            );
        }

        $count = M($table, $dbClusterId)->selectCount($where);

        if ($count > 0) {
            $this->set_message('unique_row', '%s已经存在');

            return FALSE;
        }

        return TRUE;
    }

    function max_width($str, $val) {
        if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}
		
		$res = (mb_strwidth($str) > $val) ? FALSE : TRUE;

        if (!$res) {
            $this->set_message('max_width', "%s最大长度为{$val}");
        }

        return $res;
    }
    
    function gt($str, $n)
    {
        if (!$this->numeric($str)) {
            return FALSE;
        }

        if ($str <= $n) {
            $this->set_message('gt', '%s必须大于%s');

            return FALSE;
        }

        return TRUE;
    }

    function ge($str, $n)
    {
        if (!$this->numeric($str)) {
            return FALSE;
        }

        if ($str < $n) {
            $this->set_message('ge', '%s必须大于等于%s');

            return FALSE;
        }

        return TRUE;
    }

    function lt($str, $n)
    {
        if (!$this->numeric($str)) {
            return FALSE;
        }

        if ($str >= $n) {
            $this->set_message('lt', '%s必须小于%s');

            return FALSE;
        }

        return TRUE;
    }

    function le($str, $n)
    {
        if (!$this->numeric($str)) {
            return FALSE;
        }

        if ($str > $n) {
            $this->set_message('le', '%s必须小于等于%s');

            return FALSE;
        }

        return TRUE;
    }

    
	function reset_rules($group, $newRules)
    {
        $rules = & $this->_config_rules[$group];
        if ( ! $rules) {
            return FALSE;
        }
        if (is_real_array($rules)) {
            foreach ($rules as &$item) {
                if (isset($newRules[$item['field']])) {
                    $item['rule'] = $newRules[$item['field']];
                }
            }
        } else {
            foreach ($rules as $key => &$rule) {
                $field = array_shift(explode('|', $key));
                if (isset($newRules[$field])) {
                    $rule = $newRules[$field];
                }
            }
        }
        return TRUE;
    }
	
    function set_rules($field, $label = '', $rules = '')
    {
        if (is_array($field) && is_hashmap($field)) {
            $rules = array();
            foreach ($field as $name => $rule) {
                $names = explode('|', $name, 2);
                $rules[] = array(
                    'field' => $names[0],
                    'label' => isset($names[1]) ? $names[1] : $names[0],
                    'rules' => $rule,
                );
            }
            $field = $rules;
        }
        parent::set_rules($field, $label, $rules);
    }
	
	// --------------------------------------------------------------------

	/**
	 * Executes the Validation routines
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */
	function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}

			return;
		}

		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND (is_null($postdata) || $postdata === ''))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// --------------------------------------------------------------------

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';

				if ( ! isset($this->_error_messages[$type]))
				{
					if (FALSE === ($line = $this->CI->lang->line($type)))
					{
						$line = 'The field was not set';
					}
				}
				else
				{
					$line = $this->_error_messages[$type];
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
			}

			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{
					continue;
				}

				// Run the function and grab the result
				$result = $this->CI->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}

					continue;
				}

				$result = $this->$rule($postdata, $param);

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line($rule)))
					{
						$line = 'Unable to access an error message corresponding to your field name.';
					}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}

				return;
			}
		}
	}
}
